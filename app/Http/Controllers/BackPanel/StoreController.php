<?php

namespace App\Http\Controllers\BackPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveStoreRequest;
use App\Models\BackPanel\Category;
use App\Models\BackPanel\Organization;
use App\Models\BackPanel\Post;
use App\Models\BackPanel\Store;
use App\Models\BackPanel\SubCategory;
use App\Models\Common;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    //function to redirect to store page
    public function index()
    {
        return view('backend.store.index');
    }

    //function to save store
    // public function save(SaveStoreRequest $request)
    // {
    //     try {
    //         $post           = $request->all();
    //         $post['userid'] = session('userid');
    //         $post['orgid']  = session('orgid');
    //         $type           = 'success';
    //         $message        = 'Store saved successfully';

    //         DB::beginTransaction();

    //         if (!Store::saveData($post)) {
    //             throw new Exception('Could not save record', 1);
    //         }

    //         DB::commit();
    //     } catch (QueryException $e) {
    //         DB::rollBack();
    //         $type    = 'error';
    //         $message = $e->getMessage();
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         $type    = 'error';
    //         $message = $e->getMessage();
    //     }

    //     return json_encode(['type' => $type, 'message' => $message]);
    // }
    public function save(SaveStoreRequest $request)
    {
        try {
            $post           = $request->all();
            $post['userid'] = session('userid');
            $post['orgid']  = session('orgid');
            $type           = 'success';
            $message        = !empty($post['id'])
                ? 'Store updated successfully'
                : 'Store saved successfully';

            DB::beginTransaction();

            if (!Store::saveData($post)) {
                throw new Exception('Could not save record', 1);
            }

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $e->getMessage();
        } catch (Exception $e) {
            DB::rollBack();
            $type    = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }

    //function to get list of stores
    public function list(Request $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] = session('orgid');

            $data = Store::list($post);
            $offset = !empty($request->input('iDisplayStart')) ? (int) $request->input('iDisplayStart') : 0;
            $i = 0;
            $array = [];
            $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
            $totalrecs = $data["totalrecs"];

            unset($data["totalfilteredrecs"]);
            unset($data["totalrecs"]);
            foreach ($data as $row) {
                $array[$i]["sno"] = $offset + $i + 1;
                $array[$i]["name"]    = $row->name;
                $array[$i]["radius"]    = $row->radius;
                $array[$i]["email"]    = $row->email;
                $array[$i]["address"]    = $row->address;
                $array[$i]["phone"]    = $row->phone;
                $array[$i]["city"]    = $row->city;
                $array[$i]["country"]    = $row->country;
                $array[$i]["latitude"]  = !empty($row->latitude)  ? $row->latitude  : '-';
                $array[$i]["longitude"] = !empty($row->longitude) ? $row->longitude : '-';

                $action  = '';
                $action .= '<a href="javascript:;" title="View Data"   class="tooltipdiv viewStore" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show"></i></a>';
                $action .= '<a href="javascript:;" title="Edit Data"   class="tooltipdiv editStore" style="color:blue;"  data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';
                $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteStore" style="color:red;"   data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
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


    //function to edit data
    public function form(Request $request)
    {
        try {
            $data = [];
            if (!empty($request->id)) {
                $post = $request->all();
                $post['orgid'] = session('orgid');
                $result = Store::getData($post);
                if (!$result) {
                    throw new Exception("Store not found", 1);
                }

                $data['id']     = $result->id;
                $data['name']   = $result->name;
                $data['phone']  = $result->phone;
                $data['address'] = $result->address;
                $data['country']  = $result->country;
                $data['city']  = $result->city;
                $data['email']  = $result->email;
                $data['latitude']  = $result->latitude;
                $data['longitude'] = $result->longitude;
                $data['radius']    = $result->radius;
            }
        } catch (QueryException $e) {
            $data['error'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
        }

        return view('backend.store.form', $data);
    }


    // function to delete
    public function delete(Request $request)
    {
        try {
            $type = 'success';
            $message = "Store deleted successfully";
            $post = $request->all();
            $post['orgid'] = session('orgid');
            DB::beginTransaction();
            $result = Store::deleteData($post);
            if (!$result) {
                throw new Exception("Store not delete", 1);
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

    public function view(Request $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] = session('orgid');
            $orgDetails = Store::getData($post);

            if (!$orgDetails) {
                throw new Exception("Store not found");
            }

            $data = [
                'orgDetails' => $orgDetails,
                'type'       => 'success',
                'message'    => 'Successfully fetched store data.',
            ];
        } catch (QueryException $e) {
            $data = ['type' => 'error', 'message' => 'Something went wrong.'];
        } catch (Exception $e) {
            $data = ['type' => 'error', 'message' => $e->getMessage()];
        }

        return view('backend.store.view', $data);
    }
}
