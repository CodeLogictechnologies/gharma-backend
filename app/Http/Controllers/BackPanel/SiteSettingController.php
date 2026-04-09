<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\SiteSetting;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SiteSettingController extends Controller
{
    public function siteSetting()
    {
        $siteSettings = SiteSetting::get()->first();

        $data = [
            'siteSettings' => $siteSettings
        ];
        return view('backend.site-setting.index', $data);
    }

    public function updateSiteSetting(Request $request)
    {
        try {
            $rules = [
                // 'name' => 'required|min:3|max:255',
                'email' => 'nullable|min:3|max:55',
                'phone_number' => 'nullable|min:9|max:50',
                'address' => 'required|min:3|max:255',
                'link_facebook' => 'nullable|min:5|max:255',
                'link_map' => 'nullable|min:5|max:500',
                'homepage_title' => 'nullable|min:5|max:255',
                'hmaepage_description' => 'nullable|min:5|max:50',
                'img_banner_homepage' => 'nullable|mimes:jpg,jpeg,png|max:2048',
                'img_logo' => 'nullable|mimes:jpg,jpeg,png|max:2048',
                'img_favicon' => 'nullable|mimes:jpg,jpeg,png|max:2048',
            ];
            $message = [
                // 'name.required' => 'Please enter the organization name.',
                // 'name.min' => 'The organization name must be at least 3 characters.',
                // 'name.max' => 'The organization name may not exceed 255 characters.',

                'email.min' => 'The email must be at least 3 characters.',
                'email.max' => 'The email may not exceed 55 characters.',

                'phone_number.min' => 'The phone number must be at least 9 characters.',
                'phone_number.max' => 'The phone number may not exceed 50 characters.',

                'address.required' => 'Please enter the address.',
                'address.min' => 'The address must be at least 3 characters.',
                'address.max' => 'The address may not exceed 255 characters.',

                'link_facebook.min' => 'The Facebook link must be at least 5 characters.',
                'link_facebook.max' => 'The Facebook link may not exceed 255 characters.',

                'link_map.min' => 'The map link must be at least 5 characters.',
                'link_map.max' => 'The map link may not exceed 500 characters.',

                'homepage_title.min' => 'The homepage title must be at least 5 characters.',
                'homepage_title.max' => 'The homepage title may not exceed 255 characters.',

                'hmaepage_description.min' => 'The homepage description must be at least 5 characters.',
                'hmaepage_description.max' => 'The homepage description may not exceed 50 characters.',

                'img_banner_homepage.mimes' => 'The banner image must be a file of type: jpg, jpeg, png.',
                'img_banner_homepage.max' => 'The banner image may not exceed 2048 kilobytes.',

                'img_logo.mimes' => 'The logo must be a file of type: jpg, jpeg, png.',
                'img_logo.max' => 'The logo may not exceed 2048 kilobytes.',

                'img_favicon.mimes' => 'The favicon must be a file of type: jpg, jpeg, png.',
                'img_favicon.max' => 'The favicon may not exceed 2048 kilobytes.',
            ];

            $validate = Validator::make($request->all(), $rules, $message,);
            if ($validate->fails()) {
                throw new Exception($validate->errors()->first(), 1);
            }

            $post = $request->all();

            $type = "success";
            $message = "Record saved successfully";

            DB::beginTransaction();

            $result = SiteSetting::updatedata($post);
            if (!$result)
                throw new Exception("Couldn't Save Records", 1);

            DB::commit();
        } catch (QueryException $e) {
            DB::rollback();
            $type = 'error';
            $message = $this->queryMessage;
        } catch (Exception $e) {
            DB::rollback();
            $type = 'error';
            $message = $e->getMessage();
        }
        return json_encode(['type' => $type, 'message' => $message]);
    }
}