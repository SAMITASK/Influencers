<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'cart';
    protected $primaryKey = 'intCartId';
    public $timestamps = false;

    protected $fillable = [
        'coupon',
        'dateCartFreg'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(fn() => false);
        static::creating(fn() => false);
        static::updating(fn() => false);
        static::deleting(fn() => false);
    }

    public function details()
    {
        return $this->hasMany(CartDetail::class, 'intCartId', 'intCartId');
    }
}
