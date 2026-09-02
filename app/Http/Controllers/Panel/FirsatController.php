<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CrmAsama;
use App\Models\CrmFirsat;
use App\Models\CrmHat;
use App\Models\CrmMusteri;
use App\Models\Kullanici;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Satış fırsatları — aşama panosu ve fırsat kayıtları.
 *
 * Panoda her sütun bir aşama. Kart sürüklendiğinde tasi() çağrılır;
 * aşamanın `sonuc` alanı fırsatın durumunu da belirlediği için
 * "Kazanıldı" sütununa bırakılan fırsat otomatik kapanır.
 */
class FirsatController extends Controller
{
    public function pano(Request $istek): View
    {
        $hat = $istek->query('hat')
            ? CrmHat::find($istek->query('hat'))
            : CrmHat::varsayilan();

        abort_unless($hat, 404, 'Satış hattı bulunamadı.');

        $asamalar = $hat->asamalar()->get();

        // Tek sorguda tüm fırsatlar, sonra aşamaya göre grupla —
        // sütun başına ayrı sorgu atmamak için.
        $firsatlar = CrmFirsat::gorebilecegi($istek->user())
            ->where('pipeline_id', $hat->id)
            ->with(['musteri', 'sorumlu'])
            ->orderByDesc('tutar')
            ->get()
            ->groupBy('stage_id');

        return view('panel.firsat.pano', [
            'hat'       => $hat,
            'hatlar'    => CrmHat::orderBy('sira')->get(),
            'asamalar'  => $asamalar,
            'firsatlar' => $firsatlar,
        ]);
    }

    public function olusturForm(Request $istek): View
    {
        $firsat = new CrmFirsat();

        // Müşteri detayından "fırsat ekle" ile gelindiyse önceden seç.
        if ($musteriId = $istek->query('musteri')) {
            $firsat->musteri_id = $musteriId;
        }

        return view('panel.firsat.form', $this->formVerisi($istek, $firsat));
    }

    public function kaydet(Request $istek): RedirectResponse
    {
        $veri = $this->dogrula($istek);

        $asama = CrmAsama::findOrFail($veri['stage_id']);
        $this->musteriErisimi($istek, (int) $veri['musteri_id']);

        $firsat = new CrmFirsat();
        $firsat->fill($veri);
        $firsat->pipeline_id = $asama->pipeline_id;   // aşamadan türetilir, formdan değil
        $firsat->durum       = $asama->sonuc;
        $firsat->sorumlu_id  = $this->sorumluBelirle($istek, null);
        $firsat->save();

        $firsat->ozelKaydet($istek->input('ozel', []));

        return redirect()
            ->route('panel.firsat.pano', ['hat' => $firsat->pipeline_id])
            ->with('basarili', 'Fırsat oluşturuldu.');
    }

    public function duzenleForm(Request $istek, CrmFirsat $firsat): View
    {
        $this->erisimDenetle($istek, $firsat);

        return view('panel.firsat.form', $this->formVerisi($istek, $firsat));
    }

    public function guncelle(Request $istek, CrmFirsat $firsat): RedirectResponse
    {
        $this->erisimDenetle($istek, $firsat);

        $veri  = $this->dogrula($istek);
        $asama = CrmAsama::findOrFail($veri['stage_id']);
        $this->musteriErisimi($istek, (int) $veri['musteri_id']);

        $firsat->fill($veri);
        $firsat->pipeline_id = $asama->pipeline_id;
        $firsat->durum       = $asama->sonuc;
        $firsat->sorumlu_id  = $this->sorumluBelirle($istek, $firsat);
        $firsat->save();

        $firsat->ozelKaydet($istek->input('ozel', []));

        return redirect()
            ->route('panel.firsat.pano', ['hat' => $firsat->pipeline_id])
            ->with('basarili', 'Fırsat güncellendi.');
    }

