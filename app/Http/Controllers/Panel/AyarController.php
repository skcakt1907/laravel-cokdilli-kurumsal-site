<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Ayar;
use App\Services\BirimYonlendirme;
use App\Support\Dil;
use App\Support\Metin;
use App\Support\SimgeUret;
use App\Support\VideoYukle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Site ayarları.
 *
 * Her bölüm kendi sayfasında (sol alt menüden geçilir) — tek bir dev form
 * yerine parça parça kaydedilir. Alanlar bir tanım dizisinden üretiliyor;
 * yeni bir ayar eklemek için buraya bir satır yazmak yeterli, ayrıca görünüm
 * dosyasına dokunmak gerekmiyor.
 *
 * Yalnızca burada tanımlı anahtarlar kaydedilir; istek gövdesine eklenen
 * rastgele anahtarlar sessizce atılır.
 */
class AyarController extends Controller
{
    /** [sekme => [anahtar => [etiket, tip, ipucu?]]] — tip: metin|uzun|sayi|url */
    private function tanimlar(): array
    {
        return [
            'Genel' => [
                'site_adi'     => ['Site adı', 'metin'],
                'resmi_unvan'  => ['Resmî unvan', 'metin'],
                'marka_slogan' => ['Marka sloganı', 'metin', 'Logonun altında görünür'],
                'site_baslik'  => ['Tarayıcı başlığı', 'metin', 'Arama sonuçlarında görünen başlık'],
                'site_aciklama'=> ['Site açıklaması', 'uzun', 'Arama sonuçlarındaki özet, 160 karakteri geçmesin'],
            ],
            'Anasayfa' => [
                'slogan'         => ['Hero sloganı', 'metin'],
                'hero_alt'       => ['Hero alt metni', 'uzun'],
                'sektor_seridi'  => ['Hero rozeti', 'metin'],
                'hakkimizda_kisa'=> ['Biz kimiz — özet', 'uzun'],
                'hakkimizda_uzun'=> ['Biz kimiz — uzun metin', 'uzun'],
                'yaklasim'       => ['Temel yaklaşım', 'metin', 'Artı işaretiyle ayırın: Vizyon + Dürüstlük + Büyüme'],
                'kapanis'        => ['Kapanış metni', 'uzun'],
            ],
            'Kurumsal' => [
                'vizyon_baslik' => ['Vizyon başlığı', 'metin'],
                'vizyon'        => ['Vizyon metni', 'uzun'],
                'misyon'        => ['Misyon metni', 'uzun'],
                'isbirligi'     => ['İş birliği metni', 'uzun'],
                'baskan_mesaj_baslik' => ['Başkan mesajı — başlık', 'metin'],
                'baskan_mesaj_alt'    => ['Başkan mesajı — üst etiket', 'metin'],
                'baskan_mesaji'       => ['Başkan mesajı — metin', 'uzun'],
            ],
            'İletişim' => [
                'adres_etiket'      => ['Adres etiketi', 'metin', 'ör. Merkez Ofis'],
                'adres'             => ['Adres', 'uzun'],
                'telefonlar'        => ['Telefonlar', 'uzun', 'Her satır: Etiket|Numara — WhatsApp için sonuna |wa ekleyin'],
                'departman_mailler' => ['Departman e-postaları', 'uzun', 'İletişim sayfasında kart olarak görünür. Her satır: Etiket|adres@site.com'],
                'grup_mailler'      => ['Faaliyet alanı yönlendirmesi', 'uzun', 'İletişim formu hangi adrese düşsün? Her satır: Faaliyet Alanı|adres@site.com — soldaki ad, Faaliyet Alanları\'ndaki grup adıyla BİREBİR aynı olmalı. Eşleşmeyen mesaj genel e-postaya gider.'],
                'mail'              => ['Genel e-posta', 'metin', 'Faaliyet alanı seçilmeyen mesajlar buraya düşer'],
                'telefon'           => ['Ana telefon', 'metin', 'Footer ve WhatsApp butonu bunu kullanır'],
                'whatsapp'          => ['WhatsApp numarası', 'metin'],
                'calisma_saati'     => ['Çalışma saatleri', 'metin'],
                'harita_url'        => ['Harita gömme adresi', 'url', 'Google Haritalar > Paylaş > Harita yerleştir'],
            ],
            'Sayaçlar' => [
                'yil'           => ['Yıllık tecrübe', 'sayi'],
                'personel_sayi' => ['Çalışan sayısı', 'sayi'],
                'sirket_sayi'   => ['İş ortağı sayısı', 'sayi'],
                'sektor_sayi'   => ['Faaliyet alanı sayısı', 'sayi'],
            ],
            'Sosyal Medya' => [
                'linkedin'  => ['LinkedIn', 'url'],
                'instagram' => ['Instagram', 'url'],
                'facebook'  => ['Facebook', 'url'],
                'twitter'   => ['X / Twitter', 'url'],
                'youtube'   => ['YouTube', 'url'],
            ],
        ];
    }

