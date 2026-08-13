<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\OrganizationAccess;
use App\Models\BackPanel\Organization;
use App\Models\BackPanel\Permission;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class OrganizationAccessController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    // GET admin/organization.access
    public function index()
    {
        if (!auth()->user()->can('view.organization-access')) {
            abort(403);
        }
        $organizations = Organization::where('status', 'Y')->get();
        $permissions   = Permission::select('id', 'name')->get();

        $data = [
            'organizations' => $organizations,
            'permissions'   => $permissions,
        ];

        return view('backend.organization_access.index', $data);
    }

    // POST admin/organization.access/permissions
    public function getPermissions(Request $request)
    {
        try {
            if (!auth()->user()->can('view.organization-access')) {
                throw new Exception('You do not have permission to view this data.');
            }

            $orgId = $request->id;

            if (empty($orgId)) {
                throw new Exception('Organization ID is required.', 1);
            }

            $permissionIds = OrganizationAccess::getPermissionIds($orgId);

            return json_encode([
                'type'        => 'success',
                'message'     => 'Fetched successfully',
                'permissions' => $permissionIds,
            ]);
        } catch (Exception $e) {
            return json_encode([
                'type'        => 'error',
                'message'     => $e->getMessage(),
                'permissions' => [],
            ]);
        }
    }

    // POST admin/organization.access/save-permissions
    public function savePermissions(Request $request)
    {
        try {
            if (!auth()->user()->can('edit.organization-access')) {
                throw new Exception('You do not have permission to perform this action.');
            }
            \Log::info('savePermissions payload', $request->all());
            if (empty($request->id)) {
                throw new Exception('Organization ID is required.', 1);
            }

            OrganizationAccess::saveData($request->all());

            return response()->json([
                'type'    => 'success',
                'message' => 'Permissions saved successfully.',
            ]);
        } catch (QueryException $e) {
            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    // POST admin/organization.access/list
    public function list(Request $request)
    {
        if (!auth()->user()->can('view.organization-access')) {
            return response()->json([]);
        }

        $organizations = Organization::select('id', 'name')->get();

        return response()->json($organizations);
    }

    // POST admin/organization.access/delete
    public function delete(Request $request)
    {
        try {
            if (!auth()->user()->can('delete.organization-access')) {
                throw new Exception('You do not have permission to delete this record.');
            }

            if (empty($request->id)) {
                throw new Exception('Organization ID is required.', 1);
            }

            OrganizationAccess::syncByIds($request->id, []);

            return response()->json([
                'type'    => 'success',
                'message' => 'Access removed.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    // POST admin/organization.access/save
    public function save(Request $request)
    {
        if (!auth()->user()->can('add.organization-access')) {
            return response()->json([
                'type'    => 'error',
                'message' => 'You do not have permission to perform this action.',
            ]);
        }

        return response()->json([
            'type'    => 'error',
            'message' => 'Not implemented.',
        ]);
    }
}
