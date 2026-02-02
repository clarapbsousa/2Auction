<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    public $timestamps = false;

    // Permitir atribuição em massa apenas para estas colunas
    protected $fillable = ['user_id', 'auction_id'];

    /**
     * Relacionamento com o modelo User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relacionamento com o modelo Auction.
     */
    public function auction()
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }
}
