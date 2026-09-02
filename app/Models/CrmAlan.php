<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Panelden tanımlanan özel alan.
 *
 * "Alanları sonradan belirleriz" isteğinin karşılığı: yeni bir alan
 * eklemek için veritabanı değişikliği gerekmez, buraya bir satır eklenir.
 * Değerler crm_alan_degerleri tablosunda tutulur (bkz. OzelAlanlar trait).
 */
class CrmAlan extends Model
{
    protected $table = 'crm_alan_tanimlari';

    protected $fillable = [
        'tablo', 'anahtar', 'etiket', 'etiket_en',
        'tip', 'secenekler', 'zorunlu', 'sira', 'durum',
    ];

    protected function casts(): array
    {
        return ['zorunlu' => 'boolean', 'durum' => 'boolean', 'sira' => 'integer'];
    }

    public const TIPLER = [
        'metin'      => 'Kısa metin',
        'uzun_metin' => 'Uzun metin',
        'sayi'       => 'Sayı',
        'tarih'      => 'Tarih',
        'secim'      => 'Seçim listesi',
        'onay'       => 'Onay kutusu',
    ];

    /** 'secim' tipinde seçenekler satır satır tutulur. */
    public function secenekListesi(): array
    {
        $satirlar = preg_split('/\R/', (string) $this->secenekler);

        return array_values(array_filter(array_map('trim', $satirlar), fn ($s) => $s !== ''));
    }

    public function etiketi(): string
    {
        if (app()->getLocale() === 'en' && trim((string) $this->etiket_en) !== '') {
            return $this->etiket_en;
        }

        return $this->etiket;
    }

    public function scopeAktif($q)
    {
        return $q->where('durum', 1)->orderBy('sira');
    }

    public function scopeTablo($q, string $tur)
    {
        return $q->where('tablo', $tur);
    }
}
