<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\TermsPrivacy;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Exception;

class TermsConditionController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('view.terms-conditions')) {
            abort(403);
        }

        $orgid = session('orgid');

        $termsPolicy   = TermsPrivacy::getPolicy($orgid, 'terms');
        $privacyPolicy = TermsPrivacy::getPolicy($orgid, 'privacy');

        return view('backend.terms.index', compact('termsPolicy', 'privacyPolicy'));
    }

    public function savePolicy(Request $request)
    {
        try {
            if (!auth()->user()->can('edit.terms-conditions')) {
                throw new Exception('You do not have permission to perform this action.');
            }

            $type    = 'success';
            $message = 'Policy updated successfully.';

            $post           = $request->all();
            $post['orgid']  = session('orgid');
            $post['userid'] = session('userid');

            if (empty($post['type']) || empty($post['description'])) {
                throw new Exception('Type and description are required.');
            }

            TermsPrivacy::savePolicy($post);
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
