<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class InvoiceController extends Controller
{
    public function show(Request $request)
    {
        $ordermasterid = $request->input('ordermasterid');

        if (empty($ordermasterid)) {
            return response()->json([
                'status'  => false,
                'message' => 'ordermasterid is required',
            ], 422);
        }

        $payload = JWTAuth::parseToken()->getPayload();
        $profile = $payload->get('profile');

        $orgid = is_array($profile) ? ($profile['orgid'] ?? null) : ($profile->orgid ?? null);

        if (empty($orgid)) {
            return response()->json([
                'status'  => false,
                'message' => 'orgid not found for this user',
            ], 422);
        }

        $invoice = DB::table('invoices')
            ->where('ordermasterid', $ordermasterid)
            ->where('orgid', $orgid)
            ->first([
                'id',
                'ordermasterid',
                'orgid',
                'invoicenumber',
                'storagepath',
                'created_at',
            ]);

        if (empty($invoice)) {
            return response()->json([
                'status'  => false,
                'message' => 'No invoice found for this order',
            ], 404);
        }

        $data = [
            'id'            => $invoice->id,
            'ordermasterid' => $invoice->ordermasterid,
            'invoicenumber' => $invoice->invoicenumber,
            'storagepath'   => $invoice->storagepath,
            'storage_url'   => $invoice->storagepath
                ? url('storage/' . $invoice->storagepath)
                : null,
            'created_at'    => $invoice->created_at,
        ];

        return response()->json([
            'status' => true,
            'data'   => $data,
        ]);
    }
}
