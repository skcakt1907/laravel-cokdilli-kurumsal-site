@extends('site.layout')

@php use App\Models\Ayar; @endphp

@section('icerik')

@include('site.sayfa-basi', [
  'baslik'     => __('site.nav_ortaklar'),
  'kirintilar' => [__('site.nav_ortaklar') => null],
])

{{-- ===== GRUP ŞİRKETLERİ ===== --}}
@if($gruplar->isNotEmpty())
  <section>
    <div class="container">
      <div class="section-head center">
        <span class="mini">@lang('site.grup_sirketleri_alt')</span>
        <h2>@lang('site.grup_sirketleri')</h2>
      </div>

      <div class="row g-4">
        @foreach($gruplar as $sirket)
          @include('site.ortak-kart', ['ortak' => $sirket])
        @endforeach
      </div>
    </div>
  </section>
@endif

{{-- ===== İŞ ORTAKLARI ===== --}}
<section @if($gruplar->isNotEmpty()) style="background:var(--light)" @endif>
  <div class="container">
    <div class="section-head center">
      <span class="mini">@lang('site.is_ortaklari_alt')</span>
      <h2>@lang('site.is_ortaklari')</h2>
    </div>

    @if($ortaklar->isEmpty())
      <div class="empty-state text-center">
        <i class="bi bi-people"></i>
        <p>@lang('site.ortak_yok')</p>
      </div>
    @else
      <div class="row g-4">
        @foreach($ortaklar as $ortak)
          @include('site.ortak-kart', ['ortak' => $ortak])
        @endforeach
      </div>
    @endif
  </div>
</section>

<section class="cta-strip">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-8">
        <h3>@lang('site.isbirligi_baslik')</h3>
        <p>{{ Ayar::dilli('isbirligi') }}</p>
      </div>
      <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
        <a href="{{ route('site.iletisim') }}" class="btn">
          @lang('site.nav_iletisim') <i class="bi bi-arrow-right ms-2"></i>
        </a>
      </div>
    </div>
  </div>
</section>

@endsection
