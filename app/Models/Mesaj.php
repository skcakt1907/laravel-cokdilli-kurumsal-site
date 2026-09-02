<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * İletişim formu mesajı.
 *
 * `okundu` mass-assign edilemez — ziyaretçiden gelen veriyle
 * karıştırılmasın diye bilinçli olarak $fillable dışında.
 */
class Mesaj extends Model
{
    protected $table      = 'mesajlar';
    public    $timestamps = false;

    // 'gonderildi' de bilerek dışarıda: bildirimin hangi adrese gittiğini
    // sunucu yazar, formdan gelen veri belirlemez.
    protected $fillable = ['ad', 'mail', 'tel', 'konu', 'mesaj', 'birim'];

    protected function casts(): array
    {
        return ['okundu' => 'boolean', 'tarih' => 'datetime'];
    }

    public function scopeOkunmamis($q) { return $q->where('okundu', 0); }
}