    /** İngilizce karşılığı olan alanlar (_en son ekiyle saklanır). */
    private const CIFT_DILLI = [
        'site_baslik', 'site_aciklama', 'slogan', 'hero_alt', 'sektor_seridi',
        'hakkimizda_kisa', 'hakkimizda_uzun', 'yaklasim', 'kapanis',
        'vizyon_baslik', 'vizyon', 'misyon', 'isbirligi',
        'baskan_mesaj_baslik', 'baskan_mesaj_alt', 'baskan_mesaji',
        'adres_etiket', 'adres', 'telefonlar', 'departman_mailler', 'calisma_saati',
    ];

    /**
     * Sol alt menüdeki bölümler. Alan tanımı olanlar tanimlar()'dan gelir;
     * 'logo' ve 'mail' kendi ekranı olan özel bölümlerdir.
     *
     * @return array<string, array{ad:string, ikon:string, grup:string}>
     */
    public function bolumler(): array
    {
        $ikonlar = [
            'Genel'        => ['bi-sliders',      'Site'],
            'Anasayfa'     => ['bi-house',        'Site'],
            'Kurumsal'     => ['bi-building',     'Site'],
            'İletişim'     => ['bi-telephone',    'İletişim'],
            'Sayaçlar'     => ['bi-123',          'Site'],
            'Sosyal Medya' => ['bi-share',        'Site'],
        ];

        $bolumler = [];

        foreach (array_keys($this->tanimlar()) as $ad) {
            [$ikon, $grup] = $ikonlar[$ad] ?? ['bi-dot', 'Site'];
            $bolumler[Metin::slug($ad)] = ['ad' => $ad, 'ikon' => $ikon, 'grup' => $grup];
        }

        $bolumler['logo'] = ['ad' => 'Logo ve Simge', 'ikon' => 'bi-image', 'grup' => 'Site'];
        $bolumler['mail'] = ['ad' => 'Mail ve Bildirim', 'ikon' => 'bi-envelope-check', 'grup' => 'İletişim'];

        return $bolumler;
    }

    public function index(): RedirectResponse
    {
        return redirect()->route('panel.ayar.bolum', 'genel');
    }

    public function bolum(string $bolum): View
    {
        $bolumler = $this->bolumler();

        abort_unless(isset($bolumler[$bolum]), 404);

        $tanimlar = $this->tanimlar();
        $ad       = $bolumler[$bolum]['ad'];

        // Bölüme özel veriler burada hazırlanır — görünümde @php bloğu
        // kullanılmıyor. (Blade, aynı dosyada satır içi @php(...) ile
        // blok @php...@endphp karışınca bloğu yanlış eşliyor.)
        $ek = match ($bolum) {
            'mail'  => $this->mailVerisi(),
            'logo'  => $this->simgeVerisi(),
            default => [],
        };

        return view('panel.ayar.bolum', array_merge([
            'bolumler'  => $bolumler,
            'aktif'     => $bolum,
            'baslik'    => $ad,
            'alanlar'   => $tanimlar[$ad] ?? [],
            'ciftDilli' => self::CIFT_DILLI,
            'degerler'  => Ayar::hepsi(),
        ], $ek));
    }

