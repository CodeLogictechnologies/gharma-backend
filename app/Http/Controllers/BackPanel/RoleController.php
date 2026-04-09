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
        // try {

        $rules = [
            'name' => 'required|min:3|max:255',
            'order_number' => [
                'required',
                Rule::unique('roles')->where(function ($query) {
                    return $query->where('status', 'Y');
                })->ignore($request->id),
            ],
        ];

        $message = [
            'name.required' => 'Please enter  category.',
            'order_number.required' => 'Please enter  order.',
            'order_number.unique' => 'Order number should be unique.',
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
        // } catch (QueryException $e) {
        //     DB::rollBack();
        //     $type = 'error';
        //     $message = $this->queryMessage;
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     $type = 'error';
        //     $message = $e->getMessage();
        // }
        return json_encode(['type' => $type, 'message' => $message]);
    }

    //function to list role
    public function list(Request $request)
    {
        try {
            $post = $request->all();
            $data = Role::list($post);
            $i = 0;
            $array = [];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
            $totalrecs = $data["totalrecs"];

            unset($data["totalfilteredrecs"]);
            unset($data["totalrecs"]);

            foreach ($data as $row) {
                $array[$i]["sno"]          = $i + 1;
                $array[$i]["name"]         = $row->name;
                $array[$i]["order_number"] = $row->order_number;

                $permissions = $row->permissions->pluck('name')->toJson();

                $action = '';

                // ✅ Edit button - check permission
                if (auth()->user()->can('role.edit')) {
                    $action .= '<a href="javascript:;" class="editRole"
                                data-id="' . $row->id . '"
                                data-name="' . $row->name . '"
                                data-order_number="' . $row->order_number . '"
                                data-permissions=\'' . $permissions . '\'>
                                <i class="fa-solid fa-pen-to-square text-primary"></i>
                            </a>';
                }

                // ✅ Delete button - check permission
                if (auth()->user()->can('role.delete')) {
                    $action .= ' | <a href="javascript:;" class="deleteRole"
                                data-id="' . $row->id . '">
                                <i class="fa fa-trash text-danger"></i>
                            </a>';
                }

                $array[$i]["action"] = $action;
                $i++;
            }

            if (!$filtereddata) $filtereddata = 0;
            if (!$totalrecs) $totalrecs = 0;
        } catch (QueryException $e) {
            $array = [];
            $totalrecs = 0;
            $filtereddata = 0;
        } catch (Exception $e) {
            $array = [];
            $totalrecs = 0;
            $filtereddata = 0;
        }

        return json_encode([
            "recordsFiltered" => $filtereddata,
            "recordsTotal"    => $totalrecs,
            "data"            => $array
        ]);
    }

    //function to delete role
    public function delete(Request $request)
    {
        try {
            $type = 'success';
            $message = "Record deleted successfully";

            $post = $request->all();
            $class = new Role();

            DB::beginTransaction();
            if (!Common::deleteDataFileDoesnotExists($post, $class)) {
                throw new Exception("Record does not deleted", 1);
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


    // public function delete(Request $request)
    // {
    //     try {
    //         $post = $request->all();

    //         if (empty($post['id'])) {
    //             return json_encode([
    //                 'type'    => 'error',
    //                 'message' => 'Role not found.'
    //             ]);
    //         }

    //         $role = Role::findOrFail($post['id']);

    //         $role->syncPermissions([]);

    //         $role->users()->detach();

    //         $role->delete();

    //         return json_encode([
    //             'type'    => 'success',
    //             'message' => 'Role deleted successfully.'
    //         ]);
    //     } catch (QueryException $e) {
    //         return json_encode([
    //             'type'    => 'error',
    //             'message' => $this->queryMessage
    //         ]);
    //     } catch (Exception $e) {
    //         return json_encode([
    //             'type'    => 'error',
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }
}