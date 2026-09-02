<?php

namespace App\Models;

use App\Support\Dil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Site ayarları — anahtar/değer tablosu.
 *
 * Tablo düz PHP sürümünden devralındı: birincil anahtar `anahtar`,
 * id kolonu ve zaman damgaları yok.
 *
 * Kullanım:
 *   Ayar::al('site_adi')          -> tek değer
 *   Ayar::dilli('slogan')         -> aktif dile göre (EN boşsa TR'ye düşer)
 *   Ayar::yaz('site_adi', 'FGG')  -> yazar ve önbelleği temizler
 */
class Ayar extends Model
{
    protected $table        = 'ayarlar';
    protected $primaryKey   = 'anahtar';
    public    $incrementing = false;
    protected $keyType      = 'string';
    public    $timestamps   = false;

    protected $fillable = ['anahtar', 'deger'];

    /** Tüm ayarları tek sorguda okur ve önbellekte tutar. */
    public static function hepsi(): array
    {
        return Cache::rememberForever('ayarlar', fn () => static::pluck('deger', 'anahtar')->all());
    }

    public static function al(string $anahtar, string $varsayilan = ''): string
    {
        return (string) (static::hepsi()[$anahtar] ?? $varsayilan);
    }

    /**
     * Aktif dildeki değer.
     *
     * Sıra: ceviriler tablosu > eski `_dil` anahtarı > temel dil.
     * (Orta adım geçiş güvencesi — bkz. App\Concerns\DilliAlan)
     */
    public static function dilli(string $anahtar, string $varsayilan = ''): string
    {
        $dil = app()->getLocale();

        if ($dil !== Dil::temel()) {
            $ceviri = Ceviri::al('ayarlar', Ceviri::AYAR_ID, $anahtar, $dil);

            if ($ceviri !== null) {
                return $ceviri;
            }

            $eski = trim(static::al($anahtar . '_' . $dil));

            if ($eski !== '') {
                return $eski;
            }
        }

        return static::al($anahtar, $varsayilan);
    }

    /** Belirli bir dildeki ham değer — panel formları için. */
    public static function dildeki(string $anahtar, string $dil): string
    {
        if ($dil === Dil::temel()) {
            return static::al($anahtar);
        }

        return Ceviri::al('ayarlar', Ceviri::AYAR_ID, $anahtar, $dil)
            ?? trim(static::al($anahtar . '_' . $dil));
    }

    /** Bir ayarın çevirisini yazar. */
    public static function ceviriYaz(string $anahtar, string $dil, ?string $deger): void
    {
        Ceviri::yaz('ayarlar', Ceviri::AYAR_ID, $anahtar, $dil, $deger);
    }

    public static function yaz(string $anahtar, ?string $deger): void
    {
        static::updateOrCreate(['anahtar' => $anahtar], ['deger' => (string) $deger]);
        Cache::forget('ayarlar');
    }
}
