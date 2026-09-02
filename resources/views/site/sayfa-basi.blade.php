{{--
  Ortak sayfa başlığı (page-head) — iç sayfaların üstündeki başlık + kırıntı.
  Beklenen: $baslik, $kirintilar (['etiket' => url|null, ...])
--}}
<section class="page-head">
  <div class="container">
    <h1>{{ $baslik }}</h1>
    <div class="crumb">
      <a href="{{ route('site.anasayfa') }}">@lang('site.nav_anasayfa')</a>
      @foreach($kirintilar as $etiket => $adres)
        &rsaquo;
        @if($adres)
          <a href="{{ $adres }}">{{ $etiket }}</a>
        @else
          {{ $etiket }}
        @endif
      @endforeach
    </div>
  </div>
</section>
