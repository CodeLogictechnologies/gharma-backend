<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class OrganizationRoleController extends Controller
{
    public function index()
    {
        $organizations = Organization::where('status', 'Y')->orderBy('name')->get();
        $roles         = Role::orderBy('name')->get();
        $permissions   = Permission::select('id', 'name')->get();

        return view('backend.organization-role.index', compact('organizations', 'roles', 'permissions'));
    }

    public function getUsers(Request $request)
    {
        $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'role_id'         => 'required|exists:roles,id',
        ]);

        // Get user IDs from pivot
        $userIds = DB::table('userorganizations')
            ->where('orgid', $request->organization_id)
            ->whereNull('deleted_at')
            ->pluck('userid');

        if ($userIds->isEmpty()) {
            return response()->json(['success' => true, 'users' => [], 'count' => 0]);
        }

        // Raw join — bypasses Spatie scope issue
        $users = DB::table('users')
            ->join('model_has_roles', function ($join) use ($request) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', 'App\\Models\\User')
                    ->where('model_has_roles.role_id', $request->role_id);
            })
            ->whereIn('users.id', $userIds)
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->get();

        return response()->json([
            'success' => true,
            'users'   => $users,
            'count'   => $users->count(),
        ]);
    }

    // AJAX: get permissions of a specific user
    public function getUserPermissions(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user          = User::findOrFail($request->user_id);
        $permissionIds = $user->permissions()->pluck('id')->toArray();

        return response()->json([
            'success'     => true,
            'permissions' => $permissionIds,
            'user_name'   => $user->name,
        ]);
    }

    // AJAX: save permissions for a user
    public function saveUserPermissions(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'perm_names' => 'array',
        ]);

        $user      = User::findOrFail($request->user_id);
        $permNames = $request->perm_names ?? [];

        $user->syncPermissions($permNames);

        return response()->json([
            'success' => true,
            'message' => 'Permissions saved successfully for ' . $user->name,
        ]);
    }
}
