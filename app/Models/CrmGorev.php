<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Takip görevi — son tarihli, sorumlusu olan iş. */
class CrmGorev extends Model
{
    protected $table = 'crm_tasks';

    /** `durum` ve `sorumlu_id` dışarıda — sunucu tarafında atanır. */
    protected $fillable = ['musteri_id', 'firsat_id', 'baslik', 'aciklama', 'son_tarih'];

    /** Veritabanı varsayılanı bellekte de geçerli olsun (bkz. CrmFirsat). */
    protected $attributes = ['durum' => 'bekliyor'];

    protected function casts(): array
    {
        return ['son_tarih' => 'date'];
    }

    public function musteri() { return $this->belongsTo(CrmMusteri::class, 'musteri_id'); }
    public function firsat()  { return $this->belongsTo(CrmFirsat::class, 'firsat_id'); }
    public function sorumlu() { return $this->belongsTo(Kullanici::class, 'sorumlu_id'); }

    public function scopeBekleyen($q)
    {
        return $q->where('durum', 'bekliyor');
    }

    /** Son tarihi geçmiş ve hâlâ bekleyen görevler. */
    public function scopeGecikmis($q)
    {
        return $q->bekleyen()
                 ->whereNotNull('son_tarih')
                 ->whereDate('son_tarih', '<', now()->toDateString());
    }

    public function scopeGorebilecegi($q, Kullanici $kullanici)
    {
        return $kullanici->yoneticiMi() ? $q : $q->where('sorumlu_id', $kullanici->id);
    }

    public function gecikmisMi(): bool
    {
        return $this->durum === 'bekliyor' && $this->son_tarih && $this->son_tarih->isPast();
    }
}
