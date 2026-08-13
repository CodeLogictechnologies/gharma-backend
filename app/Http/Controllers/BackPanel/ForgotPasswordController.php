<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\ForgotPassword;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function index()
    {
        return view('backend.auth.forgot_password');
    }

    // public function isRegisteredUser(Request $request)
    // {
    //     try {
    //         $rules = ['email' => 'required|email|max:50'];
    //         $message = [
    //             'email.required' => 'Email field is required',
    //             'email.email' => 'Email format does not matched',
    //         ];

    //         $validate = Validator::make($request->all(), $rules, $message);
    //         if ($validate->fails()) {
    //             throw new Exception($validate->errors()->first(), 1);
    //         }

    //         $post = $request->all();

    //         $result = DB::transaction(function () use ($post) {
    //             $result = ForgotPassword::checkRegisteredEmail($post);
    //             if (!$result) {
    //                 throw new Exception('Record does not found', 1);
    //             }
    //             return $result;
    //         });
    //     } catch (QueryException $e) {
    //         return redirect()->back()->with('error', 'Something went wrong: ')->withInput();
    //     } catch (\Throwable $e) {
    //         return redirect()->back()->with('error', $e->getMessage())->withInput();
    //     }

    //     // Persistent session value (survives multiple requests) instead of
    //     // flash data via ->with(), which only survives ONE redirect.
    //     session(['pwd_reset_user_id' => $result->id]);
    //     \Log::info('SET pwd_reset_user_id: ' . $result->id . ' | session id: ' . session()->getId());

    //     return redirect('/admin/otp');
    // }
    public function isRegisteredUser(Request $request)
{
    try {
        $rules = ['email' => 'required|email|max:50'];
        $message = [
            'email.required' => 'Email field is required',
            'email.email' => 'Email format does not matched',
        ];

        $validate = Validator::make($request->all(), $rules, $message);
        if ($validate->fails()) {
            throw new Exception($validate->errors()->first(), 1);
        }

        $post = $request->all();

        $result = DB::transaction(function () use ($post) {
            $result = ForgotPassword::checkRegisteredEmail($post);
            \Log::info('INSIDE TRANSACTION - result id: ' . var_export($result->id ?? 'NULL_RESULT', true));
            if (!$result) {
                throw new Exception('Record does not found', 1);
            }
            return $result;
        });

        \Log::info('AFTER TRANSACTION - result id: ' . var_export($result->id ?? 'NULL_RESULT', true) . ' | class: ' . get_class($result));
    } catch (QueryException $e) {
        return redirect()->back()->with('error', 'Something went wrong: ')->withInput();
    } catch (\Throwable $e) {
        \Log::error('isRegisteredUser EXCEPTION: ' . $e->getMessage());
        return redirect()->back()->with('error', $e->getMessage())->withInput();
    }

    session(['pwd_reset_user_id' => $result->id]);
    \Log::info('SET pwd_reset_user_id RAW: ' . var_export($result->id, true) . ' | session id: ' . session()->getId());

    return redirect('/admin/otp');
}

    public function updatePassword(Request $request)
    {
        try {
            $rules = [
                'password' => 'required|max:250',
                'confirm_password' => 'required|max:250',
            ];
            $message = [
                'password.required' => 'Please enter new password',
                'confirm_password.required' => 'Please enter confirm password',
            ];

            $validate = Validator::make($request->all(), $rules, $message);
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 1);
            }

            $userId = session('pwd_reset_user_id');
            if (is_null($userId)) {
                throw new Exception('Session expired. Please try again.', 1);
            }

            $post = $request->all();
            $post['id'] = $userId;

            DB::beginTransaction();

            if (!ForgotPassword::updateData($post)) {
                throw new Exception('Could not save record', 1);
            }

            DB::commit();

            // Clear it now that password reset is complete
            session()->forget('pwd_reset_user_id');
        } catch (QueryException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ')->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }

        return redirect('/admin/login')->with('success', 'password changed successfully');
    }
}