<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CrmMusteri;
use App\Models\Mesaj;
use App\Services\MesajdanMusteri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Siteden gelen iletişim mesajları. */
class MesajController extends Controller
{
    public function index(Request $istek): View
    {
        $sorgu = Mesaj::query()->latest('tarih');

        if ($istek->query('durum') === 'okunmamis') {
            $sorgu->okunmamis();
        }

        if ($arama = trim((string) $istek->query('ara'))) {
            $sorgu->where(function ($alt) use ($arama) {
                foreach (['ad', 'mail', 'konu', 'mesaj'] as $alan) {
                    $alt->orWhere($alan, 'like', '%' . $arama . '%');
                }
            });
        }

        return view('panel.mesaj.index', [
            'mesajlar'   => $sorgu->paginate(20)->withQueryString(),
            'okunmamis'  => Mesaj::okunmamis()->count(),
        ]);
    }

    public function detay(Mesaj $mesaj): View
    {
        // Açılan mesaj okundu sayılır.
        if (!$mesaj->okundu) {
            $mesaj->forceFill(['okundu' => 1])->save();
        }

        return view('panel.mesaj.detay', [
            'mesaj'   => $mesaj,
            // Bu mesajdan açılmış ya da aynı e-postaya sahip CRM kaydı
            'musteri' => CrmMusteri::where('mesaj_id', $mesaj->id)
                ->orWhere(fn ($q) => $q->whereNotNull('email')->whereRaw('LOWER(email) = ?', [mb_strtolower((string) $mesaj->mail)]))
                ->first(),
        ]);
    }

    /** Okunmamış olarak geri işaretler. */
    public function okunmadi(Mesaj $mesaj): RedirectResponse
    {
        $mesaj->forceFill(['okundu' => 0])->save();

        return redirect()->route('panel.mesaj.index')->with('basarili', 'Mesaj okunmadı olarak işaretlendi.');
    }

    /**
     * Mesajı elle CRM'e aktarır. Normalde form gönderiminde otomatik
     * oluyor; bu yol otomatik aktarımın hata verdiği kayıtlar için.
     */
    public function crmeAktar(Mesaj $mesaj, MesajdanMusteri $servis): RedirectResponse
    {
        $musteri = $servis->calistir($mesaj);

        return redirect()
            ->route('panel.musteri.detay', $musteri)
            ->with('basarili', 'Mesaj CRM kaydına işlendi.');
    }

    public function sil(Request $istek, Mesaj $mesaj): RedirectResponse
    {
        abort_unless($istek->user()->yoneticiMi(), 403);

        $mesaj->delete();

        return redirect()->route('panel.mesaj.index')->with('basarili', 'Mesaj silindi.');
    }
}
