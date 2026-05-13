<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderNotificationOtp extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function saveOtp($post)
    {
        try {
            $otp = (string) random_int(100000, 999999);
            $insertTransaction = [
                'id'            => (string) Str::uuid(),
                'orgid'    => $post['orgid'],
                'customerid'    => $post['customerid'],
                'ordermasterid' => $post['ordermasterid'],
                'title'         => $post['title'],
                'message'       => $post['message'],
                'otp'           => $otp,
                'postedby'      => $post['userid'],
                'created_at'    => Carbon::now(),
            ];

            $result = DB::table('order_notification_otps')->insert($insertTransaction);

            if (!$result) {
                throw new \Exception("Couldn't send OTP.");
            }

            return true; // ← was missing

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function verifyOrderOtp($post)
    {
        try {
            // ── Find the latest OTP record for this order ──────────
            $otpRecord = DB::table('order_notification_otps')
                ->where('ordermasterid', $post['ordermasterid'])
                ->where('customerid',   $post['customerid'])
                ->whereNull('verified_at')
                ->latest('created_at')
                ->first();

            if (!$otpRecord) {
                throw new \Exception('OTP record not found.');
            }

            // ── Check if OTP is expired (10 minutes) ───────────────
            $createdAt = Carbon::parse($otpRecord->created_at);
            if ($createdAt->diffInMinutes(Carbon::now()) > 10) {
                throw new \Exception('OTP has expired. Please request a new one.');
            }

            // ── Check if OTP matches ───────────────────────────────
            if (strtoupper($post['otp']) !== strtoupper($otpRecord->otp)) {
                throw new \Exception('Invalid OTP. Please try again.');
            }


            $changeStatus = DB::table('order_statuses')
                ->where('ordermasterid', $post['ordermasterid'])
                ->update([
                    'order_status' => 'Delivered'
                ]);

            if (!$changeStatus) {
                throw new \Exception('Please try again.');
            }

            // ── Mark OTP as verified ───────────────────────────────
            DB::table('order_notification_otps')
                ->where('id', $otpRecord->id)
                ->update(['verified_at' => Carbon::now()]);

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
