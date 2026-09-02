@extends('panel.layout')
@section('baslik', $musteri->adi)

@push('stil')
<style>
  /* Sekme şeridi — İş Ortağım'daki gibi yatay, aktif olan altın çizgili */
  .sekme-serit{ display:flex; gap:.2rem; flex-wrap:wrap; border-bottom:1px solid var(--cizgi);
                margin-bottom:1.5rem; }
  .sekme-serit a{
    display:flex; align-items:center; gap:.45rem;
    padding:.7rem 1.1rem; color:var(--metin-soluk); text-decoration:none;
    font-size:.88rem; font-weight:500; border-bottom:2px solid transparent; margin-bottom:-1px;
  }
  .sekme-serit a:hover{ color:var(--metin); background:rgba(212,175,55,.05); }
  .sekme-serit a.aktif{ color:var(--ap); border-bottom-color:var(--ap); font-weight:600; }
  .sekme-serit .sayi{
    background:var(--yuzey-3); color:var(--metin-soluk);
    border-radius:20px; padding:.05em .5em; font-size:.72rem; font-weight:700;
  }
  .sekme-serit a.aktif .sayi{ background:rgba(212,175,55,.18); color:var(--ap); }
  .zaman-satir{ padding-bottom:1rem; margin-bottom:1rem; border-bottom:1px solid var(--cizgi); }
  .zaman-satir:last-child{ border-bottom:0; margin-bottom:0; padding-bottom:0; }
</style>
@endpush

@section('icerik')

{{-- ===== ÜST KÜNYE ===== --}}
<div class="card p-4 mb-3">
  <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
    <div>
      <h5 class="mb-1" style="color:#fff">{{ $musteri->adi }}</h5>
      @if($musteri->unvan)
        <div style="color:var(--metin-sonuk);font-size:.87rem">{{ $musteri->unvan }}</div>
      @endif
      <div class="mt-2 d-flex gap-3 flex-wrap" style="font-size:.85rem">
        @if($musteri->email)
          <a href="mailto:{{ $musteri->email }}"><i class="bi bi-envelope me-1"></i>{{ $musteri->email }}</a>
        @endif
        @if($musteri->telefon)
          <a href="tel:{{ $musteri->telefon }}"><i class="bi bi-telephone me-1"></i>{{ $musteri->telefon }}</a>
        @endif
      </div>
    </div>

    <div class="d-flex gap-2 align-items-start">
      <span class="badge bg-secondary">{{ $durumlar[$musteri->durum] ?? $musteri->durum }}</span>
      <a href="{{ route('panel.musteri.duzenle', $musteri) }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-pencil me-1"></i> Düzenle
      </a>
      @if(auth()->user()->yoneticiMi())
        <form method="post" action="{{ route('panel.musteri.sil', $musteri) }}"
              onsubmit="return confirm('{{ $musteri->adi }} ve bağlı tüm kayıtları silinecek. Emin misiniz?')">
          @csrf @method('delete')
          <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
        </form>
      @endif
    </div>
  </div>
</div>

{{-- ===== SEKME ŞERİDİ ===== --}}
<nav class="sekme-serit">
  @foreach($sekmeler as $anahtar => [$ikon, $etiket])
    <a href="{{ route('panel.musteri.detay', ['musteri' => $musteri, 'sekme' => $anahtar]) }}"
       class="{{ $aktifSekme === $anahtar ? 'aktif' : '' }}">
      <i class="bi {{ $ikon }}"></i>{{ $etiket }}
      @if(isset($sayilar[$anahtar]) && $sayilar[$anahtar] > 0)
        <span class="sayi">{{ $sayilar[$anahtar] }}</span>
      @endif
    </a>
  @endforeach
</nav>

