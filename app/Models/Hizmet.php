<?php

namespace App\Models;

use App\Concerns\DilliAlan;
use Illuminate\Database\Eloquent\Model;

/** Faaliyet alanı. `grup` alanı sektör ailesini tutar (menüde gruplama). */
class Hizmet extends Model
{
    use DilliAlan;

    protected $table      = 'hizmetler';
    public    $timestamps = false;

    protected $fillable = [
        'baslik', 'baslik_en', 'slug', 'grup', 'grup_en',
        'ozet', 'ozet_en', 'icerik', 'icerik_en',
        'ikon', 'gorsel', 'sira', 'durum',
    ];

    protected function casts(): array
    {
        return ['durum' => 'boolean', 'sira' => 'integer', 'created_at' => 'datetime'];
    }

    public function scopeYayinda($q) { return $q->where('durum', 1); }
    public function scopeSirali($q)  { return $q->orderBy('sira'); }
}
