<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id','variant_id','list_price','sale_price','starts_at','ends_at'
    ];

    protected $casts = [
        'list_price' => 'float',
        'sale_price' => 'float',
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
    ];

    public function product(){ return $this->belongsTo(Product::class); }
    public function variant(){ return $this->belongsTo(ProductVariant::class, 'variant_id'); }

    public function scopeActive($q)
    {
        $now = now();
        return $q->where(function($qq) use ($now) {
            $qq->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
        })->where(function($qq) use ($now) {
            $qq->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
        });
    }
}
