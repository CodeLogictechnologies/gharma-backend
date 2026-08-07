<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Category;
use App\Models\BackPanel\Item;
use App\Models\BackPanel\Promotion;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    public function index()
    {
        return view('backend.promotion.index');
    }

    public function form(Request $request)
    {
        $post          = $request->all();
        $post['orgid'] = session('orgid');

        $categories = Category::getCategory($post);
        $items      = Item::getItem($post);

        $data = [
            'id'           => null,
            'name'         => '',
            'applies_to'   => 'category',
            'item_ids'     => [],
            'category_ids' => [],
            'bg_color'     => '#ffffff',
            'sort_order'   => 0,
            'image_url'    => null,
        ];

        if (!empty($request->id)) {
            $row = Promotion::getData(['id' => $request->id]);

            if (!$row) {
                abort(404);
            }

            $data = [
                'id'           => $row->id,
                'name'         => $row->name,
                'applies_to'   => $row->applies_to,
                'item_ids'     => $row->item_ids     ?? [],
                'category_ids' => $row->category_ids ?? [],
                'bg_color'     => $row->bg_color ?? '#ffffff',
                'sort_order'   => $row->sort_order ?? 0,
                'image_url'    => $row->image_url,
            ];
        }

        return view('backend.promotion.form', compact('data', 'categories', 'items'));
    }

    public function save(Request $request)
    {
        if (!auth()->user()->can($request->id ? 'edit.promotion' : 'add.promotion')) {
            return response()->json(['type' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        try {
            $type    = 'success';
            $message = !empty($request->id) ? 'Promotion updated successfully.' : 'Promotion saved successfully.';

            $validation = Validator::make($request->all(), [
                'name'           => 'required|string|max:255',
                'applies_to'     => 'required|in:item,category',
                'item_ids'       => 'required_if:applies_to,item|array|min:1',
                'item_ids.*'     => 'exists:items,id',
                'category_ids'   => 'required_if:applies_to,category|array|min:1',
                'category_ids.*' => 'exists:categories,id',
                'bg_color'       => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
                'sort_order'     => 'nullable|integer|min:0|max:9999',
                'image'          => 'nullable|mimes:jpg,jpeg,png|max:2048',
            ], [
                'bg_color.regex'    => 'The background color must be a valid hex color code (e.g. #FF5733).',
                'sort_order.integer' => 'Sort order must be a whole number.',
                'sort_order.min'     => 'Sort order cannot be negative.',
                'sort_order.max'     => 'Sort order cannot exceed 9999.',
            ]);

            if ($validation->fails()) {
                throw new Exception($validation->errors()->first());
            }

            $post           = $request->all();
            $post['image']  = $request->file('image');
            $post['orgid']  = session('orgid');
            $post['userid'] = session('userid');

            Promotion::saveData($post);
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
        if (!auth()->user()->can('view.promotion')) {
            return response()->json(['recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
        }

        $post   = $request->all();
        $result = Promotion::list($post);

        $array        = [];
        $totalrecs    = $result['totalrecs']     ?? 0;
        $filtereddata = $result['filteredCount'] ?? $totalrecs;

        foreach ($result['data'] as $i => $row) {
            $action = '';

            if (auth()->user()->can('edit.promotion')) {
                $action .= '<a href="javascript:;" class="editPromotion px-2" style="color:blue;" data-id="' . $row->id . '"><i class="bx bx-edit-alt"></i></a>';
            }

            if (auth()->user()->can('delete.promotion')) {
                $action .= '<a href="javascript:;" class="deletePromotion px-2" style="color:red;" data-id="' . $row->id . '"><i class="bx bx-trash"></i></a>';
            }

            $array[] = [
                'id'            => $row->id,
                'sno'           => $request->input('iDisplayStart', 0) + $i + 1,
                'name'          => $row->name,
                'image_url'     => $row->image_url,
                'applies_to'    => ucfirst($row->applies_to),
                'target_names'  => $row->target_names,
                'sort_order'    => $row->sort_order,
                'bg_color'      => $row->bg_color
                    ? '<span style="display:inline-block;width:20px;height:20px;background:' . e($row->bg_color) . ';border-radius:4px;border:1px solid #ccc;" title="' . e($row->bg_color) . '"></span>'
                    : '—',
                'status'        => $row->status,
                'action'        => $action,
            ];
        }

        return response()->json([
            'recordsTotal'    => (int) $totalrecs,
            'recordsFiltered' => (int) $filtereddata,
            'data'            => $array,
        ]);
    }

    public function delete(Request $request)
    {
        if (!auth()->user()->can('delete.promotion')) {
            return response()->json(['type' => 'error', 'message' => 'Unauthorized.']);
        }

        try {
            $type    = 'success';
            $message = 'Promotion deleted successfully.';
            Promotion::deleteData($request->all());
        } catch (QueryException $e) {
            $type    = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            $type    = 'error';
            $message = $e->getMessage();
        }

        return response()->json(['type' => $type, 'message' => $message]);
    }

    public function toggleStatus(Request $request)
    {
        if (!auth()->user()->can('edit.promotion')) {
            return response()->json(['type' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        try {
            $post           = $request->all();
            $post['userid'] = session('userid');

            $newStatus = Promotion::toggleStatus($post);

            return response()->json([
                'type'    => 'success',
                'message' => $newStatus === 'Y' ? 'Promotion activated.' : 'Promotion deactivated.',
                'status'  => $newStatus,
            ]);
        } catch (QueryException $e) {
            return response()->json(['type' => 'error', 'message' => $this->queryMessage]);
        } catch (Exception $e) {
            return response()->json(['type' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
