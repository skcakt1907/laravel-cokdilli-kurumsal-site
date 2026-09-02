{{--
  Video alanı — bağlantı VEYA dosya. İkisi de aynı sütunda saklanır.
  Beklenen: $deger (mevcut değer), isteğe bağlı $etiket

  Yeni dosya yüklendiğinde ya da yerine bağlantı girildiğinde eskisi
  diskten silinir (App\Support\VideoYukle).
--}}
@php
  $vMedya = \App\Support\Medya::video($deger ?? null);
  $vSinir = \App\Support\VideoYukle::sinirMb();
  $vDosya = $vMedya && $vMedya['tip'] === 'dosya';
@endphp

<div class="col-12">
  <label class="form-label">{{ $etiket ?? 'Video' }}</label>

  @if($vMedya)
    <div class="d-flex align-items-center gap-3 mb-2 p-2"
         style="background:var(--yuzey-2);border-radius:8px;font-size:.85rem">
      <i class="bi {{ $vDosya ? 'bi-file-earmark-play' : 'bi-link-45deg' }}"
         style="font-size:1.3rem;color:var(--ap)"></i>
      <div style="min-width:0;flex:1">
        <div style="color:#fff">
          {{ $vDosya ? 'Yüklenmiş dosya' : 'Dış bağlantı (' . $vMedya['tip'] . ')' }}
        </div>
        <div style="color:var(--metin-sonuk);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
          {{ $deger }}
        </div>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="video_sil" value="1" id="video_sil">
        <label class="form-check-label" for="video_sil">Kaldır</label>
      </div>
    </div>
  @endif

  <div class="row g-2">
    <div class="col-md-7">
      <input type="url" class="form-control" name="video_link"
             placeholder="https://youtu.be/... veya https://vimeo.com/..."
             value="{{ old('video_link', $vDosya ? '' : ($deger ?? '')) }}">
      <div class="form-text">YouTube veya Vimeo bağlantısı yapıştırın.</div>
    </div>

    <div class="col-md-5">
      <input type="file" class="form-control" name="video_dosya" accept="video/mp4,video/webm,video/ogg,video/quicktime">
      <div class="form-text">
        …ya da dosya yükleyin. MP4/WebM · en fazla <strong>{{ $vSinir }} MB</strong>.
      </div>
    </div>
  </div>

  <div class="form-text mt-1">
    İkisini birden doldurursanız <strong>dosya</strong> geçerli olur.
    Uzun videolarda YouTube/Vimeo önerilir: sunucu trafiği harcamaz ve
    izleyicinin bağlantı hızına göre kalite ayarlanır.
  </div>
</div>
