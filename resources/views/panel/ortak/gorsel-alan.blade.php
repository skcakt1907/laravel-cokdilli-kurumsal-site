{{--
  Görsel yükleme alanı — mevcut görseli gösterir, kaldırma kutusu sunar.
  Beklenen: $kayit (gorsel alanı olan model), $etiket, $ipucu (isteğe bağlı)
--}}
<div class="col-md-6">
  <label class="form-label" for="f_gorsel">{{ $etiket ?? 'Görsel' }}</label>

  @if($kayit->gorsel)
    <div class="d-flex align-items-center gap-3 mb-2">
      <div class="p-2" style="background:var(--yuzey-2);border-radius:8px">
        <img src="{{ \App\Support\Metin::gorselUrl($kayit->gorsel) }}" alt=""
             style="max-height:64px;max-width:120px">
      </div>
      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="gorsel_sil" value="1" id="gorsel_sil">
        <label class="form-check-label" for="gorsel_sil" style="font-size:.85rem">Kaldır</label>
      </div>
    </div>
  @endif

  <input type="file" class="form-control" id="f_gorsel" name="gorsel"
         accept="image/png,image/jpeg,image/webp,image/gif">

  <div class="form-text">
    {{ $ipucu ?? 'PNG, JPG, WebP veya GIF · en fazla 4 MB.' }}
    @if($kayit->gorsel) Yeni dosya seçerseniz mevcut görselin yerine geçer.@endif
  </div>
</div>
