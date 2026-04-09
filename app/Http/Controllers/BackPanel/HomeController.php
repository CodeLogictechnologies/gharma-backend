<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Enquiry;
use Exception;
use Illuminate\Database\QueryException;

class HomeController extends Controller
{
    public function dashboard()
    {

        try {
            $data = [];
            $type = 'success';
            $message = 'Successfully fetched data';


            $data = [
                'type' => $type,
                'message' => $message
            ];
        } catch (QueryException $e) {
            $data['type'] = 'error';
            $data['message'] = 'Database query error: ' . $e->getMessage();
        } catch (Exception $e) {
            $data['type'] = 'error';
            $data['message'] = 'An error occurred: ' . $e->getMessage();
        }

        return view('backend.dashboard.index', $data);
    }
}
