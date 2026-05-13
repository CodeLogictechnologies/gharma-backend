<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Permission;
use App\Models\BackPanel\Role;
use Illuminate\Http\Request;
use App\Models\Common;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class RoleController extends Controller
{
    // construct
    public function __construct()
    {
        parent::__construct();
    }

    //function to redirect to role page
    public function index()
    {
        $permissions = Permission::getPermission();
        $date = [
            'permissions' => $permissions
        ];
        return view('backend.role.index', $date);
    }

    //function to save role 
    public function save(Request $request)
    {
        try {

            $rules = [
                'name' => 'required|min:3|max:255',
            ];

            $message = [
                'name.required' => 'Please enter  category.',
            ];

            $validation = Validator::make($request->all(), $rules, $message);

            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }

            $post = $request->all();
            $type = 'success';
            $message = 'Role saved successfully';

            DB::beginTransaction();

            if (!Role::saveData($post)) {
                throw new Exception('Could not save record', 1);
            }
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            DB::rollBack();
            $type = 'error';
            $message = $e->getMessage();
        }
        return json_encode(['type' => $type, 'message' => $message]);
    }

    //function to list role
    public function list(Request $request)
    {
        $post = $request->all();
        $data = Role::list($post);
        $i    = 0;
        $array = [];

        $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
        $totalrecs    = $data["totalrecs"];

        unset($data["totalfilteredrecs"]);
        unset($data["totalrecs"]);

        foreach ($data as $row) {
            $array[$i]["sno"]  = $i + 1;
            $array[$i]["name"] = $row->name;

            // ── Fixed: single encode, pluck IDs not names ──────────
            $permissionIds = $row->permissions->pluck('id')->toArray();

            $action  = '';
            $action .= '<a href="javascript:;" class="editRole"
                        data-id="'          . $row->id   . '"
                        data-name="'        . $row->name . '"
                        data-permissions=\'' . json_encode($permissionIds) . '\'>
                        <i class="fa-solid fa-pen-to-square text-primary"></i>
                    </a>';

            $action .= ' | <a href="javascript:;" class="deleteRole"
                        data-id="' . $row->id . '">
                        <i class="fa fa-trash text-danger"></i>
                    </a>';

            $array[$i]["action"] = $action;
            $i++;
        }

        if (!$filtereddata) $filtereddata = 0;
        if (!$totalrecs)    $totalrecs    = 0;

        return response()->json([
            "recordsFiltered" => $filtereddata,
            "recordsTotal"    => $totalrecs,
            "data"            => $array,
        ]);
    }
    //function to delete role
    public function delete(Request $request)
    {
        try {
            $post = $request->all();

            if (empty($post['id'])) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'Role ID is required.',
                ]);
            }

            $role = Role::findOrFail($post['id']);

            // ── Detach all permissions before deleting ─────────
            $role->permissions()->detach();

            // ── Soft delete ────────────────────────────────────
            $role->delete();

            return response()->json([
                'type'    => 'success',
                'message' => 'Role deleted successfully.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Role not found.',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Something went wrong.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

}