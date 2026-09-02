<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Ceviri;
use App\Models\Hizmet;
use App\Models\Proje;
use App\Support\GorselYukle;
use App\Support\Metin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Grup şirketleri ve iş ortakları. */
class ProjeController extends Controller
{
    private const TURLER = ['grup' => 'Grup Şirketi', 'ortak' => 'İş Ortağı'];

    public function index(Request $istek): View
    {
        $sorgu = Proje::query()->orderBy('sira');

        if ($arama = trim((string) $istek->query('ara'))) {
            $sorgu->where(fn ($q) => $q
                ->where('baslik', 'like', "%{$arama}%")
                ->orWhere('kategori', 'like', "%{$arama}%")
                ->orWhere('ulke', 'like', "%{$arama}%"));
        }

        return view('panel.proje.index', [
            'kayitlar' => $sorgu->get()->groupBy('tur'),
            'turler'   => self::TURLER,
        ]);
    }

    public function olusturForm(): View
    {
        return view('panel.proje.form', $this->formVerisi(new Proje()));
    }

    public function kaydet(Request $istek): RedirectResponse
    {
        $veri = $this->dogrula($istek);

        $proje = new Proje();
        $proje->fill($veri);
        $proje->slug   = $this->slugUret($veri['baslik']);
        $proje->gorsel = GorselYukle::calistir($istek->file('gorsel'), 'partners');
        $proje->save();

        // Çeviriler ayrı tabloda; kayıt id'si oluştuktan SONRA yazılır.
        $proje->cevirileriKaydet($istek->input('ceviri', []));

        return redirect()->route('panel.proje.index')
            ->with('basarili', "\"{$proje->baslik}\" eklendi.");
    }

    public function duzenleForm(Proje $proje): View
    {
        return view('panel.proje.form', $this->formVerisi($proje));
    }

    public function guncelle(Request $istek, Proje $proje): RedirectResponse
    {
        $veri = $this->dogrula($istek, $proje);

        $proje->fill($veri);

        if ($istek->boolean('slug_yenile')) {
            $proje->slug = $this->slugUret($veri['baslik'], $proje->id);
        }

        if ($istek->boolean('gorsel_sil')) {
            GorselYukle::sil($proje->gorsel);
            $proje->gorsel = null;
        } else {
            $proje->gorsel = GorselYukle::calistir($istek->file('gorsel'), 'partners', $proje->gorsel);
        }

        $proje->save();
        $proje->cevirileriKaydet($istek->input('ceviri', []));

        return redirect()->route('panel.proje.index')
            ->with('basarili', "\"{$proje->baslik}\" güncellendi.");
    }

    public function durumDegistir(Proje $proje): RedirectResponse
    {
        $proje->update(['durum' => !$proje->durum]);

        return back()->with('basarili', $proje->durum
            ? "\"{$proje->baslik}\" yayına alındı."
            : "\"{$proje->baslik}\" yayından kaldırıldı.");
    }

    public function sil(Proje $proje): RedirectResponse
    {
        GorselYukle::sil($proje->gorsel);
        Ceviri::kayitSil($proje->getTable(), (int) $proje->id);
        $baslik = $proje->baslik;
        $proje->delete();

        return redirect()->route('panel.proje.index')
            ->with('basarili', "\"{$baslik}\" silindi.");
    }

    // ---- Yardımcılar ---------------------------------------------------

    private function dogrula(Request $istek, ?Proje $proje = null): array
    {
        return $istek->validate([
            'baslik'      => ['required', 'string', 'max:150'],
            'ceviri'     => ['nullable', 'array'],
            'ceviri.*.*' => ['nullable', 'string', 'max:40000'],
            'baslik_en'   => ['nullable', 'string', 'max:150'],
            'tur'         => ['required', Rule::in(array_keys(self::TURLER))],
            'kategori'    => ['nullable', 'string', 'max:80'],
            'kategori_en' => ['nullable', 'string', 'max:80'],
            'ulke'        => ['nullable', 'string', 'max:80'],
            'ulke_en'     => ['nullable', 'string', 'max:80'],
            'aciklama'    => ['nullable', 'string', 'max:20000'],
            'aciklama_en' => ['nullable', 'string', 'max:20000'],
            'website'     => ['nullable', 'url', 'max:255'],
            'tarih'       => ['nullable', 'string', 'max:40'],
            'sira'        => ['nullable', 'integer', 'min:0', 'max:999'],
            'durum'       => ['nullable', 'boolean'],
            'gorsel'      => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:4096'],
        ], [], [
            'baslik'   => 'ad',
            'tur'      => 'tür',
            'kategori' => 'sektör',
            'website'  => 'web sitesi',
            'gorsel'   => 'logo',
        ]);
    }

    private function slugUret(string $baslik, ?int $haricId = null): string
    {
        $temel = Metin::slug($baslik) ?: 'kayit';
        $slug  = $temel;
        $sayac = 2;

        while (Proje::where('slug', $slug)->when($haricId, fn ($q) => $q->whereKeyNot($haricId))->exists()) {
            $slug = $temel . '-' . $sayac++;
        }

        return $slug;
    }

    private function formVerisi(Proje $proje): array
    {
        return [
            'proje'  => $proje,
            'turler' => self::TURLER,
            // Kategori faaliyet alanı başlığıyla eşleşirse o sektör
            // sayfasında da çapraz bağlantı olarak görünür.
            'alanlar' => Hizmet::yayinda()->sirali()->pluck('baslik'),
        ];
    }
}
