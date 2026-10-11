<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id','sku','options','price','stock','weight_gram','volume_ml','is_active'
    ];

    protected $casts = [
        'options'   => 'array',
        'price'     => 'float',
        'is_active' => 'boolean',
    ];

    // ---- Relationships
    public function product(){ return $this->belongsTo(Product::class); }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'variant_id')->orderBy('position');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'variant_id')->latest();
    }

    public function prices() // jika product_prices dipakai di level varian
    {
        return $this->hasMany(ProductPrice::class, 'variant_id');
    }
}
