<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Otp;
use App\Models\OrderNotificationOtp;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class OtpController extends Controller
{
    public function index()
    {
        return view('backend.auth.otp');
    }

    public function indexChangePassword()
    {
        return view('backend.auth.change_password');
    }

    public function isValidOtp(Request $request)
    {
        // try {
            $rules = [
                'otp' => 'required|min:4|max:4',
            ];
            $message = [
                'otp.required' => 'otp field is required',
            ];

            $validate = Validator::make($request->all(), $rules, $message);
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 1);
            }

            $post = $request->all();

            $userId = session('pwd_reset_user_id');
            \Log::info('READ pwd_reset_user_id: ' . $userId . ' | session id: ' . session()->getId());
            if (is_null($userId)) {
                throw new Exception('Session expired. Please try again.', 1);
            }

            $user = \App\Models\User::find($userId);
            if (!$user) {
                throw new Exception('User not found', 1);
            }
            $post['email'] = $user->email;

            DB::beginTransaction();
            if (!Otp::checkOtp($post)) {
                throw new Exception('Record does not found', 1);
            }
            DB::commit();
        // } catch (QueryException $e) {
        //     DB::rollBack();
        //     return redirect()->back()->with('error', 'Something went wrong: ')->withInput();
        // } catch (Exception $e) {
        //     DB::rollBack();
        //     return redirect()->back()->with('error', $e->getMessage())->withInput();
        // }

        return redirect('/admin/changepassword');
    }

    public function sendOrderOtp(Request $request)
    {
        try {
            $post = $request->json()->all();

            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $post['orgid']  = $profile['orgid'];
            $post['userid'] = $profile['userid'];

            $type    = 'success';
            $message = 'OTP send successfully';

            DB::beginTransaction();

            if (!OrderNotificationOtp::saveOtp($post)) {
                throw new Exception('Could not save record', 1);
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

    public function verifyOtp(Request $request)
    {
        try {
            $post = $request->json()->all();
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $post['orgid']  = $profile['orgid'];
            $post['userid'] = $profile['userid'];

            $type    = 'success';
            $message = 'OTP Match';

            DB::beginTransaction();

            if (!OrderNotificationOtp::verifyOrderOtp($post)) {
                throw new Exception('Could not save record', 1);
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
}