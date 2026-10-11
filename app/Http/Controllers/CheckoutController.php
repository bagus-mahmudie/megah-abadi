<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\Shipping\BiteshipClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\UserAddress;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $cart = $this->getCurrentCart($request);

        return view('checkout.index', [
            'cart' => $cart,
        ]);
         $cart = $this->getCurrentCart($request);
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang kamu masih kosong.');
        }

        $user       = $request->user();
        $addresses  = $user
            ? $user->addresses()->orderByDesc('is_default')->latest()->get()
            : collect();

        return view('checkout.index', [
            'cart'       => $cart,
            'addresses'  => $addresses,
            // kalau sebelumnya kamu sudah kirim variabel lain, tetap kirim juga di sini
        ]);
    }

    public function shippingRates(Request $request, BiteshipClient $biteship)
    {
        $request->validate([
            'receiver_postal_code'   => 'required|string',
            'receiver_address_line1' => 'required|string',
        ]);

        $cart = $this->getCurrentCart($request);

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang kosong, tidak bisa menghitung ongkir.',
            ], 400);
        }

        $items = $this->buildBiteshipItemsFromCart($cart);

        $destination = $request->receiver_postal_code;

        $payload = [
            // UNTUK TEST: asal = tujuan
            'origin_postal_code'      => $destination,
            'destination_postal_code' => $destination,
            'couriers'                => 'jne,jnt,sicepat,anteraja',
            'items'                   => $items,
        ];

        $result = $biteship->getRates($payload);

        if (isset($result['success']) && $result['success'] === false) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Gagal mengambil tarif ongkir dari Biteship.',
            ], 502);
        }

        return response()->json($result);
    }

        /**
     * Autocomplete area (provinsi/kota/kecamatan/kode pos) via Biteship Maps API.
     *
     * Digunakan oleh form alamat di halaman checkout.
     */
    public function searchAreas(Request $request, BiteshipClient $biteship)
    {
        $request->validate([
            'q' => 'required|string|max:255',
        ]);

        $result = $biteship->searchAreas($request->q, 'multi');

        if (isset($result['success']) && $result['success'] === false) {
            return response()->json([
                'success' => false,
                'message' => $result['error'] ?? 'Gagal ambil data area.',
            ], 502);
        }

        $areasRaw = $result['areas'] ?? [];

        $areas = collect($areasRaw)->map(function ($area) {
            return [
                'id'          => $area['id'] ?? null,
                'label'       => $area['name'] ?? '',
                'province'    => $area['administrative_division_level_1_name'] ?? '',
                'city'        => $area['administrative_division_level_2_name'] ?? '',
                'district'    => $area['administrative_division_level_3_name'] ?? '',
                'postal_code' => (string)($area['postal_code'] ?? ''),
                'lat'         => $area['latitude']  ?? null,
                'lng'         => $area['longitude'] ?? null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'areas'   => $areas,
        ]);
    }


    public function placeOrder(Request $request, BiteshipClient $biteship)
    {
        $request->validate([
            'receiver_name'          => 'required|string',
            'receiver_phone'         => 'required|string',
            'receiver_address_line1' => 'required|string',
            'receiver_postal_code'   => 'required|string',
            'receiver_province'      => 'required|string',
            'receiver_city'          => 'required|string',
            'receiver_district'      => 'required|string',
            'shipping_courier_code'  => 'required|string',
            'shipping_courier_name'  => 'required|string',
            'shipping_price'         => 'required|numeric',
        ]);


        $cart = $this->getCurrentCart($request);

        if (!$cart || $cart->items->isEmpty()) {
            return back()->with('error', 'Keranjang belanja kosong.');
        }

        return DB::transaction(function () use ($request, $cart) {

            $subtotal   = 0;
            $totalItems = 0;

            foreach ($cart->items as $item) {
                $subtotal   += $item->price_snapshot * $item->qty;
                $totalItems += $item->qty;
            }

            $shippingCost = (float) $request->shipping_price;
            $grandTotal   = $subtotal + $shippingCost;

            // 1. Buat Order
            $order = Order::create([
                'user_id'               => $cart->user_id,
                'cart_id'               => $cart->id,
                'code'                  => $this->generateOrderCode(),
                'total_items'           => $totalItems,
                'subtotal_amount'       => $subtotal,
                'shipping_cost'         => $shippingCost,
                'grand_total'           => $grandTotal,
                'currency'              => 'IDR',
                'status'                => 'pending',
                'payment_status'        => 'unpaid',
                'payment_provider'      => 'midtrans',
                'shipping_provider'     => 'biteship',
                'shipping_courier_code' => $request->shipping_courier_code,
                'shipping_courier_name' => $request->shipping_courier_name,
                'receiver_name'         => $request->receiver_name,
                'receiver_phone'        => $request->receiver_phone,
                'receiver_email'        => auth()->user()->email ?? null,
                'receiver_address_line1'=> $request->receiver_address_line1,
                'receiver_address_line2'=> $request->receiver_address_line2,
                'receiver_postal_code'  => $request->receiver_postal_code,
                'receiver_province'     => $request->receiver_province,
                'receiver_city'         => $request->receiver_city,
                'receiver_district'     => $request->receiver_district,
                'receiver_latitude'     => $request->receiver_latitude,
                'receiver_longitude'    => $request->receiver_longitude,
                'customer_notes'        => $request->customer_notes,
            ]);

            // 2. Buat OrderItem dari CartItem
            foreach ($cart->items as $cartItem) {
                $product = $cartItem->product;
                $variant = $cartItem->variant;

                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $product->id,
                    'variant_id'      => $variant?->id,
                    'product_name'    => $product->name,
                    'product_sku'     => $variant?->sku ?? $product->sku,
                    'variant_options' => $variant?->options,
                    'qty'             => $cartItem->qty,
                    'price'           => $cartItem->price_snapshot,
                    'total'           => $cartItem->price_snapshot * $cartItem->qty,
                    'weight_gram'     => $variant?->weight_gram ?? $product->weight_gram,
                    'volume_ml'       => $variant?->volume_ml ?? $product->volume_ml,
                ]);
            }

            // 3. Bersihkan cart (opsional tapi direkomendasikan)
            $cart->items()->delete();
            // $cart->delete(); // kalau mau hapus cart sekalian

            // 4. Redirect ke halaman semua pesanan (tab Belum Bayar)
            return redirect()
                ->route('orders.index', ['tab' => 'unpaid'])
                ->with('success', 'Pesanan berhasil dibuat. Silakan klik tombol Bayar pada pesanan Anda.');
        });

        $validated = $request->validate([
        'address_id'            => ['nullable', 'exists:user_addresses,id'],
        'receiver_name'         => ['required_without:address_id', 'string', 'max:255'],
        'receiver_phone'        => ['required_without:address_id', 'string', 'max:32'],
        'receiver_email'        => ['nullable', 'email'],
        'receiver_address_line1'=> ['required_without:address_id', 'string', 'max:255'],
        'receiver_address_line2'=> ['nullable', 'string', 'max:255'],
        'receiver_postal_code'  => ['required_without:address_id', 'string', 'max:16'],
        'receiver_province'     => ['nullable', 'string', 'max:255'],
        'receiver_city'         => ['nullable', 'string', 'max:255'],
        'receiver_city_id'      => ['nullable', 'string', 'max:255'],
        'receiver_district'     => ['nullable', 'string', 'max:255'],
        'receiver_district_id'  => ['nullable', 'string', 'max:255'],
        'shipping_courier_code' => ['required', 'string'],
        'shipping_courier_name' => ['required', 'string'],
        'save_address'          => ['nullable', 'boolean'],
        'address_label'         => ['nullable', 'string', 'max:255'],
    ]);

    $user = $request->user();

    DB::beginTransaction();

    try {
        // 1. Ambil data alamat yang akan dipakai untuk order
        if (!empty($validated['address_id'])) {
            $address = UserAddress::where('user_id', $user->id)
                ->findOrFail($validated['address_id']);

            $receiverData = $address->only([
                'receiver_name',
                'receiver_phone',
                'receiver_email',
                'receiver_address_line1',
                'receiver_address_line2',
                'receiver_postal_code',
                'receiver_country_code',
                'receiver_province',
                'receiver_city',
                'receiver_city_id',
                'receiver_district',
                'receiver_district_id',
                'receiver_latitude',
                'receiver_longitude',
            ]);
        } else {
            $receiverData = [
                'receiver_name'          => $validated['receiver_name'],
                'receiver_phone'         => $validated['receiver_phone'] ?? null,
                'receiver_email'         => $validated['receiver_email'] ?? $user?->email,
                'receiver_address_line1' => $validated['receiver_address_line1'],
                'receiver_address_line2' => $validated['receiver_address_line2'] ?? null,
                'receiver_postal_code'   => $validated['receiver_postal_code'],
                'receiver_country_code'  => 'ID',
                'receiver_province'      => $validated['receiver_province'] ?? null,
                'receiver_city'          => $validated['receiver_city'] ?? null,
                'receiver_city_id'       => $validated['receiver_city_id'] ?? null,
                'receiver_district'      => $validated['receiver_district'] ?? null,
                'receiver_district_id'   => $validated['receiver_district_id'] ?? null,
                'receiver_latitude'      => null,
                'receiver_longitude'     => null,
            ];

            // 2. Kalau user centang "simpan alamat", buat record di user_addresses
            if ($user && $request->boolean('save_address')) {
                $address = new UserAddress(array_merge($receiverData, [
                    'label'      => $validated['address_label'] ?? null,
                    'user_id'    => $user->id,
                    'is_default' => false,
                ]));

                $address->save();
            }
        }

        // 3. Hitung total_items, subtotal, ongkir, grand_total (logika lama kamu)
        $items       = $cart->items;
        $totalItems  = $items->sum('qty');
        $subtotal    = $items->sum(fn ($item) => $item->qty * $item->price_snapshot);
        $shippingCost= $request->input('shipping_cost', 0);
        $grandTotal  = $subtotal + $shippingCost;

        // 4. Buat order (kolom mengikuti tabel orders)
        $order = Order::create(array_merge([
            'user_id'               => $cart->user_id,
            'cart_id'               => $cart->id,
            'code'                  => $this->generateOrderCode(),
            'total_items'           => $totalItems,
            'subtotal_amount'       => $subtotal,
            'shipping_cost'         => $shippingCost,
            'grand_total'           => $grandTotal,
            'currency'              => 'IDR',
            'status'                => 'pending',
            'payment_status'        => 'unpaid',
            'payment_provider'      => 'midtrans',
            'shipping_provider'     => 'biteship',
            'shipping_courier_code' => $validated['shipping_courier_code'],
            'shipping_courier_name' => $validated['shipping_courier_name'],
        ], $receiverData));

        // 5. Simpan order_items (pakai logika yang sudah ada)
        foreach ($items as $cartItem) {
            OrderItem::create([
                'order_id'      => $order->id,
                'product_id'    => $cartItem->product_id,
                'variant_id'    => $cartItem->variant_id,
                'product_name'  => $cartItem->product->name,
                'product_sku'   => $cartItem->product->sku,
                'variant_options'=> $cartItem->metadata,
                'qty'           => $cartItem->qty,
                'price'         => $cartItem->price_snapshot,
                'total'         => $cartItem->qty * $cartItem->price_snapshot,
                'weight_gram'   => $cartItem->product->weight_gram,
                'volume_ml'     => $cartItem->product->volume_ml,
            ]);
        }

        DB::commit();

        // lanjut ke midtrans / halaman pembayaran yang sudah kamu buat
        // ...

    } catch (\Throwable $e) {
        DB::rollBack();
        throw $e;
    }
    }

    protected function getCurrentCart(Request $request): ?Cart
    {
        return Cart::with('items.product', 'items.variant')
            ->where('user_id', auth()->id())
            ->first();
    }

    protected function buildBiteshipItemsFromCart(Cart $cart): array
    {
        $items = [];

        foreach ($cart->items as $item) {
            $product = $item->product;
            $variant = $item->variant;

            $weight = $variant?->weight_gram ?? $product->weight_gram ?? 0;

            $items[] = [
                'name'        => $product->name,
                'description' => $product->sku,
                'value'       => (float) $item->price_snapshot,
                'quantity'    => $item->qty,
                'weight'      => (int) $weight,
            ];
        }

        return $items;
    }

    protected function generateOrderCode(): string
    {
        $prefix = 'MA-' . now()->format('Ymd');
        $random = strtoupper(Str::random(4));

        return $prefix . '-' . $random;
    }
}
