<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Models\Product;
use App\Models\Category;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MidtransNotificationController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\UserAddressController;

// ========== AUTH (EMAIL / PASSWORD) ==========
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ========== GOOGLE OAUTH ==========
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('oauth.google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('oauth.google.callback');

// Halaman info "cek email"
Route::get('/email/verify', function () {
    // kalau kamu ingin pakai modal di halaman terakhir, boleh redirect ke halaman sebelumnya:
    // return redirect()->to(url()->previous() ?: route('home'))->with('show_verify', true);
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// LINK VERIFIKASI (ini yang WAJIB ada — nama: verification.verify)
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // set email_verified_at
    return redirect()->route('home')->with('success', 'Email berhasil diverifikasi.');
})->middleware(['auth', 'signed'])->name('verification.verify');

// Kirim ulang link verifikasi
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'verification-link-sent');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/register/confirm/{id}', [AuthController::class, 'confirmPending'])
    ->middleware('signed')      // pastikan link valid dan belum diutak-atik
    ->name('register.confirm');
// Route::view('/', 'home')->name('home');

Route::get('/', function () {
    // POPULER (untuk carousel lama)
    $popularProducts = Product::with([
            'category',
            'images' => fn ($q) => $q->orderBy('position'),
        ])
        ->when(Schema::hasColumn('products', 'is_active'), fn ($q) => $q->where('is_active', 1))
        ->orderByDesc('favorite_count')
        ->orderByDesc('id')
        ->take(5)
        ->get();

    // TERBARU
    $newestProducts = Product::with([
            'category',
            'images' => fn ($q) => $q->orderBy('position'),
        ])
        ->when(Schema::hasColumn('products', 'is_active'), fn ($q) => $q->where('is_active', 1))
        ->orderByDesc('created_at')
        ->orderByDesc('id')
        ->take(5)
        ->get();

    // TERLARIS (pakai sold_count kalau ada, fallback ke favorite_count)
    $bestsellerProducts = Product::with([
            'category',
            'images' => fn ($q) => $q->orderBy('position'),
        ])
        ->when(Schema::hasColumn('products', 'is_active'), fn ($q) => $q->where('is_active', 1))
        ->when(
            Schema::hasColumn('products', 'sold_count'),
            fn ($q) => $q->orderByDesc('sold_count')->orderByDesc('id'),
            fn ($q) => $q->orderByDesc('favorite_count')->orderByDesc('id') // fallback aman
        )
        ->take(5)
        ->get();

    return view('home', [
        // carousel “popular” lama tetap jalan
        'popularProducts'   => $popularProducts,
        // carousel baru
        'newestProducts'    => $newestProducts,
        'bestsellerProducts'=> $bestsellerProducts,
    ]);
})->name('home');


// Route::get('/products/{product}', function (Product $product) {
//     $product->load(['category', 'images' => fn($q) => $q->orderBy('position')]);
//     return view('products.show', compact('product'));
// })->name('products.show');

Route::get('/products/filter', function (Request $request) {
    $query = Product::query()
        ->with([
            'category:id,name',
            'images' => fn($q) => $q->orderBy('position')
        ]);

    // FILTER
    if ($q = $request->get('q')) {
        $query->where('name', 'like', "%{$q}%");
    }
    if ($catIds = $request->get('categories')) {
        $query->whereIn('category_id', (array) $catIds);
    }

    // SORT
    switch ($request->get('sort', 'popular')) {
        case 'newest':
            $query->orderByDesc('products.created_at');
            break;
        case 'bestseller':
            $query->orderByDesc('products.sold_count')->orderByDesc('products.id');
            break;
        default:
            $query->orderByDesc('products.favorite_count')->orderByDesc('products.id');
            break;
    }

    $products = $query->paginate(9)->withQueryString();
    $categories = Category::orderBy('name')->get();

    // === AJAX: kembalikan potongan HTML saja ===
    if ($request->ajax()) {
        $gridHtml = view('products._grid', compact('products'))->render();
        $paginationHtml = view('products._pagination', compact('products'))->render();
        $showCount = collect($products->items())->take(9)->count();
        return response()->json([
            'grid'       => $gridHtml,
            'pagination' => $paginationHtml,
            'showing'    => $showCount,
            'total'      => $products->total(),
        ]);
    }

    return view('products.filter-product', compact('products', 'categories'));
})->name('products.filter');

Route::get('/products/{product}', [\App\Http\Controllers\ProductController::class, 'show'])
     ->name('products.show');

// simpan review (WAJIB login; tambah 'verified' bila perlu)
Route::post('/products/{product}/reviews', [ProductController::class, 'storeReview'])
    ->middleware(['auth'])
    ->name('products.reviews.store');

// Kirim link reset password ke email
Route::post('/password/email', function (Request $request) {
    $validated = $request->validate([
        'email' => 'required|email|exists:users,email',
    ], [
        'email.required' => 'Email wajib diisi.',
        'email.email'    => 'Format email tidak valid.',
        'email.exists'   => 'Email tidak terdaftar.',
    ]);

    $status = Password::sendResetLink(['email' => $validated['email']]);

    return back()->with('auth_tab', 'forgot')->with('forgot_status',
        $status === Password::RESET_LINK_SENT
            ? __($status)
            : __($status)
    );
})->name('password.email');

// (opsional) halaman reset password bawaan Laravel
Route::get('/password/reset/{token}', function ($token) {
    // Ambil data yang biasa kamu kirim ke home
    $products = Product::query()
        ->with(['category', 'images' => fn($q) => $q->orderBy('position')])
        ->active()
        ->latest('id')
        ->take(12)
        ->get();

    // Render view CONTENT (home), bukan layout
    return view('home', compact('products'))
        ->with('token', $token); // token dipakai di partial reset modal
})->name('password.reset');

Route::post('/password/reset', function (Request $request) {
    $request->validate([
        'token'    => 'required',
        'email'    => 'required|email',
        'password' => 'required|min:6|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => \Illuminate\Support\Facades\Hash::make($password),
            ])->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('home')->with('success', __($status))
        : back()->withErrors(['email' => __($status)])->with('auth_tab', 'forgot');
})->name('password.update');

// ===== CART (auth only untuk menambah/mengelola keranjang) =====
Route::middleware('auth')->group(function () {
    Route::get('/cart',            [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/item/{item}/qty', [CartController::class, 'updateQty'])->name('cart.item.qty');
    Route::delete('/cart/item/{item}',    [CartController::class, 'remove'])->name('cart.item.remove');
        // Halaman semua pesanan
    Route::get('/orders', [OrderController::class, 'index'])
        ->name('orders.index');

    // Ajax untuk bayar pesanan (Snap Midtrans)
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay'])
        ->name('orders.pay');
});

// badge count (boleh tanpa auth: kalau belum login -> 0)
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
     // NEW: autocomplete area dari Biteship
     // autocomplete area Biteship
    Route::get('/checkout/areas', [CheckoutController::class, 'searchAreas'])
        ->name('checkout.search-areas');

    // simpan alamat user
    Route::post('/user/addresses', [UserAddressController::class, 'store'])
        ->name('user-addresses.store');
    Route::post('/checkout/shipping-rates', [CheckoutController::class, 'shippingRates'])->name('checkout.shipping-rates');
    Route::post('/checkout', [CheckoutController::class, 'placeOrder'])->name('checkout.place-order');
});

Route::get('/shipments/{id}', [ShipmentController::class, 'show'])
    ->name('shipments.show');