{{--
  Ayarlar alt menüsü — tüm ayar bölümlerinin solunda durur.
  Kullanım: @include('panel.ayar._nav', ['bolumler' => ..., 'aktif' => 'genel'])

  Sınıflar panel.css'te "AYARLAR ALT MENÜSÜ" başlığı altında tanımlı.
--}}
@php
  // Bölümler başlıklarına göre gruplanır: Site / İletişim / ...
  // İkinci parametre (preserveKeys) ŞART — varsayılan false, anahtarları
  // 0,1,2'ye sıfırlar ve bağlantılar bölüm adı yerine sayıya gider.
  $gruplu = collect($bolumler)->groupBy('grup', true);
@endphp

<aside class="ayar-yan">
  @foreach($gruplu as $grupAdi => $ogeler)
    <div class="ayar-grup">{{ $grupAdi }}</div>

    @foreach($ogeler as $anahtar => $bolum)
      <a href="{{ route('panel.ayar.bolum', $anahtar) }}"
         class="{{ $aktif === $anahtar ? 'aktif' : '' }}">
        <i class="bi {{ $bolum['ikon'] }}"></i><span>{{ $bolum['ad'] }}</span>
      </a>
    @endforeach
  @endforeach
</aside>
