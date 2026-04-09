<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\ForgotPassword;
use App\Models\User;
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
    public function isRegisteredUser(Request $request)
    {
        try {

            $rules = [
                'email' => 'required|email|max:50',
            ];
            $message = [
                'email.required' => 'Email field is required',
                'email.email' => 'Email format does not matched',
            ];

            $validate = Validator::make($request->all(), $rules, $message);
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 1);
            }
            $post = $request->all();

            $type = 'success';
            $message = 'Please check email';

            DB::beginTransaction();

            $result = ForgotPassword::checkRegisteredEmail($post);
            if (!$result) {
                throw new Exception('Record does not found', 1);
            }
            DB::commit();
        } catch (QueryException $e) {

            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ')->withInput();
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
        return redirect('/admin/otp')->with('id', $result->id);
    }



    //update new password
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

            $post = $request->all();
            $type = 'success';
            $message = 'Password is updated successfully.';

            DB::beginTransaction();

            if (!ForgotPassword::updateData($post)) {
                throw new Exception('Could not save record', 1);
            }

            DB::commit();
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
