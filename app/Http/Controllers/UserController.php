<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'role', 'last_activity')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->role);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $totalUsers = User::count();

        $stats = User::selectRaw("
            SUM(CASE WHEN last_activity >= ? THEN 1 ELSE 0 END) as online,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin
        ", [now()->subMinutes(2)])->first();

        $onlineUsers = $stats->online ?? 0;
        $adminUsers = $stats->admin ?? 0;

        if ($request->ajax()) {
            return response()->json([
                'html' => view('users.partials.list', compact('users'))->render()
            ]);
        }

        // ===== VIEW =====
        return view('users.index', compact(
            'users',
            'totalUsers',
            'onlineUsers',
            'adminUsers'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
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
        if ($id == Auth::id()) {
            return response()->json([
                'error' => 'Tidak bisa hapus diri sendiri'
            ], 403);
        }

        User::findOrFail($id)->delete();

        return response()->json([
            'success' => true
        ]);
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
