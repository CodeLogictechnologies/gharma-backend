<?php

namespace App\Http\Controllers\BackPanel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemImageController extends Controller
{
    public function reorder(Request $request)
    {
        try {
            $images = $request->input('images', []);

            Log::info('Reorder called', ['images' => $images]);

            if (empty($images)) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'No images provided.'
                ]);
            }

            DB::beginTransaction();

            foreach ($images as $order => $id) {
                $affected = DB::table('item_images')
                    ->where('id', $id)
                            ->update(['order_number' => (int) $order + 1]);  // ← +1 here


                Log::info("Updated image {$id} to order {$order}, affected: {$affected}");
            }

            DB::commit();

            return response()->json([
                'type'    => 'success',
                'message' => 'Image order saved.',
                'count'   => count($images)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reorder failed: ' . $e->getMessage());
            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}