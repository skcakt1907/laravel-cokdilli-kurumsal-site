@extends('panel.layout')
@section('baslik', 'Mesaj')

@section('icerik')

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card p-4">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h5 class="mb-1" style="color:#fff">{{ $mesaj->konu ?: 'Konusuz mesaj' }}</h5>
          <div style="font-size:.84rem;color:var(--metin-sonuk)">
            {{ $mesaj->tarih?->format('d.m.Y H:i') }}
          </div>
        </div>
        <a href="{{ route('panel.mesaj.index') }}" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Listeye dön
        </a>
      </div>

      <dl class="row mb-4" style="font-size:.88rem">
        <dt class="col-sm-3">Gönderen</dt>
        <dd class="col-sm-9">{{ $mesaj->ad }}</dd>

        <dt class="col-sm-3">E-posta</dt>
        <dd class="col-sm-9"><a href="mailto:{{ $mesaj->mail }}">{{ $mesaj->mail }}</a></dd>

        <dt class="col-sm-3">Telefon</dt>
        <dd class="col-sm-9">
          @if($mesaj->tel)<a href="tel:{{ $mesaj->tel }}">{{ $mesaj->tel }}</a>@else — @endif
        </dd>

        <dt class="col-sm-3">İlgili alan</dt>
        <dd class="col-sm-9">{{ $mesaj->birim ?: 'Genel' }}</dd>

        {{-- Bildirimin hangi adrese gittiği: "bana gelmedi" tartışmasını bitirir. --}}
        <dt class="col-sm-3">Bildirim</dt>
        <dd class="col-sm-9">
          @if($mesaj->gonderildi)
            <i class="bi bi-check-circle me-1" style="color:#4caf50"></i>{{ $mesaj->gonderildi }}
          @else
            <span style="color:var(--metin-sonuk)">
              <i class="bi bi-dash-circle me-1"></i>gönderilmedi
            </span>
          @endif
        </dd>
      </dl>

      <div style="white-space:pre-line;line-height:1.7">{{ $mesaj->mesaj }}</div>

      <div class="mt-4 d-flex gap-2 flex-wrap">
        <a href="mailto:{{ $mesaj->mail }}?subject={{ rawurlencode('Re: ' . ($mesaj->konu ?: '')) }}"
           class="btn btn-primary">
          <i class="bi bi-reply me-1"></i> Cevapla
        </a>

        <form method="post" action="{{ route('panel.mesaj.okunmadi', $mesaj) }}">
          @csrf
          <button class="btn btn-outline-secondary">
            <i class="bi bi-envelope me-1"></i> Okunmadı işaretle
          </button>
        </form>

        @if(auth()->user()->yoneticiMi())
          <form method="post" action="{{ route('panel.mesaj.sil', $mesaj) }}" class="ms-auto"
                onsubmit="return confirm('Bu mesaj silinecek. Emin misiniz?')">
            @csrf @method('delete')
            <button class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i> Sil</button>
          </form>
        @endif
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card p-4">
      <h6 style="color:var(--ap);font-size:.75rem;letter-spacing:1.4px;text-transform:uppercase">
        CRM Kaydı
      </h6>

      @if($musteri)
        <p class="mt-2 mb-3" style="font-size:.87rem">
          Bu kişi CRM'de kayıtlı:
        </p>
        <a href="{{ route('panel.musteri.detay', $musteri) }}" class="btn btn-primary w-100">
          <i class="bi bi-person-lines-fill me-1"></i> {{ $musteri->adi }}
        </a>
        <div class="mt-2" style="font-size:.8rem;color:var(--metin-sonuk)">
          Durum: {{ $musteri->durum }} · Kaynak: {{ $musteri->kaynak ?: '—' }}
        </div>
      @else
        <p class="mt-2 mb-3" style="font-size:.87rem;color:var(--metin-sonuk)">
          Bu mesaj için CRM kaydı bulunamadı. Otomatik aktarım sırasında bir sorun olmuş olabilir.
        </p>
        <form method="post" action="{{ route('panel.mesaj.crm', $mesaj) }}">
          @csrf
          <button class="btn btn-primary w-100">
            <i class="bi bi-person-plus me-1"></i> CRM'e aktar
          </button>
        </form>
      @endif
    </div>
  </div>
</div>

@endsection
