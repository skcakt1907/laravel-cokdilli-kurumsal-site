{{--
  Panelden tanımlanmış tek bir özel alanın form girdisi.
  Beklenen değişkenler: $alan (CrmAlan), $mevcut (string)

  Müşteri ve fırsat formları aynı parçayı kullanır; alan tipi eklendiğinde
  tek yer güncellenir.
--}}
@php($ad = 'ozel[' . $alan->anahtar . ']')
@php($id = 'ozel_' . $alan->anahtar)

<div class="{{ $alan->tip === 'uzun_metin' ? 'col-12' : 'col-md-4' }}">
  <label class="form-label" for="{{ $id }}">
    {{ $alan->etiketi() }}
    @if($alan->zorunlu)<span style="color:var(--ap)">*</span>@endif
  </label>

  @switch($alan->tip)
    @case('uzun_metin')
      <textarea class="form-control" id="{{ $id }}" name="{{ $ad }}"
                rows="3" @required($alan->zorunlu)>{{ $mevcut }}</textarea>
      @break

    @case('sayi')
      <input type="number" step="any" class="form-control" id="{{ $id }}"
             name="{{ $ad }}" value="{{ $mevcut }}" @required($alan->zorunlu)>
      @break

    @case('tarih')
      <input type="date" class="form-control" id="{{ $id }}"
             name="{{ $ad }}" value="{{ $mevcut }}" @required($alan->zorunlu)>
      @break

    @case('secim')
      <select class="form-select" id="{{ $id }}" name="{{ $ad }}" @required($alan->zorunlu)>
        <option value="">Seçiniz</option>
        @foreach($alan->secenekListesi() as $secenek)
          <option value="{{ $secenek }}" @selected($mevcut === $secenek)>{{ $secenek }}</option>
        @endforeach
      </select>
      @break

    @case('onay')
      {{-- Gizli alan: kutu işaretsizken de anahtar gönderilsin ki
           daha önce işaretliyse temizlenebilsin. --}}
      <div class="form-check mt-2">
        <input type="hidden" name="{{ $ad }}" value="">
        <input class="form-check-input" type="checkbox" id="{{ $id }}"
               name="{{ $ad }}" value="1" @checked($mevcut === '1')>
        <label class="form-check-label" for="{{ $id }}">Evet</label>
      </div>
      @break

    @default
      <input type="text" class="form-control" id="{{ $id }}"
             name="{{ $ad }}" value="{{ $mevcut }}" @required($alan->zorunlu)>
  @endswitch
</div>
