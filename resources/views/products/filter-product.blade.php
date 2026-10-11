@extends('layouts.app')

@section('content')
@php
    $q          = request('q');
    $activeSort = request('sort', 'popular');           // popular|newest|bestseller
    $activeCats = collect((array)request('categories'))->map(fn($v)=>(int)$v)->all();
@endphp

<section class="bg-[#f5f5f5] py-[70px]">
  <div class="container mx-auto px-4">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

      {{-- === SIDEBAR FILTER (kiri) — AUTO AJAX === --}}
      <aside class="lg:col-span-3">
        <form id="filterForm" class="space-y-6" autocomplete="off">
          {{-- Search (opsional; tinggal uncomment kalau mau) --}}
          {{--
          <div class="rounded-2xl bg-white p-4 shadow-[0_0_20px_rgba(0,0,0,0.04)]">
            <label class="block mb-2 text-sm font-medium text-slate-700">Cari Produk</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="Kata kunci..."
                   class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-brand-green/30">
          </div>
          --}}

          {{-- Urutkan --}}
          <div class="rounded-2xl bg-white p-4 shadow-[0_0_20px_rgba(0,0,0,0.04)]">
            <div class="mb-3 text-[18px] font-semibold text-slate-900">Urutkan</div>
            <div class="space-y-2 text-[16px]">
              @foreach (['popular'=>'Populer','newest'=>'Terbaru','bestseller'=>'Terlaris'] as $key => $label)
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="radio" name="sort" value="{{ $key }}" class="accent-brand-green"
                       {{ $activeSort === $key ? 'checked' : '' }}>
                <span>{{ $label }}</span>
              </label>
              @endforeach
            </div>
          </div>

          {{-- Kategori --}}
          <div class="rounded-2xl bg-white p-4 shadow-[0_0_20px_rgba(0,0,0,0.04)]">
            <div class="mb-3 text-[18px] font-semibold text-slate-900">Kategori</div>
            <div class="max-h-[280px] overflow-auto pr-1 space-y-2 text-[16px]">
              @foreach ($categories as $cat)
              <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="categories[]" value="{{ $cat->id }}" class="accent-brand-green"
                       {{ in_array($cat->id, $activeCats) ? 'checked' : '' }}>
                <span>{{ $cat->name }}</span>
              </label>
              @endforeach
            </div>
          </div>
          {{-- tidak ada tombol Terapkan/Reset; semua otomatis --}}
        </form>
      </aside>

      {{-- === LIST PRODUK (kanan) === --}}
      <section class="lg:col-span-9">
        

        {{-- GRID produk (maks 9) --}}
        <div id="productGrid">
        @include('products._grid', ['products' => $products])
        </div>

        <div id="productPagination">
        @include('products._pagination', ['products' => $products])
        </div>
      </section>

    </div>
  </div>
</section>

@push('scripts')
<script>
(function(){
  // Serialize form -> querystring
  function serializeForm(form){
    const fd = new FormData(form);
    const params = new URLSearchParams();
    for (const [k, v] of fd.entries()){
      if (v === null || v === '') continue;
      params.append(k, v);
    }
    return params.toString();
  }

  async function applyAjax(url){
    const grid = document.getElementById('productGrid');
    const pag  = document.getElementById('productPagination');
    const info = document.getElementById('resultInfo');

    try {
      const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();
      grid.innerHTML = data.grid;
      pag.innerHTML  = data.pagination ?? '';
      if (info) info.textContent = `Menampilkan ${data.showing} dari ${data.total} produk`;
      // bind ulang pagination
      bindPagination();
      // update URL tanpa reload
      window.history.replaceState({}, '', url);
    } catch (e) {
      console.error('Gagal memuat produk:', e);
    }
  }

  function fireFromForm(){
    const form = document.getElementById('filterForm');
    const qs   = serializeForm(form);
    const base = "{{ route('products.filter') }}";
    const url  = qs ? `${base}?${qs}` : base;
    applyAjax(url);
  }

  // debounce untuk input search (kalau kamu aktifkan search)
  function debounce(fn, delay=350){
    let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn(...args), delay); };
  }

  function bindEvents(){
    const form = document.getElementById('filterForm');
    if (!form) return;
    // radio & checkbox auto-fire
    form.addEventListener('change', (e)=>{
      if (e.target.matches('input[type="radio"], input[type="checkbox"]')) fireFromForm();
    });
    // search (kalau ada field name="q", aktifkan debounce)
    const q = form.querySelector('input[name="q"]');
    if (q) q.addEventListener('input', debounce(fireFromForm, 400));
  }

  // Delegasi klik pagination (intercept <a href="...">)
  function bindPagination(){
    const wrapper = document.getElementById('productPagination');
    if (!wrapper) return;
    wrapper.querySelectorAll('a').forEach(a=>{
      a.addEventListener('click', function(e){
        e.preventDefault();
        const url = this.getAttribute('href');
        if (url) applyAjax(url);
      }, { once: true }); // hindari multiple binding
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    bindEvents();
    bindPagination();
  });
})();
</script>
@endpush

@endsection
