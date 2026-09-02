<?php

namespace App\Models;

use App\Concerns\DilliAlan;
use Illuminate\Database\Eloquent\Model;

/** Grup şirketi veya iş ortağı. tur: grup | ortak */
class Proje extends Model
{
    use DilliAlan;

    protected $table      = 'projeler';
    public    $timestamps = false;

    protected $fillable = [
        'baslik', 'baslik_en', 'slug', 'tur',
        'kategori', 'kategori_en', 'ulke', 'ulke_en',
        'gorsel', 'aciklama', 'aciklama_en',
        'website', 'tarih', 'sira', 'durum',
    ];

    protected function casts(): array
    {
        return ['durum' => 'boolean', 'sira' => 'integer', 'created_at' => 'datetime'];
    }

    public function scopeYayinda($q)        { return $q->where('durum', 1); }
    public function scopeSirali($q)         { return $q->orderBy('sira'); }
    public function scopeTur($q, string $t) { return $q->where('tur', $t); }
}
