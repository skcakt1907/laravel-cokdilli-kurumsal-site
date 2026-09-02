<?php

namespace App\Models;

use App\Concerns\DilliAlan;
use Illuminate\Database\Eloquent\Model;

/** Yönetim kadrosu üyesi (kurucu, CEO vb.). */
class Yonetici extends Model
{
    use DilliAlan;

    protected $table      = 'yoneticiler';
    public    $timestamps = false;

    protected $fillable = [
        'ad', 'unvan', 'unvan_en', 'ozgecmis', 'ozgecmis_en',
        'foto', 'linkedin', 'sira', 'durum',
    ];

    protected function casts(): array
    {
        return ['durum' => 'boolean', 'sira' => 'integer'];
    }

    public function scopeYayinda($q) { return $q->where('durum', 1); }
    public function scopeSirali($q)  { return $q->orderBy('sira'); }
}
