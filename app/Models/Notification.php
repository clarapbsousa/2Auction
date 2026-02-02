<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    public $timestamps  = false;

    protected $fillable = [
        'auctionname',
        'bidvalue',
        'sellerid',
        'imagepath',
        'auctionid',
        'bidderid',
        'auctionurl',
        'bidderurl',
        'reporturl',
        'read',
        'creationdate',
        'type',
    ];

}
