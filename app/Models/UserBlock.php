<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'blocker_id',
        'blocked_id',
        'block_date',
    ];

    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocker_id');
    }

    public function blocked()
    {
        return $this->belongsTo(User::class, 'blocked_id');
    }
}