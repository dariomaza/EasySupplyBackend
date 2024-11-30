<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Str;

class Supplier extends Model
{
    use HasFactory;

    protected $guarded = []; // YOLO

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            $supplier->{$supplier->getKeyName()} = (string) Str::uuid();
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

    public function workspace()  {
        return $this->belongsTo(Workspace::class);
    }

    public function products() {
        return $this->hasMany(Product::class);
    }
}
