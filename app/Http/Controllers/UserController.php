<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $currentStoreId = session('current_store_id');

        // Get users based on access level
        $usersQuery = User::with(['stores', 'roles'])
            ->whereHas('stores', function ($query) use ($currentStoreId, $user) {
                if ($user->canAccessAllStores()) {
                    // Head office admin sees all users
                    if ($currentStoreId) {
                        $query->where('stores.id', $currentStoreId);
                    }
                } else {
                    // Store admin only sees users in their accessible stores
                    $query->where('stores.id', $currentStoreId);
                }
            });

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $usersQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->orderBy('name')->paginate(15)->withQueryString();

        // Get available stores for the form
        $stores = $user->canAccessAllStores()
            ? Store::active()->get()
            : $user->stores()->where('is_active', true)->get();

        // Get available roles
        $roles = Role::whereIn('name', ['store-admin', 'cashier'])->get();

        return Inertia::render('users/index', [
            'users' => $users,
            'stores' => $stores,
            'roles' => $roles,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        // Only store admin or head office admin can create users
        if (!$user->isStoreAdmin() && !$user->isHeadOfficeAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', Password::defaults()],
            'store_ids' => ['required', 'array', 'min:1'],
            'store_ids.*' => ['required', 'integer', 'exists:stores,id'],
            'role' => ['required', 'string', Rule::in(['store-admin', 'cashier'])],
        ]);

        // Verify user has access to assign these stores
        foreach ($validated['store_ids'] as $storeId) {
            if (!$user->canAccessStore($storeId)) {
                abort(403, 'You do not have access to assign this store.');
            }
        }

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $newUser->stores()->attach($validated['store_ids']);
        $newUser->assignRole($validated['role']);

        return back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $targetUser)
    {
        $user = $request->user();

        // Only store admin or head office admin can update users
        if (!$user->isStoreAdmin() && !$user->isHeadOfficeAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Cannot edit head office admin
        if ($targetUser->isHeadOfficeAdmin() && !$user->isHeadOfficeAdmin()) {
            abort(403, 'Cannot modify head office admin.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($targetUser->id)],
            'password' => ['nullable', Password::defaults()],
            'store_ids' => ['required', 'array', 'min:1'],
            'store_ids.*' => ['required', 'integer', 'exists:stores,id'],
            'role' => ['required', 'string', Rule::in(['store-admin', 'cashier'])],
        ]);

        // Verify user has access to assign these stores
        foreach ($validated['store_ids'] as $storeId) {
            if (!$user->canAccessStore($storeId)) {
                abort(403, 'You do not have access to assign this store.');
            }
        }

        $targetUser->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $targetUser->update(['password' => Hash::make($validated['password'])]);
        }

        $targetUser->stores()->sync($validated['store_ids']);
        $targetUser->syncRoles([$validated['role']]);

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $targetUser)
    {
        $user = $request->user();

        // Only store admin or head office admin can delete users
        if (!$user->isStoreAdmin() && !$user->isHeadOfficeAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Cannot delete yourself
        if ($user->id === $targetUser->id) {
            abort(403, 'Cannot delete yourself.');
        }

        // Cannot delete head office admin
        if ($targetUser->isHeadOfficeAdmin()) {
            abort(403, 'Cannot delete head office admin.');
        }

        // Verify user has access to at least one of target user's stores
        $hasAccess = $targetUser->stores->some(fn ($store) => $user->canAccessStore($store->id));

        if (!$hasAccess) {
            abort(403, 'You do not have access to delete this user.');
        }

        $targetUser->stores()->detach();
        $targetUser->delete();

        return back()->with('success', 'User deleted successfully.');
    }
}
