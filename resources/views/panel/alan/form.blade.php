@extends('panel.layout')
@section('baslik', $alan->exists ? 'Alanı Düzenle' : 'Yeni Özel Alan')

@section('icerik')

<form method="post"
      action="{{ $alan->exists ? route('panel.alan.guncelle', $alan) : route('panel.alan.kaydet') }}"
      class="card p-4" style="max-width:760px" novalidate>
  @csrf
  @if($alan->exists) @method('put') @endif

  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label" for="tablo">Nereye Eklenecek <span style="color:var(--ap)">*</span></label>
      <select class="form-select" id="tablo" name="tablo" required @disabled($alan->exists)>
        @foreach($tablolar as $tabloAnahtar => $tabloBaslik)
          <option value="{{ $tabloAnahtar }}" @selected(old('tablo', $alan->tablo) === $tabloAnahtar)>
            {{ $tabloBaslik }} formu
          </option>
        @endforeach
      </select>
      @if($alan->exists)
        <input type="hidden" name="tablo" value="{{ $alan->tablo }}">
        <div class="form-text">Girilmiş veriler buna bağlı olduğu için sonradan değiştirilemez.</div>
      @endif
    </div>

    <div class="col-md-6">
      <label class="form-label" for="tip">Tip <span style="color:var(--ap)">*</span></label>
      <select class="form-select" id="tip" name="tip" required>
        @foreach($tipler as $tipAnahtar => $tipEtiket)
          <option value="{{ $tipAnahtar }}" @selected(old('tip', $alan->tip) === $tipAnahtar)>{{ $tipEtiket }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-6">
      <label class="form-label" for="etiket">Alan Adı <span style="color:var(--ap)">*</span></label>
      <input type="text" class="form-control" id="etiket" name="etiket"
             value="{{ old('etiket', $alan->etiket) }}" required maxlength="150" autofocus
             placeholder="ör. Proje Bütçesi">
    </div>

    <div class="col-md-6">
      <label class="form-label" for="etiket_en">İngilizce Adı</label>
      <input type="text" class="form-control" id="etiket_en" name="etiket_en"
             value="{{ old('etiket_en', $alan->etiket_en) }}" maxlength="150"
             placeholder="Project Budget">
      <div class="form-text">Boş bırakılırsa İngilizce sayfada da Türkçe adı görünür.</div>
    </div>

    <div class="col-12" id="secenekKutusu">
      <label class="form-label" for="secenekler">Seçenekler</label>
      <textarea class="form-control" id="secenekler" name="secenekler" rows="4"
                placeholder="Her satıra bir seçenek yazın">{{ old('secenekler', $alan->secenekler) }}</textarea>
      <div class="form-text">Yalnızca “Seçim listesi” tipinde kullanılır. Her satır bir seçenek.</div>
    </div>

    <div class="col-md-4">
      <label class="form-label" for="sira">Sıra</label>
      <input type="number" class="form-control" id="sira" name="sira" min="0" max="999"
             value="{{ old('sira', $alan->sira ?? 0) }}">
      <div class="form-text">Küçük olan üstte görünür.</div>
    </div>

    <div class="col-md-5 d-flex align-items-center">
      <div class="form-check mt-3">
        <input type="hidden" name="zorunlu" value="0">
        <input class="form-check-input" type="checkbox" id="zorunlu" name="zorunlu" value="1"
               @checked(old('zorunlu', $alan->zorunlu))>
        <label class="form-check-label" for="zorunlu">Doldurulması zorunlu</label>
      </div>
    </div>
  </div>

  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Kaydet</button>
    <a href="{{ route('panel.alan.index') }}" class="btn btn-outline-secondary">İptal</a>
  </div>
</form>

@endsection

@push('betik')
<script>
// Seçenekler kutusu yalnızca "Seçim listesi" tipinde anlamlı; diğerlerinde gizlenir.
(() => {
  const tip  = document.getElementById('tip');
  const kutu = document.getElementById('secenekKutusu');
  const guncelle = () => { kutu.style.display = tip.value === 'secim' ? '' : 'none'; };
  tip.addEventListener('change', guncelle);
  guncelle();
})();
</script>
@endpush
