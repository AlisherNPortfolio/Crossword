<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\DashboardRoleCreateRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            'checkRole:administrator',
            'checkPermission:roles.manage'
        ];
    }

    public function index()
    {
        $roles = Role::withCount('users', 'permissions')->get();
        return view('dashboard.roles.index', [
            'roles' => $roles
        ]);
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->slug)[0];
        });

        return view('dashboard.roles.create', [
            'permissions' => $permissions
        ]);
    }

    public function store(DashboardRoleCreateRequest $request)
    {
        $request->validated();

        $role = Role::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        $role->permissions()->attach($request->permissions);

        return redirect()->route('dashboard.roles.index')
            ->with('success', 'Rol yaratildi!');
    }

    public function edit(Role $role)
    {
        if (in_array($role->slug, ['administrator', 'creator', 'solver'])) {
            return redirect()->route('dashboard.roles.index')
                ->with('error', 'Default rollarni o\'zgartirish mumkin emas!');
        }

        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->slug)[0];
        });

        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('dashboard.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions
        ]);
    }

    public function update(Request $request, Role $role)
    {
        if (in_array($role->slug, ['administrator', 'creator', 'solver'])) {
            return redirect()->route('dashboard.roles.index')
                ->with('error', 'Default rollarni o\'zgartirish mumkin emas!');
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($role->id),
            ],
            'description' => 'nullable|string',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        $role->permissions()->sync($request->permissions);

        return redirect()->route('dashboard.roles.index')
            ->with('success', 'Rol o\'zgartirildi!');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->slug, ['administrator', 'creator', 'solver'])) {
            return redirect()->route('dashboard.roles.index')
                ->with('error', 'Default rollarni o\'chirish mumkin emas!');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('dashboard.roles.index')
                ->with('error', 'Foydalanuvchilarga bog\'langan rollarni o\'chirish mumkin emas!');
        }

        $role->delete();

        return redirect()->route('dashboard.roles.index')
            ->with('success', 'Rol o\'chirildi!');
    }
}
