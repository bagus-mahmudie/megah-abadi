<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductRatingsSummary;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Detail produk + ringkasan rating + daftar review + carousel terkait
     */
    public function show(Product $product)
    {
        $product->load(['category', 'images' => fn($q) => $q->orderBy('position')]);

        // Ringkasan rating (fallback nol bila belum ada)
        $summary = ProductRatingsSummary::firstOrNew(
            ['product_id' => $product->id],
            ['avg_rating' => 0, 'total_reviews' => 0, 'count_5' => 0, 'count_4' => 0, 'count_3' => 0, 'count_2' => 0, 'count_1' => 0]
        );

        // List review (pakai pagination)
        $reviews = Review::with('user:id,name,avatar')
            ->where('product_id', $product->id)
            ->latest()
            ->paginate(6)
            ->withQueryString();

        // Harga final
        $hasDiscount = ($product->discount_percent ?? 0) > 0;
        $finalPrice  = $hasDiscount
            ? round((float) $product->base_price * (1 - $product->discount_percent / 100))
            : (float) $product->base_price;

        // Cek apakah user yang login sudah pernah mereview produk ini
        $userReview = auth()->check()
            ? Review::where('product_id', $product->id)
                ->where('user_id', auth()->id())
                ->first()
            : null;
        $alreadyReviewed = (bool) $userReview;

        /* ================== TAMBAHAN: Carousel sesuai sumber klik ================== */

        // Normalisasi sumber “from”
        $from = request()->query('from', 'popular');
        if (! in_array($from, ['popular','newest','bestseller'], true)) {
            $from = 'popular';
        }

        // Query dasar & exclude produk yang sedang dibuka
        $carouselBase = Product::query()
            ->with(['category:id,name', 'images' => fn($q) => $q->orderBy('position')])
            ->whereKeyNot($product->id);

        // Urutan & judul/subjudul berdasarkan ?from=
        switch ($from) {
            case 'newest':
                $carouselBase->orderByDesc('products.created_at')->orderByDesc('products.id');
                $carouselTitle    = 'Produk Terbaru';
                $carouselSubtitle = 'Baru Rilis';
                break;

            case 'bestseller':
                $carouselBase->orderByDesc('products.sold_count')->orderByDesc('products.id');
                $carouselTitle    = 'Produk Terlaris';
                $carouselSubtitle = 'Paling Laku';
                break;

            default: // 'popular'
                $carouselBase->orderByDesc('products.favorite_count')->orderByDesc('products.id');
                $carouselTitle    = 'Produk Populer';
                $carouselSubtitle = 'Disukai Banyak Orang';
                $from = 'popular';
                break;
        }

        // Ambil beberapa item untuk carousel
        $carouselProducts = $carouselBase->limit(12)->get();

        // Fallback kalau kosong (misal sumber klik tidak punya cukup data)
        if ($carouselProducts->isEmpty()) {
            $carouselProducts = Product::query()
                ->with(['category:id,name', 'images' => fn($q) => $q->orderBy('position')])
                ->whereKeyNot($product->id)
                ->orderByDesc('products.favorite_count')->orderByDesc('products.id')
                ->limit(12)->get();

            $from              = 'popular';
            $carouselTitle     = 'Produk Populer';
            $carouselSubtitle  = 'Disukai Banyak Orang';
        }

        /* ========================================================================== */

        return view('products.show', [
            'product'          => $product,
            'finalPrice'       => $finalPrice,
            'hasDiscount'      => $hasDiscount,
            'summary'          => $summary,
            'reviews'          => $reviews,
            'userReview'       => $userReview,
            'alreadyReviewed'  => $alreadyReviewed,

            // — data untuk carousel detail —
            'carouselProducts' => $carouselProducts,
            'carouselFrom'     => $from,            // kirim ke product-card agar menempel di URL ?from=...
            'carouselTitle'    => $carouselTitle,   // judul section
            'carouselSubtitle' => $carouselSubtitle // subjudul kecil
        ]);
    }


    /**
     * Simpan review
     */
    public function storeReview(Request $request, Product $product)
    {
        $data = $request->validate([
            'variant_id' => [
                'nullable',
                Rule::exists('product_variants', 'id')
                    ->where(fn($q) => $q->where('product_id', $product->id)),
            ],
            'rating' => 'required|integer|min:1|max:5',
            'body'   => 'required|string|min:10',
            'agree'  => 'accepted',
        ], [
            'agree.accepted' => 'Anda harus menyetujui syarat dan ketentuan.',
        ]);

        // Cegah double review user untuk produk yang sama
        $already = Review::where('product_id', $product->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($already) {
            return back()
                ->withErrors(['duplicate_review' => 'Anda sudah memberi review untuk produk ini.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $product, $data) {
            Review::create([
                'product_id'    => $product->id,
                'variant_id'    => $data['variant_id'] ?? null,
                'user_id'       => $request->user()->id,
                'author_name'   => null,
                'rating'        => $data['rating'],
                'title'         => null,
                'body'          => $data['body'],
                'helpful_count' => 0,
            ]);

            // Hitung ulang ringkasan
            $this->recalcSummary($product->id);
        });

        return back()->with('success', 'Terima kasih! Ulasan berhasil dikirim.');
    }

    /**
     * Hapus review lalu update ringkasan.
     */
    public function destroyReview(Request $request, Product $product, Review $review)
    {
        if ((int) $review->product_id !== (int) $product->id) {
            abort(404);
        }

        if (! $request->user() || ($request->user()->id !== (int) $review->user_id && ! $request->user()->is_admin)) {
            abort(403);
        }

        DB::transaction(function () use ($product, $review) {
            $review->delete();
            $this->recalcSummary($product->id);
        });

        return back()->with('success', 'Review berhasil dihapus.');
    }

    public function filter(Request $request)
    {
        $query = Product::query()
            ->with([
                'category:id,name',
                'images' => fn($q) => $q->orderBy('position'),
            ]);

        if ($q = $request->get('q')) {
            $query->where('name', 'like', "%{$q}%");
        }
        if ($catIds = $request->get('categories')) {
            $query->whereIn('category_id', (array) $catIds);
        }

        switch ($request->get('sort', 'popular')) {
            case 'newest':
                $query->orderByDesc('products.created_at')->orderByDesc('products.id');
                break;
            case 'bestseller':
                $query->orderByDesc('products.sold_count')->orderByDesc('products.id');
                break;
            default:
                $query->orderByDesc('products.favorite_count')->orderByDesc('products.id');
                break;
        }

        $products   = $query->paginate(24)->withQueryString();
        $categories = Category::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'grid'       => view('products._grid', compact('products'))->render(),
                'pagination' => view('products._pagination', compact('products'))->render(),
                'total'      => $products->total(),
            ]);
        }

        return view('products.filter-product', compact('products', 'categories'));
    }

    /* ===================== Helper ===================== */

    private function recalcSummary(int $productId): void
    {
        /** @var ProductRatingsSummary $sum */
        $sum = ProductRatingsSummary::firstOrNew(
            ['product_id' => $productId],
            ['avg_rating' => 0, 'total_reviews' => 0, 'count_5' => 0, 'count_4' => 0, 'count_3' => 0, 'count_2' => 0, 'count_1' => 0]
        );

        $counts = Review::select('rating', DB::raw('COUNT(*) as c'))
            ->where('product_id', $productId)
            ->groupBy('rating')
            ->pluck('c', 'rating');

        $total = (int) $counts->sum();
        $avg   = $total ? round(Review::where('product_id', $productId)->avg('rating'), 1) : 0.0;

        $sum->total_reviews = $total;
        $sum->avg_rating    = $avg;
        $sum->count_5       = (int) ($counts[5] ?? 0);
        $sum->count_4       = (int) ($counts[4] ?? 0);
        $sum->count_3       = (int) ($counts[3] ?? 0);
        $sum->count_2       = (int) ($counts[2] ?? 0);
        $sum->count_1       = (int) ($counts[1] ?? 0);
        $sum->save();
    }
}
