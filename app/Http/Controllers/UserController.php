<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::select('id', 'name', 'role', 'is_active', 'last_activity')
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

        $onlineUsers = User::where('is_active', true)
            ->where('last_activity', '>=', now()->subMinutes(2))
            ->count();
        $adminUsers = User::where('role', 'admin')->count();

        $onlinePercentage = $totalUsers > 0 ? round(($onlineUsers / $totalUsers) * 100) : 0;

        $newUsersThisWeek = User::where('created_at', '>=', now()->subWeek())->count();

        return view('users.index', compact(
            'users',
            'totalUsers',
            'onlineUsers',
            'adminUsers',
            'onlinePercentage',
            'newUsersThisWeek'
        ));
    }

    public function store(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->name),
        ]);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'name'),
            ],
            'password' => 'required|min:6',
            'role' => 'required|in:admin,operator,user'
        ]);

        $user = User::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $user->name,
            'ac' => $user->role,
            'activity' => 'add_user'
        ]);

        return back()->with('success', 'User berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|in:admin,operator,user'
        ]);

        $user = User::findOrFail($id);

        if ($id == Auth::id()) {
            return response()->json([
                'error' => 'Tidak bisa mengubah role sendiri'
            ], 403);
        }

        $oldRole = $user->role;
        $user->role = $request->role;
        $user->save();

        if ($oldRole !== $user->role) {
            UserLog::create([
                'user_id' => Auth::id(),
                'room' => $user->name,
                'ac' => "{$oldRole} -> {$user->role}",
                'activity' => 'update_role'
            ]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Role user berhasil diubah');
    }

    public function destroy($id)
    {
        if ($id == Auth::id()) {
            return response()->json([
                'error' => 'Tidak bisa hapus diri sendiri'
            ], 403);
        }

        $user = User::findOrFail($id);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $user->name,
            'ac' => $user->role,
            'activity' => 'delete_user'
        ]);

        $user->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function profile()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();

        UserLog::create([
            'user_id' => $user->id,
            'room' => $user->name,
            'ac' => '-',
            'activity' => 'change_password'
        ]);

        return back()->with('success', 'Password berhasil diubah');
    }

    public function changeStatus($id)
    {
        $user = User::findOrFail($id);

        if ($id == Auth::id()) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun sendiri');
        }

        $user->is_active = !$user->is_active;

        if (!$user->is_active) {
            $user->is_online = false;
            $user->last_activity = null;
            $user->last_logout_at = now();
        }

        $user->save();

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => $user->name,
            'ac' => $user->role,
            'activity' => $user->is_active ? 'activate_user' : 'deactivate_user'
        ]);

        return back()->with('success', $user->is_active
            ? 'User berhasil diaktifkan'
            : 'User berhasil dinonaktifkan');
    }
}
