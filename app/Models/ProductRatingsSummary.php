<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductRatingsSummary extends Model
{
    use HasFactory;

    protected $table = 'product_ratings_summary';
    protected $primaryKey = 'product_id';
    public $incrementing = false;

    protected $fillable = [
        'product_id','avg_rating','total_reviews',
        'count_5','count_4','count_3','count_2','count_1',
    ];

    protected $casts = [
        'avg_rating'   => 'float',
        'total_reviews'=> 'int',
        'count_5' => 'int','count_4' => 'int','count_3' => 'int','count_2' => 'int','count_1' => 'int',
    ];

    public function product(){ return $this->belongsTo(Product::class); }
}
