<?php

namespace App\Concerns;

use App\Models\Ceviri;
use App\Support\Dil;

/**
 * Çok dilli alan erişimi.
 *
 *   $hizmet->d('baslik')
 *
 * Sıra:
 *   1) ceviriler tablosunda aktif dilin karşılığı
 *   2) eski `_en` sütunu  (geçiş dönemi güvencesi)
 *   3) temel dildeki sütun (fallback)
 *
 * 2. adım bilerek duruyor: çeviriler tablosuna taşımada bir şey
 * atlanmışsa site boş metin göstermesin. Sütunlar kaldırıldığında
 * bu adım kendiliğinden devre dışı kalır.
 *
 * Görünümler bu metodun içini bilmez — imza değişmediği için
 * çeviri altyapısı değiştiğinde tek bir Blade dosyasına dokunulmadı.
 */
trait DilliAlan
{
    public function d(string $alan): string
    {
        $dil = app()->getLocale();

        if ($dil !== Dil::temel()) {
            // 1) çeviriler tablosu
            $ceviri = Ceviri::al($this->getTable(), (int) $this->getKey(), $alan, $dil);

            if ($ceviri !== null) {
                return $ceviri;
            }

            // 2) eski sütun (varsa)
            $eski = trim((string) ($this->{$alan . '_' . $dil} ?? ''));

            if ($eski !== '') {
                return $eski;
            }
        }

        // 3) temel dil
        return (string) ($this->{$alan} ?? '');
    }

    /** Belirli bir dildeki ham çeviri — panel formları için. */
    public function ceviri(string $alan, string $dil): string
    {
        if ($dil === Dil::temel()) {
            return (string) ($this->{$alan} ?? '');
        }

        return Ceviri::al($this->getTable(), (int) $this->getKey(), $alan, $dil)
            ?? trim((string) ($this->{$alan . '_' . $dil} ?? ''));
    }

    /** Panelden gelen çevirileri kaydeder: ['en' => ['baslik' => '...']] */
    public function cevirileriKaydet(array $diller): void
    {
        foreach ($diller as $dil => $alanlar) {
            if ($dil === Dil::temel() || !Dil::gecerliMi($dil)) {
                continue;
            }

            foreach ($alanlar as $alan => $deger) {
                Ceviri::yaz($this->getTable(), (int) $this->getKey(), $alan, $dil, $deger);
            }
        }
    }
}