    /** Mail bölümü: gönderim durumu ve yönlendirme tablosu. */
    private function mailVerisi(): array
    {
        $yonlendirme = app(BirimYonlendirme::class);
        $birimler    = $yonlendirme->secenekler();

        $satirlar = [];

        foreach ($birimler as $deger => $etiket) {
            $hedef = $yonlendirme->adres($deger);
            $satirlar[] = [
                'ad'      => $deger,
                'hedef'   => $hedef,
                'eslesme' => isset($yonlendirme->harita()[$deger]),
            ];
        }

        return [
            'birimler'    => $birimler,
            'satirlar'    => $satirlar,
            'genelAdres'  => $yonlendirme->genelAdres(),
            'kipLog'      => config('mail.default') === 'log',
            'sifreVar'    => (string) config('mail.mailers.smtp.password') !== '',
            'smtp'        => [
                'Sürücü'         => config('mail.default'),
                'Sunucu'         => config('mail.mailers.smtp.host'),
                'Port'           => config('mail.mailers.smtp.port'),
                'Şifreleme'      => config('mail.mailers.smtp.scheme') ?: config('mail.mailers.smtp.encryption') ?: '—',
                'Kullanıcı'      => config('mail.mailers.smtp.username') ?: '—',
                'Gönderen adres' => config('mail.from.address'),
            ],
        ];
    }

    /** Logo bölümü: yüklenmiş sekme simgesi bilgisi. */
    private function simgeVerisi(): array
    {
        $yol = trim(Ayar::al('favicon'));
        $var = $yol !== '' && is_file(public_path($yol));

        return [
            'simgeVar'      => $var,
            'simgeOnizleme' => $var
                ? asset(SimgeUret::boyut($yol, 180)) . '?v=' . @filemtime(public_path($yol))
                : asset('img/favicon-180.png'),
        ];
    }

