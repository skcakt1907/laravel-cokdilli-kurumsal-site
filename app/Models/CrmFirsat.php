<?php

namespace App\Models;

use App\Concerns\OzelAlanlar;
use Illuminate\Database\Eloquent\Model;

/** Satış fırsatı — bir müşteriye bağlı, bir aşamada duran iş. */
class CrmFirsat extends Model
{
    use OzelAlanlar;

    protected $table = 'crm_opportunities';

    /** `durum` ve `sorumlu_id` dışarıda — ikisi de sunucu tarafında atanır. */
    protected $fillable = [
        'musteri_id', 'pipeline_id', 'stage_id', 'baslik',
        'tutar', 'para_birimi', 'kapanis_tarihi', 'aciklama',
    ];

    /**
     * Veritabanı varsayılanı bellekte de geçerli olsun: create() sonrası
     * kayıt yeniden okunmadan durum'a bakan kod boş değer görmesin.
     */
    protected $attributes = ['durum' => 'acik'];

    protected function casts(): array
    {
        return ['tutar' => 'decimal:2', 'kapanis_tarihi' => 'date'];
    }

    public function alanTablosu(): string
    {
        return 'firsat';
    }

    public function musteri() { return $this->belongsTo(CrmMusteri::class, 'musteri_id'); }
    public function hat()     { return $this->belongsTo(CrmHat::class, 'pipeline_id'); }
    public function asama()   { return $this->belongsTo(CrmAsama::class, 'stage_id'); }
    public function sorumlu() { return $this->belongsTo(Kullanici::class, 'sorumlu_id'); }
    public function notlar()  { return $this->hasMany(CrmNot::class, 'firsat_id')->latest(); }

    public function scopeAcik($q)
    {
        return $q->where('durum', 'acik');
    }

    public function scopeGorebilecegi($q, Kullanici $kullanici)
    {
        return $kullanici->yoneticiMi() ? $q : $q->where('sorumlu_id', $kullanici->id);
    }

    /**
     * Fırsatı bir aşamaya taşır. Aşamanın `sonuc` alanı fırsatın durumunu
     * da belirler — "Kazanıldı" aşamasına taşınan fırsat kapanmış sayılır.
     */
    public function asamayaTasi(CrmAsama $asama): void
    {
        $this->stage_id    = $asama->id;
        $this->pipeline_id = $asama->pipeline_id;
        $this->durum       = $asama->sonuc;
        $this->save();
    }
}
