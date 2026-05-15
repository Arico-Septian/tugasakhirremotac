<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::select('id', 'name', 'avatar', 'role', 'is_active', 'last_activity')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->role);
            });

        // Sorting
        $sort = $request->input('sort', 'created_at');
        $order = $request->input('order', 'desc');

        if (!in_array($sort, ['name', 'role', 'last_activity', 'is_active', 'created_at'])) {
            $sort = 'created_at';
        }
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }

        $query->orderBy($sort, $order);

        $users = $query->paginate(15)->withQueryString();

        $totalUsers = User::count();

        $onlineUsers = User::where('is_active', true)
            ->where('last_activity', '>=', now()->subMinutes(2))
            ->count();
        $adminUsers = User::where('role', 'admin')->count();
        $inactiveUsers = User::where('is_active', false)->count();

        $onlinePercentage = $totalUsers > 0 ? round(($onlineUsers / $totalUsers) * 100) : 0;

        $newUsersThisWeek = User::where('created_at', '>=', now()->subWeek())->count();

        return view('users.index', compact(
            'users',
            'totalUsers',
            'onlineUsers',
            'adminUsers',
            'inactiveUsers',
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
                'regex:/^[A-Z]\S*$/',
                Rule::unique('users', 'name'),
            ],
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,operator,user'
        ], [
            'name.regex' => 'Huruf awal username harus kapital dan tidak boleh mengandung spasi.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        UserLog::create([
            'user_id' => Auth::id(),
            'room' => '-',
            'ac' => '-',
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

        $user->role = $request->role;
        $user->save();

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

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'avatar.required' => 'Pilih file gambar dulu.',
            'avatar.image'    => 'File harus berupa gambar.',
            'avatar.mimes'    => 'Format yang didukung: JPG, PNG, WEBP.',
            'avatar.max'      => 'Ukuran maksimal 2 MB.',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function deleteAvatar()
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = null;
        $user->save();

        return back()->with('success', 'Foto profil dihapus.');
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

        return back()->with('success', $user->is_active
            ? 'User berhasil diaktifkan'
            : 'User berhasil dinonaktifkan');
    }
}
