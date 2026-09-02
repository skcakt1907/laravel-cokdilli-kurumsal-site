@extends('site.layout')

@php use App\Support\Metin; @endphp

@section('icerik')

@include('site.sayfa-basi', [
  'baslik'     => __('site.nav_faaliyet'),
  'kirintilar' => [__('site.nav_faaliyet') => null],
])

{{-- Sektör ailelerine hızlı geçiş şeridi --}}
@if($aileler->isNotEmpty())
  <div class="anchor-bar">
    <div class="container">
      @foreach($aileler as $grupAdi => $alanlar)
        <a href="#{{ Metin::slug($grupAdi) }}">{{ $alanlar->first()->d('grup') }}</a>
      @endforeach
    </div>
  </div>
@endif

@forelse($aileler as $grupAdi => $alanlar)
  <section id="{{ Metin::slug($grupAdi) }}" @if($loop->odd) style="background:var(--light)" @endif>
    <div class="container">
      <div class="section-head">
        <span class="mini">{{ $alanlar->count() }} @lang('site.sayac_sektor')</span>
        <h2>{{ $alanlar->first()->d('grup') }}</h2>
      </div>

      <div class="row g-4">
        @foreach($alanlar as $alan)
          <div class="col-lg-4 col-md-6">
            <a class="area-card h-100 d-block" href="{{ route('site.faaliyet', $alan->slug) }}">
              <div class="ac-icon"><i class="bi {{ $alan->ikon }}"></i></div>
              <h4>{{ $alan->d('baslik') }}</h4>
              <p>{{ $alan->d('ozet') }}</p>
              <span class="ac-link">@lang('site.incele') <i class="bi bi-arrow-right"></i></span>
            </a>
          </div>
        @endforeach
      </div>
    </div>
  </section>
@empty
  <section>
    <div class="container">
      <div class="empty-state text-center">
        <i class="bi bi-diagram-3"></i>
        <p>@lang('site.bulunamadi')</p>
      </div>
    </div>
  </section>
@endforelse

@endsection
