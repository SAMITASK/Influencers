<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InfluencerCode extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'influencer_id',
        'code',
        'description',
    ];

    public function influencer()
    {
        return $this->belongsTo(UserInfluencer::class, 'influencer_id');
    }
}
