@extends('layouts.app')

@section('content')
<div class="container my-[100px]">
    <h2>Bayar Pesanan {{ $order->code }}</h2>
    <p>Total: <strong>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</strong></p>

    <button id="pay-button" class="btn btn-primary">Bayar Sekarang</button>
</div>

<script src="{{ config('midtrans.snap_js_url') }}"
        data-client-key="{{ $clientKey }}"></script>
<script type="text/javascript">
    var payButton = document.getElementById('pay-button');
    payButton.addEventListener('click', function () {
        window.snap.pay(@json($snapToken), {
            onSuccess: function (result) {
                window.location.href = '{{ route('orders.index', ['tab' => 'completed']) }}?status=success';
            },
            onPending: function (result) {
                window.location.href = '{{ route('orders.index', ['tab' => 'unpaid']) }}?status=pending';
            },
            onError: function (result) {
                alert('Pembayaran gagal, silakan coba lagi.');
            },
            onClose: function () {
                alert('Kamu menutup popup tanpa menyelesaikan pembayaran.');
            }
        });
    });
</script>
@endsection
