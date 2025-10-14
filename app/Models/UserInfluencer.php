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
        'status',
    ];

    public function codes()
    {
        return $this->hasMany(InfluencerCode::class, 'influencer_id', 'id')
            ->select('id', 'influencer_id', 'code');
    }
}
