<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CrmAlan;
use App\Models\CrmHat;
use App\Models\CrmMusteri;
use App\Models\CrmNot;
use App\Models\Kullanici;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRM müşteri yönetimi.
 *
 * Yetki kuralı: temsilci yalnızca sorumlusu olduğu kayda erişir. Bu
 * denetim tek bir yerde (gorebilir kontrolü) toplandı; her metotta
 * elle koşul yazmak yerine oradan geçiyor — biri unutulup veri sızmasın.
 */
class MusteriController extends Controller
{
    private const DURUMLAR = [
        'aday'      => 'Aday',
        'gorusuluyor' => 'Görüşülüyor',
        'musteri'   => 'Müşteri',
        'pasif'     => 'Pasif',
    ];

    public function index(Request $istek): View
    {
        $sorgu = CrmMusteri::gorebilecegi($istek->user())
            ->with('sorumlu')
            ->withCount('firsatlar')
            ->ara($istek->query('ara'));

        if ($durum = $istek->query('durum')) {
            $sorgu->where('durum', $durum);
        }

        if ($sorumlu = $istek->query('sorumlu')) {
            $sorgu->where('sorumlu_id', $sorumlu);
        }

        return view('panel.musteri.index', [
            'musteriler' => $sorgu->latest()->paginate(20)->withQueryString(),
            'durumlar'   => self::DURUMLAR,
            'kullanicilar' => $istek->user()->yoneticiMi()
                ? Kullanici::aktif()->orderBy('ad_soyad')->get()
                : collect(),
        ]);
    }

    public function olusturForm(Request $istek): View
    {
        return view('panel.musteri.form', $this->formVerisi($istek, new CrmMusteri()));
    }

    public function kaydet(Request $istek): RedirectResponse
    {
        $veri = $this->dogrula($istek);

        $musteri = new CrmMusteri();
        $musteri->fill($veri);
        $musteri->sorumlu_id = $this->sorumluBelirle($istek, null);
        $musteri->save();

        $musteri->ozelKaydet($istek->input('ozel', []));

        return redirect()
            ->route('panel.musteri.detay', $musteri)
            ->with('basarili', 'Müşteri kaydedildi.');
    }

    /** Müşteri detayındaki sekmeler. */
    private const SEKMELER = [
        'bilgiler' => ['bi-card-list',       'Bilgiler'],
        'notlar'   => ['bi-journal-text',    'Notlar'],
        'firsatlar'=> ['bi-briefcase',       'Fırsatlar'],
        'mesajlar' => ['bi-envelope',        'Mesajlar'],
    ];

    public function detay(Request $istek, CrmMusteri $musteri): View
    {
        $this->erisimDenetle($istek, $musteri);

        $sekme = $istek->query('sekme', 'bilgiler');

        if (!array_key_exists($sekme, self::SEKMELER)) {
            $sekme = 'bilgiler';
        }

        $musteri->load(['sorumlu', 'firsatlar.asama', 'notlar.yazan']);
        $mesajlar = $musteri->mesajlar()->get();

        return view('panel.musteri.detay', [
            'musteri'      => $musteri,
            'durumlar'     => self::DURUMLAR,
            'sekmeler'     => self::SEKMELER,
            'aktifSekme'   => $sekme,
            'mesajlar'     => $mesajlar,
            'ozelAlanlar'  => $musteri->ozelAlanTanimlari(),
            'ozelDegerler' => $musteri->ozelDegerler(),
            // Sekme rozetlerindeki sayılar
            'sayilar'      => [
                'notlar'    => $musteri->notlar->count(),
                'firsatlar' => $musteri->firsatlar->count(),
                'mesajlar'  => $mesajlar->count(),
            ],
        ]);
    }

    public function duzenleForm(Request $istek, CrmMusteri $musteri): View
    {
        $this->erisimDenetle($istek, $musteri);

        return view('panel.musteri.form', $this->formVerisi($istek, $musteri));
    }

