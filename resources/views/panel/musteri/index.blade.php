@extends('panel.layout')
@section('baslik', 'Müşteriler')

@section('icerik')

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <form method="get" class="d-flex flex-wrap gap-2 align-items-center">
    <input type="search" name="ara" value="{{ request('ara') }}" class="form-control form-control-sm"
           placeholder="Ad, e-posta, telefon, sektör…" style="min-width:230px">

    <select name="durum" class="form-select form-select-sm" style="width:auto">
      <option value="">Tüm durumlar</option>
      @foreach($durumlar as $anahtar => $etiket)
        <option value="{{ $anahtar }}" @selected(request('durum') === $anahtar)>{{ $etiket }}</option>
      @endforeach
    </select>

    @if($kullanicilar->isNotEmpty())
      <select name="sorumlu" class="form-select form-select-sm" style="width:auto">
        <option value="">Tüm sorumlular</option>
        @foreach($kullanicilar as $k)
          <option value="{{ $k->id }}" @selected((string) request('sorumlu') === (string) $k->id)>
            {{ $k->ad_soyad ?: $k->kullanici }}
          </option>
        @endforeach
      </select>
    @endif

    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>

    @if(request()->hasAny(['ara', 'durum', 'sorumlu']))
      <a href="{{ route('panel.musteri.index') }}" class="btn btn-sm btn-outline-secondary" title="Filtreyi temizle">
        <i class="bi bi-x-lg"></i>
      </a>
    @endif
  </form>

  <a href="{{ route('panel.musteri.olustur') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> Yeni Müşteri
  </a>
</div>

@if($musteriler->isEmpty())
  <div class="card p-5 text-center">
    <i class="bi bi-people" style="font-size:2.4rem;color:var(--cizgi-guclu)"></i>
    <p class="mt-3 mb-1" style="color:var(--metin-soluk)">
      @if(request()->hasAny(['ara', 'durum', 'sorumlu']))
        Bu filtreye uyan kayıt yok.
      @else
        Henüz müşteri kaydı yok. Siteden gelen iletişim mesajları da buraya düşecek.
      @endif
    </p>
  </div>
@else
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Firma / Kişi</th>
            <th>İletişim</th>
            <th>Sektör</th>
            <th class="text-center">Fırsat</th>
            <th>Sorumlu</th>
            <th>Durum</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        @foreach($musteriler as $m)
          <tr>
            <td>
              <a href="{{ route('panel.musteri.detay', $m) }}" class="fw-semibold">{{ $m->adi }}</a>
              @if($m->unvan)<div style="font-size:.78rem;color:var(--metin-sonuk)">{{ $m->unvan }}</div>@endif
            </td>
            <td style="font-size:.84rem">
              @if($m->email)<div><i class="bi bi-envelope me-1"></i>{{ $m->email }}</div>@endif
              @if($m->telefon)<div><i class="bi bi-telephone me-1"></i>{{ $m->telefon }}</div>@endif
              @unless($m->email || $m->telefon)<span style="color:var(--metin-sonuk)">—</span>@endunless
            </td>
            <td style="font-size:.85rem">{{ $m->sektor ?: '—' }}</td>
            <td class="text-center">
              @if($m->firsatlar_count)
                <span class="badge bg-secondary">{{ $m->firsatlar_count }}</span>
              @else
                <span style="color:var(--metin-sonuk)">—</span>
              @endif
            </td>
            <td style="font-size:.84rem">{{ $m->sorumlu?->ad_soyad ?? 'Atanmamış' }}</td>
            <td><span class="badge bg-secondary">{{ $durumlar[$m->durum] ?? $m->durum }}</span></td>
            <td class="text-end">
              <div class="d-flex gap-1 justify-content-end">
                <a href="{{ route('panel.musteri.detay', $m) }}"
                   class="btn btn-sm btn-outline-secondary" title="Detay">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="{{ route('panel.musteri.duzenle', $m) }}"
                   class="btn btn-sm btn-outline-secondary" title="Düzenle">
                  <i class="bi bi-pencil"></i>
                </a>
              </div>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">{{ $musteriler->links() }}</div>
@endif

@endsection
