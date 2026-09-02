<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Kullanici;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Panel kullanıcıları. Yalnızca "sahip" rolü erişebilir (rota katmanında
 * middleware ile kısıtlı).
 *
 * Kilitlenme koruması — üç kural:
 *   1. Kullanıcı kendi rolünü değiştiremez
 *   2. Kullanıcı kendi hesabını pasifleştiremez veya silemez
 *   3. Sistemde en az bir aktif sahip kalmalı
 *
 * Bunlar olmadan tek sahip kendini temsilciye çevirip panele bir daha
 * giremez ve düzeltmenin tek yolu veritabanına elle müdahale olurdu.
 */
class KullaniciController extends Controller
{
    public function index(): View
    {
        return view('panel.kullanici.index', [
            'kullanicilar' => Kullanici::orderByRaw("FIELD(rol,'sahip','yonetici','temsilci')")
                                       ->orderBy('ad_soyad')
                                       ->get(),
            'roller'       => Kullanici::ROLLER,
        ]);
    }

    public function olusturForm(): View
    {
        return view('panel.kullanici.form', [
            'kayit'  => new Kullanici(),
            'roller' => Kullanici::ROLLER,
        ]);
    }

    public function kaydet(Request $istek): RedirectResponse
    {
        $veri = $istek->validate([
            'kullanici' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('admin', 'kullanici')],
            'ad_soyad'  => ['required', 'string', 'max:120'],
            'eposta'    => ['nullable', 'email', 'max:150'],
            'telefon'   => ['nullable', 'string', 'max:50'],
            'rol'       => ['required', Rule::in(array_keys(Kullanici::ROLLER))],
            'sifre'     => ['required', 'confirmed', Password::min(8)],
        ], [], $this->alanAdlari());

        $kayit = new Kullanici();
        $kayit->fill($veri);
        // rol, durum ve şifre $fillable dışında — açıkça atanır.
        $kayit->forceFill([
            'rol'        => $veri['rol'],
            'durum'      => 1,
            'sifre_hash' => Hash::make($veri['sifre']),
        ])->save();

        return redirect()
            ->route('panel.kullanici.index')
            ->with('basarili', "{$kayit->ad_soyad} eklendi.");
    }

    public function duzenleForm(Kullanici $kullanici): View
    {
        return view('panel.kullanici.form', [
            'kayit'  => $kullanici,
            'roller' => Kullanici::ROLLER,
        ]);
    }

    public function guncelle(Request $istek, Kullanici $kullanici): RedirectResponse
    {
        $kendisi = $istek->user()->id === $kullanici->id;

        $veri = $istek->validate([
            'kullanici' => ['required', 'string', 'max:60', 'alpha_dash',
                            Rule::unique('admin', 'kullanici')->ignore($kullanici->id)],
            'ad_soyad'  => ['required', 'string', 'max:120'],
            'eposta'    => ['nullable', 'email', 'max:150'],
            'telefon'   => ['nullable', 'string', 'max:50'],
            'rol'       => ['required', Rule::in(array_keys(Kullanici::ROLLER))],
            'sifre'     => ['nullable', 'confirmed', Password::min(8)],
        ], [], $this->alanAdlari());

        // Kural 1: kendi rolünü değiştiremez.
        if ($kendisi && $veri['rol'] !== $kullanici->rol) {
            return back()->withInput()
                ->with('hata', 'Kendi rolünüzü değiştiremezsiniz. Başka bir sahip bunu yapabilir.');
        }

        // Kural 3: son aktif sahip rolünü bırakamaz.
        if ($kullanici->rol === 'sahip' && $veri['rol'] !== 'sahip' && $this->sonAktifSahipMi($kullanici)) {
            return back()->withInput()
                ->with('hata', 'Sistemde en az bir aktif sahip kalmalı. Önce başka birini sahip yapın.');
        }

        $kullanici->fill($veri);
        $kullanici->forceFill(['rol' => $veri['rol']]);

        if (!empty($veri['sifre'])) {
            $kullanici->forceFill(['sifre_hash' => Hash::make($veri['sifre'])]);
        }

        $kullanici->save();

        return redirect()
            ->route('panel.kullanici.index')
            ->with('basarili', "{$kullanici->ad_soyad} güncellendi.");
    }

    /** Hesabı aktif/pasif yapar. Silmek yerine bu tercih edilmeli. */
    public function durumDegistir(Request $istek, Kullanici $kullanici): RedirectResponse
    {
        // Kural 2: kendi hesabını pasifleştiremez.
        if ($istek->user()->id === $kullanici->id) {
            return back()->with('hata', 'Kendi hesabınızı pasifleştiremezsiniz.');
        }

        if ($kullanici->durum && $kullanici->rol === 'sahip' && $this->sonAktifSahipMi($kullanici)) {
            return back()->with('hata', 'Sistemde en az bir aktif sahip kalmalı.');
        }

        $kullanici->forceFill(['durum' => !$kullanici->durum])->save();

        return back()->with(
            'basarili',
            $kullanici->durum
                ? "{$kullanici->ad_soyad} yeniden aktifleştirildi."
                : "{$kullanici->ad_soyad} pasifleştirildi; artık giriş yapamaz.",
        );
    }

    public function sil(Request $istek, Kullanici $kullanici): RedirectResponse
    {
        if ($istek->user()->id === $kullanici->id) {
            return back()->with('hata', 'Kendi hesabınızı silemezsiniz.');
        }

        if ($kullanici->rol === 'sahip' && $this->sonAktifSahipMi($kullanici)) {
            return back()->with('hata', 'Sistemdeki son sahip silinemez.');
        }

        // CRM kayıtlarındaki sorumlu bağı ON DELETE SET NULL ile boşalır;
        // kayıtlar silinmez, yalnızca sahipsiz kalır.
        $ad = $kullanici->ad_soyad;
        $kullanici->delete();

        return redirect()
            ->route('panel.kullanici.index')
            ->with('basarili', "{$ad} silindi. Kayıtları sahipsiz kaldı, yeniden atayabilirsiniz.");
    }

    // ---- Yardımcılar ---------------------------------------------------

    private function sonAktifSahipMi(Kullanici $kullanici): bool
    {
        return Kullanici::where('rol', 'sahip')
                        ->where('durum', 1)
                        ->whereKeyNot($kullanici->id)
                        ->doesntExist();
    }

    private function alanAdlari(): array
    {
        return [
            'kullanici' => 'kullanıcı adı',
            'ad_soyad'  => 'ad soyad',
            'eposta'    => 'e-posta',
            'rol'       => 'rol',
            'sifre'     => 'şifre',
        ];
    }
}