    public function guncelle(Request $istek, CrmMusteri $musteri): RedirectResponse
    {
        $this->erisimDenetle($istek, $musteri);

        $musteri->fill($this->dogrula($istek, $musteri));
        $musteri->sorumlu_id = $this->sorumluBelirle($istek, $musteri);
        $musteri->save();

        $musteri->ozelKaydet($istek->input('ozel', []));

        return redirect()
            ->route('panel.musteri.detay', $musteri)
            ->with('basarili', 'Müşteri güncellendi.');
    }

    public function sil(Request $istek, CrmMusteri $musteri): RedirectResponse
    {
        // Silme yalnızca yöneticilerde: temsilci kendi kaydını da silemez,
        // yanlışlıkla geçmişi yok etmesin.
        abort_unless($istek->user()->yoneticiMi(), 403, 'Silme yetkiniz yok.');

        $ad = $musteri->adi;
        $musteri->delete();   // fırsat, not ve görevler cascade ile gider

        return redirect()
            ->route('panel.musteri.index')
            ->with('basarili', "\"{$ad}\" ve bağlı kayıtları silindi.");
    }

    /** Müşteriye not ekler. */
    public function notEkle(Request $istek, CrmMusteri $musteri): RedirectResponse
    {
        $this->erisimDenetle($istek, $musteri);

        $veri = $istek->validate(
            ['icerik' => ['required', 'string', 'max:5000']],
            [],
            ['icerik' => 'not'],
        );

        $not = new CrmNot(['musteri_id' => $musteri->id, 'icerik' => $veri['icerik']]);
        $not->yazan_id = $istek->user()->id;   // yazan her zaman oturumdaki kişi
        $not->save();

        return back()->with('basarili', 'Not eklendi.');
    }

    // ---- Yardımcılar ---------------------------------------------------

    private function dogrula(Request $istek, ?CrmMusteri $musteri = null): array
    {
        return $istek->validate([
            'adi'      => ['required', 'string', 'max:150'],
            'unvan'    => ['nullable', 'string', 'max:150'],
            'email'    => ['nullable', 'email', 'max:150'],
            'telefon'  => ['nullable', 'string', 'max:50'],
            'sektor'   => ['nullable', 'string', 'max:100'],
            'kaynak'   => ['nullable', 'string', 'max:100'],
            'ulke'     => ['nullable', 'string', 'max:80'],
            'adres'    => ['nullable', 'string', 'max:2000'],
            'durum'    => ['required', 'in:' . implode(',', array_keys(self::DURUMLAR))],
        ], [], [
            'adi'    => 'firma / kişi adı',
            'email'  => 'e-posta',
            'durum'  => 'durum',
        ]);
    }

    /**
     * Sorumlu ataması. Temsilci başkasına atayamaz, kendi üstünde kalır —
     * bu yüzden sorumlu_id modelin $fillable'ında değil.
     */
    private function sorumluBelirle(Request $istek, ?CrmMusteri $musteri): ?int
    {
        if (!$istek->user()->yoneticiMi()) {
            return $musteri?->sorumlu_id ?? $istek->user()->id;
        }

        $secilen = $istek->input('sorumlu_id');

        if ($secilen === null || $secilen === '') {
            return null;
        }

        // Var olmayan veya pasif kullanıcıya atama yapılmasın.
        return Kullanici::aktif()->whereKey($secilen)->value('id');
    }

    private function formVerisi(Request $istek, CrmMusteri $musteri): array
    {
        return [
            'musteri'      => $musteri,
            'durumlar'     => self::DURUMLAR,
            'ozelAlanlar'  => CrmAlan::tablo('musteri')->aktif()->get(),
            'ozelDegerler' => $musteri->exists ? $musteri->ozelDegerler() : [],
            'kullanicilar' => $istek->user()->yoneticiMi()
                ? Kullanici::aktif()->orderBy('ad_soyad')->get()
                : collect(),
        ];
    }

    private function erisimDenetle(Request $istek, CrmMusteri $musteri): void
    {
        $kullanici = $istek->user();

        abort_unless(
            $kullanici->yoneticiMi() || $musteri->sorumlu_id === $kullanici->id,
            403,
            'Bu müşteri size atanmamış.',
        );
    }
}
