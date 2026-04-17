<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $totalUsers = User::toBase()->count();

        $users = User::select('id', 'name', 'role', 'last_activity')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(10)->withQueryString();

        $users->getCollection()->transform(function ($u) {
            $u->isOnline = $u->last_activity && $u->last_activity >= now()->subMinutes(2);
            return $u;
        });
        return view('users.index', compact('users', 'totalUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,operator,user'
        ]);

        User::create([
            'name' => $request->name,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ]);

        return back()->with('success', 'User berhasil ditambahkan');
    }

    public function destroy($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'User berhasil dihapus');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function changeStatus($id)
    {
        $user = User::findOrFail($id);

        $user->is_active = !$user->is_active;
        $user->save();

        return back();
    }
}
