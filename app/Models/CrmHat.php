<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Satış hattı (pipeline). */
class CrmHat extends Model
{
    protected $table = 'crm_pipelines';

    protected $fillable = ['adi', 'aciklama', 'sira', 'varsayilan'];

    protected function casts(): array
    {
        return ['varsayilan' => 'boolean', 'sira' => 'integer'];
    }

    public function asamalar()
    {
        return $this->hasMany(CrmAsama::class, 'pipeline_id')->orderBy('sira');
    }

    public static function varsayilan(): ?self
    {
        return static::where('varsayilan', 1)->first() ?? static::orderBy('sira')->first();
    }
}
