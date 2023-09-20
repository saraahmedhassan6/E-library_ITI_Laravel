<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Policies\PermissionPolicy;


class UserController extends Controller
{
    public function index()
    {
        $this->authorize('Users', User::class);
        $users=User::paginate(8);
        return view('Dashboard.Users.user',compact('users'));
    }
}
