<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfluencer extends Model
{
    use HasFactory;

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
