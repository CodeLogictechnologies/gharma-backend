<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\ReturnRefund;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;

class ReturnRefundController extends Controller
{
    public function index()
    {
        $orgid = session('orgid');

        $returnPolicy = ReturnRefund::getPolicy($orgid, 'return');
        $refundPolicy = ReturnRefund::getPolicy($orgid, 'refund');

        return view('backend.refunds.index', compact('returnPolicy', 'refundPolicy'));
    }

    public function savePolicy(Request $request)
    {
        try {
            $type    = 'success';
            $message = 'Policy updated successfully.';

            $post            = $request->all();
            $post['orgid']   = session('orgid');
            $post['userid']  = session('userid');

            if (empty($post['type']) || empty($post['description'])) {
                throw new Exception('Type and description are required.');
            }

            ReturnRefund::savePolicy($post);

        } catch (QueryException $e) {
            $type    = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            $type    = 'error';
            $message = $e->getMessage();
        }

        return json_encode(['type' => $type, 'message' => $message]);
    }
}