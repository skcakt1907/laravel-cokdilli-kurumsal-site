<?php

namespace App\Http\Controllers;

use App\Models\Ayar;
use App\Models\Blok;
use App\Models\Hizmet;
use App\Models\Mesaj;
use App\Models\Proje;
use App\Models\Yazi;
use App\Models\Yonetici;
use App\Mail\YeniIletisimMesaji;
use App\Services\BirimYonlendirme;
use App\Services\MesajdanMusteri;
use App\Support\Metin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Tanıtım sitesi.
 *
 * Adresler düz PHP sürümüyle birebir aynı tutuldu (/kurumsal,
 * /faaliyet-alanlari, /is-ortaklari …) — canlıdaki bağlantılar ve
 * arama motoru kayıtları bozulmasın diye.
 */
class SiteController extends Controller
{
    public function anasayfa(): View
    {
        // Faaliyet alanları sektör ailelerine göre gruplanır.
        $aileler = Hizmet::yayinda()->sirali()->get()
            ->groupBy(fn ($h) => $h->grup ?: '—')
            ->map(fn ($alanlar) => [
                'ad'      => $alanlar->first()->d('grup'),
                'slug'    => Metin::slug($alanlar->first()->grup),
                'ikon'    => $alanlar->first()->ikon,
                'alanlar' => $alanlar,
            ]);

        return view('site.anasayfa', [
            'aileler'  => $aileler,
            'nedenler' => Blok::yayinda()->tip('neden')->sirali()->get(),
            'surec'    => Blok::yayinda()->tip('surec')->sirali()->get(),
            'bolgeler' => Blok::yayinda()->tip('bolge')->sirali()->get(),
            'haberler' => Yazi::yayinda()->yeni()->limit(3)->get(),
            'sayaclar' => $this->sayaclar(),
            // Anasayfadaki başkan bandı için; mesaj girilmemişse bölüm gizlenir.
            'baskan'   => Yonetici::yayinda()->sirali()->first(),
        ]);
    }

    public function kurumsal(): View
    {
        return view('site.kurumsal', [
            'pageTitle'   => __('site.nav_kurumsal') . ' — ' . Ayar::al('site_adi'),
            'pageDesc'    => Ayar::dilli('hakkimizda_kisa'),
            'yoneticiler' => Yonetici::yayinda()->sirali()->get(),
            'nedenler'    => Blok::yayinda()->tip('neden')->sirali()->get(),
            'surec'       => Blok::yayinda()->tip('surec')->sirali()->get(),
            'bolgeler'    => Blok::yayinda()->tip('bolge')->sirali()->get(),
        ]);
    }

    public function baskanMesaji(): View
    {
        $metin = Ayar::dilli('baskan_mesaji');

        abort_if($metin === '', 404);

        return view('site.baskan', [
            'pageTitle' => Ayar::dilli('baskan_mesaj_baslik') . ' — ' . Ayar::al('site_adi'),
            'pageDesc'  => Ayar::dilli('baskan_mesaj_alt'),
            'metin'     => $metin,
            'baskan'    => Yonetici::yayinda()->sirali()->first(),
        ]);
    }

    public function faaliyetler(): View
    {
        $aileler = Hizmet::yayinda()->sirali()->get()
            ->groupBy(fn ($h) => $h->grup ?: '—');

        return view('site.faaliyetler', [
            'pageTitle' => __('site.nav_faaliyet') . ' — ' . Ayar::al('site_adi'),
            'pageDesc'  => __('site.faaliyet_alt'),
            'aileler'   => $aileler,
        ]);
    }

    public function faaliyet(string $slug): View
    {
        $alan = Hizmet::yayinda()->where('slug', $slug)->firstOrFail();

        return view('site.faaliyet', [
            'pageTitle' => $alan->d('baslik') . ' — ' . Ayar::al('site_adi'),
            'pageDesc'  => $alan->d('ozet'),
            'alan'      => $alan,
            // Aynı ailedeki diğer alanlar
            'kardesler' => Hizmet::yayinda()->sirali()
                ->where('grup', $alan->grup)
                ->whereKeyNot($alan->id)
                ->get(),
            // Bu alanda faaliyet gösteren şirket / ortaklar
            'ortaklar'  => Proje::yayinda()->sirali()->where('kategori', $alan->baslik)->get(),
        ]);
    }

    public function ortaklar(): View
    {
        return view('site.ortaklar', [
            'pageTitle' => __('site.nav_ortaklar') . ' — ' . Ayar::al('site_adi'),
            'pageDesc'  => __('site.ortaklar_alt'),
            'gruplar'   => Proje::yayinda()->sirali()->tur('grup')->get(),
            'ortaklar'  => Proje::yayinda()->sirali()->tur('ortak')->get(),
        ]);
    }

    public function ortak(string $slug): View
    {
        $ortak = Proje::yayinda()->where('slug', $slug)->firstOrFail();

        return view('site.ortak', [
            'pageTitle' => $ortak->d('baslik') . ' — ' . Ayar::al('site_adi'),
            'pageDesc'  => mb_substr(strip_tags($ortak->d('aciklama')), 0, 160),
            'ortak'     => $ortak,
            'digerleri' => Proje::yayinda()->sirali()
                ->where('tur', $ortak->tur)
                ->whereKeyNot($ortak->id)
                ->limit(4)
                ->get(),
        ]);
    }

    public function yonetici(int $id): View
    {
        $yonetici = Yonetici::yayinda()->findOrFail($id);

        return view('site.yonetici', [
            'pageTitle' => $yonetici->ad . ' — ' . Ayar::al('site_adi'),
            'pageDesc'  => $yonetici->d('unvan'),
            'yonetici'  => $yonetici,
        ]);
    }

