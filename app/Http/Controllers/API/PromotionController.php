<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{
    public function index()
    {
        try {
            $promotions = DB::table('promotions')
                ->where('status', 'Y')
                ->whereNull('deleted_at')
                ->select('id', 'name', 'image', 'bg_color', 'applies_to')
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->get();

            if ($promotions->isEmpty()) {
                return response()->json([
                    'type'    => 'success',
                    'message' => 'No promotions found.',
                    'result'  => [],
                ], 200);
            }

            $itemPromotionIds     = $promotions->where('applies_to', 'item')->pluck('id');
            $categoryPromotionIds = $promotions->where('applies_to', 'category')->pluck('id');

            $itemsByPromotion = collect();
            if ($itemPromotionIds->isNotEmpty()) {
                $rows = DB::table('promotion_items as pi')
                    ->join('items as i', 'i.id', '=', 'pi.item_id')
                    ->join('itemvariations as iv', 'iv.item_id', '=', 'i.id')
                    ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
                    ->whereIn('pi.promotion_id', $itemPromotionIds)
                    ->select(
                        'pi.promotion_id',
                        'i.id as productid',
                        'i.title',
                        'p.price',
                        DB::raw('MIN(iv.id::text) as variationid')
                    )
                    ->groupBy('pi.promotion_id', 'i.id', 'i.title', 'p.price')
                    ->get();

                $productIds = $rows->pluck('productid')->unique();

                $images = DB::table('item_images')
                    ->whereIn('item_id', $productIds)
                    ->orderBy('created_at')
                    ->get()
                    ->groupBy('item_id');

                $itemsByPromotion = $rows->map(function ($row) use ($images) {
                    $firstImage = optional($images[$row->productid]->first())->image ?? null;
                    $row->image = $firstImage ? url('storage/items/' . $firstImage) : null;
                    return $row;
                })->groupBy('promotion_id');
            }

            $categoriesByPromotion = collect();
            if ($categoryPromotionIds->isNotEmpty()) {
                $categoriesByPromotion = DB::table('promotion_categories as pc')
                    ->join('categories as c', 'c.id', '=', 'pc.category_id')
                    ->whereIn('pc.promotion_id', $categoryPromotionIds)
                    ->select('pc.promotion_id', 'c.id', 'c.title')
                    ->get()
                    ->groupBy('promotion_id');
            }

            $result = $promotions->map(function ($promo) use ($itemsByPromotion, $categoriesByPromotion) {
                $data = [
                    'id'         => $promo->id,
                    'name'       => $promo->name,
                    'image_url'  => $promo->image ? url('storage/promotions/' . $promo->image) : null,
                    'bg_color'   => $promo->bg_color,
                    'applies_to' => $promo->applies_to,
                ];

                if ($promo->applies_to === 'item') {
                    $data['items'] = ($itemsByPromotion[$promo->id] ?? collect())->values();
                } else {
                    $data['categories'] = ($categoriesByPromotion[$promo->id] ?? collect())->values();
                }

                return $data;
            });

            return response()->json([
                'type'    => 'success',
                'message' => 'Promotions fetched successfully.',
                'result'  => $result,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'type'    => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
