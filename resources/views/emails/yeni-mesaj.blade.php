{{--
  Birim bildirim e-postası. Sade HTML: kurumsal posta istemcileri
  (Outlook, Roundcube) modern CSS'i budadığı için tablo + satır içi stil.
--}}
@php($vurgu = '#b48a3f')
<!doctype html>
<html lang="tr">
<head><meta charset="utf-8"><title>Yeni iletişim mesajı</title></head>
<body style="margin:0;padding:24px;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#222">

  <table role="presentation" cellpadding="0" cellspacing="0" width="100%"
         style="max-width:620px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden">

    <tr>
      <td style="background:#0b0b0b;padding:18px 24px">
        <div style="color:#fff;font-size:17px;font-weight:bold;letter-spacing:.5px">
          {{ \App\Models\Ayar::al('site_adi', 'FGG Holding') }}
        </div>
        @if($mesaj->birim)
          <div style="color:{{ $vurgu }};font-size:12px;margin-top:4px;text-transform:uppercase;letter-spacing:1px">
            {{ $mesaj->birim }}
          </div>
        @endif
      </td>
    </tr>

    <tr>
      <td style="padding:24px">
        <p style="margin:0 0 18px;font-size:15px">
          Web sitesindeki iletişim formundan yeni bir mesaj geldi.
        </p>

        <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="font-size:14px">
          @foreach ([
            'Gönderen'  => $mesaj->ad,
            'E-posta'   => $mesaj->mail,
            'Telefon'   => $mesaj->tel,
            'Konu'      => $mesaj->konu,
            'İlgili birim' => $mesaj->birim,
          ] as $etiket => $deger)
            @if($deger)
              <tr>
                <td style="padding:7px 12px 7px 0;color:#666;white-space:nowrap;vertical-align:top;width:120px">
                  {{ $etiket }}
                </td>
                <td style="padding:7px 0;border-bottom:1px solid #eee">
                  @if($etiket === 'E-posta')
                    <a href="mailto:{{ $deger }}" style="color:#0b5ed7">{{ $deger }}</a>
                  @else
                    {{ $deger }}
                  @endif
                </td>
              </tr>
            @endif
          @endforeach
        </table>

        <div style="margin:22px 0 0;padding:16px;background:#f7f7f8;border-left:3px solid {{ $vurgu }};
                    font-size:14px;line-height:1.7;white-space:pre-wrap">{{ $mesaj->mesaj }}</div>

        <p style="margin:22px 0 0;font-size:13px;color:#666">
          Bu mesaja doğrudan <strong>Yanıtla</strong> diyerek cevap verebilirsiniz;
          yanıt gönderene gider.
        </p>
      </td>
    </tr>

    <tr>
      <td style="padding:14px 24px;background:#fafafa;border-top:1px solid #eee;font-size:12px;color:#888">
        {{ \App\Models\Ayar::al('resmi_unvan', 'FGG Holding') }} —
        {{ \App\Support\Metin::tarih(optional($mesaj->tarih)->format('Y-m-d H:i:s')) }}
      </td>
    </tr>
  </table>

</body>
</html>
