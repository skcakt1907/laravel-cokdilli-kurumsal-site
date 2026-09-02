{{--
  Video oynatıcı — YouTube, Vimeo ve yüklenmiş dosyayı aynı çerçevede sunar.
  Kullanım: @include('parcalar.video', ['deger' => $yazi->video])

  Oran kutusu (.video-kutu) 16:9 sabitler; iframe ve <video> aynı davranır.
--}}
@php($medya = \App\Support\Medya::video($deger ?? null))

@if($medya)
  @if($medya['tip'] === 'dosya')
    <div class="video-kutu">
      {{-- preload=metadata: sayfa açılınca video indirilmez, sadece
           süre/boyut bilgisi alınır. Trafik için önemli. --}}
      <video controls preload="metadata" playsinline>
        <source src="{{ $medya['kaynak'] }}">
        Tarayıcınız video oynatmayı desteklemiyor.
        <a href="{{ $medya['kaynak'] }}">Videoyu indirin</a>.
      </video>
    </div>

  @elseif($medya['tip'] === 'baglanti')
    <a href="{{ $medya['kaynak'] }}" target="_blank" rel="noopener" class="btn btn-orange">
      <i class="bi bi-play-circle me-1"></i> @lang('site.videoyu_izle')
    </a>

  @else
    <div class="video-kutu">
      <iframe src="{{ $medya['kaynak'] }}"
              title="{{ $baslik ?? 'Video' }}"
              loading="lazy"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen></iframe>
    </div>
  @endif
@endif
