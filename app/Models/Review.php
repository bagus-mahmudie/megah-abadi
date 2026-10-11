<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Actions\UpdateProductRatingSummary;

class Review extends Model
{
    protected $fillable = [
        'product_id','variant_id','user_id','author_name',
        'rating','title','body','helpful_count',
    ];

    protected static function booted()
    {
        $recalc = function (Review $r) {
            if ($r->product_id) {
                UpdateProductRatingSummary::run((int) $r->product_id);
            }
        };

        static::created($recalc);
        static::updated($recalc);
        static::deleted($recalc);

        // remove these if you don't use SoftDeletes:
        // static::restored($recalc);
        // static::forceDeleted($recalc);
    }

    public function user()    { return $this->belongsTo(User::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
