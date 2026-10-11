<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavorite extends Model
{
    protected $table = 'user_favorites';
    public $timestamps = false; // tabel hanya punya created_at (diisi default), tidak managed Eloquent
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = ['user_id','product_id','created_at'];

    public function product(){ return $this->belongsTo(Product::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
