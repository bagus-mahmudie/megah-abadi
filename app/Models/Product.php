<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id','name','brand','sku','description_html','spec_html',
        'base_price','discount_percent','currency','favorite_count', 'sold_count',
        'average_rating','rating_count','min_qty','max_qty','step_qty',
        'weight_gram','volume_ml','status','seller_id',
    ];

    protected $appends = ['final_price','has_discount'];

    // ---- Casts (angka keluar sebagai number)
    protected function basePrice(): Attribute
    {
        return Attribute::make(get: fn($v) => is_null($v) ? null : (float)$v);
    }
    protected function discountPercent(): Attribute
    {
        return Attribute::make(get: fn($v) => is_null($v) ? null : (float)$v);
    }
    protected function averageRating(): Attribute
    {
        return Attribute::make(get: fn($v) => is_null($v) ? 0.0 : (float)$v);
    }

    // ---- Relationships
    public function category(){ return $this->belongsTo(Category::class); }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->orderBy('position');
    }

    public function variants(){ return $this->hasMany(ProductVariant::class); }
    public function activeVariants(){ return $this->hasMany(ProductVariant::class)->where('is_active', true); }

    public function ratingsSummary(){ return $this->hasOne(ProductRatingsSummary::class, 'product_id'); }

    public function reviews(){ return $this->hasMany(Review::class)->latest(); }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'user_favorites')
                    ->withPivot('created_at');
    }

    public function cartItems(){ return $this->hasMany(CartItem::class); }

    public function prices() // jika tabel product_prices dipakai
    {
        return $this->hasMany(ProductPrice::class);
    }

    // ---- Accessors
    protected function hasDiscount(): Attribute
    {
        return Attribute::make(get: fn() => !is_null($this->discount_percent) && $this->discount_percent > 0);
    }

    protected function finalPrice(): Attribute
    {
        return Attribute::make(get: function () {
            $price = (float)($this->base_price ?? 0);
            if ($this->discount_percent) {
                $price -= $price * ((float)$this->discount_percent / 100);
            }
            return round($price, 2);
        });
    }

    // Harga final untuk varian tertentu (kalau varian override price)
    public function priceForVariant(?ProductVariant $variant): float
    {
        $price = (float)($variant?->price ?? $this->base_price ?? 0);
        if ($this->discount_percent) {
            $price -= $price * ((float)$this->discount_percent / 100);
        }
        return round($price, 2);
    }

    // ---- Scopes
    public function scopeActive(Builder $q){ return $q->where('status', 'active'); }

    public function scopeCategory(Builder $q, int|string $category): Builder
    {
        if (is_numeric($category)) return $q->where('category_id', (int)$category);
        return $q->whereHas('category', fn($qq) => $qq->where('slug', $category));
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        if (!$term) return $q;
        return $q->where(function ($qq) use ($term) {
            $qq->where('name', 'like', "%{$term}%")
               ->orWhere('brand', 'like', "%{$term}%")
               ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    public function addImage(string $url, ?string $alt = null, int $position = 0, ?int $variantId = null): ProductImage
    {
        return $this->images()->create([
            'image_url'  => $url,
            'alt'        => $alt,
            'position'   => $position,
            'variant_id' => $variantId,
        ]);
    }
}
