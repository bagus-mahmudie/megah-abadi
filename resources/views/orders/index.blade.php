@extends('layouts.app')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<section class="py-[80px] bg-[#f5f5f5] min-h-screen">
    <div class="container mx-auto">

        {{-- ========================= TAB NAV ========================= --}}
        <div class="mb-6">
            <div class="w-full bg-white rounded-[14px] border border-slate-100 shadow-sm px-6 py-3 overflow-x-auto">
                <div class="flex items-center justify-between gap-4 min-w-max w-full">
                    @foreach ($tabs as $key => $tab)
                        @php $isActive = $activeTab === $key; @endphp

                        <a href="{{ route('orders.index', ['tab' => $key]) }}"
                           class="flex items-center gap-2 transition whitespace-nowrap
                                {{ $isActive
                                    ? 'px-5 py-1.5 rounded-[14px] bg-[#f5f5f5] text-slate-900 font-medium'
                                    : 'text-slate-500 hover:text-slate-900' }}">
                            <span>{{ $tab['label'] }}</span>

                            @if (($tab['count'] ?? 0) > 0)
                                <span class="inline-flex items-center justify-center rounded-full text-[12px] min-w-[22px] h-[22px]
                                            {{ $isActive ? 'bg-brand-green text-white' : 'bg-[#f5f5f5] text-slate-600' }}">
                                    {{ $tab['count'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ========================= LIST PESANAN + DETAIL ========================= --}}
        @if ($orders->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 py-12 text-center text-slate-500">
                Belum ada pesanan pada tab ini.
            </div>
        @else
            {{-- KALAU BELUM PILIH DETAIL -> TAMPILAN LAMA, GRID 2 KOLOM --}}
            @if (!$selectedOrder)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($orders as $order)
                        @php
                            $shipmentStatus = optional($order->shipment)->status;

                            if ($order->payment_status === 'unpaid') {
                                $statusLabel = 'Belum Bayar';
                                $statusClass = 'bg-red-50 text-red-600';
                            } elseif (in_array($shipmentStatus, ['picked','picked_up','droppingOff','dropping_off','on_delivery'])) {
                                $statusLabel = 'Dikirim';
                                $statusClass = 'bg-blue-50 text-blue-700';
                            } elseif ($shipmentStatus === 'delivered') {
                                $statusLabel = 'Selesai';
                                $statusClass = 'bg-emerald-50 text-emerald-700';
                            } elseif ($order->status === 'awaiting_shipment' || in_array($shipmentStatus, ['pending','confirmed'])) {
                                $statusLabel = 'Sedang Dikemas';
                                $statusClass = 'bg-yellow-50 text-yellow-700';
                            } elseif ($order->status === 'cancelled') {
                                $statusLabel = 'Dibatalkan';
                                $statusClass = 'bg-[#f5f5f5] text-slate-500';
                            } else {
                                $statusLabel = ucfirst(str_replace('_', ' ', $order->status ?? ''));
                                $statusClass = 'bg-[#f5f5f5] text-slate-600';
                            }
                        @endphp

                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full">
                            {{-- HEADER KARTU --}}
                            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[15px] font-medium {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                {{-- TOMBOL DETAIL -> AKTIFKAN MODE DETAIL --}}
                                <a href="{{ route('orders.index', ['tab' => $activeTab, 'order' => $order->id]) }}#order-detail"
                                   class="px-4 py-1.5 rounded-xl text-[15px] font-medium bg-[#f5f5f5] text-slate-500 hover:bg-slate-200 transition">
                                    Detail
                                </a>
                            </div>

                            {{-- ITEM PESANAN --}}
                            <div class="flex-1 bg-white px-3 py-3">
                                <div class="px-3 py-3 space-y-3 max-h-[380px] overflow-y-auto bg-[#f5f5f5] rounded-2xl">
                                    @foreach ($order->items as $item)
                                        @php
                                            $p      = $item->product;
                                            $name   = $item->product_name ?? optional($p)->name ?? 'Produk';
                                            $variantText = $item->variant_options ?: optional($item->variant)->option_summary;

                                            $categoryName = optional(optional($p)->category)->name;

                                            $rawDescription = $p->description_html ?? null;
                                            $description    = $rawDescription ? Str::limit(strip_tags($rawDescription), 80) : null;

                                            $imgRel = optional(optional($p)->images->first())->image_url;
                                            $img    = $imgRel && file_exists(public_path(ltrim($imgRel, '/')))
                                                        ? asset(ltrim($imgRel, '/'))
                                                        : asset('assets/img/placeholder-product.png');

                                            $qty  = $item->qty ?? 1;

                                            $originalPrice = $p && $p->base_price !== null
                                                ? (float) $p->base_price
                                                : 0;

                                            $discountPrice = $item->price ?? $item->price_snapshot;

                                            if ($discountPrice === null && $originalPrice > 0) {
                                                if ($p && $p->discount_percent !== null) {
                                                    $discountPrice = $originalPrice * (1 - ((float) $p->discount_percent / 100));
                                                } else {
                                                    $discountPrice = $originalPrice;
                                                }
                                            }

                                            if ($discountPrice === null) {
                                                $discountPrice = $originalPrice;
                                            }

                                            $discountPrice = (float) $discountPrice;
                                            if ($p && $p->discount_percent !== null) {
                                                $discountPercent = (int) $p->discount_percent;
                                            } elseif ($originalPrice > 0 && $discountPrice < $originalPrice) {
                                                $discountPercent = (int) floor((($originalPrice - $discountPrice) / $originalPrice) * 100);
                                            } else {
                                                $discountPercent = null;
                                            }

                                            $subtotal = $discountPrice * $qty;
                                        @endphp

                                        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                                            <div class="flex gap-3 p-3">
                                                <div class="w-[150px] rounded-xl overflow-hidden border border-slate-100 bg-[#f5f5f5] shrink-0 flex">
                                                    <img src="{{ $img }}" alt="{{ $name }}" class="w-full h-full object-cover object-center">
                                                </div>

                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <div class="min-w-0">
                                                            @if ($categoryName)
                                                                <p class="text-[14px] text-emerald-600 font-medium tracking-wide truncate">
                                                                    {{ $categoryName }}
                                                                </p>
                                                            @endif

                                                            <h3 class="text-[16px] font-medium text-slate-900 leading-[1.3] pb-[5px] line-clamp-2 min-h-[46.59px]">
                                                                {{ $name }}
                                                            </h3>

                                                            @if ($description || $variantText)
                                                                <p class="mt-0.5 text-[14px] text-slate-500 line-clamp-2">
                                                                    @if ($variantText)
                                                                        <span class="font-medium">{{ $variantText }}</span>
                                                                        @if ($description) &mdash; @endif
                                                                    @endif
                                                                    @if ($description)
                                                                        <span>{{ $description }}</span>
                                                                    @endif
                                                                </p>
                                                            @endif
                                                        </div>

                                                        <div class="text-right text-[14px] text-slate-500">
                                                            {{ $qty }}&nbspitem
                                                        </div>
                                                    </div>

                                                    <div class="mt-2 text-[15px] flex items-center justify-between">
                                                        <span class="text-slate-500">Subtotal ( x{{ $qty }} )</span>
                                                        <div class="text-right">
                                                            <span class="font-semibold text-slate-900">
                                                                Rp {{ number_format($subtotal, 0, ',', '.') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- FOOTER --}}
                            <div class="border-t border-slate-100 px-4 pb-4 pt-3 mt-auto">
                                <div class="flex items-center justify-between rounded-xl bg-[#f5f5f5] px-4 py-3 mb-3">
                                    <span class="text-[16px] font-semibold">Total Bayar</span>
                                    <span class="text-[16px] font-semibold text-slate-900">
                                        Rp {{ number_format($order->grand_total ?? 0, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div class="flex gap-3">
                                    <button type="button"
                                            class="flex-1 h-[44px] inline-flex items-center justify-center rounded-xl bg-[#f5f5f5] text-slate-400 text-[14px] font-medium cursor-not-allowed">
                                        Ajukan Retur
                                    </button>

                                    @if ($order->payment_status === 'unpaid')
                                        <button type="button"
                                                data-action="pay-order"
                                                data-order-id="{{ $order->id }}"
                                                class="flex-1 h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-brand-green text-white text-[14px] font-semibold hover:bg-emerald-600 disabled:opacity-60 disabled:cursor-not-allowed">
                                            Bayar
                                        </button>
                                    @else
                                        <button type="button"
                                                class="flex-1 h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-brand-green text-white text-[14px] font-semibold hover:bg-emerald-600">
                                            Beli Lagi
                                            <span class="text-[18px] leading-none">&rarr;</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

            {{-- KALAU SUDAH PILIH DETAIL -> MODE 2 KOLOM: LIST KIRI + DETAIL KANAN --}}
            @else
                <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-4 items-start">

                    {{-- LIST ORDER KIRI (1 KOLOM) --}}
                    <div class="space-y-4">
                        @foreach ($orders as $order)
                            @php
                                $shipmentStatus = optional($order->shipment)->status;

                                if ($order->payment_status === 'unpaid') {
                                    $statusLabel = 'Belum Bayar';
                                    $statusClass = 'bg-red-50 text-red-600';
                                } elseif (in_array($shipmentStatus, ['picked','picked_up','droppingOff','dropping_off','on_delivery'])) {
                                    $statusLabel = 'Dikirim';
                                    $statusClass = 'bg-blue-50 text-blue-700';
                                } elseif ($shipmentStatus === 'delivered') {
                                    $statusLabel = 'Selesai';
                                    $statusClass = 'bg-emerald-50 text-emerald-700';
                                } elseif ($order->status === 'awaiting_shipment' || in_array($shipmentStatus, ['pending','confirmed'])) {
                                    $statusLabel = 'Sedang Dikemas';
                                    $statusClass = 'bg-yellow-50 text-yellow-700';
                                } elseif ($order->status === 'cancelled') {
                                    $statusLabel = 'Dibatalkan';
                                    $statusClass = 'bg-[#f5f5f5] text-slate-500';
                                } else {
                                    $statusLabel = ucfirst(str_replace('_', ' ', $order->status ?? ''));
                                    $statusClass = 'bg-[#f5f5f5] text-slate-600';
                                }

                                $isSelected  = $selectedOrder && $selectedOrder->id === $order->id;
                                $wrapperClass = $isSelected
                                    ? 'border-emerald-400 ring-1 ring-emerald-100'
                                    : 'border-slate-100';
                            @endphp

                            {{-- CARD ORDER UTAMA – SAMA PERSIS DENGAN TAMPILAN AWAL --}}
                            <div class="bg-white rounded-2xl shadow-sm border {{ $wrapperClass }} overflow-hidden flex flex-col h-full">
                                {{-- HEADER KARTU --}}
                                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[15px] font-medium {{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>

                                    {{-- TOMBOL DETAIL --}}
                                    <a href="{{ route('orders.index', ['tab' => $activeTab, 'order' => $order->id]) }}#order-detail"
                                    class="px-4 py-1.5 rounded-xl text-[15px] font-medium {{ $isSelected ? 'bg-emerald-500 text-white' : 'bg-[#f5f5f5] text-slate-500 hover:bg-slate-200' }} transition">
                                        Detail
                                    </a>
                                </div>

                                {{-- ITEM PESANAN: CARD DI DALAM CARD --}}
                                <div class="flex-1 bg-white px-3 py-3">
                                    <div class="px-3 py-3 space-y-3 max-h-[380px] overflow-y-auto bg-[#f5f5f5] rounded-2xl">
                                        @foreach ($order->items as $item)
                                            @php
                                                $p      = $item->product;
                                                $name   = $item->product_name ?? optional($p)->name ?? 'Produk';
                                                $variantText = $item->variant_options ?: optional($item->variant)->option_summary;

                                                $categoryName = optional(optional($p)->category)->name;

                                                $rawDescription = $p->description_html ?? null;
                                                $description    = $rawDescription ? Str::limit(strip_tags($rawDescription), 80) : null;

                                                $imgRel = optional(optional($p)->images->first())->image_url;
                                                $img    = $imgRel && file_exists(public_path(ltrim($imgRel, '/')))
                                                            ? asset(ltrim($imgRel, '/'))
                                                            : asset('assets/img/placeholder-product.png');

                                                $qty  = $item->qty ?? 1;

                                                $originalPrice = $p && $p->base_price !== null
                                                    ? (float) $p->base_price
                                                    : 0;

                                                $discountPrice = $item->price ?? $item->price_snapshot;

                                                if ($discountPrice === null && $originalPrice > 0) {
                                                    if ($p && $p->discount_percent !== null) {
                                                        $discountPrice = $originalPrice * (1 - ((float) $p->discount_percent / 100));
                                                    } else {
                                                        $discountPrice = $originalPrice;
                                                    }
                                                }

                                                if ($discountPrice === null) {
                                                    $discountPrice = $originalPrice;
                                                }

                                                $discountPrice = (float) $discountPrice;

                                                if ($p && $p->discount_percent !== null) {
                                                    $discountPercent = (int) $p->discount_percent;
                                                } elseif ($originalPrice > 0 && $discountPrice < $originalPrice) {
                                                    $discountPercent = (int) floor((($originalPrice - $discountPrice) / $originalPrice) * 100);
                                                } else {
                                                    $discountPercent = null;
                                                }

                                                $subtotal = $discountPrice * $qty;
                                            @endphp

                                            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                                                <div class="flex gap-3 p-3">
                                                    <div class="w-[150px] rounded-xl overflow-hidden border border-slate-100 bg-[#f5f5f5] shrink-0 flex">
                                                        <img src="{{ $img }}"
                                                            alt="{{ $name }}"
                                                            class="w-full h-full object-cover object-center">
                                                    </div>

                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-start justify-between gap-2">
                                                            <div class="min-w-0">
                                                                @if ($categoryName)
                                                                    <p class="text-[14px] text-emerald-600 font-medium tracking-wide truncate ">
                                                                        {{ $categoryName }}
                                                                    </p>
                                                                @endif

                                                                <h3 class="text-[16px] sm:text-[16px] font-medium text-slate-900 leading-[1.3] pb-[5px] line-clamp-2 min-h-[46.59px]">
                                                                    {{ $name }}
                                                                </h3>

                                                                @if ($description || $variantText)
                                                                    <p class="mt-0.5 text-[14px] text-slate-500 line-clamp-2">
                                                                        @if ($variantText)
                                                                            <span class="font-medium">{{ $variantText }}</span>
                                                                            @if ($description) &mdash; @endif
                                                                        @endif
                                                                        @if ($description)
                                                                            <span>{{ $description }}</span>
                                                                        @endif
                                                                    </p>
                                                                @endif
                                                            </div>

                                                            <div class="text-right text-[14px] text-slate-500">
                                                                {{ $qty }}&nbspitem
                                                            </div>
                                                        </div>

                                                        <div class="mt-2 text-[15px] flex items-center justify-between">
                                                            <span class="text-slate-500">Subtotal ( x{{ $qty }} )</span>
                                                            <div class="text-right">
                                                                <span class="font-semibold text-slate-900">
                                                                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- FOOTER KARTU (TOTAL + BUTTONS) --}}
                                <div class="border-t border-slate-100 px-4 pb-4 pt-3 mt-auto">
                                    <div class="flex items-center justify-between rounded-xl bg-[#f5f5f5] px-4 py-3 mb-3">
                                        <span class="text-[16px] font-semibold">
                                            Total Bayar
                                        </span>
                                        <span class="text-[16px] font-semibold text-slate-900">
                                            Rp {{ number_format($order->grand_total ?? 0, 0, ',', '.') }}
                                        </span>
                                    </div>

                                    <div class="flex gap-3">
                                        <button type="button"
                                                class="flex-1 h-[44px] inline-flex items-center justify-center rounded-xl bg-[#f5f5f5] text-slate-400 text-[14px] font-medium cursor-not-allowed">
                                            Ajukan Retur
                                        </button>

                                        @if ($order->payment_status === 'unpaid')
                                            <button type="button"
                                                    data-action="pay-order"
                                                    data-order-id="{{ $order->id }}"
                                                    class="flex-1 h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-brand-green text-white text-[14px] font-semibold hover:bg-emerald-600 disabled:opacity-60 disabled:cursor-not-allowed">
                                                Bayar
                                            </button>
                                        @else
                                            <button type="button"
                                                    class="flex-1 h-[44px] inline-flex items-center justify-center gap-2 rounded-xl bg-brand-green text-white text-[14px] font-semibold hover:bg-emerald-600">
                                                Beli Lagi
                                                <span class="text-[18px] leading-none">&rarr;</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>


                    {{-- PANEL DETAIL KANAN --}}
                    <div id="order-detail" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 min-h-[400px]">
                        @php
                            $o = $selectedOrder;

                            // bisa jadi null kalau shipment belum dibuat
                            $shipment = $o->shipment;

                            // tracking dari controller (bisa null kalau belum ada resi / belum dipanggil)
                            $trackingStatus  = $selectedTrackingStatus  ?? null;
                            $trackingHistory = $selectedTrackingHistory ?? [];

                            // status utama: kalau Biteship ada, pakai itu; kalau tidak, fallback ke kolom shipment lokal
                            $shipStatus = $trackingStatus ?? ($shipment->status ?? null);

                            // STEP:
                            // - Dibuat selalu aktif
                            // - Dibayarkan aktif kalau payment paid
                            // - Dikirim aktif kalau paket sudah di-pickup (pickup time ada)
                            // - Selesai aktif kalau delivered (delivered time ada)
                            $stepCreated = true;
                            $stepPaid    = $o->payment_status === 'paid';

                            $originCity = 'Jakarta, Indonesia';
                            $destCity   = $o->receiver_city ?? 'Tujuan tidak diketahui';
                            $waybill    = $shipment->waybill_id ?? '-';

                            // gunakan nullsafe operator biar tidak error kalau shipment null
                            $courierCompany = $shipment?->courier_company ?? $o->shipping_courier_name;
                            $courierName    = $courierCompany ? Str::headline($courierCompany) : '-';

                            // tanggal penting dasar (dari DB)
                            $createdAt = \Carbon\Carbon::parse($o->created_at);
                            $paidAt    = $stepPaid ? $o->updated_at : null;
                            $pickedAt  = $shipment?->picked_up_at;
                            $doneAt    = $shipment?->delivered_at;

                            // kalau Biteship punya history, coba ambil picked_at & delivered_at dari sana
                            foreach ($trackingHistory as $event) {
                                if (! isset($event['status']) || ! isset($event['updated_at'])) {
                                    continue;
                                }

                                $eventTime = Carbon::parse($event['updated_at']);

                                if (! $pickedAt && in_array($event['status'], ['picked_up', 'on_delivery'])) {
                                    $pickedAt = $eventTime;
                                }

                                if (! $doneAt && $event['status'] === 'delivered') {
                                    $doneAt = $eventTime;
                                }
                            }

                            $stepShipped   = $pickedAt !== null;
                            $stepCompleted = $doneAt   !== null;
                        @endphp


                        {{-- STEP PROGRESS --}}
                        @php
                            // ganti src img dengan path icon yang kamu punya di public/assets/img
                            $steps = [
                                [
                                    'label'  => 'Dibuat',
                                    'active' => $stepCreated,
                                    'icon'   => asset('assets/img/icon/solar_clipboard-linear.png'),
                                ],
                                [
                                    'label'  => 'Dibayarkan',
                                    'active' => $stepPaid,
                                    'icon'   => asset('assets/img/icon/solar_wallet-linear.png'),
                                ],
                                [
                                    'label'  => 'Dikirim',
                                    'active' => $stepShipped,
                                    'icon'   => asset('assets/img/icon/humbleicons_truck.png'),
                                ],
                                [
                                    'label'  => 'Selesai',
                                    'active' => $stepCompleted,
                                    'icon'   => asset('assets/img/icon/lets-icons_done-ring-round.png'),
                                ],
                            ];
                        @endphp

                        <div class="flex items-center justify-between gap-2 mb-6">
                            @foreach ($steps as $index => $step)
                                <div class="flex-1 flex flex-col items-center">
                                    <div class="w-[50px] h-[50px] rounded-full flex items-center justify-center
                                                {{ $step['active'] ? 'bg-[#1880561A] shadow-sm' : 'bg-slate-100' }}">
                                        <img src="{{ $step['icon'] }}"
                                            alt="{{ $step['label'] }}"
                                            class="w-[30px] h-[30px] {{ $step['active'] ? 'opacity-100' : 'opacity-50' }}">
                                    </div>
                                    <span class="mt-2 text-[16px] {{ $step['active'] ? 'text-slate-900' : 'text-slate-400' }}">
                                        {{ $step['label'] }}
                                    </span>
                                </div>

                                @if (! $loop->last)
                                    <div class="w-8 h-[1px] {{ ($index === 0 && $stepPaid) || ($index === 1 && $stepShipped) || ($index === 2 && $stepCompleted) ? 'bg-emerald-300' : 'bg-slate-200' }} border-dashed"></div>
                                @endif
                            @endforeach
                        </div>

                        {{-- ALAMAT PENGIRIMAN --}}
                        <div class="mb-6">
                            <h3 class="text-[18px] border-b pb-3 text-slate-900 mb-4">Alamat pengiriman</h3>
                            <div class="rounded-2xl border border-slate-100 bg-[#f5f5f5] px-4 py-3  text-[16px]">
                                <p class="font-semibold">
                                    {{ $o->receiver_name }}
                                    <span class="text-slate-500">• {{ $o->receiver_phone }}</span>
                                </p>
                                <p class="mt-1 text-slate-600 text-[14px] leading-relaxed">
                                    {{ $o->receiver_address_line1 }}
                                    @if ($o->receiver_address_line2)
                                        , {{ $o->receiver_address_line2 }}
                                    @endif
                                    @if ($o->receiver_city)
                                        , {{ $o->receiver_city }}
                                    @endif
                                    @if ($o->receiver_postal_code)
                                        , {{ $o->receiver_postal_code }}
                                    @endif
                                </p>
                            </div>

                            {{-- NO. PESANAN + ACCORDION TANGGAL --}}
                            <div class="mt-4 text-[13px]"
                                data-order-meta>

                                {{-- HEADER NO. PESANAN --}}
                                <div class="flex items-center justify-between gap-3 border-b pb-4"
                                    data-order-meta-header>

                                    {{-- ICON KIRI + TEXT --}}
                                    <div class="flex items-center gap-3">
                                        <div class="w-[50px] h-[50px] rounded-xl bg-[#f5f5f5] flex items-center justify-center">
                                            <img src="{{ asset('assets/img/icon/bi_truck.png') }}"
                                                alt="Order"
                                                class="w-[30px] h-[30px]">
                                        </div>

                                        <div class="flex flex-col">
                                            <span class="text-[14px] text-slate-400 leading-none mb-2">
                                                No. Pesanan
                                            </span>
                                            <span class="text-[15px] font-semibold text-slate-900 leading-none">
                                                {{ $o->code }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- TOMBOL KANAN: COPY + CHEVRON --}}
                                    <div class="flex items-center gap-2">
                                        {{-- COPY --}}
                                        <button type="button"
                                                class="w-[40px] h-[40px] rounded-[14px] bg-[#f5f5f5] flex items-center justify-center text-slate-500 hover:bg-slate-100 text-[18px]"
                                                data-copy-order-code="{{ $o->code }}"
                                                title="Salin kode pesanan">
                                            <i class="bi bi-copy"></i>
                                        </button>

                                        {{-- CHEVRON / ACCORDION TOGGLE --}}
                                        <button type="button"
                                                class="w-[40px] h-[40px] rounded-full bg-[#f5f5f5] flex items-center justify-center text-slate-500 hover:bg-slate-100 text-[18px] transition-transform"
                                                data-order-meta-toggle
                                                aria-expanded="true">
                                            <i class="bi bi-chevron-up"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- ISI ACCORDION: TANGGAL2 --}}
                                <div class="mt-3 pb-3 border-b border-slate-200 space-y-1.5 text-[16px]"
                                    data-order-meta-body>
                                    <div class="flex items-center justify-between ">
                                        <span class="text-slate-500">Pesanan Dibuat :</span>
                                        <span class="font-medium text-slate-900">
                                            {{ $createdAt?->format('d-m-Y H:i') ?? '-' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">Pesanan Dibayarkan :</span>
                                        <span class="font-medium text-slate-900">
                                            {{ $paidAt?->format('d-m-Y H:i') ?? '-' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">Pesanan Dikirim :</span>
                                        <span class="font-medium text-slate-900">
                                            {{ $pickedAt?->format('d-m-Y H:i') ?? '-' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">Pesanan Selesai :</span>
                                        <span class="font-medium text-slate-900">
                                            {{ $doneAt?->format('d-m-Y H:i') ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- INFORMASI PENGIRIMAN --}}
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-lg text-emerald-600">
                                        🚚
                                    </div>
                                    <h3 class="text-sm font-semibold text-slate-900">Informasi pengiriman</h3>
                                </div>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                            {{ $stepCompleted ? 'bg-emerald-50 text-emerald-700' : 'bg-yellow-50 text-yellow-700' }}">
                                    {{ $stepCompleted ? 'Selesai' : ($shipStatus ? Str::headline($shipStatus) : 'Belum dikirim') }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="space-y-1">
                                    <p class="text-xs text-slate-400">Dari</p>
                                    <p class="font-medium text-slate-900">{{ $originCity }}</p>
                                </div>
                                <div class="space-y-1 md:text-right">
                                    <p class="text-xs text-slate-400">Tujuan</p>
                                    <p class="font-medium text-slate-900">{{ $destCity }}</p>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs text-slate-400">Nomor Resi</p>
                                    <p class="font-medium text-slate-900">{{ $waybill }}</p>
                                </div>
                                <div class="space-y-1 md:text-right">
                                    <p class="text-xs text-slate-400">Kurir</p>
                                    <p class="font-medium text-slate-900">{{ $courierName }}</p>
                                </div>
                            </div>

                            {{-- TIMELINE SEDERHANA (PAKAI Biteship JIKA ADA) --}}
                            <div class="mt-4">
                                <h4 class="text-xs font-semibold text-slate-900 mb-2">Progress Tracking</h4>

                                @if (!empty($trackingHistory))
                                    <ul class="space-y-3 text-xs">
                                        @foreach ($trackingHistory as $event)
                                            @php
                                                $time = $event['updated_at'] ?? $event['created_at'] ?? null;
                                                $dt   = $time ? Carbon::parse($time) : null;
                                                $note = $event['note'] ?? $event['description'] ?? $event['status'] ?? '';
                                            @endphp

                                            <li class="flex gap-3">
                                                <div class="w-[110px] text-slate-500">
                                                    {{ $dt?->format('d-m-Y H:i') ?? '-' }}
                                                </div>
                                                <div class="flex flex-col items-center">
                                                    <div class="w-7 h-7 rounded-full border border-emerald-400 flex items-center justify-center text-[11px] text-emerald-500">↑</div>
                                                    @if (! $loop->last)
                                                        <div class="w-px flex-1 bg-slate-200"></div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 text-slate-600">
                                                    {{ $note }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    {{-- fallback lama kalau belum ada history dari Biteship --}}
                                    <ul class="space-y-3 text-xs">
                                        <li class="flex gap-3">
                                            <div class="w-[110px] text-slate-500">
                                                {{ $createdAt?->format('d-m-Y H:i') ?? '-' }}
                                            </div>
                                            <div class="flex flex-col items-center">
                                                <div class="w-7 h-7 rounded-full border border-emerald-400 flex items-center justify-center text-[11px] text-emerald-500">↑</div>
                                                <div class="w-px flex-1 bg-slate-200"></div>
                                            </div>
                                            <div class="flex-1 text-slate-600">
                                                Pesanan dibuat dan menunggu pembayaran.
                                            </div>
                                        </li>

                                        @if ($stepPaid)
                                            <li class="flex gap-3">
                                                <div class="w-[110px] text-slate-500">
                                                    {{ $paidAt?->format('d-m-Y H:i') ?? '-' }}
                                                </div>
                                                <div class="flex flex-col items-center">
                                                    <div class="w-7 h-7 rounded-full border border-emerald-400 flex items-center justify-center text-[11px] text-emerald-500">↑</div>
                                                    <div class="w-px flex-1 bg-slate-200"></div>
                                                </div>
                                                <div class="flex-1 text-slate-600">
                                                    Pembayaran berhasil, pesanan menunggu proses pengiriman.
                                                </div>
                                            </li>
                                        @endif

                                        @if ($pickedAt)
                                            <li class="flex gap-3">
                                                <div class="w-[110px] text-slate-500">
                                                    {{ $pickedAt?->format('d-m-Y H:i') ?? '-' }}
                                                </div>
                                                <div class="flex flex-col items-center">
                                                    <div class="w-7 h-7 rounded-full border border-emerald-400 flex items-center justify-center text-[11px] text-emerald-500">↑</div>
                                                    <div class="w-px flex-1 bg-slate-200"></div>
                                                </div>
                                                <div class="flex-1 text-slate-600">
                                                    Paket di-pickup kurir dan sedang dalam perjalanan.
                                                </div>
                                            </li>
                                        @endif

                                        @if ($doneAt)
                                            <li class="flex gap-3">
                                                <div class="w-[110px] text-slate-500">
                                                    {{ $doneAt?->format('d-m-Y H:i') ?? '-' }}
                                                </div>
                                                <div class="flex flex-col items-center">
                                                    <div class="w-7 h-7 rounded-full border border-emerald-400 flex items-center justify-center text-[11px] text-emerald-500">↑</div>
                                                </div>
                                                <div class="flex-1 text-slate-600">
                                                    Pesanan tiba di alamat tujuan dan diterima oleh penerima.
                                                </div>
                                            </li>
                                        @endif
                                    </ul>
                                @endif
                            </div>
                        </div>

                        {{-- PRODUK & TOTAL --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 mb-3">Produk Dipesan</h3>

                            {{-- CARD PRODUK: SAMA SEPERTI DI KOLOM KIRI, MAKS 2 CARD, LEBIH DARI ITU SCROLL --}}
                            <div class="px-3 py-3 bg-[#f5f5f5] rounded-2xl space-y-3 max-h-[380px] overflow-y-auto">
                                @foreach ($o->items as $item)
                                    @php
                                        $p      = $item->product;
                                        $name   = $item->product_name ?? optional($p)->name ?? 'Produk';
                                        $variantText = $item->variant_options ?: optional($item->variant)->option_summary;

                                        $categoryName = optional(optional($p)->category)->name;

                                        $rawDescription = $p->description_html ?? null;
                                        $description    = $rawDescription ? Str::limit(strip_tags($rawDescription), 80) : null;

                                        $imgRel = optional(optional($p)->images->first())->image_url;
                                        $img    = $imgRel && file_exists(public_path(ltrim($imgRel, '/')))
                                                    ? asset(ltrim($imgRel, '/'))
                                                    : asset('assets/img/placeholder-product.png');

                                        $qty  = $item->qty ?? 1;

                                        $originalPrice = $p && $p->base_price !== null
                                            ? (float) $p->base_price
                                            : 0;

                                        $discountPrice = $item->price ?? $item->price_snapshot;

                                        if ($discountPrice === null && $originalPrice > 0) {
                                            if ($p && $p->discount_percent !== null) {
                                                $discountPrice = $originalPrice * (1 - ((float) $p->discount_percent / 100));
                                            } else {
                                                $discountPrice = $originalPrice;
                                            }
                                        }

                                        if ($discountPrice === null) {
                                            $discountPrice = $originalPrice;
                                        }

                                        $discountPrice = (float) $discountPrice;

                                        if ($p && $p->discount_percent !== null) {
                                            $discountPercent = (int) $p->discount_percent;
                                        } elseif ($originalPrice > 0 && $discountPrice < $originalPrice) {
                                            $discountPercent = (int) floor((($originalPrice - $discountPrice) / $originalPrice) * 100);
                                        } else {
                                            $discountPercent = null;
                                        }

                                        $subtotal = $discountPrice * $qty;
                                    @endphp

                                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                                        <div class="flex gap-3 p-3">
                                            <div class="w-[150px] rounded-xl overflow-hidden border border-slate-100 bg-[#f5f5f5] shrink-0 flex">
                                                <img src="{{ $img }}"
                                                    alt="{{ $name }}"
                                                    class="w-full h-full object-cover object-center">
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="min-w-0">
                                                        @if ($categoryName)
                                                            <p class="text-[14px] text-emerald-600 font-medium tracking-wide truncate ">
                                                                {{ $categoryName }}
                                                            </p>
                                                        @endif

                                                        <h3 class="text-[16px] sm:text-[16px] font-medium text-slate-900 leading-[1.3] pb-[5px] line-clamp-2 min-h-[40px]">
                                                            {{ $name }}
                                                        </h3>

                                                        @if ($description || $variantText)
                                                            <p class="mt-0.5 text-[14px] text-slate-500 line-clamp-2">
                                                                @if ($variantText)
                                                                    <span class="font-medium">{{ $variantText }}</span>
                                                                    @if ($description) &mdash; @endif
                                                                @endif
                                                                @if ($description)
                                                                    <span>{{ $description }}</span>
                                                                @endif
                                                            </p>
                                                        @endif
                                                    </div>

                                                    <div class="text-right text-[14px] text-slate-500">
                                                        {{ $qty }} item
                                                    </div>
                                                </div>

                                                <div class="mt-2 text-[15px] flex items-center justify-between">
                                                    <span class="text-slate-500">Subtotal ( x{{ $qty }} )</span>
                                                    <div class="text-right">
                                                        <span class="font-semibold text-slate-900">
                                                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- RINGKASAN TOTAL --}}
                            <div class="mt-4 border-t border-slate-200 pt-3 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Subtotal</span>
                                    <span class="font-semibold text-slate-900">
                                        Rp {{ number_format($o->subtotal_amount, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-slate-500">Ongkir</span>
                                    <span class="font-semibold text-slate-900">
                                        Rp {{ number_format($o->shipping_cost, 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-slate-500">Total Bayar</span>
                                    <span class="font-semibold text-slate-900">
                                        Rp {{ number_format($o->grand_total, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>



                </div>
            @endif
        @endif
    </div>
</section>
@endsection

@push('scripts')
    {{-- Snap JS Midtrans --}}
    <script src="{{ config('midtrans.snap_js_url') }}"
            data-client-key="{{ $midtransClientKey }}"></script>

    <script>
        (function () {
            // ==== Midtrans bayar (script lama kamu) ====
            const buttons = document.querySelectorAll('[data-action="pay-order"]');
            if (buttons.length) {
                const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                buttons.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const orderId = this.dataset.orderId;
                        if (!orderId) return;

                        const originalText = this.textContent;
                        this.disabled = true;
                        this.textContent = 'Memproses...';

                        fetch("{{ route('orders.pay', ':id') }}".replace(':id', orderId), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrf
                            },
                            body: JSON.stringify({})
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (!data.snap_token) {
                                    alert(data.message || 'Tidak dapat memulai pembayaran.');
                                    return;
                                }

                                window.snap.pay(data.snap_token, {
                                    onSuccess: function () {
                                        window.location.href = "{{ route('orders.index', ['tab' => 'completed']) }}?status=success";
                                    },
                                    onPending: function () {
                                        window.location.href = "{{ route('orders.index', ['tab' => 'unpaid']) }}?status=pending";
                                    },
                                    onError: function () {
                                        alert('Pembayaran gagal, silakan coba lagi.');
                                    },
                                    onClose: function () {}
                                });
                            })
                            .catch(() => {
                                alert('Terjadi kesalahan, silakan coba lagi.');
                            })
                            .finally(() => {
                                btn.disabled = false;
                                btn.textContent = originalText;
                            });
                    });
                });
            }

            // ==== COPY KODE PESANAN ====
            document.querySelectorAll('[data-copy-order-code]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const code = this.getAttribute('data-copy-order-code');
                    if (!code) return;

                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(code).then(() => {
                            // efek kecil: ubah ikon sebentar
                            const icon = this.querySelector('i');
                            if (icon) {
                                const old = icon.className;
                                icon.className = 'bi bi-check-lg text-emerald-500';
                                setTimeout(() => { icon.className = old; }, 1200);
                            }
                        });
                    } else {
                        // fallback
                        alert('Kode pesanan: ' + code);
                    }
                });
            });

            // ==== ACCORDION NO PESANAN ====
            const meta = document.querySelector('[data-order-meta]');
            if (meta) {
                const toggleBtn = meta.querySelector('[data-order-meta-toggle]');
                const body      = meta.querySelector('[data-order-meta-body]');
                const icon      = toggleBtn ? toggleBtn.querySelector('i') : null;

                if (toggleBtn && body) {
                    // default: terbuka
                    let open = true;

                    toggleBtn.addEventListener('click', function () {
                        open = !open;
                        body.style.display = open ? 'block' : 'none';
                        toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');

                        if (icon) {
                            icon.className = open ? 'bi bi-chevron-up' : 'bi bi-chevron-down';
                        }
                    });
                }
            }
        })();
    </script>
@endpush

