<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CrmAlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Özel alan tanımları — "alanları sonradan belirleriz" isteğinin panel tarafı.
 *
 * Buradan eklenen her alan, müşteri veya fırsat formunda otomatik belirir.
 * Veritabanına kolon eklenmez; değerler crm_alan_degerleri tablosunda durur.
 */
class AlanController extends Controller
{
    private const TABLOLAR = ['musteri' => 'Müşteri', 'firsat' => 'Fırsat'];

    public function index(): View
    {
        return view('panel.alan.index', [
            'alanlar'  => CrmAlan::orderBy('tablo')->orderBy('sira')->get()->groupBy('tablo'),
            'tablolar' => self::TABLOLAR,
            'tipler'   => CrmAlan::TIPLER,
            // Hangi alan kaç kayıtta dolu — silmeden önce görülsün.
            'kullanim' => DB::table('crm_alan_degerleri')
                            ->selectRaw('tablo, anahtar, COUNT(*) AS adet')
                            ->groupBy('tablo', 'anahtar')
                            ->get()
                            ->keyBy(fn ($s) => $s->tablo . '|' . $s->anahtar),
        ]);
    }

    public function olusturForm(): View
    {
        return view('panel.alan.form', [
            'alan'     => new CrmAlan(),
            'tablolar' => self::TABLOLAR,
            'tipler'   => CrmAlan::TIPLER,
        ]);
    }

    public function kaydet(Request $istek): RedirectResponse
    {
        $veri = $this->dogrula($istek);
        $veri['anahtar'] = $this->anahtarUret($veri['etiket'], $veri['tablo']);

        CrmAlan::create($veri);

        return redirect()
            ->route('panel.alan.index')
            ->with('basarili', "\"{$veri['etiket']}\" alanı eklendi. İlgili formda görünür oldu.");
    }

    public function duzenleForm(CrmAlan $alan): View
    {
        return view('panel.alan.form', [
            'alan'     => $alan,
            'tablolar' => self::TABLOLAR,
            'tipler'   => CrmAlan::TIPLER,
        ]);
    }

    public function guncelle(Request $istek, CrmAlan $alan): RedirectResponse
    {
        $veri = $this->dogrula($istek, $alan);

        // `anahtar` ve `tablo` değişmez: kayıtlı değerler bu ikisiyle
        // eşleşiyor, değiştirmek girilmiş verinin bağını koparırdı.
        unset($veri['tablo']);

        $alan->update($veri);

        return redirect()
            ->route('panel.alan.index')
            ->with('basarili', "\"{$alan->etiket}\" güncellendi.");
    }

    public function sil(CrmAlan $alan): RedirectResponse
    {
        $dolu = DB::table('crm_alan_degerleri')
            ->where('tablo', $alan->tablo)
            ->where('anahtar', $alan->anahtar)
            ->count();

        // Veri girilmiş bir alan silinmiyor: pasifleştirmek yeterli, böylece
        // formdan kalkar ama geçmiş kayıtlardaki değerler kaybolmaz.
        if ($dolu > 0) {
            return back()->with(
                'hata',
                "Bu alan {$dolu} kayıtta dolu. Silmek yerine pasifleştirin — " .
                'formdan kalkar, girilmiş veriler korunur.',
            );
        }

        $etiket = $alan->etiket;
        $alan->delete();

        return back()->with('basarili', "\"{$etiket}\" silindi.");
    }

    public function durumDegistir(CrmAlan $alan): RedirectResponse
    {
        $alan->update(['durum' => !$alan->durum]);

        return back()->with(
            'basarili',
            $alan->durum
                ? "\"{$alan->etiket}\" yeniden forma eklendi."
                : "\"{$alan->etiket}\" formdan kaldırıldı; girilmiş veriler duruyor.",
        );
    }

    // ---- Yardımcılar ---------------------------------------------------

    private function dogrula(Request $istek, ?CrmAlan $alan = null): array
    {
        return $istek->validate([
            'tablo'      => ['required', Rule::in(array_keys(self::TABLOLAR))],
            'etiket'     => ['required', 'string', 'max:150'],
            'etiket_en'  => ['nullable', 'string', 'max:150'],
            'tip'        => ['required', Rule::in(array_keys(CrmAlan::TIPLER))],
            'secenekler' => ['nullable', 'string', 'max:3000', 'required_if:tip,secim'],
            'zorunlu'    => ['nullable', 'boolean'],
            'sira'       => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'secenekler.required_if' => 'Seçim listesi için en az bir seçenek girmelisiniz.',
        ], [
            'tablo'      => 'alan nereye eklenecek',
            'etiket'     => 'alan adı',
            'etiket_en'  => 'İngilizce adı',
            'tip'        => 'tip',
            'secenekler' => 'seçenekler',
        ]);
    }

    /**
     * Etiketten benzersiz bir anahtar üretir (proje_butcesi gibi).
     * Kullanıcı anahtarı elle girmez — Türkçe karakter, boşluk ve
     * çakışma sorunları buradan çıkardı.
     */
    private function anahtarUret(string $etiket, string $tablo): string
    {
        $temel = Str::slug($etiket, '_') ?: 'alan';
        $temel = Str::limit($temel, 50, '');

        $anahtar = $temel;
        $sayac   = 2;

        while (CrmAlan::where('tablo', $tablo)->where('anahtar', $anahtar)->exists()) {
            $anahtar = $temel . '_' . $sayac++;
        }

        return $anahtar;
    }
}
