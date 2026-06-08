<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class HomeTabController extends Controller
{
    public function index()
    {
        try {
            $tabs = DB::table('home_tabs as ht')
                ->where('ht.status', 'Y')
                ->select('ht.id', 'ht.tab_name', 'ht.icon_name', 'ht.bg_color')
                ->orderBy('ht.created_at', 'asc')
                ->get();

            return response()->json([
                'type'    => 'success',
                'message' => 'Home data fetched successfully.',
                'result'  => $tabs,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}