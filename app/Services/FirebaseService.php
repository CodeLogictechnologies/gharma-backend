<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AssignDriver;
use App\Models\BackPanel\AssignDriver as BackPanelAssignDriver;
use App\Http\Controllers\BsdateController;

class FirebaseService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials'));

        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($token, $title, $body)
    {
        $message = [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
        ];
        return $this->messaging->send($message);
    }

    public static function AssignDriverNotice($post)
    {
        try {
            // The assign-date input now collects a B.S. (Nepali) date. Convert it to
            // A.D. so existing consumers of delivery_date/date_eng keep working.
            $dateNep = $post['delivery_date'];
            $bsdate  = new BsdateController();
            $dateEng = $bsdate->nep_to_eng($dateNep);

            // One active assignment per order — update it if it already exists
            // instead of inserting a duplicate row on reassignment.
            $existing = BackPanelAssignDriver::where('ordermasterid', $post['ordermasterid'])
                ->where('status', 'Y')
                ->first();

            if ($existing) {
                $updated = BackPanelAssignDriver::where('id', $existing->id)->update([
                    'driverid'      => $post['driver_id'],
                    'delivery_date' => $dateEng,
                    'date_eng'      => $dateEng,
                    'date_nep'      => $dateNep,
                    'orgid'         => $post['orgid'],
                    'updated_at'    => Carbon::now(),
                ]);

                if ($updated === false) {
                    throw new \Exception("Couldn't reassign driver");
                }
            } else {
                $dataArray = [
                    'status'        => 'Y',
                    'orgid'         => $post['orgid'],
                    'delivery_date' => $dateEng,
                    'date_eng'      => $dateEng,
                    'date_nep'      => $dateNep,
                    'id'            => (string) Str::uuid(),
                    'ordermasterid' => $post['ordermasterid'],
                    'driverid'      => $post['driver_id'],
                    'created_at'    => Carbon::now(),
                ];

                if (!BackPanelAssignDriver::insert($dataArray)) {
                    throw new \Exception("Couldn't Assign Driver");
                }
            }


            DB::table('order_masters')
                ->where('id', $post['ordermasterid'])
                ->update(['order_status' => 'Shipped']);

            // ── SEND NOTIFICATION ──────────────────────────────
            // Get the customer's userid from the order
            $order = DB::table('order_masters')
                ->where('id', $post['ordermasterid'])
                ->first();

            if ($order) {
                // Fetch all device tokens for that user
                $tokens = DB::table('userdevicetokens')
                    ->where('userid', $order->userid)
                    ->pluck('devicetoken');


                if ($tokens->isNotEmpty()) {
                    $firebase = new self(); // instantiate to use sendNotification()

                    foreach ($tokens as $token) {
                        try {
                            $firebase->sendNotification(
                                $token,
                                'Order Shipped',
                                'You have been assigned an order. Open the app to see details.',
                                // [
                                //     'ordermasterid' => (string) $post['ordermasterid'],
                                //     'order_status'  => 'Shipped',
                                // ]
                            );
                        } catch (\Exception $e) {
                            // log bad token but don't break the flow
                            \Log::warning('FCM send failed for token ' . $token . ': ' . $e->getMessage());
                        }
                    }
                }
            }


            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}