@extends('layouts.app')

@push('styles')
<link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>
@endpush

@section('content')
<section class="py-[40px] md:py-[60px] bg-slate-50">
  <div class="max-w-6xl mx-auto px-4 md:px-0 grid gap-6 md:grid-cols-[1.5fr_1fr]">

    {{-- ========================= LEFT: ALAMAT + PENGIRIMAN ========================= --}}
    <div class="space-y-6">
      <h1 class="text-[22px] md:text-[26px] font-semibold">Checkout</h1>

      {{-- ALAMAT PENGIRIMAN --}}
      <div class="rounded-2xl bg-white p-5 shadow-md">
        <h2 class="text-[18px] md:text-[20px] font-semibold mb-4">Alamat pengiriman</h2>

        {{-- Nama & Telepon --}}
        <div class="grid gap-4 md:grid-cols-2 mb-4">
          <div class="space-y-1">
            <p class="text-sm font-medium text-slate-700">Nama lengkap</p>
            <div class="w-full rounded-xl bg-slate-100 text-[14px] md:text-[15px] px-4 py-2.5 text-slate-700">
              {{ auth()->user()->name ?? 'Nama belum diisi' }}
            </div>
            <input type="hidden" id="receiver_name" name="receiver_name" form="checkout-form"
                  value="{{ auth()->user()->name ?? '' }}">
          </div>
          <div class="space-y-1">
            <p class="text-sm font-medium text-slate-700">Nomor Telepon</p>
            <input id="receiver_phone" name="receiver_phone" type="text"
                  form="checkout-form"
                  value="{{ old('receiver_phone') }}"
                  placeholder="+62 ..."
                  class="w-full rounded-xl bg-slate-100 border border-transparent px-4 py-2.5 text-[14px] md:text-[15px] text-slate-700 focus:bg-white focus:border-brand-green focus:ring-brand-green">
          </div>
        </div>

        {{-- BAR ATAS: teks Provinsi, Kota, Kecamatan, Kode Pos --}}
        <div class="space-y-2">
          <p class="text-sm font-medium text-slate-700">
            Provinsi, Kota, Kecamatan, Kode Pos
          </p>

          <button type="button"
                  id="btn-area-display"
                  class="w-full flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-left text-[13px] md:text-[14px] text-slate-700">
            <span id="area_display_text" class="truncate">
              Pilih lokasi (provinsi, kota, kecamatan, kode pos)
            </span>
            <i class="bi bi-chevron-down text-slate-500 text-sm"></i>
          </button>

          {{-- EDITOR: search alamat (Google Places) --}}
          <div id="area_editor"
              class="mt-1 rounded-2xl border border-slate-200 bg-slate-50 overflow-hidden hidden">

            {{-- “Tab” header (hanya visual, tidak pakai data sendiri) --}}
            <div id="area_tabs"
                class="grid grid-cols-4 text-[11px] md:text-[12px] text-center bg-white border-b border-slate-200">
              <div class="py-2 border-b-2 border-brand-green font-semibold text-slate-900">Provinsi</div>
              <div class="py-2 text-slate-500">Kota</div>
              <div class="py-2 text-slate-500">Kecamatan</div>
              <div class="py-2 text-slate-500">Kode Pos</div>
            </div>

            {{-- List suggestion dari Google Places --}}
            <div id="area_list" class="max-h-60 overflow-y-auto bg-slate-50 text-[13px] md:text-[14px]">
              <p class="px-4 py-3 text-slate-500">
                Ketik nama jalan / kecamatan / kota di kolom pencarian di bawah ini, lalu pilih salah satu alamat.
              </p>
            </div>

            {{-- Input search yang di-attach ke Google Places Autocomplete --}}
            <div class="px-4 py-3 bg-white border-t border-slate-200 flex items-center gap-2">
              <i class="bi bi-search text-slate-400 text-sm"></i>
              <input id="area_search"
                    type="text"
                    placeholder="Cari alamat (contoh: Jl. Melati, Pasar Minggu, Jakarta)"
                    class="w-full border-none bg-transparent text-[13px] md:text-[14px] text-slate-700 focus:ring-0">
            </div>
          </div>
        </div>

        {{-- INPUT HIDDEN YANG DIKIRIM KE SERVER (HARUS SAMA DENGAN KOLOM DI TABLE ORDERS) --}}
        <input type="hidden" id="receiver_province"    name="receiver_province"    form="checkout-form">
        <input type="hidden" id="receiver_city"        name="receiver_city"        form="checkout-form">
        <input type="hidden" id="receiver_district"    name="receiver_district"    form="checkout-form">
        <input type="hidden" id="receiver_postal_code" name="receiver_postal_code" form="checkout-form">
        <input type="hidden" id="receiver_latitude"    name="receiver_latitude"    form="checkout-form">
        <input type="hidden" id="receiver_longitude"   name="receiver_longitude"   form="checkout-form">

        {{-- NAMA JALAN & DETAIL --}}
        <div class="mt-4 grid gap-4 md:grid-cols-[1.4fr_0.6fr]">
          <div class="space-y-1">
            <label class="block text-sm font-medium text-slate-700">
              Nama jalan, Gedung, No. Rumah
            </label>
            <input id="receiver_address_line1" name="receiver_address_line1" type="text" form="checkout-form"
                  class="w-full rounded-xl bg-slate-100 border border-transparent px-4 py-2.5 text-[13px] md:text-[14px] text-slate-700 focus:bg-white focus:border-brand-green focus:ring-brand-green"
                  placeholder="Jl. Melati No. 12, Ruko Green Garden Blok B">
          </div>

          <div class="space-y-1">
            <label class="block text-sm font-medium text-slate-700">
              Detail lainnya (Cth: Blok/ Unit No., Patokan)
            </label>
            <input id="receiver_address_line2" name="receiver_address_line2" type="text" form="checkout-form"
                  class="w-full rounded-xl bg-slate-100 border border-transparent px-4 py-2.5 text-[13px] md:text-[14px] text-slate-700 focus:bg-white focus:border-brand-green focus:ring-brand-green"
                  placeholder="Blok A3, Dekat Indomaret, Sebelah Rumah Sakit">
          </div>
        </div>

        {{-- MAP --}}
        <div class="mt-4">
          <div id="map" class="h-[180px] md:h-[210px] w-full rounded-2xl overflow-hidden"></div>
          <p class="mt-2 text-[11px] md:text-[12px] text-slate-500">
            Pastikan alamat kamu lengkap dan benar agar kurir mudah menemukan lokasi pengiriman.
          </p>
        </div>

        {{-- SIMPAN ALAMAT --}}
        <div class="mt-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
          <label class="inline-flex items-center gap-2 text-[12px] md:text-[13px] text-slate-700">
            <input type="checkbox" id="save_address_check"
                  class="rounded border-slate-300 text-brand-green focus:ring-brand-green" checked>
            <span>Simpan alamat ini</span>
          </label>
          <input type="text" id="address_label"
                placeholder="Label alamat (Rumah, Kantor, dll)"
                class="mt-2 md:mt-0 w-full md:max-w-xs rounded-xl bg-slate-100 border border-transparent px-3 py-2 text-[12px] md:text-[13px] focus:bg-white focus:border-brand-green focus:ring-brand-green">
        </div>

        <div class="mt-4 flex gap-3">
          <button type="button" id="btn-cancel-address"
                  class="flex-1 md:flex-none md:px-6 h-11 rounded-2xl border border-slate-300 text-[13px] md:text-[14px] text-slate-700">
            Nanti saja
          </button>
          <button type="button" id="btn-save-address"
                  class="flex-1 md:flex-none md:px-6 h-11 rounded-2xl bg-brand-green text-white text-[13px] md:text-[14px] font-medium">
            Simpan
          </button>
        </div>
      </div>



      {{-- PILIH JASA PENGIRIMAN --}}
      <div class="rounded-2xl bg-white p-5 shadow-md">
        <div class="flex items-center justify-between gap-3 mb-4">
          <div>
            <h2 class="text-[18px] font-semibold">Pilih Jasa Pengiriman</h2>
            <p class="text-[13px] md:text-[14px] text-slate-500">
              Isi alamat terlebih dahulu lalu klik "Hitung Ongkir" untuk melihat pilihan kurir.
            </p>
          </div>
          <button id="btn-check-rates" type="button"
                  class="inline-flex items-center rounded-xl bg-brand-green px-4 py-2 text-[14px] font-medium text-white hover:bg-brand-greenDark">
            Hitung Ongkir
          </button>
        </div>

        {{-- pilihan terpilih (accordion header) --}}
        <div id="shipping-selected" class="mb-3 hidden rounded-xl border border-brand-green bg-emerald-50 px-4 py-3 text-[14px]">
          <div class="flex items-center justify-between gap-2">
            <div>
              <div class="font-semibold" id="selected-courier-name"></div>
              <div class="text-xs text-slate-600" id="selected-courier-etd"></div>
            </div>
            <div class="font-semibold" id="selected-courier-price"></div>
          </div>
        </div>

        {{-- list opsi kurir (accordion body) --}}
        <div id="shipping-options" class="space-y-2 text-[14px] md:text-[15px]">
          <p class="text-slate-500 text-sm">
            Belum ada opsi pengiriman. Silakan isi alamat & klik "Hitung Ongkir".
          </p>
        </div>

        {{-- hidden fields --}}
        <input type="hidden" form="checkout-form" id="shipping_courier_code" name="shipping_courier_code">
        <input type="hidden" form="checkout-form" id="shipping_courier_name" name="shipping_courier_name">
        <input type="hidden" form="checkout-form" id="shipping_price" name="shipping_price" value="0">
      </div>

      {{-- PESAN --}}
      <div class="rounded-2xl bg-white p-5 shadow-md">
        <label for="customer_notes" class="block text-sm font-medium text-slate-700 mb-2">
          Pesan (opsional)
        </label>
        <textarea id="customer_notes" name="customer_notes" rows="3"
                  form="checkout-form"
                  class="w-full rounded-xl border-slate-300 text-[14px] md:text-[15px] focus:border-brand-green focus:ring-brand-green"
                  placeholder="Contoh: Titip di satpam, paket boleh ditaruh di rak sepatu">{{ old('customer_notes') }}</textarea>
      </div>
    </div>

    {{-- ========================= RIGHT: PRODUK + RINGKASAN ========================= --}}
    <aside class="space-y-4">
      {{-- PRODUK DIPESAN --}}
      <div class="rounded-2xl bg-white p-5 shadow-md">
        <h2 class="text-[18px] font-semibold mb-4">Produk dipesan</h2>

        @php
          $items = $cart?->items ?? collect();
        @endphp

        @forelse ($items as $it)
          @php
            $p       = $it->product;
            $cat     = optional($p->category)->name ?: 'Kategori';
            $rating  = number_format($p->average_rating ?? 4.8, 1);
            $imgRel  = optional($p->images->first())->image_url;
            $img     = $imgRel && file_exists(public_path(ltrim($imgRel,'/')))
                        ? asset(ltrim($imgRel,'/'))
                        : asset('assets/img/placeholder-product.png');
            $unit    = $it->price_snapshot;
          @endphp

          <div class="mb-4 last:mb-0 rounded-2xl border border-slate-100 bg-white shadow-sm p-3 sm:p-4">
            <div class="grid grid-cols-[0.9fr_1.1fr] gap-4">
              {{-- Gambar --}}
              <div class="relative">
                <div class="overflow-hidden rounded-xl border border-slate-100">
                  <img src="{{ $img }}" alt="{{ $p->name }}" class="h-24 w-full object-cover sm:h-28">
                </div>
              </div>

              {{-- Info --}}
              <div class="flex flex-col justify-between min-w-0">
                <div>
                  <div class="flex items-center justify-between gap-2">
                    <span class="text-brand-green text-[13px] sm:text-[14px]">{{ $cat }}</span>
                    <div class="flex items-center gap-1 text-[13px]">
                      <span class="text-yellow-400">★</span>
                      <span class="text-slate-700 font-medium">{{ $rating }}</span>
                    </div>
                  </div>

                  <h3 class="mt-1 text-[14px] sm:text-[16px] font-[500] leading-snug line-clamp-2">
                    {{ $p->name }}
                  </h3>

                  <p class="mt-1 text-[13px] sm:text-[14px] text-slate-500 line-clamp-2">
                    {{ strip_tags($p->short_description ?? '') }}
                  </p>
                </div>

                <div class="mt-2 flex items-center justify-between">
                  <div class="text-[15px] sm:text-[16px] font-[600]">
                    Rp {{ number_format($unit * $it->qty, 0, ',', '.') }}
                  </div>
                  <div class="text-[13px] text-slate-500">
                    Qty: <span class="font-medium">{{ $it->qty }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @empty
          <p class="text-sm text-slate-500">Belum ada produk di keranjang.</p>
        @endforelse
      </div>

      {{-- RINGKASAN PEMBAYARAN --}}
      @php
        $subtotal        = $items->sum(fn($row) => $row->price_snapshot * $row->qty);
        $initialShipping = 0;
        $discount        = 0;
        $total           = $subtotal + $initialShipping - $discount;
      @endphp

      <div class="rounded-2xl bg-white p-5 shadow-md space-y-3">
        <h2 class="text-[18px] font-semibold">Ringkasan Pembayaran</h2>
        <div class="space-y-2 text-[14px] md:text-[15px]">
          <div class="flex justify-between">
            <span>Subtotal</span>
            <span id="sum-subtotal">Rp {{ number_format($subtotal,0,',','.') }}</span>
          </div>
          <div class="flex justify-between">
            <span>Ongkir</span>
            <span id="sum-shipping">Rp {{ number_format($initialShipping,0,',','.') }}</span>
          </div>
          <div class="flex justify-between">
            <span>Diskon Promo</span>
            <span id="sum-discount">- Rp {{ number_format($discount,0,',','.') }}</span>
          </div>
          <hr class="my-2">
          <div class="flex justify-between font-semibold text-[15px] md:text-[16px]">
            <span>Total Bayar</span>
            <span id="sum-total">Rp {{ number_format($total,0,',','.') }}</span>
          </div>
        </div>

        {{-- input voucher dummy (optional, bisa kamu fungsikan nanti) --}}
        <div>
          <input type="text"
                 class="w-full rounded-xl border-slate-300 text-[14px] md:text-[15px] focus:border-brand-green focus:ring-brand-green"
                 placeholder="Masukkan voucher (opsional)">
        </div>

        <button type="submit" form="checkout-form"
                class="mt-3 w-full rounded-xl bg-brand-green py-3 text-center text-[15px] md:text-[16px] font-semibold text-white hover:bg-brand-greenDark">
          Buat Pesanan
        </button>
      </div>
    </aside>

    {{-- FORM SEBENARNYA --}}
    <form id="checkout-form" method="POST" action="{{ route('checkout.place-order') }}">
      @csrf
    </form>
  </div>
</section>
@endsection

@push('scripts')
{{-- Leaflet JS untuk map (bukan Google Maps) --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
// ===================== ALAMAT OFFLINE: PROVINSI / KOTA / KEC / KODEPOS =====================
(function () {
  const API_BASE = '{{ url('/api') }}';

  const btnAreaDisplay   = document.getElementById('btn-area-display');
  const areaDisplayText  = document.getElementById('area_display_text');
  const areaEditor       = document.getElementById('area_editor');
  const areaTabs         = document.getElementById('area_tabs');
  const areaList         = document.getElementById('area_list');
  const areaSearchInput  = document.getElementById('area_search');

  const inputProvince    = document.getElementById('receiver_province');
  const inputCity        = document.getElementById('receiver_city');
  const inputDistrict    = document.getElementById('receiver_district');
  const inputPostal      = document.getElementById('receiver_postal_code');
  const inputLat         = document.getElementById('receiver_latitude');
  const inputLng         = document.getElementById('receiver_longitude');

  const inputAddr1       = document.getElementById('receiver_address_line1');
  const inputPhone       = document.getElementById('receiver_phone');

  let activeTab = 'province';
  let provinces = [];
  let cities    = [];
  let districts = [];
  let postals   = [];
  let currentItems = [];
  let currentLevel = 'province';

  const selected = {
    province_id: null,
    province:    null,
    city_id:     null,
    city:        null,
    district_id: null,
    district:    null,
    postal_id:   null,
    postal:      null,
    lat:         null,
    lng:         null,
  };

  // ============ MAP Leaflet ============
  let map = null;
  let marker = null;

  function initMap() {
    const el = document.getElementById('map');
    if (!el) return;
    map = L.map(el).setView([-2.5, 118], 5); // tengah Indonesia
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);
  }

  function moveMap(lat, lng) {
    if (!map) return;
    if (!lat || !lng) return;
    const latlng = [lat, lng];
    if (marker) {
      marker.setLatLng(latlng);
    } else {
      marker = L.marker(latlng).addTo(map);
    }
    map.setView(latlng, 14);
    setTimeout(() => map.invalidateSize(), 300);
  }

  // ============ UI Helper ============
  function setTabHeader(tab) {
    activeTab = tab;
    if (!areaTabs) return;
    const children = Array.from(areaTabs.children);
    const tabIndex = { province: 0, city: 1, district: 2, postal: 3 }[tab] ?? 0;

    children.forEach((el, idx) => {
      if (idx === tabIndex) {
        el.classList.add('border-b-2', 'border-brand-green', 'font-semibold', 'text-slate-900');
        el.classList.remove('text-slate-500');
      } else {
        el.classList.remove('border-b-2', 'border-brand-green', 'font-semibold', 'text-slate-900');
        el.classList.add('text-slate-500');
      }
    });
  }

  function updateAreaDisplayText() {
    const parts = [];
    if (selected.province) parts.push(selected.province);
    if (selected.city)     parts.push(selected.city);
    if (selected.district) parts.push(selected.district);
    if (selected.postal)   parts.push(selected.postal);
    areaDisplayText.textContent = parts.length
      ? parts.join(', ')
      : 'Pilih lokasi (provinsi, kota, kecamatan, kode pos)';
  }

  function setHiddenInputsFromSelected() {
    inputProvince.value = selected.province || '';
    inputCity.value     = selected.city || '';
    inputDistrict.value = selected.district || '';
    inputPostal.value   = selected.postal || '';
    inputLat.value      = selected.lat || '';
    inputLng.value      = selected.lng || '';
  }

  function showEditor() {
    areaEditor.classList.remove('hidden');
    setTimeout(() => map && map.invalidateSize(), 300);
  }

  function hideEditor() {
    areaEditor.classList.add('hidden');
  }

  function renderList() {
    if (!areaList) return;
    areaList.innerHTML = '';

    if (!currentItems.length) {
      areaList.innerHTML =
        '<p class="px-4 py-3 text-[13px] text-slate-500">Tidak ada data untuk tab ini.</p>';
      return;
    }

    currentItems.forEach(item => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className =
        'w-full text-left px-4 py-2 border-b border-slate-100 last:border-b-0 hover:bg-emerald-50 text-[13px]';

      let text = '';
      if (currentLevel === 'province') {
        text = item.name;
      } else if (currentLevel === 'city') {
        text = (item.type ? item.type + ' ' : '') + item.name;
      } else if (currentLevel === 'district') {
        text = item.name;
      } else if (currentLevel === 'postal') {
        text = `${item.postal_code} - ${(item.village || '').trim()}`;
      }

      btn.textContent = text;
      btn.addEventListener('click', () => onSelectItem(item));
      areaList.appendChild(btn);
    });
  }

  // ============ Ambil data dari API ============
  async function loadProvinces() {
    try {
      const res = await fetch(API_BASE + '/provinces');
      provinces = await res.json();
      currentItems = provinces;
      currentLevel = 'province';
      setTabHeader('province');
      renderList();
    } catch (e) {
      console.error(e);
      areaList.innerHTML =
        '<p class="px-4 py-3 text-[13px] text-red-500">Gagal memuat daftar provinsi.</p>';
    }
  }

  async function loadCities(provinceId) {
    try {
      const res = await fetch(API_BASE + '/cities?province_id=' + provinceId);
      cities = await res.json();
      currentItems = cities;
      currentLevel = 'city';
      setTabHeader('city');
      renderList();
    } catch (e) {
      console.error(e);
      areaList.innerHTML =
        '<p class="px-4 py-3 text-[13px] text-red-500">Gagal memuat daftar kota.</p>';
    }
  }

  async function loadDistricts(cityId) {
    try {
      const res = await fetch(API_BASE + '/districts?city_id=' + cityId);
      districts = await res.json();
      currentItems = districts;
      currentLevel = 'district';
      setTabHeader('district');
      renderList();
    } catch (e) {
      console.error(e);
      areaList.innerHTML =
        '<p class="px-4 py-3 text-[13px] text-red-500">Gagal memuat daftar kecamatan.</p>';
    }
  }

  async function loadPostals(districtId) {
    try {
      const res = await fetch(API_BASE + '/postal-codes?district_id=' + districtId);
      postals = await res.json();
      currentItems = postals;
      currentLevel = 'postal';
      setTabHeader('postal');
      renderList();
    } catch (e) {
      console.error(e);
      areaList.innerHTML =
        '<p class="px-4 py-3 text-[13px] text-red-500">Gagal memuat daftar kode pos.</p>';
    }
  }

  // ============ Saat pilih item di list ============
  async function onSelectItem(item) {
    if (currentLevel === 'province') {
      selected.province_id = item.id;
      selected.province    = item.name;
      selected.city_id = selected.city = null;
      selected.district_id = selected.district = null;
      selected.postal_id = selected.postal = null;
      setHiddenInputsFromSelected();
      updateAreaDisplayText();
      await loadCities(item.id);
    } else if (currentLevel === 'city') {
      selected.city_id = item.id;
      selected.city    = item.name;
      selected.district_id = selected.district = null;
      selected.postal_id = selected.postal = null;
      setHiddenInputsFromSelected();
      updateAreaDisplayText();
      await loadDistricts(item.id);
    } else if (currentLevel === 'district') {
      selected.district_id = item.id;
      selected.district    = item.name;
      selected.postal_id = selected.postal = null;
      setHiddenInputsFromSelected();
      updateAreaDisplayText();
      await loadPostals(item.id);
    } else if (currentLevel === 'postal') {
      selected.postal_id = item.id;
      selected.postal    = item.postal_code;
      selected.lat       = item.latitude || null;
      selected.lng       = item.longitude || null;

      setHiddenInputsFromSelected();
      updateAreaDisplayText();
      hideEditor();
      if (selected.lat && selected.lng) {
        moveMap(selected.lat, selected.lng);
      }
    }
  }

  // ============ Search di dalam level sekarang ============
  if (areaSearchInput) {
    areaSearchInput.addEventListener('input', function () {
      const q = this.value.trim().toLowerCase();
      let source = [];

      if (currentLevel === 'province') source = provinces;
      else if (currentLevel === 'city') source = cities;
      else if (currentLevel === 'district') source = districts;
      else if (currentLevel === 'postal') source = postals;

      if (!q) {
        currentItems = source;
      } else {
        currentItems = (source || []).filter(item => {
          if (currentLevel === 'province') {
            return item.name.toLowerCase().includes(q);
          } else if (currentLevel === 'city') {
            return (item.name + ' ' + (item.type || '')).toLowerCase().includes(q);
          } else if (currentLevel === 'district') {
            return item.name.toLowerCase().includes(q);
          } else if (currentLevel === 'postal') {
            return (
              String(item.postal_code).toLowerCase().includes(q) ||
              (item.village || '').toLowerCase().includes(q)
            );
          }
          return false;
        });
      }
      renderList();
    });
  }

  // ============ Events ============

  if (btnAreaDisplay) {
    btnAreaDisplay.addEventListener('click', function () {
      if (areaEditor.classList.contains('hidden')) {
        showEditor();
        if (!provinces.length) {
          loadProvinces();
        }
      } else {
        hideEditor();
      }
    });
  }

  // init map sekali
  initMap();
})();
</script>

<script>
// ===================== SCRIPT ONGKIR (PUNYA KAMU, TETAP) =====================
(function () {
  const token = '{{ csrf_token() }}';
  const btnRates = document.getElementById('btn-check-rates');
  const elOptions = document.getElementById('shipping-options');
  const elSelectedBox = document.getElementById('shipping-selected');
  const elSelectedName = document.getElementById('selected-courier-name');
  const elSelectedEtd = document.getElementById('selected-courier-etd');
  const elSelectedPrice = document.getElementById('selected-courier-price');

  const inputCourierCode = document.getElementById('shipping_courier_code');
  const inputCourierName = document.getElementById('shipping_courier_name');
  const inputShippingPrice = document.getElementById('shipping_price');

  const sumShipping = document.getElementById('sum-shipping');
  const sumTotal = document.getElementById('sum-total');
  const subtotalNumber = {{ (int) $subtotal }};

  const rupiah = n => new Intl.NumberFormat('id-ID').format(n || 0);

  function updateSummary(price) {
    const ongkir = Number(price || 0);
    sumShipping.textContent = 'Rp ' + rupiah(ongkir);
    sumTotal.textContent = 'Rp ' + rupiah(subtotalNumber + ongkir);
  }

  if (btnRates) {
    btnRates.addEventListener('click', async function () {
      const postal = document.getElementById('receiver_postal_code').value.trim();
      const addr1  = document.getElementById('receiver_address_line1').value.trim();

      if (!postal || !addr1) {
        alert('Isi alamat dan kode pos terlebih dahulu.');
        return;
      }

      elOptions.innerHTML = '<p class="text-sm text-slate-500">Mengambil data ongkir...</p>';

      try {
        const res = await fetch('{{ route('checkout.shipping-rates') }}', {
            method: 'POST',
            headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
            },
            body: JSON.stringify({
            receiver_postal_code: postal,
            receiver_address_line1: addr1
            })
        });

        let data;
        try {
            data = await res.json();
        } catch (_) {
            data = null;
        }

        if (!res.ok) {
            const msg =
            (data && data.message) ? data.message :
            (data && data.error)   ? data.error   :
            'Gagal mengambil tarif ongkir.';
            throw new Error(msg);
        }

        const pricing = data.pricing || [];

        if (!pricing.length) {
          elOptions.innerHTML = '<p class="text-sm text-slate-500">Tidak ada opsi pengiriman yang tersedia.</p>';
          return;
        }

        const list = document.createElement('div');
        list.className = 'space-y-2';

        pricing.forEach((opt, idx) => {
          const id = 'ship-' + idx;
          const price = opt.price || opt.shipping_fee || 0;

          const courierCompany = (opt.company || opt.courier_code || '').trim();
          const courierType    = (opt.type || opt.courier_service_code || '').trim();

          const courierCode = courierCompany && courierType
            ? `${courierCompany}:${courierType}`
            : courierCompany;

          const courierName = (opt.courier_name || courierCompany || 'Kurir').trim();
          const serviceName = opt.courier_service_name || opt.service_type || '';

          const etdText = opt.duration ||
                          (opt.shipment_duration_range
                            ? opt.shipment_duration_range + ' ' + (opt.shipment_duration_unit || 'hari')
                            : '');

          const label = document.createElement('label');
          label.setAttribute('for', id);
          label.className =
            'flex items-start gap-3 rounded-xl border border-slate-200 px-4 py-3 cursor-pointer hover:border-brand-green';

          label.innerHTML = `
            <input type="radio" id="${id}" name="shipping_option"
                   class="mt-1 h-4 w-4 border-slate-300 text-brand-green focus:ring-brand-green"
                   data-code="${courierCode}"
                   data-name="${courierName}${serviceName ? ' - ' + serviceName : ''}"
                   data-etd="${etdText}"
                   data-price="${price}">
            <div class="flex-1">
              <div class="flex items-center justify-between gap-2">
                <div class="flex flex-col">
                  <span class="text-[14px] font-semibold text-slate-900">
                    ${courierName}${serviceName ? ' - ' + serviceName : ''}
                  </span>
                  <span class="text-[12px] text-slate-500">
                    ${etdText || ''}
                  </span>
                </div>
                <span class="text-[14px] font-semibold text-slate-900">
                  Rp ${rupiah(price)}
                </span>
              </div>
            </div>
          `;

          list.appendChild(label);
        });

        elOptions.innerHTML = '';
        elOptions.appendChild(list);

        elOptions.querySelectorAll('input[name="shipping_option"]').forEach(radio => {
          radio.addEventListener('change', function () {
            const code = this.getAttribute('data-code');
            const name = this.getAttribute('data-name');
            const price = Number(this.getAttribute('data-price') || 0);
            const etd = this.getAttribute('data-etd') || '';

            inputCourierCode.value = code;
            inputCourierName.value = name;
            inputShippingPrice.value = price;

            elSelectedName.textContent = name;
            elSelectedEtd.textContent = etd ? `Estimasi tiba: ${etd}` : '';
            elSelectedPrice.textContent = 'Rp ' + rupiah(price);
            elSelectedBox.classList.remove('hidden');

            updateSummary(price);
          });
        });

      } catch (e) {
        console.error(e);
        elOptions.innerHTML = `<p class="text-sm text-red-500">${e.message}</p>`;
      }
    });
  }
})();
</script>
@endpush






