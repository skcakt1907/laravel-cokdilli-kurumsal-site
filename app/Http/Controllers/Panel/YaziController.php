<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Ceviri;
use App\Models\Yazi;
use App\Support\GorselYukle;
use App\Support\Metin;
use App\Support\VideoYukle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Haberler / blog yazıları. */
class YaziController extends Controller
{
    public function index(Request $istek): View
    {
        $sorgu = Yazi::query()->orderByDesc('tarih');

        if ($arama = trim((string) $istek->query('ara'))) {
            $sorgu->where(fn ($q) => $q
                ->where('baslik', 'like', "%{$arama}%")
                ->orWhere('kategori', 'like', "%{$arama}%"));
        }

        return view('panel.yazi.index', [
            'yazilar' => $sorgu->paginate(20)->withQueryString(),
        ]);
    }

    public function olusturForm(): View
    {
        $yazi = new Yazi();
        $yazi->tarih = now();      // varsayılan bugün

        return view('panel.yazi.form', ['yazi' => $yazi]);
    }

    public function kaydet(Request $istek): RedirectResponse
    {
        $veri = $this->dogrula($istek);

        $yazi = new Yazi();
        $yazi->fill($veri);
        $yazi->slug   = $this->slugUret($veri['baslik']);
        $yazi->gorsel = GorselYukle::calistir($istek->file('gorsel'), 'haberler');
        $yazi->video  = VideoYukle::calistir($istek->file('video_dosya'), $istek->input('video_link'));
        $yazi->save();

        // Çeviriler ayrı tabloda; kayıt id'si oluştuktan SONRA yazılır.
        $yazi->cevirileriKaydet($istek->input('ceviri', []));

        return redirect()->route('panel.yazi.index')
            ->with('basarili', "\"{$yazi->baslik}\" eklendi.");
    }

    public function duzenleForm(Yazi $yazi): View
    {
        return view('panel.yazi.form', ['yazi' => $yazi]);
    }

    public function guncelle(Request $istek, Yazi $yazi): RedirectResponse
    {
        $veri = $this->dogrula($istek, $yazi);

        $yazi->fill($veri);

        if ($istek->boolean('slug_yenile')) {
            $yazi->slug = $this->slugUret($veri['baslik'], $yazi->id);
        }

        if ($istek->boolean('gorsel_sil')) {
            GorselYukle::sil($yazi->gorsel);
            $yazi->gorsel = null;
        } else {
            $yazi->gorsel = GorselYukle::calistir($istek->file('gorsel'), 'haberler', $yazi->gorsel);
        }

        // Yeni video geldiğinde eskisi diskten silinir (VideoYukle içinde).
        $yazi->video = VideoYukle::calistir(
            $istek->file('video_dosya'),
            $istek->input('video_link'),
            $yazi->video,
            $istek->boolean('video_sil')
        );

        $yazi->save();
        $yazi->cevirileriKaydet($istek->input('ceviri', []));

        return redirect()->route('panel.yazi.index')
            ->with('basarili', "\"{$yazi->baslik}\" güncellendi.");
    }

    public function durumDegistir(Yazi $yazi): RedirectResponse
    {
        $yazi->update(['durum' => !$yazi->durum]);

        return back()->with('basarili', $yazi->durum
            ? "\"{$yazi->baslik}\" yayına alındı."
            : "\"{$yazi->baslik}\" yayından kaldırıldı.");
    }

    public function sil(Yazi $yazi): RedirectResponse
    {
        GorselYukle::sil($yazi->gorsel);
        VideoYukle::sil($yazi->video);
        Ceviri::kayitSil($yazi->getTable(), (int) $yazi->id);
        $baslik = $yazi->baslik;
        $yazi->delete();

        return redirect()->route('panel.yazi.index')
            ->with('basarili', "\"{$baslik}\" silindi.");
    }

    // ---- Yardımcılar ---------------------------------------------------

    private function dogrula(Request $istek, ?Yazi $yazi = null): array
    {
        return $istek->validate([
            'baslik'      => ['required', 'string', 'max:150'],
            'ceviri'     => ['nullable', 'array'],
            'ceviri.*.*' => ['nullable', 'string', 'max:40000'],
            'baslik_en'   => ['nullable', 'string', 'max:150'],
            'kategori'    => ['nullable', 'string', 'max:80'],
            'kategori_en' => ['nullable', 'string', 'max:80'],
            'ozet'        => ['nullable', 'string', 'max:1000'],
            'ozet_en'     => ['nullable', 'string', 'max:1000'],
            'icerik'      => ['nullable', 'string', 'max:40000'],
            'icerik_en'   => ['nullable', 'string', 'max:40000'],
            'tarih'       => ['required', 'date'],
            'durum'       => ['nullable', 'boolean'],
            'gorsel'      => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:4096'],

            // Video: ya bağlantı ya dosya. max: KB cinsinden, sunucunun
            // kendi sınırından büyük olamayacağı için oradan türetiliyor.
            'video_link'   => ['nullable', 'url', 'max:255'],
            'video_dosya'  => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime',
                               'max:' . (VideoYukle::sinirMb() * 1024)],
        ], [
            'video_dosya.mimetypes' => 'Video dosyası MP4, WebM, OGG veya MOV olmalı.',
            'video_dosya.max'       => 'Video en fazla ' . VideoYukle::sinirMb() . ' MB olabilir. '
                                     . 'Daha büyükse YouTube/Vimeo bağlantısı kullanın.',
            'video_link.url'        => 'Video bağlantısı geçerli bir adres olmalı (https:// ile başlamalı).',
        ], [
            'baslik'      => 'başlık',
            'tarih'       => 'yayın tarihi',
            'gorsel'      => 'kapak görseli',
            'video_link'  => 'video bağlantısı',
            'video_dosya' => 'video dosyası',
        ]);
    }

    private function slugUret(string $baslik, ?int $haricId = null): string
    {
        $temel = Metin::slug($baslik) ?: 'haber';
        $slug  = $temel;
        $sayac = 2;

        while (Yazi::where('slug', $slug)->when($haricId, fn ($q) => $q->whereKeyNot($haricId))->exists()) {
            $slug = $temel . '-' . $sayac++;
        }

        return $slug;
    }
}
