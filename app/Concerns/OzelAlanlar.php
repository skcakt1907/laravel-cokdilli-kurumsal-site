<?php

namespace App\Concerns;

use App\Models\CrmAlan;
use Illuminate\Support\Facades\DB;

/**
 * Panelden tanımlanan özel alanları modele bağlar.
 *
 * "Alanları sonradan belirleriz" isteğinin çalışan tarafı: değerler
 * crm_alan_degerleri tablosunda anahtar/değer olarak durur, yeni alan
 * eklemek için tabloya kolon açmak gerekmez.
 *
 * Kullanan model `alanTablosu()` ile türünü bildirir ('musteri' | 'firsat').
 */
trait OzelAlanlar
{
    /** Bu kayıt için tanımlı alanların değerleri: [anahtar => deger] */
    public function ozelDegerler(): array
    {
        return DB::table('crm_alan_degerleri')
            ->where('tablo', $this->alanTablosu())
            ->where('kayit_id', $this->getKey())
            ->pluck('deger', 'anahtar')
            ->all();
    }

    public function ozel(string $anahtar, ?string $varsayilan = null): ?string
    {
        return $this->ozelDegerler()[$anahtar] ?? $varsayilan;
    }

    /**
     * Gelen değerleri yazar. Yalnızca tanımlı ve aktif alanlar kabul edilir —
     * istek gövdesine eklenen rastgele anahtarlar sessizce atılır.
     */
    public function ozelKaydet(array $degerler): void
    {
        $tanimli = CrmAlan::tablo($this->alanTablosu())->aktif()->pluck('anahtar')->all();

        foreach ($degerler as $anahtar => $deger) {
            if (!in_array($anahtar, $tanimli, true)) {
                continue;
            }

            $deger = is_array($deger) ? implode(', ', $deger) : (string) $deger;

            if (trim($deger) === '') {
                DB::table('crm_alan_degerleri')
                    ->where('tablo', $this->alanTablosu())
                    ->where('kayit_id', $this->getKey())
                    ->where('anahtar', $anahtar)
                    ->delete();
                continue;
            }

            DB::table('crm_alan_degerleri')->updateOrInsert(
                [
                    'tablo'    => $this->alanTablosu(),
                    'kayit_id' => $this->getKey(),
                    'anahtar'  => $anahtar,
                ],
                ['deger' => $deger],
            );
        }
    }

    /** Bu model türü için tanımlı alanlar. */
    public function ozelAlanTanimlari()
    {
        return CrmAlan::tablo($this->alanTablosu())->aktif()->get();
    }
}
