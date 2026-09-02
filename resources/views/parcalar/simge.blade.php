{{--
  Sekme simgesi (favicon).
  Site, panel ve panel girişi bu tek parçayı kullanır — üç ayrı yerde
  aynı bağlantıları tutmak yerine.

  Panelden simge yüklendiyse (Ayarlar > Logo ve Simge) o kullanılır;
  yüklenmemişse pakette gelen varsayılan altın amblem devreye girer.

  ?v= şart: tarayıcılar sekme simgesini çok uzun süre önbellekte tutar,
  damgasız yeni simge günlerce görünmez.
--}}
@php
  use App\Support\SimgeUret;

  $simge = trim(\App\Models\Ayar::al('favicon'));
  $ozel  = $simge !== '' && is_file(public_path($simge));

  if ($ozel) {
      $surum = @filemtime(public_path($simge));
      $ico   = null;                                   // yüklenen simgede .ico yok
      $k16   = asset(SimgeUret::boyut($simge, 16));
      $k32   = asset(SimgeUret::boyut($simge, 32));
      $k180  = asset(SimgeUret::boyut($simge, 180));
  } else {
      $surum = @filemtime(public_path('img/favicon-32.png'));
      $ico   = asset('favicon.ico');
      $k16   = asset('img/favicon-16.png');
      $k32   = asset('img/favicon-32.png');
      $k180  = asset('img/favicon-180.png');
  }
@endphp

@if($ico)
  <link rel="icon" href="{{ $ico }}?v={{ $surum }}" sizes="any">
@endif
<link rel="icon" type="image/png" sizes="32x32" href="{{ $k32 }}?v={{ $surum }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ $k16 }}?v={{ $surum }}">
<link rel="apple-touch-icon" href="{{ $k180 }}?v={{ $surum }}">
