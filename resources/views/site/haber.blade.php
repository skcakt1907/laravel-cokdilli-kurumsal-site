@extends('site.layout')

@section('icerik')

@include('site.sayfa-basi', [
  'baslik'     => $haber->d('baslik'),
  'kirintilar' => [
    __('site.nav_haberler') => route('site.haberler'),
    $haber->d('baslik')     => null,
  ],
])

<section>
  <div class="container">
    <div class="row g-5">

      <div class="col-lg-8">
        {{-- Video girilmişse kapak görselinin yerine geçer --}}
        @if($haber->video)
          @include('parcalar.video', ['deger' => $haber->video, 'baslik' => $haber->d('baslik')])
        @elseif($haber->gorsel)
          <img src="@gorsel($haber->gorsel)" class="w-100 rounded mb-4" alt="{{ $haber->d('baslik') }}">
        @endif

        <div class="meta mb-3" style="color:var(--gray)">
          <i class="bi bi-calendar3 me-1"></i>@tarih($haber->tarih)
          @if($haber->d('kategori'))
            <span class="ms-3"><i class="bi bi-tag me-1"></i>{{ $haber->d('kategori') }}</span>
          @endif
        </div>

        <p class="lead" style="color:var(--gray)">{{ $haber->d('ozet') }}</p>
        <div class="rich-text">@zengin($haber->d('icerik'))</div>

        <a href="{{ route('site.haberler') }}" class="ac-link mt-4 d-inline-block">
          <i class="bi bi-arrow-left me-1"></i> @lang('site.geri')
        </a>
      </div>

      <div class="col-lg-4">
        @if($digerleri->isNotEmpty())
          <div class="yan-liste">
            <h4>@lang('site.nav_haberler')</h4>

            @foreach($digerleri as $diger)
              <a class="yl-oge" href="{{ route('site.haber', $diger->slug) }}">
                <i class="bi bi-newspaper"></i>
                <span>{{ $diger->d('baslik') }}</span>
              </a>
            @endforeach

            <a class="yl-tumu" href="{{ route('site.haberler') }}">
              @lang('site.tumu') <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        @endif

        <div class="vm-card text-center mt-4">
          <i class="bi bi-chat-dots"></i>
          <h4 style="font-size:1.05rem">@lang('site.bize_ulasin')</h4>
          <p class="mb-3">@lang('site.iletisim_alt')</p>
          <a href="{{ route('site.iletisim') }}" class="btn btn-orange w-100">@lang('site.nav_iletisim')</a>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
