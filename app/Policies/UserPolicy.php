<?php

namespace App\Policies;

use App\Models\Auction;
use App\Models\User;
use Auth;

class UserPolicy
{

    public function __construct()
    {
        //
    }

    public function updateAction(User $admin)
    {
        return $admin->isadmin;
    }

    public function viewAdminPanel(User $user)
    {
        return $user->isadmin;
    }
}
