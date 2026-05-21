<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Category;
use App\Models\BackPanel\HomeTab;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HomeTabController extends Controller
{
    public function index()
    {
        $post['orgid'] = session('orgid');
        $categories    = Category::getCategory($post);
        return view('backend.hometab.index', compact('categories'));
    }

    public function form(Request $request)
    {
        $post               = $request->all();
        $post['orgid']      = session('orgid');
        $categories         = Category::getCategory($post);
        $data               = ['id' => null, 'tab_name' => '', 'category_id' => ''];

        if (!empty($request->id)) {
            $row  = HomeTab::getData(['id' => $request->id]);
            $data = [
                'id'          => $row->id,
                'tab_name'    => $row->tab_name,
                'category_ids' => $row->category_ids,
            ];
        }

        return view('backend.hometab.form', compact('data', 'categories'));
    }

    public function save(Request $request)
    {
        try {
            $type    = 'success';
            $message = 'Record saved successfully.';

            $validation = Validator::make($request->all(), [
                'tab_name'      => 'required|string|max:255',
                'category_ids'  => 'required|array|min:1',
                'category_ids.*' => 'exists:categories,id',
            ]);

            if ($validation->fails()) {
                throw new Exception($validation->errors()->first());
            }

            $post           = $request->all();
            $post['orgid']  = session('orgid');
            $post['userid'] = session('userid');

            HomeTab::saveData($post);
        } catch (QueryException $e) {
            $type    = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            $type    = 'error';
            $message = $e->getMessage();
        }

        return response()->json(['type' => $type, 'message' => $message]);
    }

    public function list(Request $request)
    {
        $post   = $request->all();
        $result = HomeTab::list($post);

        $array        = [];
        $i            = 0;
        $totalrecs    = $result['totalrecs']     ?? 0;
        $filtereddata = $result['filteredCount'] ?? $totalrecs;

        foreach ($result['data'] as $row) {
            $array[$i]['sno']            = $request->input('start', 0) + $i + 1;
            $array[$i]['tab_name']       = $row->tab_name       ?? '—';
            $array[$i]['category_names'] = $row->category_names ?? '—';

            $action  = '<a href="javascript:;" class="deleteHomeTab px-2" style="color:red;"  data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
            $action .= '<a href="javascript:;" class="editHomeTab"         style="color:blue;" data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';

            $array[$i]['action'] = $action;
            $i++;
        }

        return response()->json([
            'recordsTotal'    => (int) $totalrecs,
            'recordsFiltered' => (int) $filtereddata,
            'data'            => $array,
        ]);
    }

    public function delete(Request $request)
    {
        try {
            $type    = 'success';
            $message = 'Record deleted successfully.';
            HomeTab::deleteData($request->all());
        } catch (QueryException $e) {
            $type    = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            $type    = 'error';
            $message = $e->getMessage();
        }

        return response()->json(['type' => $type, 'message' => $message]);
    }
}