    public function haberler(): View
    {
        return view('site.haberler', [
            'pageTitle' => __('site.nav_haberler') . ' — ' . Ayar::al('site_adi'),
            'haberler'  => Yazi::yayinda()->yeni()->paginate(9),
        ]);
    }

    public function haber(string $slug): View
    {
        $haber = Yazi::yayinda()->where('slug', $slug)->firstOrFail();

        return view('site.haber', [
            'pageTitle' => $haber->d('baslik') . ' — ' . Ayar::al('site_adi'),
            'pageDesc'  => $haber->d('ozet'),
            'haber'     => $haber,
            'digerleri' => Yazi::yayinda()->yeni()->whereKeyNot($haber->id)->limit(3)->get(),
        ]);
    }

    public function iletisim(Request $istek, BirimYonlendirme $yonlendirme): View
    {
        $birimler = $yonlendirme->secenekler();

        // Sektör sayfasındaki "İletişim" bağlantısı ?birim=... ile gelir;
        // ziyaretçi listeden tekrar seçmek zorunda kalmasın.
        $secili = (string) $istek->query('birim', '');

        return view('site.iletisim', [
            'pageTitle' => __('site.nav_iletisim') . ' — ' . Ayar::al('site_adi'),
            'pageDesc'  => __('site.iletisim_alt'),
            'birimler'  => $birimler,
            'secili'    => array_key_exists($secili, $birimler) ? $secili : null,
        ]);
    }

    /**
     * İletişim formu. Mesaj veritabanına yazılır; CRM'e aday müşteri
     * olarak düşürme adımı ayrı bir işte bağlanacak.
     */
    public function iletisimGonder(Request $istek, BirimYonlendirme $yonlendirme): RedirectResponse
    {
        $veri = $istek->validate([
            'ad'    => ['required', 'string', 'max:100'],
            'mail'  => ['required', 'email', 'max:150'],
            'tel'   => ['nullable', 'string', 'max:40'],
            'konu'  => ['nullable', 'string', 'max:200'],
            'mesaj' => ['required', 'string', 'max:3000'],
            // Yalnızca sitede yayında olan sektör aileleri kabul edilir;
            // uydurma bir değerle bildirim başka adrese yönlendirilemesin.
            'birim' => ['nullable', 'string', Rule::in($yonlendirme->gecerliDegerler())],
            // Bot tuzağı: gerçek ziyaretçi bu alanı boş bırakır.
            'website' => ['prohibited'],
        ], [
            'website.prohibited' => 'Form gönderilemedi.',
        ], [
            'ad'    => __('site.form_ad'),
            'mail'  => __('site.form_mail'),
            'mesaj' => __('site.form_mesaj'),
            'birim' => __('site.form_birim'),
        ]);

        unset($veri['website']);
        $mesaj = Mesaj::create($veri);

        // İlgili birime bildir. Gönderim başarısız olursa ziyaretçiye hata
        // gösterilmez — mesaj zaten kaydedildi ve panelde duruyor.
        $this->birimeBildir($mesaj, $yonlendirme);

        // Mesajı CRM'e aday müşteri olarak düşür. Bu adım başarısız olursa
        // ziyaretçiye hata gösterilmez — mesaj zaten kaydedildi, panelden
        // elle CRM'e aktarılabilir.
        try {
            app(MesajdanMusteri::class)->calistir($mesaj);
        } catch (\Throwable $hata) {
            Log::error('Mesaj CRM\'e aktarılamadı', [
                'mesaj_id' => $mesaj->id,
                'hata'     => $hata->getMessage(),
            ]);
        }

        return back()
            ->with('formBasarili', __('site.form_basarili'))
            ->withFragment('iletisim-form');
    }

    /**
     * Mesajı ilgili birimin adresine bildirir.
     *
     * Hedef, ziyaretçinin seçtiği sektör ailesine göre Ayarlar > grup_mailler
     * üzerinden çözülür; eşleşme yoksa genel adrese (support@) düşer.
     * Hangi adrese gittiği mesaj kaydına yazılır — panelde görünsün ve
     * "bu bana gelmedi" tartışması yaşanmasın.
     */
    private function birimeBildir(Mesaj $mesaj, BirimYonlendirme $yonlendirme): void
    {
        $hedef = $yonlendirme->adres($mesaj->birim);

        if ($hedef === '') {
            Log::warning('İletişim bildirimi gönderilemedi: hedef adres tanımsız', [
                'mesaj_id' => $mesaj->id,
                'birim'    => $mesaj->birim,
            ]);

            return;
        }

        try {
            Mail::to($hedef)->send(new YeniIletisimMesaji($mesaj));

            $mesaj->forceFill(['gonderildi' => $hedef])->save();
        } catch (\Throwable $hata) {
            Log::error('İletişim bildirimi gönderilemedi', [
                'mesaj_id' => $mesaj->id,
                'hedef'    => $hedef,
                'hata'     => $hata->getMessage(),
            ]);
        }
    }

    // ---- Yardımcılar ---------------------------------------------------

    private function sayaclar(): array
    {
        $tanim = [
            ['bi-award',        'yil',           'sayac_yil'],
            ['bi-people',       'personel_sayi', 'sayac_personel'],
            ['bi-diagram-3',    'sirket_sayi',   'sayac_sirket'],
            ['bi-grid-3x3-gap', 'sektor_sayi',   'sayac_sektor'],
        ];

        return collect($tanim)
            ->map(fn ($s) => ['ikon' => $s[0], 'deger' => (int) Ayar::al($s[1]), 'etiket' => __('site.' . $s[2])])
            ->filter(fn ($s) => $s['deger'] > 0)
            ->values()
            ->all();
    }
}
