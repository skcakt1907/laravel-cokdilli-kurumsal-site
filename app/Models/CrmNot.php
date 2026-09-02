<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Müşteri veya fırsata düşülen not. */
class CrmNot extends Model
{
    protected $table = 'crm_notes';

    /** `yazan_id` dışarıda: her zaman oturumdaki kullanıcıdan atanır. */
    protected $fillable = ['musteri_id', 'firsat_id', 'icerik'];

    public function musteri() { return $this->belongsTo(CrmMusteri::class, 'musteri_id'); }
    public function firsat()  { return $this->belongsTo(CrmFirsat::class, 'firsat_id'); }
    public function yazan()   { return $this->belongsTo(Kullanici::class, 'yazan_id'); }
}
