@extends('site.layout')

@section('icerik')

@include('site.sayfa-basi', [
  'baslik'     => __('site.nav_haberler'),
  'kirintilar' => [__('site.nav_haberler') => null],
])

<section>
  <div class="container">
    @if($haberler->isEmpty())
      <div class="empty-state text-center">
        <i class="bi bi-newspaper"></i>
        <p>@lang('site.bulunamadi')</p>
      </div>
    @else
      <div class="row g-4">
        @foreach($haberler as $haber)
          <div class="col-lg-4 col-md-6">
            <div class="blog-card h-100">
              <div class="img">
                <img src="@gorsel($haber->gorsel)" alt="{{ $haber->d('baslik') }}" loading="lazy">
                @if($haber->d('kategori'))
                  <span class="cat">{{ $haber->d('kategori') }}</span>
                @endif
              </div>
              <div class="blog-body">
                <div class="meta"><i class="bi bi-calendar3"></i>@tarih($haber->tarih)</div>
                <h5><a href="{{ route('site.haber', $haber->slug) }}">{{ $haber->d('baslik') }}</a></h5>
                <p>{{ $haber->d('ozet') }}</p>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <div class="mt-4">{{ $haberler->links() }}</div>
    @endif
  </div>
</section>

@endsection
