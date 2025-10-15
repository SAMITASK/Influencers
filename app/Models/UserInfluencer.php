<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class UserInfluencer extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $table = 'user_influencer';
    protected $primaryKey = 'id';

    protected $fillable = [
        'firebase_uid',
        'name',
        'phone_number',
        'email',
        'social_handle',
        'code',
        'code_description',
        'status',
        'role',
    ];

    // Ya no necesitas la relación codes() porque el código está directamente en este modelo

    /**
     * Scope para buscar por código
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Verificar si el usuario es influencer
     */
    public function isInfluencer()
    {
        return $this->role === 'influencer';
    }
}
