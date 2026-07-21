<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use App\Models\BackPanel\Report\Inventory;
use App\Models\BackPanel\Report\Sales;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            $post = $request->all();
            $post['orgid'] = session('orgid');

            if (!$post['orgid']) {
                return view('backend.dashboard.index', [
                    'type' => 'error',
                    'message' => 'Organization ID not found in session',
                    'getSalesReport' => collect([]),
                    'getInventory' => collect([])
                ]);
            }

            $getSalesReport = Sales::saleReport($post);
            $getInventory = Inventory::inventoryReport($post);

            return view('backend.dashboard.index', [
                'getSalesReport' => $getSalesReport,
                'getInventory' => $getInventory,
                'type' => 'success',
                'message' => 'Successfully fetched data'
            ]);
            
        } catch (QueryException $e) {
            return view('backend.dashboard.index', [
                'type' => 'error',
                'message' => 'Database query error: ' . $e->getMessage(),
                'getSalesReport' => collect([]),
                'getInventory' => collect([])
            ]);
            
        } catch (Exception $e) {
            return view('backend.dashboard.index', [
                'type' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage(),
                'getSalesReport' => collect([]),
                'getInventory' => collect([])
            ]);
        }
    }
}