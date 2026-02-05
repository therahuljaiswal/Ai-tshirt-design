<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::paginate(20);
        return view('admin.users', compact('users'));
    }

    public function addCredits(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'credits' => 'required|integer|min:1',
        ]);

        $user = User::findOrFail($request->user_id);
        $user->increment('credits', $request->credits);

        return back()->with('status', "Added {$request->credits} credits to {$user->name}.");
    }
}
