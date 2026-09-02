@extends('panel.layout')
@section('baslik', 'Özel Alanlar')

@section('icerik')

<div class="d-flex justify-content-between align-items-start mb-3 gap-3 flex-wrap">
  <p class="mb-0" style="color:var(--metin-sonuk);font-size:.86rem;max-width:640px">
    Buradan eklediğiniz alan, ilgili formda otomatik belirir. Veritabanına dokunmaya gerek yok —
    ihtiyaç çıktıkça alan ekleyip çıkarabilirsiniz.
  </p>
  <a href="{{ route('panel.alan.olustur') }}" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg"></i> Yeni Alan
  </a>
</div>

@foreach($tablolar as $tabloAnahtar => $tabloBaslik)
  @php($liste = $alanlar[$tabloAnahtar] ?? collect())

  <div class="card mb-4">
    <div class="p-3" style="border-bottom:1px solid var(--cizgi)">
      <strong style="color:var(--ap);font-size:.78rem;letter-spacing:1.4px;text-transform:uppercase">
        {{ $tabloBaslik }} Formu
      </strong>
      <span class="ms-2" style="color:var(--metin-sonuk);font-size:.8rem">{{ $liste->count() }} alan</span>
    </div>

    @if($liste->isEmpty())
      <div class="p-4 text-center" style="color:var(--metin-sonuk);font-size:.86rem">
        Bu forma tanımlı özel alan yok.
      </div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Sıra</th><th>Alan Adı</th><th>Tip</th><th>Anahtar</th>
              <th class="text-center">Dolu Kayıt</th><th>Durum</th><th></th>
            </tr>
          </thead>
          <tbody>
          @foreach($liste as $alan)
            @php($adet = $kullanim[$alan->tablo . '|' . $alan->anahtar]->adet ?? 0)
            <tr @class(['opacity-50' => !$alan->durum])>
              <td style="color:var(--metin-sonuk)">{{ $alan->sira }}</td>
              <td>
                <span class="fw-semibold">{{ $alan->etiket }}</span>
                @if($alan->zorunlu)
                  <span class="badge bg-secondary ms-1" style="font-size:.62rem">zorunlu</span>
                @endif
                @if($alan->etiket_en)
                  <div style="font-size:.76rem;color:var(--metin-sonuk)">{{ $alan->etiket_en }}</div>
                @endif
              </td>
              <td style="font-size:.85rem">{{ $tipler[$alan->tip] ?? $alan->tip }}</td>
              <td><code style="font-size:.78rem;color:var(--metin-sonuk)">{{ $alan->anahtar }}</code></td>
              <td class="text-center">
                @if($adet)
                  <span class="badge bg-secondary">{{ $adet }}</span>
                @else
                  <span style="color:var(--metin-sonuk)">—</span>
                @endif
              </td>
              <td>
                @if($alan->durum)
                  <span class="badge" style="background:#2f6d3a;color:#fff">Formda</span>
                @else
                  <span class="badge" style="background:#5c3a38;color:#fff">Gizli</span>
                @endif
              </td>
              <td class="text-end">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="{{ route('panel.alan.duzenle', $alan) }}"
                     class="btn btn-sm btn-outline-secondary" title="Düzenle">
                    <i class="bi bi-pencil"></i>
                  </a>

                  <form method="post" action="{{ route('panel.alan.durum', $alan) }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary"
                            title="{{ $alan->durum ? 'Formdan kaldır' : 'Forma ekle' }}">
                      <i class="bi bi-{{ $alan->durum ? 'eye-slash' : 'eye' }}"></i>
                    </button>
                  </form>

                  @if($adet === 0)
                    <form method="post" action="{{ route('panel.alan.sil', $alan) }}"
                          onsubmit="return confirm('{{ $alan->etiket }} silinecek. Emin misiniz?')">
                      @csrf @method('delete')
                      <button class="btn btn-sm btn-outline-danger" title="Sil">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
@endforeach

<p style="color:var(--metin-sonuk);font-size:.8rem">
  <i class="bi bi-info-circle me-1"></i>
  Veri girilmiş bir alan silinemez — <strong>gizleyin</strong>. Formdan kalkar, geçmiş kayıtlardaki
  değerler korunur ve tekrar açtığınızda geri gelir.
</p>

@endsection
