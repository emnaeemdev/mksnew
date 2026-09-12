<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create', [
            'roles' => User::roleLabels(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_EDITOR])],
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => strtolower($request->username),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'تم إنشاء المستخدم بنجاح');
    }

    public function show(User $user, Request $request, ActivityLogger $activityLogger)
    {
        $action = (string) $request->query('action', '');
        $allowed = ['login', 'login_failed', 'logout', 'created', 'updated', 'deleted', 'published', 'unpublished', 'exported', 'downloaded'];

        $logs = ActivityLog::query()
            ->where('user_id', $user->id)
            ->when(in_array($action, $allowed, true), fn ($query) => $query->where('action', $action))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.show', compact('user', 'logs', 'action', 'activityLogger'));
    }

    public function clearActivityLogs(User $user)
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $count = ActivityLog::query()->where('user_id', $user->id)->delete();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', $count > 0
                ? "تم مسح {$count} حدث من سجل هذا المستخدم."
                : 'سجل هذا المستخدم فارغ بالفعل.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => User::roleLabels(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'min:3', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_EDITOR])],
        ]);

        // Prevent locking yourself out of admin by demoting the last admin accidentally on self.
        if ($user->id === auth()->id() && $request->role !== User::ROLE_ADMIN && $user->isAdmin()) {
            $otherAdmins = User::where('role', User::ROLE_ADMIN)->where('id', '!=', $user->id)->count();
            if ($otherAdmins === 0) {
                return back()->withErrors(['role' => 'لا يمكن إزالة صلاحية المدير من آخر حساب مدير في النظام.'])->withInput();
            }
        }

        $data = [
            'name' => $request->name,
            'username' => strtolower($request->username),
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'تم تحديث المستخدم بنجاح');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'لا يمكنك حذف حسابك الخاص');
        }

        if ($user->isAdmin()) {
            $otherAdmins = User::where('role', User::ROLE_ADMIN)->where('id', '!=', $user->id)->count();
            if ($otherAdmins === 0) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'لا يمكن حذف آخر مدير في النظام');
            }
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'تم حذف المستخدم بنجاح');
    }
}