    /**
     * Kartı başka aşamaya taşır. Panodaki sürükle-bırak buraya istek atar.
     * Yanıt JSON: sürükleme sonrası sayfa yenilenmediği için sütun
     * toplamlarını güncelleyebilmek adına yeni durum geri döner.
     */
    public function tasi(Request $istek, CrmFirsat $firsat): JsonResponse
    {
        $this->erisimDenetle($istek, $firsat);

        $veri = $istek->validate([
            'stage_id' => ['required', 'integer', 'exists:crm_stages,id'],
        ]);

        $asama = CrmAsama::findOrFail($veri['stage_id']);
        $firsat->asamayaTasi($asama);

        return response()->json([
            'tamam'  => true,
            'durum'  => $firsat->durum,
            'asama'  => $asama->adi,
            'kapali' => $asama->kapanisMi(),
        ]);
    }

    public function sil(Request $istek, CrmFirsat $firsat): RedirectResponse
    {
        abort_unless($istek->user()->yoneticiMi(), 403, 'Silme yetkiniz yok.');

        $hatId = $firsat->pipeline_id;
        $firsat->delete();

        return redirect()
            ->route('panel.firsat.pano', ['hat' => $hatId])
            ->with('basarili', 'Fırsat silindi.');
    }

    // ---- Yardımcılar ---------------------------------------------------

    private function dogrula(Request $istek): array
    {
        return $istek->validate([
            'musteri_id'     => ['required', 'integer', 'exists:crm_customers,id'],
            'stage_id'       => ['required', 'integer', 'exists:crm_stages,id'],
            'baslik'         => ['required', 'string', 'max:180'],
            'tutar'          => ['nullable', 'numeric', 'min:0', 'max:99999999999'],
            'para_birimi'    => ['required', 'string', 'max:10'],
            'kapanis_tarihi' => ['nullable', 'date'],
            'aciklama'       => ['nullable', 'string', 'max:5000'],
        ], [], [
            'musteri_id'     => 'müşteri',
            'stage_id'       => 'aşama',
            'baslik'         => 'başlık',
            'tutar'          => 'tutar',
            'kapanis_tarihi' => 'tahmini kapanış',
        ]);
    }

    /** Temsilci kendisine atanmamış bir müşteriye fırsat açamaz. */
    private function musteriErisimi(Request $istek, int $musteriId): void
    {
        if ($istek->user()->yoneticiMi()) {
            return;
        }

        $sahibi = CrmMusteri::whereKey($musteriId)->value('sorumlu_id');

        abort_unless($sahibi === $istek->user()->id, 403, 'Bu müşteri size atanmamış.');
    }

    private function sorumluBelirle(Request $istek, ?CrmFirsat $firsat): ?int
    {
        if (!$istek->user()->yoneticiMi()) {
            return $firsat?->sorumlu_id ?? $istek->user()->id;
        }

        $secilen = $istek->input('sorumlu_id');

        if ($secilen === null || $secilen === '') {
            return null;
        }

        return Kullanici::aktif()->whereKey($secilen)->value('id');
    }

    private function formVerisi(Request $istek, CrmFirsat $firsat): array
    {
        return [
            'firsat'       => $firsat,
            'musteriler'   => CrmMusteri::gorebilecegi($istek->user())->orderBy('adi')->get(),
            'hatlar'       => CrmHat::with('asamalar')->orderBy('sira')->get(),
            'ozelAlanlar'  => $firsat->ozelAlanTanimlari(),
            'ozelDegerler' => $firsat->exists ? $firsat->ozelDegerler() : [],
            'kullanicilar' => $istek->user()->yoneticiMi()
                ? Kullanici::aktif()->orderBy('ad_soyad')->get()
                : collect(),
        ];
    }

    private function erisimDenetle(Request $istek, CrmFirsat $firsat): void
    {
        $kullanici = $istek->user();

        abort_unless(
            $kullanici->yoneticiMi() || $firsat->sorumlu_id === $kullanici->id,
            403,
            'Bu fırsat size atanmamış.',
        );
    }
}
