<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartDetail extends Model
{
    protected $table = 'cartdet';
    protected $primaryKey = 'intCartdetId';
    public $timestamps = false;

    protected $fillable = [
        'coupon',
        'decCartdetPu',
        'dateCartdetFreg',
        'intBoletoId'
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function () {
            return false;
        });

        static::creating(function () {
            return false;
        });

        static::updating(function () {
            return false;
        });

        static::deleting(function () {
            return false;
        });
    }

    public function getTypeEntrie()
    {
        return match ($this->intBoletoId) {
            11 => 'ENTRADA GENERAL TERROR',
            17 => 'ENTRADA LIGHT TERROR',
            default => 'Desconocido',
        };
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'intCartId', 'intCartId');
    }

    public static function rawColumnCartDate()
    {
        return function ($query) {
            $query->select('dateCartFreg')
                ->from('cart')
                ->whereColumn('cart.intCartId', 'cartdet.intCartId')
                ->limit(1);
        };
    }
}
