<?php

namespace App\Models;

use App\Concerns\DilliAlan;
use Illuminate\Database\Eloquent\Model;

/** Haber / blog yazısı. Tablo adı `blog`. */
class Yazi extends Model
{
    use DilliAlan;

    protected $table      = 'blog';
    public    $timestamps = false;

    // 'video' bilerek $fillable dışında: değerini VideoYukle üretir
    // (dosya yolu ya da doğrulanmış bağlantı), formdan ham veri yazılmaz.
    protected $fillable = [
        'baslik', 'baslik_en', 'slug', 'kategori', 'kategori_en',
        'ozet', 'ozet_en', 'icerik', 'icerik_en',
        'gorsel', 'tarih', 'durum',
    ];

    protected function casts(): array
    {
        return ['durum' => 'boolean', 'tarih' => 'date', 'created_at' => 'datetime'];
    }

    public function scopeYayinda($q) { return $q->where('durum', 1); }
    public function scopeYeni($q)    { return $q->orderByDesc('tarih'); }
}
