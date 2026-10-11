<?php


namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /** Ambil / buat cart milik user login */
    
    private function currentCart(Request $request): Cart
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['session_id' => null]
        );

        return $cart;
    }

    /** GET /cart */
    public function index(Request $request)
    {
        // === Jika guest, langsung pulangkan ke Home ===
        if (!Auth::check()) {
            return redirect()->route('home'); // pastikan route('home') ada
        }
        $cart = $request->user()
            ? Cart::with([
                'items.product.category:id,name',
                'items.product.images' => fn($q) => $q->orderBy('position')
            ])->firstOrCreate(['user_id' => $request->user()->id], ['session_id' => null])
            : null;

        $items = $cart?->items()->with(['product.category','product.images' => fn($q)=>$q->orderBy('position')])->get() ?? collect();

        // hitung ringkasan
        $subtotal = $items->sum(fn($it) => ($it->price_snapshot ?? 0) * ($it->qty ?? 0));
        $shipping = 0;
        $discount = 0;
        $total    = $subtotal + $shipping - $discount;

        return view('cart.index', compact('cart','items','subtotal','shipping','discount','total'));
    }

    /** POST /cart/add/{product} */
    public function add(Request $request, Product $product)
    {
        if (!$request->user()) {
            return response()->json(['ok' => false, 'need_login' => true], 401);
        }

        $qty = max(1, (int) $request->input('qty', 1));

        // harga final (sesuai logic show)
        $base = (float) ($product->base_price ?? 0);
        $disc = (float) ($product->discount_percent ?? 0);
        $final = $disc > 0 ? round($base * (1 - $disc/100)) : $base;

        $cart = $this->currentCart($request);

        DB::transaction(function () use ($cart, $product, $qty, $final) {
            $item = $cart->items()->firstOrNew([
                'product_id' => $product->id,
                'variant_id' => null,
            ]);

            // qty awal 0 kalau belum pernah ada
            $current = (int) ($item->qty ?? 0);

            $item->qty = $current + $qty;       // tidak ada auto +1 lagi
            $item->price_snapshot = $final;
            $item->save();
        });

        // badge = JUMLAH ITEM UNIK (bukan total qty)
        $count = $cart->items()->count();
        return response()->json(['ok' => true, 'cart_count' => $count]);
    }

    /** PATCH /cart/item/{item}/qty */
    public function updateQty(Request $request, CartItem $item)
    {
        if (!$request->user()) abort(401);

        // Pastikan item milik cart user ini
        $cart = $this->currentCart($request);
        if ((int)$item->cart_id !== (int)$cart->id) abort(403);

        $qty = max(1, (int) $request->input('qty', 1));
        $item->qty = $qty;
        $item->save();

        // balikan ringkasan
        $items    = $cart->items()->get();
        $subtotal = $items->sum(fn($it) => ($it->price_snapshot ?? 0) * ($it->qty ?? 0));
        $shipping = 0;
        $discount = 0;
        $total    = $subtotal + $shipping - $discount;
        $count = $cart->items()->count();

        return response()->json([
            'ok'        => true,
            'line_total'=> $item->price_snapshot * $item->qty,
            'subtotal'  => $subtotal,
            'total'     => $total,
            'cart_count'=> $count,
        ]);
    }

    /** DELETE /cart/item/{item} */
    public function remove(Request $request, CartItem $item)
    {
        if (!$request->user()) abort(401);
        $cart = $this->currentCart($request);
        if ((int)$item->cart_id !== (int)$cart->id) abort(403);

        $item->delete();

        $items    = $cart->items()->get();
        $subtotal = $items->sum(fn($it) => ($it->price_snapshot ?? 0) * ($it->qty ?? 0));
        $shipping = 0;
        $discount = 0;
        $total    = $subtotal + $shipping - $discount;
        $count = $cart->items()->count();

        return response()->json([
            'ok'        => true,
            'subtotal'  => $subtotal,
            'total'     => $total,
            'cart_count'=> $count,
        ]);
    }

    /** GET /cart/count (untuk badge navbar) */
    public function count(Request $request)
    {
        if (!$request->user()) return response()->json(['count' => 0]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id], ['session_id' => null]);
        $count = $cart->items()->count();
        return response()->json(['count' => $count]);
    }
}