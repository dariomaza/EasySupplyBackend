<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Str;

class Workspace extends Model
{
    use HasFactory;

    protected $guarded = []; // YOLO

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($workspace) {
            $workspace->{$workspace->getKeyName()} = (string) Str::uuid();
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

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_workspaces')
                    ->withPivot('mainUser');
    }

    public function suppliers()
    {
        return $this->hasMany(Supplier::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }
}
