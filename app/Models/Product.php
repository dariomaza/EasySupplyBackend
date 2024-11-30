<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    protected $guarded = []; // YOLO

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->{$product->getKeyName()} = (string) Str::uuid();
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }

    public function carts() {
        return $this->belongsToMany(Cart::class, 'products_carts');
    }

    public function orders() {
        return $this->belongsToMany(Order::class, 'product_orders');
    }
}
