<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Unit;
use App\Models\BackPanel\VariationAttribute;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get all variation attributes
     *
     * @return void
     */
    public function index()
    {
        if (!auth()->user()->can('view.unit')) {
            abort(403);
        }
        return view('backend.unit.index');
    }

    /**
     * Save and update variation attribute
     *
     * @param Request $request
     * @return void
     */
    public function save(Request $request)
    {
        try {
            $action = !empty($request->id) ? 'edit.unit' : 'add.unit';

            if (!auth()->user()->can($action)) {
                return json_encode(['type' => 'error', 'message' => 'You do not have permission to perform this action.']);
            }

            $orgid = session('orgid');
            $id    = $request->input('id');

            $uniqueRule = Rule::unique('units', 'unit_name')
                ->where('orgid', $orgid)
                ->where('status', 'Y');

            if (!empty($id)) {
                $uniqueRule = $uniqueRule->ignore($id);
            }

            $rules = [
                'name' => ['required', 'min:2', 'max:100', $uniqueRule],
            ];

            $messages = [
                'name.required' => 'Please enter an unit name.',
                'name.min'      => 'Unit name must be at least 2 characters.',
                'name.unique'   => 'This unit already exists.',
            ];

            $validation = Validator::make($request->all(), $rules, $messages);

            if ($validation->fails()) {
                throw new Exception($validation->errors()->first(), 1);
            }

            $post            = $request->all();
            $post['orgid']   = $orgid;
            $post['postedby']  = Auth::id();
            $post['updatedby'] = Auth::id();

            $type    = 'success';
            $message = 'Record saved successfully';

            DB::beginTransaction();

            if (!Unit::saveData($post)) {
                throw new Exception('Could not save record', 1);
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    /**
     * List variation attributes
     *
     * @param Request $request
     * @return void
     */
    public function list(Request $request)
    {
        try {
            if (!auth()->user()->can('view.unit')) {
                throw new Exception('You do not have permission to view this data.');
            }
            $post = $request->all();
            $post['orgid'] = session('orgid');
            $data = Unit::list($post);

            $i              = 0;
            $array          = [];
            $filtereddata   = ($data['totalfilteredrecs'] > 0 ? $data['totalfilteredrecs'] : $data['totalrecs']);
            $totalrecs      = $data['totalrecs'];

            unset($data['totalfilteredrecs']);
            unset($data['totalrecs']);

            foreach ($data as $row) {
                $array[$i]['sno']  = $i + 1;
                $array[$i]['name'] = $row->name;

                $action  = '';
                if (auth()->user()->can('edit.unit')) {
                    $action .= '<a href="javascript:;" title="Edit" class="editUnit px-2" style="color:blue;" ';
                    $action .= 'data-id="' . $row->id . '" ';
                    $action .= 'data-name="' . htmlspecialchars($row->name, ENT_QUOTES) . '">';
                    $action .= '<i class="bx bx-edit-alt"></i></a>';
                }
                if (auth()->user()->can('delete.unit')) {
                    $action .= '<a href="javascript:;" title="Delete" class="deleteUnit px-2" style="color:red;" data-id="' . $row->id . '">';
                    $action .= '<i class="bx bx-trash"></i></a>';
                }

                $array[$i]['action'] = $action;

                $i++;
            }

            if (!$filtereddata) $filtereddata = 0;
            if (!$totalrecs)    $totalrecs    = 0;
        } catch (QueryException $e) {
            $array        = [];
            $totalrecs    = 0;
            $filtereddata = 0;
        } catch (Exception $e) {
            $array        = [];
            $totalrecs    = 0;
            $filtereddata = 0;
        }

        return json_encode([
            'recordsFiltered' => $filtereddata,
            'recordsTotal'    => $totalrecs,
            'data'            => $array,
        ]);
    }

    /**
     * Soft delete variation attribute
     *
     * @param Request $request
     * @return void
     */
    public function delete(Request $request)
    {
        try {
            if (!auth()->user()->can('delete.unit')) {
                throw new Exception('You do not have permission to delete this record.');
            }

            $type    = 'success';
            $message = 'Record deleted successfully';

            $post = $request->all();
            $post['orgid'] = session('orgid');


            DB::beginTransaction();
            Unit::deleteRecord($post);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }
}
