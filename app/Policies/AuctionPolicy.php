<?php

namespace App\Policies;

use App\Models\Auction;
use App\Models\User;

class AuctionPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function updateAuctionPermission(User $user, Auction $auction)
    {
        return $user->id === $auction->seller; // Ensure seller stores user_id
    }
}
