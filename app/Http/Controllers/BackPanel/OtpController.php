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
        try {

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
            $type = 'success';
            $message = 'Please reset password';

            DB::beginTransaction();
            if (!Otp::checkOtp($post)) {
                throw new Exception('Record does not found', 1);
            }
            DB::commit();
        } catch (QueryException $e) {
            $type = 'error';

            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ')->withInput();
        } catch (Exception $e) {
            $type = 'error';

            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
        return redirect('/admin/changepassword')->with('id', $request->id);
    }


    public function sendOrderOtp(Request $request)
    {
        try {
            $post = $request->json()->all();

            // ── Decode JWT token ───────────────────────────────────────
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $orgid  = $profile['orgid'];
            $userid = $profile['userid'];

            // ── Merge into post data ───────────────────────────────────
            $post['orgid']  = $orgid;
            $post['userid'] = $userid;

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
            // ── Decode JWT token ───────────────────────────────────────
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $orgid  = $profile['orgid'];
            $userid = $profile['userid'];

            // ── Merge into post data ───────────────────────────────────
            $post['orgid']  = $orgid;
            $post['userid'] = $userid;

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
