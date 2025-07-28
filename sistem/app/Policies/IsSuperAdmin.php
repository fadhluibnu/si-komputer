<?php

namespace App\Policies;

use App\Models\User;

class IsSuperAdmin
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function superadmin($user)
    {
        // Allow all actions for superadmin
        if ($user->role === 'superadmin') {
            return true;
        }
    }
}
