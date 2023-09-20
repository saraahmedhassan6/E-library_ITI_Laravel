<?php

namespace App\Policies;

use App\Models\User;

class PermissionPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function Profile(User $user)
    {
        return $user->role->name === 'Admin'|| $user->role->name === 'User';
    }

    public function Users(User $user)
    {
        return $user->role->name === 'Admin';
    }

    public function Admin_Books(User $user)
    {
        return $user->role->name === 'Admin';
    }
    
    public function User_Books(User $user)
    {
        return $user->role->name === 'User';
    }
    public function AllowBorrow(User $user)
    {
        return $user->role->name === 'User';
    }


    
}
