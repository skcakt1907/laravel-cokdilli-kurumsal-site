@extends('panel.layout')
@section('baslik', $yazi->exists ? 'Haberi Düzenle' : 'Yeni Haber')

@section('icerik')

<form method="post" enctype="multipart/form-data" novalidate
      action="{{ $yazi->exists ? route('panel.yazi.guncelle', $yazi) : route('panel.yazi.kaydet') }}">
  @csrf
  @if($yazi->exists) @method('put') @endif

  <div class="card p-4">
    <div class="row g-3">

      @include('panel.ortak.dilli-alan', [
        'ad' => 'baslik', 'etiket' => 'Başlık', 'kayit' => $yazi, 'zorunlu' => true,
      ])

      @include('panel.ortak.dilli-alan', [
        'ad' => 'kategori', 'etiket' => 'Kategori', 'kayit' => $yazi,
      ])

      @include('panel.ortak.dilli-alan', [
        'ad' => 'ozet', 'etiket' => 'Özet', 'kayit' => $yazi, 'tip' => 'uzun', 'satir' => 2,
        'ipucu' => 'Kartlarda ve arama sonuçlarında görünür.',
      ])

      @include('panel.ortak.dilli-alan', [
        'ad' => 'icerik', 'etiket' => 'İçerik', 'kayit' => $yazi, 'tip' => 'uzun', 'satir' => 12,
        'ipucu' => 'Boş satır yeni paragraf açar. "- " ile madde, "## " ile ara başlık yazılır.',
      ])

      <div class="col-md-4">
        <label class="form-label" for="f_tarih">Yayın Tarihi <span style="color:var(--ap)">*</span></label>
        <input type="date" class="form-control" id="f_tarih" name="tarih" required
               value="{{ old('tarih', $yazi->tarih?->format('Y-m-d')) }}">
      </div>

      @include('panel.ortak.gorsel-alan', [
        'kayit' => $yazi, 'etiket' => 'Kapak Görseli',
      ])

      @include('panel.ortak.video-alan', [
        'deger' => $yazi->video, 'etiket' => 'Video (isteğe bağlı)',
      ])

      <div class="col-12">
        <div class="form-check">
          <input type="hidden" name="durum" value="0">
          <input class="form-check-input" type="checkbox" name="durum" value="1" id="f_durum"
                 @checked(old('durum', $yazi->exists ? $yazi->durum : true))>
          <label class="form-check-label" for="f_durum">Sitede yayında</label>
        </div>
        <div class="form-text">İşaretlenmezse taslak olarak kalır, sitede görünmez.</div>

        @if($yazi->exists)
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" name="slug_yenile" value="1" id="f_slug">
            <label class="form-check-label" for="f_slug" style="font-size:.86rem">
              Adresi başlığa göre yenile
            </label>
          </div>
          <div class="form-text">Şu anki adres: <code>/haberler/{{ $yazi->slug }}</code></div>
        @endif
      </div>
    </div>
  </div>

  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Kaydet</button>
    <a href="{{ route('panel.yazi.index') }}" class="btn btn-outline-secondary">İptal</a>

    @if($yazi->exists)
      <button type="submit" form="silFormu" class="btn btn-outline-danger ms-auto"
              onclick="return confirm('{{ $yazi->baslik }} silinecek. Emin misiniz?')">
        <i class="bi bi-trash me-1"></i> Sil
      </button>
    @endif
  </div>
</form>

{{-- Silme formu ana formun DIŞINDA: iç içe <form> geçersizdir ve
     içindeki _method=delete dış forma karışıp "Kaydet"i silmeye çevirir. --}}
@if($yazi->exists)
  <form id="silFormu" method="post" action="{{ route('panel.yazi.sil', $yazi) }}" class="d-none">
    @csrf @method('delete')
  </form>
@endif

@endsection
