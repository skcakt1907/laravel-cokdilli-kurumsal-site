<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satış hattındaki aşama.
 *
 * `sonuc` alanı aşamanın kapanış anlamını taşır: bir fırsat "Kazanıldı"
 * aşamasına taşındığında durumu da otomatik olarak kapanır.
 */
class CrmAsama extends Model
{
    protected $table = 'crm_stages';

    protected $fillable = ['pipeline_id', 'adi', 'olasilik', 'renk', 'sira', 'sonuc'];

    protected function casts(): array
    {
        return ['olasilik' => 'integer', 'sira' => 'integer'];
    }

    public function hat()
    {
        return $this->belongsTo(CrmHat::class, 'pipeline_id');
    }

    public function firsatlar()
    {
        return $this->hasMany(CrmFirsat::class, 'stage_id');
    }

    public function kapanisMi(): bool
    {
        return $this->sonuc !== 'acik';
    }
}
