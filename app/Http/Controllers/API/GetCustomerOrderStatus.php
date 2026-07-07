<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\QueryException;

class GetCustomerOrderStatus extends Controller
{
    public function getstatus(Request $request, $ordermasterid)
    {
        try {
            $order = DB::table('order_masters')
                ->where('id', $ordermasterid)
                ->first();

            if (!$order) {
                return response()->json(['type' => 'error', 'message' => 'Order not found'], 404);
            }

            // Normal happy-path sequence
            $normalFlow = ['Pending', 'Confirmed', 'Packed', 'Shipped', 'Delivered'];

            $exceptionStatuses = ['Cancelled', 'Returned', 'Refunded'];

            $currentStatus = $order->order_status;
            $timeline = [];

            if (in_array($currentStatus, $exceptionStatuses)) {

                foreach ($normalFlow as $step) {
                    if ($step === 'Delivered') {
                        continue;
                    }
                    $timeline[] = [
                        'status' => $step,
                        'value'  => 'Y',
                    ];
                }

                $timeline[] = [
                    'status' => $currentStatus,
                    'value'  => 'Y',
                ];
            } else {
                $currentIndex = array_search($currentStatus, $normalFlow);

                foreach ($normalFlow as $i => $step) {
                    $timeline[] = [
                        'status' => $step,
                        'value'  => $i <= $currentIndex ? 'Y' : 'N',
                    ];
                }
            }

            return response()->json([
                'type'           => 'success',
                'current_status' => $currentStatus,
                'timeline'       => $timeline,
            ]);
        } catch (QueryException $e) {
            return response()->json(['type' => 'error', 'message' => 'Somthing Went Wrong.'], 500);
        } catch (\Exception $e) {
            return response()->json(['type' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}