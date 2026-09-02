<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Ceviri;
use App\Models\Hizmet;
use App\Support\GorselYukle;
use App\Support\Metin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Faaliyet alanları (site tarafındaki 23 sektör kalemi). */
class HizmetController extends Controller
{
    public function index(Request $istek): View
    {
        $sorgu = Hizmet::query()->orderBy('sira');

        if ($arama = trim((string) $istek->query('ara'))) {
            $sorgu->where(fn ($q) => $q
                ->where('baslik', 'like', "%{$arama}%")
                ->orWhere('grup', 'like', "%{$arama}%"));
        }

        return view('panel.hizmet.index', [
            'hizmetler' => $sorgu->get()->groupBy(fn ($h) => $h->grup ?: '—'),
        ]);
    }

    public function olusturForm(): View
    {
        return view('panel.hizmet.form', $this->formVerisi(new Hizmet()));
    }

    public function kaydet(Request $istek): RedirectResponse
    {
        $veri = $this->dogrula($istek);

        $hizmet = new Hizmet();
        $hizmet->fill($veri);
        $hizmet->slug   = $this->slugUret($veri['baslik']);
        $hizmet->gorsel = GorselYukle::calistir($istek->file('gorsel'), 'hizmetler');
        $hizmet->save();

        // Çeviriler ayrı tabloda; kayıt id'si oluştuktan SONRA yazılır.
        $hizmet->cevirileriKaydet($istek->input('ceviri', []));

        return redirect()->route('panel.hizmet.index')
            ->with('basarili', "\"{$hizmet->baslik}\" eklendi.");
    }

    public function duzenleForm(Hizmet $hizmet): View
    {
        return view('panel.hizmet.form', $this->formVerisi($hizmet));
    }

    public function guncelle(Request $istek, Hizmet $hizmet): RedirectResponse
    {
        $veri = $this->dogrula($istek, $hizmet);

        $hizmet->fill($veri);

        // Başlık değiştiyse slug da yenilenir; eski adresler kırılmasın
        // diye yalnızca kullanıcı açıkça isterse.
        if ($istek->boolean('slug_yenile')) {
            $hizmet->slug = $this->slugUret($veri['baslik'], $hizmet->id);
        }

        if ($istek->boolean('gorsel_sil')) {
            GorselYukle::sil($hizmet->gorsel);
            $hizmet->gorsel = null;
        } else {
            $hizmet->gorsel = GorselYukle::calistir($istek->file('gorsel'), 'hizmetler', $hizmet->gorsel);
        }

        $hizmet->save();
        $hizmet->cevirileriKaydet($istek->input('ceviri', []));

        return redirect()->route('panel.hizmet.index')
            ->with('basarili', "\"{$hizmet->baslik}\" güncellendi.");
    }

    public function durumDegistir(Hizmet $hizmet): RedirectResponse
    {
        $hizmet->update(['durum' => !$hizmet->durum]);

        return back()->with('basarili', $hizmet->durum
            ? "\"{$hizmet->baslik}\" yayına alındı."
            : "\"{$hizmet->baslik}\" yayından kaldırıldı.");
    }

    public function sil(Hizmet $hizmet): RedirectResponse
    {
        GorselYukle::sil($hizmet->gorsel);
        Ceviri::kayitSil($hizmet->getTable(), (int) $hizmet->id);
        $baslik = $hizmet->baslik;
        $hizmet->delete();

        return redirect()->route('panel.hizmet.index')
            ->with('basarili', "\"{$baslik}\" silindi.");
    }

    // ---- Yardımcılar ---------------------------------------------------

    private function dogrula(Request $istek, ?Hizmet $hizmet = null): array
    {
        return $istek->validate([
            'baslik'    => ['required', 'string', 'max:150'],
            'ceviri'     => ['nullable', 'array'],
            'ceviri.*.*' => ['nullable', 'string', 'max:40000'],
            'baslik_en' => ['nullable', 'string', 'max:150'],
            'grup'      => ['required', 'string', 'max:100'],
            'grup_en'   => ['nullable', 'string', 'max:100'],
            'ozet'      => ['nullable', 'string', 'max:1000'],
            'ozet_en'   => ['nullable', 'string', 'max:1000'],
            'icerik'    => ['nullable', 'string', 'max:20000'],
            'icerik_en' => ['nullable', 'string', 'max:20000'],
            'ikon'      => ['nullable', 'string', 'max:60'],
            'sira'      => ['nullable', 'integer', 'min:0', 'max:999'],
            'durum'     => ['nullable', 'boolean'],
            'gorsel'    => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,gif', 'max:4096'],
        ], [], [
            'baslik' => 'başlık',
            'grup'   => 'sektör ailesi',
            'gorsel' => 'görsel',
        ]);
    }

    private function slugUret(string $baslik, ?int $haricId = null): string
    {
        $temel = Metin::slug($baslik) ?: 'alan';
        $slug  = $temel;
        $sayac = 2;

        while (Hizmet::where('slug', $slug)->when($haricId, fn ($q) => $q->whereKeyNot($haricId))->exists()) {
            $slug = $temel . '-' . $sayac++;
        }

        return $slug;
    }

    private function formVerisi(Hizmet $hizmet): array
    {
        return [
            'hizmet'   => $hizmet,
            // Mevcut aileler öneri olarak sunulur; yenisi de yazılabilir.
            'gruplar'  => Hizmet::query()->distinct()->orderBy('grup')->pluck('grup')->filter()->values(),
        ];
    }
}
