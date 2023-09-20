<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Policies\PermissionPolicy;



use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $this->authorize('Profile', User::class);
        $Profiles=Auth::user();
        return view('Dashboard.Profiles.ShowProfile',compact('Profiles'));
    }

    public function update(Request $request)
    {
        $this->authorize('Profile', User::class);
        $id=User::findOrFail($request->id);
        $id->update(
            [
                'name'=>$request->name,
                'email'=>$request->email,
                'password'=>$request->password,
                'phone'=>$request->phone,
                'address'=>$request->address,
            ]);

        return redirect('/Profile/index');
    }
}
