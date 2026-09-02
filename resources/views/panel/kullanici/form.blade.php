@extends('panel.layout')
@section('baslik', $kayit->exists ? 'Kullanıcıyı Düzenle' : 'Yeni Kullanıcı')

@section('icerik')

<form method="post"
      action="{{ $kayit->exists ? route('panel.kullanici.guncelle', $kayit) : route('panel.kullanici.kaydet') }}"
      class="card p-4" style="max-width:720px" novalidate>
  @csrf
  @if($kayit->exists) @method('put') @endif

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label" for="ad_soyad">Ad Soyad <span style="color:var(--ap)">*</span></label>
      <input type="text" class="form-control" id="ad_soyad" name="ad_soyad"
             value="{{ old('ad_soyad', $kayit->ad_soyad) }}" required maxlength="120" autofocus>
    </div>

    <div class="col-md-6">
      <label class="form-label" for="kullanici">Kullanıcı Adı <span style="color:var(--ap)">*</span></label>
      <input type="text" class="form-control" id="kullanici" name="kullanici"
             value="{{ old('kullanici', $kayit->kullanici) }}" required maxlength="60"
             autocomplete="off">
      <div class="form-text">Harf, rakam, tire ve alt çizgi. Girişte bu ad kullanılır.</div>
    </div>

    <div class="col-md-6">
      <label class="form-label" for="eposta">E-posta</label>
      <input type="email" class="form-control" id="eposta" name="eposta"
             value="{{ old('eposta', $kayit->eposta) }}" maxlength="150">
    </div>

    <div class="col-md-6">
      <label class="form-label" for="telefon">Telefon</label>
      <input type="text" class="form-control" id="telefon" name="telefon"
             value="{{ old('telefon', $kayit->telefon) }}" maxlength="50">
    </div>

    <div class="col-md-6">
      <label class="form-label" for="rol">Rol <span style="color:var(--ap)">*</span></label>
      <select class="form-select" id="rol" name="rol" required
              @disabled($kayit->exists && $kayit->id === auth()->id())>
        @foreach($roller as $anahtar => $etiket)
          <option value="{{ $anahtar }}" @selected(old('rol', $kayit->rol ?: 'temsilci') === $anahtar)>
            {{ $etiket }}
          </option>
        @endforeach
      </select>
      @if($kayit->exists && $kayit->id === auth()->id())
        {{-- Alan kapalıyken tarayıcı göndermez; sunucu yine de reddediyor. --}}
        <input type="hidden" name="rol" value="{{ $kayit->rol }}">
        <div class="form-text">Kendi rolünüzü değiştiremezsiniz.</div>
      @endif
    </div>

    <div class="col-md-6">
      <label class="form-label" for="sifre">
        Şifre @unless($kayit->exists)<span style="color:var(--ap)">*</span>@endunless
      </label>
      <input type="password" class="form-control" id="sifre" name="sifre"
             @required(!$kayit->exists) autocomplete="new-password">
      <div class="form-text">
        En az 8 karakter.@if($kayit->exists) Boş bırakırsanız şifre değişmez.@endif
      </div>
    </div>

    <div class="col-md-6">
      <label class="form-label" for="sifre_confirmation">Şifre (tekrar)</label>
      <input type="password" class="form-control" id="sifre_confirmation" name="sifre_confirmation"
             @required(!$kayit->exists) autocomplete="new-password">
    </div>
  </div>

  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Kaydet</button>
    <a href="{{ route('panel.kullanici.index') }}" class="btn btn-outline-secondary">İptal</a>

    @if($kayit->exists && $kayit->id !== auth()->id())
      <button type="submit" form="silFormu" class="btn btn-outline-danger ms-auto"
              onclick="return confirm('{{ $kayit->ad_soyad }} silinecek. CRM kayıtları sahipsiz kalacak. Emin misiniz?')">
        <i class="bi bi-trash me-1"></i> Sil
      </button>
    @endif
  </div>
</form>

{{-- Silme formu ana formun DIŞINDA: iç içe <form> geçersizdir ve
     içindeki _method=delete dış forma karışıp "Kaydet"i silmeye çevirir. --}}
@if($kayit->exists && $kayit->id !== auth()->id())
  <form id="silFormu" method="post" action="{{ route('panel.kullanici.sil', $kayit) }}" class="d-none">
    @csrf @method('delete')
  </form>
@endif

@endsection
