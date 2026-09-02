<?php

namespace App\Models;

use App\Support\Dil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Bir alanın belirli bir dildeki karşılığı.
 *
 * PERFORMANS: d() bir sayfada yüzlerce kez çağrılıyor. Her çağrıda
 * sorgu atmak olmazdı; bir dilin TÜM çevirileri tek sorguyla alınıp
 * önbelleğe konur (birkaç yüz satır, birkaç KB). Ayar::hepsi() ile
 * aynı yaklaşım.
 *
 * Önbellek yazma anında temizlenir — panelden kaydeden kullanıcı
 * değişikliği anında görür.
 */
class Ceviri extends Model
{
    protected $table = 'ceviriler';

    protected $fillable = ['tablo', 'kayit_id', 'alan', 'dil', 'deger'];

    /** Ayarlar tablosunun kayıt kimliği yoktur; hepsi 0 altında. */
    public const AYAR_ID = 0;

    /**
     * Bir dilin tüm çevirileri: "tablo.kayit_id.alan" => değer
     *
     * @return array<string, string>
     */
    public static function harita(string $dil): array
    {
        return Cache::rememberForever(
            self::onbellekAnahtari($dil),
            fn () => static::query()
                ->where('dil', $dil)
                ->get(['tablo', 'kayit_id', 'alan', 'deger'])
                ->mapWithKeys(fn ($c) => ["{$c->tablo}.{$c->kayit_id}.{$c->alan}" => (string) $c->deger])
                ->all()
        );
    }

    /** Tek bir çeviriyi okur; yoksa null. */
    public static function al(string $tablo, int $kayitId, string $alan, string $dil): ?string
    {
        $deger = static::harita($dil)["{$tablo}.{$kayitId}.{$alan}"] ?? null;

        return ($deger !== null && trim($deger) !== '') ? $deger : null;
    }

    /**
     * Çeviriyi yazar. Boş değer gönderilirse kayıt silinir —
     * boş satır tutmanın anlamı yok, fallback zaten temel dile düşer.
     */
    public static function yaz(string $tablo, int $kayitId, string $alan, string $dil, ?string $deger): void
    {
        $deger = trim((string) $deger);
        $olcut = compact('tablo', 'alan', 'dil') + ['kayit_id' => $kayitId];

        if ($deger === '') {
            static::where($olcut)->delete();
        } else {
            static::updateOrCreate($olcut, ['deger' => $deger]);
        }

        Cache::forget(self::onbellekAnahtari($dil));
    }

    /** Bir kaydın tüm çevirileri (kayıt silinince çağrılır). */
    public static function kayitSil(string $tablo, int $kayitId): void
    {
        static::where(['tablo' => $tablo, 'kayit_id' => $kayitId])->delete();
        static::onbellegiTemizle();
    }

    public static function onbellegiTemizle(): void
    {
        foreach (Dil::kodlar() as $kod) {
            Cache::forget(self::onbellekAnahtari($kod));
        }
    }

    private static function onbellekAnahtari(string $dil): string
    {
        return "ceviriler_{$dil}";
    }
}
