{{--
  Çok dilli alan. Temel dil (Türkçe) tablonun kendi sütununa, diğer
  diller `ceviriler` tablosuna yazılır.

  Alan adları:
    temel dil : baslik
    diğerleri : ceviri[en][baslik]   ceviri[ar][baslik] ...

  Aktif dil listesi config/diller.php'den gelir — yeni dil açıldığında
  bu dosyaya dokunmadan alanlar kendiliğinden çoğalır.

  Beklenen:
    $ad      -> kolon adı ('baslik')
    $etiket  -> görünen etiket
    $kayit   -> model
    $tip     -> 'metin' | 'uzun' (varsayılan metin)
    $satir   -> uzun metinde satır sayısı (varsayılan 3)
    $genislik-> bootstrap sütun sınıfı (varsayılan col-md-6)
    $ipucu   -> alt açıklama (isteğe bağlı)
    $zorunlu -> bool
--}}
@php
  $tip       = $tip      ?? 'metin';
  $satir     = $satir    ?? 3;
  $genislik  = $genislik ?? 'col-md-6';
  $zorunlu   = $zorunlu  ?? false;
  $temelDil  = \App\Support\Dil::temel();
  $digerler  = \App\Support\Dil::cevrilecek();
@endphp

<div class="{{ $tip === 'uzun' ? 'col-12' : $genislik }}">
  {{-- Temel dil --}}
  <label class="form-label" for="f_{{ $ad }}">
    {{ $etiket }} <span class="en-hint">{{ strtoupper($temelDil) }}</span>
    @if($zorunlu)<span style="color:var(--ap)">*</span>@endif
  </label>

  @if($tip === 'uzun')
    <textarea class="form-control" id="f_{{ $ad }}" name="{{ $ad }}" rows="{{ $satir }}"
              @required($zorunlu)>{{ old($ad, $kayit->{$ad}) }}</textarea>
  @else
    <input type="text" class="form-control" id="f_{{ $ad }}" name="{{ $ad }}"
           value="{{ old($ad, $kayit->{$ad}) }}" @required($zorunlu)>
  @endif

  @isset($ipucu)<div class="form-text">{{ $ipucu }}</div>@endisset

  {{-- Çeviriler --}}
  @foreach($digerler as $kod => $tanim)
    @php
      $alanAdi = "ceviri[{$kod}][{$ad}]";
      $eskiAd  = "ceviri.{$kod}.{$ad}";
      $deger   = old($eskiAd, $kayit->exists ? $kayit->ceviri($ad, $kod) : '');
      $rtl     = ($tanim['yon'] ?? 'ltr') === 'rtl';
    @endphp

    <label class="form-label mt-2" for="f_{{ $ad }}_{{ $kod }}">
      {{ $etiket }} <span class="en-hint">{{ strtoupper($kod) }}</span>
    </label>

    @if($tip === 'uzun')
      <textarea class="form-control" id="f_{{ $ad }}_{{ $kod }}" name="{{ $alanAdi }}"
                rows="{{ $satir }}"
                @if($rtl) dir="rtl" @endif>{{ $deger }}</textarea>
    @else
      <input type="text" class="form-control" id="f_{{ $ad }}_{{ $kod }}" name="{{ $alanAdi }}"
             value="{{ $deger }}" @if($rtl) dir="rtl" @endif>
    @endif
  @endforeach

  @if($digerler)
    <div class="form-text">Boş bırakılan çeviri için sitede Türkçesi görünür.</div>
  @endif
</div>
