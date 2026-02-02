<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    use HasFactory;

    public $timestamps  = false;

    protected $fillable = ['value', 'bidder', 'auctionid'];

    public function user()
    {
        return $this->belongsTo(User::class, 'bidder', 'id');
    }

    public function bid()
    {
        return $this->belongsTo(Bid::class,'auctionid', 'id');
    }
}
