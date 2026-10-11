@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto">
    <h1 class="text-lg font-bold mb-4">
        Tracking Pengiriman #{{ $shipment->id }}
    </h1>

    <div class="mb-4 text-sm">
        <p><strong>Kurir:</strong> {{ $shipment->courier_company }} ({{ $shipment->courier_service }})</p>
        <p><strong>No. Resi:</strong> {{ $shipment->waybill_id ?? '-' }}</p>
        <p><strong>Status:</strong> {{ $shipment->status }}</p>
    </div>

    @if($tracking && ($tracking['success'] ?? false))
        <h2 class="font-semibold mb-2">Riwayat Status</h2>
        <ul class="text-xs space-y-1">
            @foreach(array_reverse($tracking['history'] ?? []) as $item)
                <li>
                    <span class="font-semibold">{{ $item['status'] ?? '-' }}</span>
                    - {{ $item['note'] ?? '' }}
                    <span class="text-gray-500">({{ $item['updated_at'] ?? '' }})</span>
                </li>
            @endforeach
        </ul>

        @if(!empty($tracking['link']))
            <a href="{{ $tracking['link'] }}" class="inline-block mt-3 text-blue-600 underline" target="_blank">
                Lihat di halaman tracking Biteship
            </a>
        @endif
    @else
        <p class="text-sm text-gray-500">
            Belum ada data tracking untuk pengiriman ini.
        </p>
    @endif
</div>
@endsection
