<?php

namespace App\Http\Controllers\BackPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BackPanel\Notification;
use App\Models\BackPanel\Organization;
use App\Models\BackPanel\Post;
use App\Models\BackPanel\Vendor;
use App\Models\Common;
use App\Models\User;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    public function index()
    {

        if (!auth()->user()->can('view.notification')) {
            abort(403);
        }
        return view('backend.notification.index');
    }

    public function save(Request $request)
    {
        try {
            $action = !empty($request->id) ? 'edit.notification' : 'add.notification';

            if (!auth()->user()->can($action)) {
                throw new Exception('You do not have permission to perform this action.');
            }
            $rules = [
                'type' => 'required',
                'user_id' => 'required',
                'title' => 'required|min:2|max:255',
                'message' => 'required|min:2',
            ];

            $messages = [
                'type.required' => 'Please select a type',
                'user_id.required' => 'Please select a user',
                'title.required' => 'Title is required',
                'title.min' => 'Title must be at least 2 characters',
                'title.max' => 'Title must not exceed 255 characters',
                'message.required' => 'Message is required',
                'message.min' => 'Message must be at least 2 characters',
            ];

            $validate = Validator::make($request->all(), $rules, $messages);

            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 1);
            }

            $post = $request->all();
            $post['orgid'] = session('orgid');
            $post['userid'] = session('userid');
            $type = 'success';
            $message = !empty($request->id) ? 'Notification updated successfully' : 'Notification saved successfully';


            DB::beginTransaction();

            if (!Notification::saveData($post)) {
                throw new Exception('Could not save notice', 1);
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

    public function list(Request $request)
    {
        // try {
        $post = $request->all();
        $post['orgid'] = session('orgid');
        $data = Notification::list($post);
        $i = 0;
        $array = [];
        $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
        $totalrecs = $data["totalrecs"];

        unset($data["totalfilteredrecs"]);
        unset($data["totalrecs"]);
        foreach ($data as $row) {
            $array[$i]["sno"] = $i + 1;
            $array[$i]["username"]    = $row->username;
            $array[$i]["title"]    = $row->title;
            $array[$i]["message"] = '<span style="display:inline-block; max-width:280px; white-space:normal; word-wrap:break-word;">'
                . \Illuminate\Support\Str::limit(strip_tags($row->message), 100, '...')
                . '</span>';
            $array[$i]["type"]    = $row->type;



            $action = '';

            if (auth()->user()->can('delete.notification')) {
                $action .= '<a href="javascript:;" title="Delete Data" class="tooltipdiv deleteNotice px-2" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
            }

            if (auth()->user()->can('view.notification')) {
                $action .= '<a href="javascript:;" title="View Data" class="tooltipdiv viewNotice" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show-alt"></i></a>';
            }

            if (auth()->user()->can('edit.notification')) {
                $action .= '<a href="javascript:;" title="Edit Data" class="tooltipdiv editNotice" style="color:blue;" data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';
            }

            $array[$i]["action"]  = $action;

            $i++;
        }
        if (!$filtereddata) $filtereddata = 0;
        if (!$totalrecs) $totalrecs = 0;
        // } catch (QueryException $e) {
        //     $array = [];
        //     $totalrecs = 0;
        //     $filtereddata = 0;
        // } catch (Exception $e) {
        //     $array = [];
        //     $totalrecs = 0;
        //     $filtereddata = 0;
        // }
        return json_encode(array("recordsFiltered" => $filtereddata, "recordsTotal" => $totalrecs, "data" => $array));
    }

    public function form(Request $request)
    {
        $action = !empty($request->id) ? 'edit.notification' : 'add.notification';

        if (!auth()->user()->can($action)) {
            abort(403);
        }
        try {

            $post = $request->all();
            $post['orgid'] = session('orgid');

            $data = [];
            $data['users'] = User::getUserData($post);

            if (!empty($request->id)) {
                $result = Notification::getData($post);
                if (!$result) {
                    throw new Exception("Notification not found", 1);
                }

                $data['id']      = $result->id;
                $data['type']    = $result->type;
                $data['user_id'] = $result->user_id;
                $data['title']   = $result->title;
                $data['message'] = $result->message;
            }
        } catch (QueryException $e) {
            $data['error'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['error'] = $e->getMessage();
        }

        return view('backend.notification.form', $data);
    }


    // Delete
    public function delete(Request $request)
    {
        try {
            if (!auth()->user()->can('delete.notification')) {
                return json_encode(['type' => 'error', 'message' => 'You do not have permission to delete this record.']);
            }

            $type = 'success';
            $message = "Record deleted successfully";
            $post = $request->all();
            $post['orgid'] = session('orgid');
            $post['userid'] = session('userid');

            DB::beginTransaction();
            $result = Notification::deleteDate($post);
            if (!$result) {
                throw new Exception("Vendor not delete", 1);
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

    //view
    public function view(Request $request)
    {
        if (!auth()->user()->can('view.notification')) {
            abort(403);
        }
        try {
            $post = $request->all();
            $post['orgid'] = session('orgid');
            $noticeDetail = Notification::getData($post);

            $data = [
                'noticeDetail' => $noticeDetail,
            ];

            $data['type'] = 'success';
            $data['message'] = 'Successfully fetched data of Vendor.';
        } catch (QueryException $e) {
            $data['type'] = 'error';
            $data['message'] = $this->queryMessage;
        } catch (Exception $e) {
            $data['type'] = 'error';
            $data['message'] = $e->getMessage();
        }
        return view('backend.notification.view', $data);
    }
}
