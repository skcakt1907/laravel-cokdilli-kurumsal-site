<?php

namespace App\Models;

use App\Concerns\DilliAlan;
use Illuminate\Database\Eloquent\Model;

/** İçerik bloğu. tip: neden | surec | bolge */
class Blok extends Model
{
    use DilliAlan;

    protected $table      = 'bloklar';
    public    $timestamps = false;

    protected $fillable = [
        'tip', 'baslik', 'baslik_en', 'ozet', 'ozet_en',
        'ikon', 'etiket', 'sira', 'durum',
    ];

    protected function casts(): array
    {
        return ['durum' => 'boolean', 'sira' => 'integer'];
    }

    public function scopeYayinda($q)        { return $q->where('durum', 1); }
    public function scopeSirali($q)         { return $q->orderBy('sira'); }
    public function scopeTip($q, string $t) { return $q->where('tip', $t); }
}
