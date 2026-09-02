@extends('site.layout')

@php use App\Models\Ayar; @endphp

@section('icerik')

@include('site.sayfa-basi', [
  'baslik'     => $yonetici->ad,
  'kirintilar' => [
    __('site.nav_kurumsal') => route('site.kurumsal'),
    __('site.yonetim')      => route('site.kurumsal') . '#yonetim',
    $yonetici->ad           => null,
  ],
])

<section>
  <div class="container">
    <div class="row g-5">

      <div class="col-lg-4">
        <div class="vm-card">
          @if($yonetici->foto)
            <img src="@gorsel($yonetici->foto)" class="w-100 rounded mb-3" alt="{{ $yonetici->ad }}">
          @endif

          <h4 style="font-size:1.15rem">{{ $yonetici->ad }}</h4>
          <p style="font-size:.9rem">{{ $yonetici->d('unvan') }}</p>

          @if($yonetici->linkedin)
            <a href="{{ $yonetici->linkedin }}" target="_blank" rel="noopener" class="btn btn-line w-100 mt-2">
              <i class="bi bi-linkedin me-1"></i> LinkedIn
            </a>
          @endif

          @if(Ayar::dilli('baskan_mesaji'))
            <a href="{{ route('site.baskan') }}" class="btn btn-orange w-100 mt-2">
              {{ Ayar::dilli('baskan_mesaj_baslik') }}
            </a>
          @endif
        </div>
      </div>

      <div class="col-lg-8">
        <div class="section-head" style="margin-bottom:1rem">
          <span class="mini">{{ $yonetici->d('unvan') }}</span>
          <h2>{{ $yonetici->ad }}</h2>
        </div>

        <div class="rich-text">@zengin($yonetici->d('ozgecmis'))</div>

        <a href="{{ route('site.kurumsal') }}" class="ac-link mt-4 d-inline-block">
          <i class="bi bi-arrow-left me-1"></i> @lang('site.geri')
        </a>
      </div>
    </div>
  </div>
</section>

@endsection
