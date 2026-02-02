<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Added to define Eloquent relationships.
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'imagepath',
        'description',
        'email',
        'password',
        'balance',
        'avgrating',
        'isbanned',
        'isadmin',
        'number_of_bids',
        'number_of_auctions',
        'auctions_followed'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'balance' => 'float',
        'avgrating' => 'decimal:2',
        'isbanned' => 'boolean',
        'isadmin' => 'boolean',
    ];

    public function topups()
    {
        return $this->hasMany(Topup::class, 'userid');
    }

    public function auctions()
    {
        return $this->hasMany(Auction::class, 'seller', 'id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller', 'id');
    }
    
    public function showSellerProfile($id)
    {
        // Buscar o utilizador (vendedor) pelo ID
        $seller = User::findOrFail($id);

        // Retornar a view com os dados do vendedor
        return view('pages.auctions.sellerprofile', compact('seller'));
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter');
    }

    public function blocks()
    {
        return $this->hasMany(UserBlock::class, 'blocker_id');
    }

    public function blockedBy()
    {
        return $this->hasMany(UserBlock::class, 'blocked_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'user_id');
    }

    public function averageRating()
    {
        return $this->ratings()->avg('avgrating');
    }
}