{{-- ===== SEKME İÇERİĞİ ===== --}}
@switch($aktifSekme)

  @case('notlar')
    <div class="card p-4">
      <form method="post" action="{{ route('panel.musteri.not', $musteri) }}" class="mb-4">
        @csrf
        <textarea name="icerik" class="form-control mb-2" rows="3" maxlength="5000"
                  placeholder="Görüşme notu, hatırlatma, karar…" required></textarea>
        <button class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i> Not Ekle</button>
      </form>

      @forelse($musteri->notlar as $not)
        <div class="zaman-satir">
          <div class="d-flex justify-content-between" style="font-size:.76rem;color:var(--metin-sonuk)">
            <span>
              <i class="bi bi-person-circle me-1"></i>
              {{ $not->yazan?->ad_soyad ?? 'Site formundan' }}
            </span>
            <span>{{ $not->created_at?->format('d.m.Y H:i') }}</span>
          </div>
          <div class="mt-1" style="white-space:pre-line;font-size:.9rem">{{ $not->icerik }}</div>
        </div>
      @empty
        <p style="color:var(--metin-sonuk);font-size:.87rem;margin:0">Henüz not yok.</p>
      @endforelse
    </div>
    @break

  @case('firsatlar')
    <div class="card p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <span style="color:var(--metin-sonuk);font-size:.86rem">
          Bu müşteriye bağlı satış fırsatları.
        </span>
        <a href="{{ route('panel.firsat.olustur', ['musteri' => $musteri->id]) }}"
           class="btn btn-sm btn-primary">
          <i class="bi bi-plus-lg me-1"></i> Yeni Fırsat
        </a>
      </div>

      @forelse($musteri->firsatlar as $firsat)
        <div class="zaman-satir d-flex justify-content-between align-items-center gap-3">
          <div>
            <a href="{{ route('panel.firsat.duzenle', $firsat) }}" class="fw-semibold">{{ $firsat->baslik }}</a>
            @if($firsat->kapanis_tarihi)
              <div style="font-size:.78rem;color:var(--metin-sonuk)">
                <i class="bi bi-calendar-event me-1"></i>Tahmini kapanış:
                {{ $firsat->kapanis_tarihi->format('d.m.Y') }}
              </div>
            @endif
          </div>
          <div class="text-end" style="white-space:nowrap">
            <div style="color:var(--ap);font-weight:700">
              {{ number_format((float) $firsat->tutar, 0, ',', '.') }} {{ $firsat->para_birimi }}
            </div>
            @if($firsat->asama)
              <span class="badge" style="background:{{ $firsat->asama->renk ?: 'var(--yuzey-3)' }};color:#0b0b0b">
                {{ $firsat->asama->adi }}
              </span>
            @endif
          </div>
        </div>
      @empty
        <p style="color:var(--metin-sonuk);font-size:.87rem;margin:0">
          Fırsat yok. Görüşme somutlaştığında buradan bir fırsat açın; panoda takip edebilirsiniz.
        </p>
      @endforelse
    </div>
    @break

  @case('mesajlar')
    <div class="card p-4">
      <p style="color:var(--metin-sonuk);font-size:.86rem">
        Bu kişiden siteye gelen iletişim formu mesajları.
      </p>

      @forelse($mesajlar as $mesaj)
        <div class="zaman-satir">
          <div class="d-flex justify-content-between" style="font-size:.76rem;color:var(--metin-sonuk)">
            <span>{{ $mesaj->konu ?: 'Konusuz' }}</span>
            <span>{{ $mesaj->tarih?->format('d.m.Y H:i') }}</span>
          </div>
          <div class="mt-1" style="white-space:pre-line;font-size:.9rem">{{ $mesaj->mesaj }}</div>
          <a href="{{ route('panel.mesaj.detay', $mesaj) }}" class="ac-link"
             style="font-size:.8rem;color:var(--ap)">Mesaj sayfasına git</a>
        </div>
      @empty
        <p style="color:var(--metin-sonuk);font-size:.87rem;margin:0">
          Bu kişiden siteye gelmiş mesaj yok. Kayıt elle eklenmiş olabilir.
        </p>
      @endforelse
    </div>
    @break

  @default
    <div class="card p-4">
      <dl class="row mb-0" style="font-size:.88rem">
        <dt class="col-sm-3">Durum</dt>
        <dd class="col-sm-9">{{ $durumlar[$musteri->durum] ?? $musteri->durum }}</dd>

        <dt class="col-sm-3">Sektör</dt>
        <dd class="col-sm-9">{{ $musteri->sektor ?: '—' }}</dd>

        <dt class="col-sm-3">Kaynak</dt>
        <dd class="col-sm-9">{{ $musteri->kaynak ?: '—' }}</dd>

        <dt class="col-sm-3">Ülke</dt>
        <dd class="col-sm-9">{{ $musteri->ulke ?: '—' }}</dd>

        <dt class="col-sm-3">Sorumlu</dt>
        <dd class="col-sm-9">{{ $musteri->sorumlu?->ad_soyad ?? 'Atanmamış' }}</dd>

        <dt class="col-sm-3">Kayıt tarihi</dt>
        <dd class="col-sm-9">{{ $musteri->created_at?->format('d.m.Y H:i') }}</dd>

        @if($musteri->adres)
          <dt class="col-sm-3">Adres</dt>
          <dd class="col-sm-9" style="white-space:pre-line">{{ $musteri->adres }}</dd>
        @endif

        @foreach($ozelAlanlar as $alan)
          @php($deger = $ozelDegerler[$alan->anahtar] ?? null)
          @if($deger !== null && $deger !== '')
            <dt class="col-sm-3">{{ $alan->etiketi() }}</dt>
            <dd class="col-sm-9">{{ $alan->tip === 'onay' ? ($deger === '1' ? 'Evet' : 'Hayır') : $deger }}</dd>
          @endif
        @endforeach
      </dl>
    </div>
@endswitch

@endsection