    public function kaydet(Request $istek): RedirectResponse
    {
        // İzinli anahtar listesi tanımlardan türetilir.
        $izinli = [];
        foreach ($this->tanimlar() as $alanlar) {
            foreach (array_keys($alanlar) as $anahtar) {
                $izinli[] = $anahtar;
                if (in_array($anahtar, self::CIFT_DILLI, true)) {
                    $izinli[] = $anahtar . '_en';
                }
            }
        }

        $gelen = $istek->input('ayar', []);
        $sayac = 0;

        foreach ($gelen as $anahtar => $deger) {
            if (!in_array($anahtar, $izinli, true)) {
                continue;   // tanımsız anahtar — sessizce atılır
            }

            Ayar::yaz($anahtar, is_string($deger) ? trim($deger) : (string) $deger);
            $sayac++;
        }

        // Çeviriler ayrı tabloda: ceviri[dil][anahtar]
        foreach ((array) $istek->input('ceviri', []) as $dil => $alanlar) {
            if (!Dil::gecerliMi($dil) || $dil === Dil::temel() || !is_array($alanlar)) {
                continue;
            }

            foreach ($alanlar as $anahtar => $deger) {
                if (!in_array($anahtar, self::CIFT_DILLI, true)) {
                    continue;   // tanımsız anahtar — sessizce atılır
                }

                Ayar::ceviriYaz($anahtar, $dil, is_string($deger) ? trim($deger) : null);
                $sayac++;
            }
        }

        // Logo yüklemesi
        if ($istek->hasFile('logo')) {
            $istek->validate([
                'logo' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            ], [], ['logo' => 'logo']);

            $dosya = $istek->file('logo');
            $ad    = 'logo-' . now()->format('YmdHis') . '.' . $dosya->guessExtension();
            $dosya->move(public_path('uploads'), $ad);

            Ayar::yaz('logo', 'uploads/' . $ad);
        }

        if ($istek->boolean('logo_sil')) {
            Ayar::yaz('logo', '');
        }

        // Sekme simgesi (favicon) — yüklenen görselden 16/32/180 üretilir.
        if ($istek->hasFile('favicon')) {
            $istek->validate([
                'favicon' => ['image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            ], [], ['favicon' => 'sekme simgesi']);

            $yeni = SimgeUret::calistir($istek->file('favicon'), Ayar::al('favicon'));

            if ($yeni === null) {
                return back()->with('hata', 'Sekme simgesi işlenemedi. PNG veya JPG deneyin.');
            }

            Ayar::yaz('favicon', $yeni);
        }

        if ($istek->boolean('favicon_sil')) {
            SimgeUret::sil(Ayar::al('favicon'));
            Ayar::yaz('favicon', '');
        }

        // Başkan mesajı videosu — bağlantı veya dosya (Kurumsal bölümü).
        if ($istek->hasFile('video_dosya') || $istek->filled('video_link') || $istek->boolean('video_sil')) {
            $istek->validate([
                'video_link'  => ['nullable', 'url', 'max:255'],
                'video_dosya' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime',
                                  'max:' . (VideoYukle::sinirMb() * 1024)],
            ], [
                'video_dosya.mimetypes' => 'Video dosyası MP4, WebM, OGG veya MOV olmalı.',
                'video_dosya.max'       => 'Video en fazla ' . VideoYukle::sinirMb() . ' MB olabilir. '
                                         . 'Daha büyükse YouTube/Vimeo bağlantısı kullanın.',
            ], ['video_link' => 'video bağlantısı', 'video_dosya' => 'video dosyası']);

            Ayar::yaz('baskan_video', (string) VideoYukle::calistir(
                $istek->file('video_dosya'),
                $istek->input('video_link'),
                Ayar::al('baskan_video'),
                $istek->boolean('video_sil')
            ));
        }

        return back()->with('basarili', "{$sayac} ayar kaydedildi.");
    }

    /**
     * Test e-postası gönderir.
     *
     * İki kip var:
     *  - Adres girilir  → doğrudan o adrese gider (SMTP çalışıyor mu?)
     *  - Birim seçilir  → yönlendirme tablosundan adres çözülür ve oraya gider
     *    (mesaj gerçekten doğru departmana düşüyor mu?)
     *
     * Hata mesajı olduğu gibi gösterilir; "gönderilemedi" demek yerine
     * sunucunun ne dediğini görmek sorunu çözmenin tek yolu.
     */
    public function mailTesti(Request $istek, BirimYonlendirme $yonlendirme): RedirectResponse
    {
        $veri = $istek->validate([
            'hedef_tipi' => ['required', 'in:adres,birim'],
            'test_adres' => ['required_if:hedef_tipi,adres', 'nullable', 'email', 'max:150'],
            'test_birim' => ['required_if:hedef_tipi,birim', 'nullable', 'string'],
        ], [
            'test_adres.required_if' => 'Test için bir e-posta adresi girin.',
            'test_adres.email'       => 'Geçerli bir e-posta adresi girin.',
            'test_birim.required_if' => 'Bir faaliyet alanı seçin.',
        ]);

        if ($veri['hedef_tipi'] === 'birim') {
            $birim = $veri['test_birim'];
            $hedef = $yonlendirme->adres($birim);
            $not   = "\"{$birim}\" için çözülen adres: {$hedef}";
        } else {
            $birim = null;
            $hedef = $veri['test_adres'];
            $not   = "Hedef: {$hedef}";
        }

        if ($hedef === '') {
            return back()->with('hata', 'Hedef adres çözülemedi. Genel e-posta ayarı boş olabilir.');
        }

        $govde = "Bu bir test e-postasıdır.\n\n"
               . $not . "\n"
               . 'Gönderim zamanı: ' . now()->format('d.m.Y H:i:s') . "\n\n"
               . 'Bu mesajı görüyorsanız e-posta ayarları çalışıyor demektir.';

        try {
            Mail::raw($govde, function ($m) use ($hedef, $birim) {
                $m->to($hedef)->subject(
                    $birim ? "FGG test — {$birim}" : 'FGG e-posta ayarı testi'
                );
            });
        } catch (\Throwable $hata) {
            Log::error('Ayarlar: test maili gönderilemedi', [
                'hedef' => $hedef,
                'hata'  => $hata->getMessage(),
            ]);

            return back()->with('hata', 'Gönderilemedi — sunucunun yanıtı: ' . $hata->getMessage());
        }

        $ek = config('mail.default') === 'log'
            ? ' (DİKKAT: MAIL_MAILER=log — mesaj gerçekten gönderilmedi, log dosyasına yazıldı.)'
            : '';

        return back()->with('basarili', "Test e-postası {$hedef} adresine gönderildi.{$ek}");
    }
}
