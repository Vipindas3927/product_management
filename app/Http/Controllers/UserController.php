<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        if ($user->user_type == 'admin') {
            $message = 'This is the admin panel. You can create sub-admins and manage all products.';
        } elseif ($user->user_type == 'sub_admin') {
            $message = 'This is the sub-admin panel. You can add and edit products, except those created by the admin.';
        } else {
            $message = 'Unknown user';
        }
        return view('dashboard', compact('message'));

    }
    public function index(Request $request)
    {
        if(!$this->checkAccess()) abort(403);
        $users = User::SubAdmins()->paginate(10);
        return view('sub-admin', compact('users'));
    }

    public function add(Request $request)
    {
        if(!$this->checkAccess()) abort(403);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);
        try{
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'user_type' => 'sub_admin',
                'password' => Hash::make($request->password),
                'status' => 1
            ]);
            $user->assignRole('sub_admin');
        }catch(\Throwable $th){
            dd($th);
        }
       

        return response()->json(['success' => 'User added successfully']);
    }

    public function toggleStatus(Request $request)
    {
        if(!$this->checkAccess()) abort(403);
        $user = User::find($request->id);
        $user->status = (int)$request->status;
        $user->save();

        return response()->json(['message' => 'User status updated successfully'], 200);
    }

    protected function checkAccess()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) return true;

        return false;
    }
}
