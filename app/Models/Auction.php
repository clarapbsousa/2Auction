<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;


class Auction extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'itemname',
        'startingprice',
        'currentprice',
        'creationdate',
        'increment',
        'deadline',
        'status',
        'subcategory',
        'description',
        'imagepath',
        'seller',
    ];

    

    protected $casts = [
    'currentprice' => 'float',
    'creationdate' => 'datetime',
    'deadline' => 'datetime',
    ];


    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory');
    }
    
    public function seller() : BelongsTo
    {
        return $this->belongsTo(User::class, 'seller');
    }
    
    public function reports()
    {
        return $this->hasMany(Report::class, 'auction_id');
    }
}
