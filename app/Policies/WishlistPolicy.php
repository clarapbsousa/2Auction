<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wishlist;

class WishlistPolicy
{
    public function add(User $user)
    {
        return $user !== null;
    }

    public function remove(User $user, Wishlist $wishlist)
    {
        return $user->id === $wishlist->user_id;
    }
}

