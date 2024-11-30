<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Str;

class Cart extends Model
{
    use HasFactory;
    protected $guarded = []; // YOLO

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cart) {
            $cart->{$cart->getKeyName()} = (string) Str::uuid();
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

    public function products() {
        return $this->belongsToMany(Product::class, 'products_carts');
    }

    public function workspace() {
        return $this->belongsTo(Workspace::class);
    }
}
