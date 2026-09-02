<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CrmFirsat;
use App\Models\CrmMusteri;
use App\Models\Mesaj;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Panel giriş ekranı — kullanıcının rolüne göre daralan özet. */
class PanoController extends Controller
{
    public function goster(Request $istek): View
    {
        $kullanici = $istek->user();

        // Her sorgu gorebilecegi() ile daraltılıyor: temsilci yalnızca
        // kendi sorumlu olduğu kayıtların sayısını görür.
        $sayilar = [
            'musteri'     => CrmMusteri::gorebilecegi($kullanici)->count(),
            'acik_firsat' => CrmFirsat::gorebilecegi($kullanici)->acik()->count(),
            // Bu ay eklenen adaylar — web formundan gelen akışın hızını gösterir
            'bu_ay'       => CrmMusteri::gorebilecegi($kullanici)
                                 ->where('created_at', '>=', now()->startOfMonth())
                                 ->count(),
            'sahipsiz'    => CrmMusteri::gorebilecegi($kullanici)->whereNull('sorumlu_id')->count(),
        ];

        // Açık fırsatların toplam değeri — para birimine göre ayrı ayrı,
        // kurları bilmediğimiz için toplama yapılmıyor.
        $boruHacmi = CrmFirsat::gorebilecegi($kullanici)
            ->acik()
            ->selectRaw('para_birimi, SUM(tutar) AS toplam')
            ->groupBy('para_birimi')
            ->pluck('toplam', 'para_birimi');

        // Mesaj rozeti kenar menüde de görünsün
        view()->share('okunmamisMesaj', $kullanici->yoneticiMi() ? Mesaj::okunmamis()->count() : 0);

        return view('panel.pano', [
            'sayilar'      => $sayilar,
            'boruHacmi'    => $boruHacmi,
            'sonMusteriler'=> CrmMusteri::gorebilecegi($kullanici)->latest()->limit(5)->get(),
            // Okunmamış mesaj sayısı yalnızca yöneticiler için anlamlı.
            'okunmamisMesaj' => $kullanici->yoneticiMi() ? Mesaj::okunmamis()->count() : null,
        ]);
    }
}
