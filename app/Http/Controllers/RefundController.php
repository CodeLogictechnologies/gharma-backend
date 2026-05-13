<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BackPanel\Order;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    public function index()
    {
        return view('backend.refund.index');
    }

    public function list(Request $request)
    {
        $post = $request->all();
        $data = Refund::list($post);
        $i = 0;
        $array = [];
        $filtereddata = ($data["totalfilteredrecs"] > 0 ? $data["totalfilteredrecs"] : $data["totalrecs"]);
        $totalrecs = $data["totalrecs"];

        unset($data["totalfilteredrecs"]);
        unset($data["totalrecs"]);

        $statuses = [
            'PENDING',
            'UNDER_REVIEW',
            'APPROVED',
            'REJECTED',
            'PROCESSING',
            'COMPLETED',
            'CANCELLED'
        ];
        foreach ($data as $row) {
            $array[$i]["sno"]        = $i + 1;
            $array[$i]["username"]   = $row->username;
            $array[$i]["email"]      = $row->email;
            $array[$i]["reason"] = $row->reason;
            $array[$i]["product"] = $row->title . ' - ' . $row->value;
            // Build the status dropdown with data-current and data-id
            $options = '';
            foreach ($statuses as $status) {
                $selected = ($row->refund_status === $status) ? 'selected' : '';
                $options .= "<option value='{$status}' {$selected}>" . ucfirst($status) . "</option>";
            }
            $array[$i]["refund_status"] = "
            <select class='form-select changeStatus'
                    data-id='{$row->id}'
                    data-current='{$row->refund_status}'>
                {$options}
            </select>";

            $action  = '<a href="javascript:;" title="View Refund" class="tooltipdiv viewRefund" style="color:green;" data-id="' . $row->id . '"><i class="bx bx-show-alt"></i></a>';
            $array[$i]["action"] = $action;
            $i++;
        }

        if (!$filtereddata) $filtereddata = 0;
        if (!$totalrecs)    $totalrecs    = 0;

        return json_encode([
            "recordsFiltered" => $filtereddata,
            "recordsTotal"    => $totalrecs,
            "data"            => $array
        ]);
    }

    public function view(Request $request)
    {
        // try {
        $post = $request->all();
        $post['orgid'] = session('orgid');
        $refundDetails = Refund::getData($post);
        $data = [
            'refundDetails' => $refundDetails,
        ];
        $data['type'] = 'success';
        $data['message'] = 'Successfully fetched data of refund.';
        // } catch (QueryException $e) {
        //     $data['type'] = 'error';
        //     $data['message'] = $this->queryMessage;
        // } catch (Exception $e) {
        //     $data['type'] = 'error';
        //     $data['message'] = $e->getMessage();
        // }
        return view('backend.refund.view', $data);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);

        $updated = DB::table('refunds')
            ->where('id', $request->id)
            ->update([
                'refund_status' => $request->status,
                'admin_reason' => $request->admin_reason,
            ]);

        if ($updated) {
            return response()->json([
                'type' => 'success',
                'message' => 'Order status updated successfully'
            ]);
        } else {
            return response()->json([
                'type' => 'error',
                'message' => 'No changes made or invalid ID'
            ]);
        }
    }
}