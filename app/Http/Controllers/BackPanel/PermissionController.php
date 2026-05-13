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

class PermissionController extends Controller
{
    // construct
    public function __construct()
    {
        parent::__construct();
    }

    //function to redirect to permission page
    public function index()
    {
        return view('backend.permission.index');
    }

    //function to save permission 
    public function save(Request $request)
    {
        try {
            $rules = [
                'name' => [
                    'required',
                    Rule::unique('permissions')->ignore($request->id),
                ],
            ];

            $message = [
                'name.required' => 'Please enter permission.',
                'name.unique' => 'Permission should be unique.',
            ];

            $validation = Validator::make($request->all(), $rules, $message);

            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }

            $post = $request->all();
            $type = 'success';
            $message = 'Permission saved successfully';

            DB::beginTransaction();

            if (!Permission::saveData($post)) {
                throw new Exception('Could not save permission', 1);
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

    //function to list permission
    public function list(Request $request)
    {
        try {
            $post = $request->all();
            $data = Permission::list($post);
            $i = 0;
            $array = [];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
            $totalrecs = $data["totalrecs"];

            unset($data["totalfilteredrecs"]);
            unset($data["totalrecs"]);
            foreach ($data as $row) {
                $array[$i]["sno"] = $i + 1;
                $array[$i]["name"]    = $row->name;
                $action = '';
                $action .= '<a href="javascript:;" class="editPermission" name="Edit Data" data-id="' . $row->id . '" data-name="' . $row->name . '"><i class="fa-solid fa-pen-to-square text-primary"></i></a> ';

                $action .= '| <a href="javascript:;" class="deletePermission" name="Delete Data" data-id="' . $row->id . '"><i class="fa fa-trash text-danger"></i></a>';

                $array[$i]["action"]  = $action;
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
        return json_encode(array("recordsFiltered" => $filtereddata, "recordsTotal" => $totalrecs, "data" => $array));
    }

    //function to delete role
    public function delete(Request $request)
    {
        try {
            $type = 'success';
            $message = "Record deleted successfully";

            $post = $request->all();
            $class = new Permission();

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
}