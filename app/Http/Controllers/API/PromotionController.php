<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class PromotionController extends Controller
{

    private function paginateResponse($data)
    {
        return [
            'data' => $data->items(),
            'pagination' => [
                'current_page' => $data->currentPage(),
                'next_page' => $data->hasMorePages() ? $data->currentPage() + 1 : null,
                'prev_page' => $data->currentPage() > 1 ? $data->currentPage() - 1 : null,
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'has_more' => $data->hasMorePages(),
            ]
        ];
    }
    public function index()
    {
        try {
            $promotionBaseUrl = url('storage/promotions'); // adjust to match your actual promotion-image storage path
            $itemBaseUrl       = url('storage/items');

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

            $activeDiscount = DB::table('discount_details as dd')
                ->join('discount_masters as dm', 'dm.id', '=', 'dd.discount_master_id')
                ->where('dd.status', 'Y')
                ->where('dm.status', 'Y')
                ->where(function ($q) {
                    $q->whereNull('dm.start_date_ad')
                        ->orWhere('dm.start_date_ad', '<=', now()->toDateString());
                })
                ->where(function ($q) {
                    $q->whereNull('dm.end_date_ad')
                        ->orWhere('dm.end_date_ad', '>=', now()->toDateString());
                })
                ->select(
                    'dd.variation_id',
                    'dd.discount_type',
                    'dd.discount_value',
                    'dd.discount_amount',
                    DB::raw('ROW_NUMBER() OVER (PARTITION BY dd.variation_id ORDER BY dd.created_at DESC) as rn')
                );

            // --- reusable SQL fragments ---
            $variationDiscount = "
            CASE
                WHEN iv.discount_type = 'percentage' THEN (p.price * iv.discount / 100)
                WHEN iv.discount_type = 'fixed' THEN iv.discount_amount
                ELSE 0
            END
        ";

            $campaignDiscount = "
            CASE
                WHEN ad.discount_type = 'percentage' THEN (p.price * ad.discount_value / 100)
                WHEN ad.discount_type = 'fixed' THEN ad.discount_amount
                ELSE 0
            END
        ";

            $priceAfterAllDiscounts = "(p.price - ($variationDiscount) - ($campaignDiscount))";

            $exciseBefore = "
            CASE
                WHEN i.excise_status = 'Y' AND i.excise_type = 'percentage' THEN p.price * (i.excise_percentage / 100)
                WHEN i.excise_status = 'Y' AND i.excise_type = 'fixed' THEN i.excise_value
                ELSE 0
            END
        ";

            $exciseAfter = "
            CASE
                WHEN i.excise_status = 'Y' AND i.excise_type = 'percentage' THEN ($priceAfterAllDiscounts) * (i.excise_percentage / 100)
                WHEN i.excise_status = 'Y' AND i.excise_type = 'fixed' THEN i.excise_value
                ELSE 0
            END
        ";

            $vatBefore = "(p.price + ($exciseBefore)) * (i.vat_percent / 100)";
            $vatAfter  = "(($priceAfterAllDiscounts) + ($exciseAfter)) * (i.vat_percent / 100)";

            // ── Fresh query builder per call, so filters/limits never leak across promotions ──
            $buildBaseQuery = function () use ($activeDiscount, $priceAfterAllDiscounts, $exciseBefore, $exciseAfter, $vatBefore, $vatAfter) {
                return DB::table('items as i')
                    ->join('itemvariations as iv', 'iv.item_id', '=', 'i.id')
                    ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
                    ->leftJoinSub(
                        DB::table('item_images')
                            ->select('item_id', DB::raw("STRING_AGG(image::text, ',') as images"))
                            ->groupBy('item_id'),
                        'im',
                        'im.item_id',
                        '=',
                        'i.id'
                    )
                    ->leftJoinSub($activeDiscount, 'ad', function ($join) {
                        $join->on('ad.variation_id', '=', 'iv.id')
                            ->where('ad.rn', '=', 1);
                    })
                    ->select(
                        'i.id as productid',
                        'iv.id as variationid',
                        'iv.value as variation_name',
                        'i.title as title',
                        'i.brand_id as brand_id',
                        DB::raw("
                        CASE
                            WHEN ad.discount_type IS NULL THEN p.price
                            WHEN ad.discount_type = 'percentage' THEN ROUND(CAST(p.price - (p.price * ad.discount_value / 100) AS numeric), 2)
                            WHEN ad.discount_type = 'fixed' THEN ROUND(CAST(p.price - ad.discount_amount AS numeric), 2)
                            ELSE p.price
                        END as price
                    "),
                        'p.price as raw_price',
                        'im.images',
                        'ad.discount_type as discount_type',
                        DB::raw("
                        CASE
                            WHEN ad.discount_type = 'fixed' THEN ad.discount_amount
                            ELSE ad.discount_value
                        END as discount_value
                    "),
                        DB::raw("
                        CASE
                            WHEN ad.discount_type = 'percentage' THEN ad.discount_value
                            ELSE NULL
                        END as discount_percentage
                    "),
                        DB::raw("ROUND(CAST(p.price + ($exciseBefore) + ($vatBefore) AS numeric), 2) as price_before_discount"),
                        DB::raw("ROUND(CAST(($priceAfterAllDiscounts) + ($exciseAfter) + ($vatAfter) AS numeric), 2) as price_after_discount")
                    )
                    ->where('p.status', 'Y')
                    ->where('iv.status', 'Y')
                    ->orderBy('i.id')
                    ->orderBy('iv.id');
            };

            // ── Build item-id list per promotion, fetch rows, group into productid → variations ──
            $result = $promotions->map(function ($promo) use ($buildBaseQuery, $promotionBaseUrl, $itemBaseUrl) {
                if ($promo->applies_to === 'item') {
                    $itemIds = DB::table('promotion_items')
                        ->where('promotion_id', $promo->id)
                        ->pluck('item_id');
                } elseif ($promo->applies_to === 'category') {
                    $categoryIds = DB::table('promotion_categories')
                        ->where('promotion_id', $promo->id)
                        ->pluck('category_id');

                    // NOTE: category_items references the item via `itemid`, not `id`
                    $itemIds = DB::table('category_items')
                        ->whereIn('categoryid', $categoryIds)
                        ->where('status', 'Y')
                        ->pluck('itemid');
                } else {
                    $itemIds = collect();
                }

                $items = collect();

                if ($itemIds->isNotEmpty()) {
                    $rows = $buildBaseQuery()
                        ->whereIn('i.id', $itemIds)
                        ->get();

                    $grouped = $rows->groupBy('productid');

                    $items = $grouped->take(6)->map(function ($variationRows) use ($itemBaseUrl) {
                        $first = $variationRows->first();

                        $imagesArray = !empty($first->images)
                            ? array_map(
                                fn($img) => $itemBaseUrl . '/' . trim($img),
                                array_values(array_filter(explode(',', $first->images)))
                            )
                            : [];

                        return [
                            'productid'             => $first->productid,
                            'variationid'           => $first->variationid,
                            'title'                 => $first->title,
                            'brand_id'              => $first->brand_id,
                            'price'                 => $first->price,
                            'images'                => $imagesArray,
                            'discount_type'         => $first->discount_type,
                            'discount_value'        => $first->discount_value,
                            'discount_percentage'   => $first->discount_percentage,
                            'price_before_discount' => $first->price_before_discount,
                            'price_after_discount'  => $first->price_after_discount,
                            'variations'            => $variationRows->map(function ($v) {
                                return [
                                    'variationid' => $v->variationid,
                                    'productid'   => $v->productid,
                                    'name'        => $v->variation_name,
                                    'price'       => $v->raw_price,
                                ];
                            })->values(),
                        ];
                    })->values();
                }

                return [
                    'id'         => $promo->id,
                    'name'       => $promo->name,
                    'image'      => $promo->image ? $promotionBaseUrl . '/' . trim($promo->image) : null,
                    'bg_color'   => $promo->bg_color,
                    'applies_to' => $promo->applies_to,
                    'items'      => $items,
                ];
            })->values();

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
    public function getPromotionItem(Request $request, $id, $applies_to)
    {
        $validator = Validator::make(
            [
                'id'          => $id,
                'applies_to'  => $applies_to,
                'perPage'     => $request->input('perPage', 10),
            ],
            [
                'id'          => 'required',
                'applies_to'  => 'required',
                'perPage'     => 'nullable|integer|min:1|max:100',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'type'    => 'error',
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $perPage = $request->input('perPage', 10);

        if ($applies_to === 'item') {
            $itemIds = DB::table('promotion_items')
                ->where('promotion_id', $id)
                ->pluck('item_id');
        } else {
            $categoryIds = DB::table('promotion_categories')
                ->where('promotion_id', $id)
                ->pluck('category_id');

            $itemIds = DB::table('category_items')
                ->whereIn('categoryid', $categoryIds)
                ->where('status', 'Y')
                ->pluck('itemid');
        }

        if ($itemIds->isEmpty()) {
            return response()->json([
                'type'    => 'error',
                'message' => 'No items found for this promotion.',
                'result'  => [],
            ], 404);
        }

        $activeDiscount = DB::table('discount_details as dd')
            ->join('discount_masters as dm', 'dm.id', '=', 'dd.discount_master_id')
            ->where('dd.status', 'Y')
            ->where('dm.status', 'Y')
            ->where(function ($q) {
                $q->whereNull('dm.start_date_ad')
                    ->orWhere('dm.start_date_ad', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('dm.end_date_ad')
                    ->orWhere('dm.end_date_ad', '>=', now()->toDateString());
            })
            ->select(
                'dd.variation_id',
                'dd.discount_type',
                'dd.discount_value',
                'dd.discount_amount',
                DB::raw('ROW_NUMBER() OVER (PARTITION BY dd.variation_id ORDER BY dd.created_at DESC) as rn')
            );

        $variationDiscount = "
        CASE
            WHEN iv.discount_type = 'percentage' THEN (p.price * iv.discount / 100)
            WHEN iv.discount_type = 'fixed' THEN iv.discount_amount
            ELSE 0
        END
    ";

        $campaignDiscount = "
        CASE
            WHEN ad.discount_type = 'percentage' THEN (p.price * ad.discount_value / 100)
            WHEN ad.discount_type = 'fixed' THEN ad.discount_amount
            ELSE 0
        END
    ";

        $priceAfterAllDiscounts = "(p.price - ($variationDiscount) - ($campaignDiscount))";

        $exciseBefore = "
        CASE
            WHEN i.excise_status = 'Y' AND i.excise_type = 'percentage' THEN p.price * (i.excise_percentage / 100)
            WHEN i.excise_status = 'Y' AND i.excise_type = 'fixed' THEN i.excise_value
            ELSE 0
        END
    ";

        $exciseAfter = "
        CASE
            WHEN i.excise_status = 'Y' AND i.excise_type = 'percentage' THEN ($priceAfterAllDiscounts) * (i.excise_percentage / 100)
            WHEN i.excise_status = 'Y' AND i.excise_type = 'fixed' THEN i.excise_value
            ELSE 0
        END
    ";

        $vatBefore = "(p.price + ($exciseBefore)) * (i.vat_percent / 100)";
        $vatAfter  = "(($priceAfterAllDiscounts) + ($exciseAfter)) * (i.vat_percent / 100)";

        $query = DB::table('items as i')
            ->join('itemvariations as iv', 'iv.item_id', '=', 'i.id')
            ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
            ->leftJoinSub(
                DB::table('item_images')
                    ->select('item_id', DB::raw("COALESCE(STRING_AGG(image::text, ','), '') as images"))
                    ->groupBy('item_id'),
                'im',
                'im.item_id',
                '=',
                'i.id'
            )
            ->leftJoinSub($activeDiscount, 'ad', function ($join) {
                $join->on('ad.variation_id', '=', 'iv.id')
                    ->where('ad.rn', '=', 1);
            })
            ->select(
                'i.id as productid',
                'iv.id as variationid',
                'i.title as title',
                'i.brand_id as brand_id',
                DB::raw("
                CASE
                    WHEN ad.discount_type IS NULL THEN p.price
                    WHEN ad.discount_type = 'percentage' THEN ROUND(CAST(p.price - (p.price * ad.discount_value / 100) AS numeric), 2)
                    WHEN ad.discount_type = 'fixed' THEN ROUND(CAST(p.price - ad.discount_amount AS numeric), 2)
                    ELSE p.price
                END as price
            "),
                'im.images',
                'ad.discount_type as discount_type',
                DB::raw("
                CASE
                    WHEN ad.discount_type = 'fixed' THEN ad.discount_amount
                    ELSE ad.discount_value
                END as discount_value
            "),
                DB::raw("
                CASE
                    WHEN ad.discount_type = 'percentage' THEN ad.discount_value
                    ELSE NULL
                END as discount_percentage
            "),
                DB::raw("ROUND(CAST(p.price + ($exciseBefore) + ($vatBefore) AS numeric), 2) as price_before_discount"),
                DB::raw("ROUND(CAST(($priceAfterAllDiscounts) + ($exciseAfter) + ($vatAfter) AS numeric), 2) as price_after_discount")
            )
            ->whereIn('i.id', $itemIds)
            ->where('p.status', 'Y')
            ->where('iv.status', 'Y')
            ->groupBy(
                'i.id',
                'i.title',
                'iv.id',
                'iv.value',
                'im.images',
                'p.price',
                'ad.discount_type',
                'ad.discount_value',
                'ad.discount_amount'
            )
            ->orderBy('iv.created_at', 'desc');

        $items = $query->paginate($perPage);

        if ($items->isEmpty()) {
            return response()->json([
                'type'    => 'error',
                'message' => 'No items found.',
                'result'  => $this->paginateResponse($items),
            ]);
        }

        // Use asset() helper for better URL generation
        $baseUrl = asset('storage/items');

        $productIds = collect($items->items())->pluck('productid')->unique()->toArray();

        $allVariations = DB::table('itemvariations as iv')
            ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
            ->select(
                'iv.id as variationid',
                'iv.item_id as productid',
                DB::raw("CONCAT(iv.value) as name"),
                'p.price'
            )
            ->whereIn('iv.item_id', $productIds)
            ->where('iv.status', 'Y')
            ->where('p.status', 'Y')
            ->get()
            ->groupBy('productid');

        $isWholesaler      = $request->boolean('is_wholesaler');
        $wholesalerDetails = collect();

        $items->getCollection()->transform(function ($item) use ($baseUrl, $allVariations, $wholesalerDetails, $isWholesaler) {
            // Transform images to full URLs
            if (!empty($item->images)) {
                $imageArray = explode(',', $item->images);
                $item->images = array_map(function ($img) use ($baseUrl) {
                    $img = trim($img);
                    // Skip if empty
                    if (empty($img)) {
                        return null;
                    }
                    // If already a full URL, return as is
                    if (filter_var($img, FILTER_VALIDATE_URL)) {
                        return $img;
                    }
                    // Otherwise, prepend base URL
                    return $baseUrl . '/' . ltrim($img, '/');
                }, $imageArray);
                // Remove any null values
                $item->images = array_values(array_filter($item->images));
            } else {
                $item->images = [];
            }

            $item->variations = $allVariations[$item->productid] ?? collect();

            if ($isWholesaler) {
                $item->wholesaler_price = !empty($item->wholesaler_price_id)
                    ? $wholesalerDetails->get($item->wholesaler_price_id, collect([]))->values()
                    : [];
                unset($item->wholesaler_price_id);
            }

            return $item;
        });

        return response()->json([
            'type'    => 'success',
            'message' => 'Items fetched successfully.',
            'result'  => $this->paginateResponse($items)
        ]);
    }
}