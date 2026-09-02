@extends('site.layout')

@php use App\Support\Metin; @endphp

@section('icerik')

@include('site.sayfa-basi', [
  'baslik'     => $alan->d('baslik'),
  'kirintilar' => [
    __('site.nav_faaliyet') => route('site.faaliyetler'),
    $alan->d('grup')        => route('site.faaliyetler') . '#' . Metin::slug($alan->grup),
    $alan->d('baslik')      => null,
  ],
])

<section>
  <div class="container">
    <div class="row g-5">

      {{-- Sol: içerik --}}
      <div class="col-lg-8">
        @if($alan->gorsel)
          <img src="@gorsel($alan->gorsel)" class="w-100 rounded mb-4" alt="{{ $alan->d('baslik') }}">
        @endif

        <div class="section-head" style="margin-bottom:1rem">
          <span class="mini">{{ $alan->d('grup') }}</span>
          <h2>{{ $alan->d('baslik') }}</h2>
        </div>

        <p class="lead" style="color:var(--gray)">{{ $alan->d('ozet') }}</p>
        <div class="rich-text">@zengin($alan->d('icerik'))</div>

        {{-- Bu alanda faaliyet gösteren şirket / ortaklar --}}
        @if($ortaklar->isNotEmpty())
          <div class="section-head mt-5" style="margin-bottom:1rem">
            <span class="mini">@lang('site.isbirligi_alt')</span>
            <h3 style="font-size:1.3rem">@lang('site.nav_ortaklar')</h3>
          </div>

          <div class="row g-3">
            @foreach($ortaklar as $ortak)
              <div class="col-md-6">
                <a class="company-card h-100 d-block" href="{{ route('site.ortak', $ortak->slug) }}">
                  @if($ortak->gorsel)
                    <div class="cc-img"><img src="@gorsel($ortak->gorsel)" alt="{{ $ortak->d('baslik') }}"></div>
                  @endif
                  <div class="p-3">
                    <h5>{{ $ortak->d('baslik') }}</h5>
                    <span class="cc-sector">{{ $ortak->d('kategori') }}</span>
                  </div>
                </a>
              </div>
            @endforeach
          </div>
        @endif
      </div>

      {{-- Sağ: aynı ailedeki diğer alanlar + iletişim --}}
      <div class="col-lg-4">
        @if($kardesler->isNotEmpty())
          {{-- .yan-liste kartın kendisidir; ayrıca .vm-card ile sarılmaz --}}
          <div class="yan-liste mb-4">
            <h4>{{ $alan->d('grup') }}</h4>

            @foreach($kardesler as $kardes)
              <a class="yl-oge" href="{{ route('site.faaliyet', $kardes->slug) }}">
                <i class="bi {{ $kardes->ikon }}"></i>
                <span>{{ $kardes->d('baslik') }}</span>
              </a>
            @endforeach

            <a class="yl-tumu" href="{{ route('site.faaliyetler') }}">
              @lang('site.tum_sektorler') <i class="bi bi-arrow-right"></i>
            </a>
          </div>
        @endif

        {{-- birim parametresi: form bu sektörün ailesiyle ön seçili açılır,
             mesaj doğrudan o ailenin e-posta adresine gider. --}}
        <div class="vm-card text-center">
          <i class="bi bi-chat-dots"></i>
          <h4 style="font-size:1.05rem">@lang('site.bize_ulasin')</h4>
          <p class="mb-3">@lang('site.iletisim_alt')</p>
          <a href="{{ route('site.iletisim', ['birim' => $alan->grup]) }}"
             class="btn btn-orange w-100">@lang('site.nav_iletisim')</a>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
