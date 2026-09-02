@extends('site.layout')

@section('icerik')

@include('site.sayfa-basi', [
  'baslik'     => $ortak->d('baslik'),
  'kirintilar' => [
    __('site.nav_ortaklar') => route('site.ortaklar'),
    $ortak->d('baslik')     => null,
  ],
])

<section>
  <div class="container">
    <div class="row g-5">

      <div class="col-lg-8">
        @if($ortak->gorsel)
          <div class="cc-img mb-4" style="border-radius:12px;overflow:hidden">
            <img src="@gorsel($ortak->gorsel)" alt="{{ $ortak->d('baslik') }}" class="w-100">
          </div>
        @endif

        <div class="section-head" style="margin-bottom:1rem">
          <span class="mini">
            {{ $ortak->tur === 'grup' ? __('site.grup_sirketleri') : __('site.is_ortaklari') }}
          </span>
          <h2>{{ $ortak->d('baslik') }}</h2>
        </div>

        <div class="rich-text">@zengin($ortak->d('aciklama'))</div>
      </div>

      <div class="col-lg-4">
        <div class="vm-card mb-4">
          <h4 style="font-size:1.05rem">{{ $ortak->d('baslik') }}</h4>

          <dl class="row mb-0 mt-3" style="font-size:.88rem">
            @if($ortak->d('kategori'))
              <dt class="col-5">@lang('site.sektor')</dt>
              <dd class="col-7">{{ $ortak->d('kategori') }}</dd>
            @endif

            @if($ortak->d('ulke'))
              <dt class="col-5">@lang('site.ulke')</dt>
              <dd class="col-7">{{ $ortak->d('ulke') }}</dd>
            @endif

            @if($ortak->tarih)
              <dt class="col-5">@lang('site.kurulus_yili')</dt>
              <dd class="col-7">{{ $ortak->tarih }}</dd>
            @endif
          </dl>

          @if($ortak->website)
            <a href="{{ $ortak->website }}" target="_blank" rel="noopener"
               class="btn btn-line w-100 mt-3">
              <i class="bi bi-box-arrow-up-right me-1"></i> @lang('site.web_sitesi')
            </a>
          @endif
        </div>

        <a href="{{ route('site.ortaklar') }}" class="ac-link">
          <i class="bi bi-arrow-left me-1"></i> @lang('site.geri')
        </a>
      </div>
    </div>

    {{-- Aynı türdeki diğer kayıtlar --}}
    @if($digerleri->isNotEmpty())
      <div class="section-head center mt-5">
        <h3 style="font-size:1.4rem">
          {{ $ortak->tur === 'grup' ? __('site.grup_sirketleri') : __('site.is_ortaklari') }}
        </h3>
      </div>
      <div class="row g-4">
        @foreach($digerleri as $diger)
          @include('site.ortak-kart', ['ortak' => $diger])
        @endforeach
      </div>
    @endif
  </div>
</section>

@endsection
