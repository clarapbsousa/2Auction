<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';



    protected $fillable = [
        'reason',
        'reported_id',
        "issolved",
        'date',
        'reporter',
        'reviewer',
        'description',
        'type',
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class, 'reported_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public $timestamps = false;
}
