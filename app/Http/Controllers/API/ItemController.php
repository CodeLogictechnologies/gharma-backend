<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\ItemDetailRequest;
use App\Models\API\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;

class ItemController extends Controller
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


    // public function recommended(Request $request)
    // {
    //     $payload = JWTAuth::parseToken()->getPayload();
    //     $profile = $payload->get('profile');

    //     $perPage     = (int) $request->get('per_page', 10);
    //     $userId      = $profile['userid'];
    //     $orgId       = $profile['orgid'];
    //     $tabId       = $request->input('tab_id');
    //     $categoryIds = [];
    //     $post['roleid'] = $request->header('roleid');

    //     // ── Resolve tab by id OR tab_name ──
    //     if (!empty($tabId)) {
    //         $tab = DB::table('home_tabs')
    //             ->where('status', 'Y')
    //             ->where(function ($q) use ($tabId) {
    //                 $q->where('id', $tabId)
    //                     ->orWhereRaw('LOWER(tab_name) = ?', [strtolower($tabId)]);
    //             })
    //             ->first();

    //         if (!$tab) {
    //             return response()->json([
    //                 'type'    => 'error',
    //                 'message' => 'Tab not found or inactive.',
    //                 'result'  => []
    //             ]);
    //         }

    //         if (strtolower($tab->tab_name) !== 'all') {
    //             $categoryIds = DB::table('home_tab_categories')
    //                 ->where('home_tab_id', $tab->id)
    //                 ->pluck('category_id')
    //                 ->toArray();

    //             if (empty($categoryIds)) {
    //                 return response()->json([
    //                     'type'    => 'error',
    //                     'message' => 'No categories assigned to this tab.',
    //                     'result'  => []
    //                 ]);
    //             }
    //         }
    //     }

    //     $hasOrders = DB::table('order_details')
    //         ->where('userid', $userId)
    //         ->exists();

    //     if (!empty($post['roleid']) && $post['roleid'] == "550e8400-e29b-41d4-a716-446655440002") {

    //         $query = DB::table('itemvariations as iv')
    //             ->join('items as it', 'it.id', '=', 'iv.item_id')
    //             ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
    //             ->leftJoin('wholesaler_prices as wp', function ($join) {
    //                 $join->on('wp.itemid', '=', 'it.id')
    //                     ->on('wp.variation_id', '=', 'iv.id')
    //                     ->where('wp.status', 'Y');
    //             })
    //             ->leftJoinSub(
    //                 DB::table('item_images')
    //                     ->select('item_id', DB::raw("STRING_AGG(image::text, ',') as images"))
    //                     ->groupBy('item_id'),
    //                 'img',
    //                 'img.item_id',
    //                 '=',
    //                 'it.id'
    //             )
    //             ->where('it.orgid', $orgId)
    //             ->where('iv.status', 'Y')
    //             ->where('p.status', 'Y')
    //             ->select(
    //                 'it.id as productid',
    //                 'it.title as title',
    //                 'iv.id as variationid',
    //                 'iv.value',
    //                 'img.images',
    //                 'wp.id as wholesaler_price_id',
    //                 'p.price',
    //                 'iv.created_at'
    //             )
    //             ->groupBy(
    //                 'it.id',
    //                 'it.title',
    //                 'iv.id',
    //                 'iv.value',
    //                 'img.images',
    //                 'wp.id',
    //                 'p.price',
    //                 'iv.created_at'
    //             )
    //             ->orderBy('iv.created_at', 'asc');
    //     } else {

    //         $query = DB::table('items as i')
    //             ->join('itemvariations as iv', 'iv.item_id', '=', 'i.id')
    //             ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
    //             ->leftJoinSub(
    //                 DB::table('item_images')
    //                     ->select('item_id', DB::raw("STRING_AGG(image::text, ',') as images"))
    //                     ->groupBy('item_id'),
    //                 'im',
    //                 'im.item_id',
    //                 '=',
    //                 'i.id'
    //             )
    //             ->leftJoin('discounts as d', function ($join) {
    //                 $join->where(function ($q) {
    //                     $q->where('d.applies_to', 'entire')
    //                         ->where('d.status', 'Y')
    //                         ->whereRaw('CURRENT_DATE BETWEEN d.starts_at AND d.ends_at');
    //                 })
    //                     ->orWhere(function ($q) {
    //                         $q->where('d.applies_to', 'item')
    //                             ->whereColumn('d.item_id', 'i.id')
    //                             ->where('d.status', 'Y')
    //                             ->whereRaw('CURRENT_DATE BETWEEN d.starts_at AND d.ends_at');
    //                     })
    //                     ->orWhere(function ($q) {
    //                         $q->where('d.applies_to', 'variation')
    //                             ->whereColumn('d.variation_id', 'iv.id')
    //                             ->where('d.status', 'Y')
    //                             ->whereRaw('CURRENT_DATE BETWEEN d.starts_at AND d.ends_at');
    //                     });
    //             })
    //             ->select(
    //                 'i.id as productid',
    //                 'iv.id as variationid',
    //                 'i.title as title',
    //                 'iv.value',
    //                 DB::raw("
    //             CASE
    //                 WHEN MAX(d.id::text) IS NULL THEN p.price
    //                 WHEN MAX(d.type) = 'percentage' THEN ROUND(CAST(p.price - (p.price * MAX(d.percentage) / 100) AS numeric), 2)
    //                 WHEN MAX(d.type) = 'fixed' THEN ROUND(CAST(p.price - MAX(d.value) AS numeric), 2)
    //                 ELSE p.price
    //             END as price
    //         "),
    //                 'im.images',
    //                 DB::raw("MAX(d.type) as discount_type"),
    //                 DB::raw("MAX(d.value) as discount_value"),
    //                 DB::raw("MAX(d.percentage) as discount_percentage"),
    //                 DB::raw("
    //             CASE
    //                 WHEN MAX(d.id::text) IS NULL THEN NULL
    //                 ELSE p.price
    //             END as original_price
    //         ")
    //             )
    //             ->whereRaw("p.status = 'Y'")
    //             ->whereRaw("iv.status = 'Y'")
    //             ->groupBy('i.id', 'i.title', 'iv.id', 'iv.value', 'im.images', 'p.price');
    //     }
    //     $data = $query->paginate($perPage);

    //     if ($data->isEmpty()) {
    //         return response()->json([
    //             'type'    => 'error',
    //             'message' => 'No Item found.',
    //             'result'  => $this->paginationMeta($data)
    //         ]);
    //     }

    //     $productIds = collect($data->items())
    //         ->pluck('productid')
    //         ->unique()
    //         ->toArray();

    //     $allVariations = DB::table('itemvariations as iv')
    //         ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
    //         ->select(
    //             'iv.id as variationid',
    //             'iv.item_id as productid',
    //             'iv.value as name',
    //             'p.price'
    //         )
    //         ->whereIn('iv.item_id', $productIds)
    //         ->where('iv.status', 'Y')
    //         ->where('p.status', 'Y')
    //         ->get()
    //         ->groupBy('productid');

    //     $wholesalerDetails = DB::table('wholesaler_price_details as wd')
    //         ->join('wholesaler_prices as wp', 'wp.id', '=', 'wd.wholesalermasterid')
    //         ->where('wd.status', 'Y')
    //         ->where('wp.status', 'Y')
    //         ->where('wp.orgid', $orgId)
    //         ->select(
    //             'wd.wholesalermasterid',
    //             'wd.min_qty',
    //             'wd.max_qty',
    //             'wd.price'
    //         )
    //         ->orderBy('wd.min_qty', 'asc')
    //         ->get()
    //         ->groupBy('wholesalermasterid')
    //         ->map(function ($details) {
    //             return $details->map(function ($detail) {
    //                 return [
    //                     'price'   => $detail->price,
    //                     'min_qty' => $detail->min_qty,
    //                     'max_qty' => $detail->max_qty,
    //                 ];
    //             })->values();
    //         });

    //     if (!empty($post['roleid']) && $post['roleid'] == '550e8400-e29b-41d4-a716-446655440002') {

    //         $items = collect($data->items())->map(function ($row) use ($allVariations, $wholesalerDetails) {
    //             return [
    //                 'productid'        => $row->productid,
    //                 'title'            => $row->title,
    //                 'variationid'      => $row->variationid,
    //                 'value'            => $row->value,
    //                 'price'            => $row->price,
    //                 'images'           => $row->images
    //                     ? array_map(fn($img) => url('storage/items/' . trim($img)), explode(',', $row->images))
    //                     : [],
    //                 'wholesaler_price' => !empty($row->wholesaler_price_id)
    //                     ? $wholesalerDetails->get($row->wholesaler_price_id, collect([]))->values()
    //                     : [],
    //                 'variations'       => $allVariations[$row->productid] ?? [],
    //             ];
    //         });
    //     } else {

    //         $items = collect($data->items())->map(function ($row) use ($allVariations) {
    //             return [
    //                 'productid'           => $row->productid,
    //                 'title'               => $row->title,
    //                 'variationid'         => $row->variationid,
    //                 'value'               => $row->value,
    //                 'price'               => $row->price,
    //                 'original_price'      => $row->original_price ?? null,
    //                 'discount_type'       => $row->discount_type ?? null,
    //                 'discount_value'      => $row->discount_value ?? null,
    //                 'discount_percentage' => $row->discount_percentage ?? null,
    //                 'images'              => $row->images
    //                     ? array_map(fn($img) => url('storage/items/' . trim($img)), explode(',', $row->images))
    //                     : [],
    //                 'variations'          => $allVariations[$row->productid] ?? [],
    //             ];
    //         });
    //     }

    //     return response()->json([
    //         'type'    => 'success',
    //         'message' => $hasOrders
    //             ? 'Recommended items fetched successfully.'
    //             : 'No order history found. Showing all items.',
    //         'result'  => [
    //             'is_personalized' => $hasOrders,
    //             'data'            => $items,
    //             'pagination'      => $this->paginationMeta($data),
    //         ],
    //     ]);
    // }

    public function recommended(Request $request)
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $profile = $payload->get('profile');

        $perPage        = (int) $request->get('per_page', 10);
        $userId         = $profile['userid'];
        $orgId          = $profile['orgid'];
        $tabId          = $request->input('tab_id');
        $categoryId     = $request->input('category_id');
        $subcategory_id = $request->input('subcategory_id');
        $categoryIds    = [];
        $post['roleid'] = $request->header('roleid');

        // ── Resolve tab by id OR tab_name ──
        if (!empty($tabId) && empty($categoryId)) {
            $tab = DB::table('home_tabs')
                ->where('status', 'Y')
                ->where(function ($q) use ($tabId) {
                    $q->where('id', $tabId)
                        ->orWhereRaw('LOWER(tab_name) = ?', [strtolower($tabId)]);
                })
                ->first();

            if (!$tab) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'Tab not found or inactive.',
                    'result'  => []
                ]);
            }

            if (strtolower($tab->tab_name) !== 'all') {
                $categoryIds = DB::table('home_tab_categories')
                    ->where('home_tab_id', $tab->id)
                    ->pluck('category_id')
                    ->toArray();

                if (empty($categoryIds)) {
                    return response()->json([
                        'type'    => 'error',
                        'message' => 'No categories assigned to this tab.',
                        'result'  => []
                    ]);
                }
                $categoryIds = $this->expandCategoryIds($categoryIds); // ✅ expand parent → children
            }
        }

        $hasOrders = DB::table('order_details')
            ->where('userid', $userId)
            ->exists();

        if (!empty($post['roleid']) && $post['roleid'] == "550e8400-e29b-41d4-a716-446655440002") {

            $query = DB::table('itemvariations as iv')
                ->join('items as it', 'it.id', '=', 'iv.item_id')
                ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
                ->leftJoin('wholesaler_prices as wp', function ($join) {
                    $join->on('wp.itemid', '=', 'it.id')
                        ->on('wp.variation_id', '=', 'iv.id')
                        ->where('wp.status', 'Y');
                })
                ->leftJoinSub(
                    DB::table('item_images')
                        ->select('item_id', DB::raw("STRING_AGG(image::text, ',') as images"))
                        ->groupBy('item_id'),
                    'img',
                    'img.item_id',
                    '=',
                    'it.id'
                )
                ->where('it.orgid', $orgId)
                ->where('iv.status', 'Y')
                ->where('p.status', 'Y')
                ->select(
                    'it.id as productid',
                    'it.title as title',
                    'iv.id as variationid',
                    'iv.value',
                    'img.images',
                    'wp.id as wholesaler_price_id',
                    'p.price',
                    'iv.created_at'
                )
                ->groupBy(
                    'it.id',
                    'it.title',
                    'iv.id',
                    'iv.value',
                    'img.images',
                    'wp.id',
                    'p.price',
                    'iv.created_at'
                )
                ->orderBy('iv.created_at', 'asc');
        } else {

            $query = DB::table('items as i')
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
                ->leftJoin('discounts as d', function ($join) {
                    $join->where(function ($q) {
                        $q->where('d.applies_to', 'entire')
                            ->where('d.status', 'Y')
                            ->whereRaw('CURRENT_DATE BETWEEN d.starts_at AND d.ends_at');
                    })
                        ->orWhere(function ($q) {
                            $q->where('d.applies_to', 'item')
                                ->whereColumn('d.item_id', 'i.id')
                                ->where('d.status', 'Y')
                                ->whereRaw('CURRENT_DATE BETWEEN d.starts_at AND d.ends_at');
                        })
                        ->orWhere(function ($q) {
                            $q->where('d.applies_to', 'variation')
                                ->whereColumn('d.variation_id', 'iv.id')
                                ->where('d.status', 'Y')
                                ->whereRaw('CURRENT_DATE BETWEEN d.starts_at AND d.ends_at');
                        });
                })
                ->select(
                    'i.id as productid',
                    'iv.id as variationid',
                    'i.title as title',
                    'iv.value',
                    DB::raw("
                    CASE
                        WHEN MAX(d.id::text) IS NULL THEN p.price
                        WHEN MAX(d.type) = 'percentage' THEN ROUND(CAST(p.price - (p.price * MAX(d.percentage) / 100) AS numeric), 2)
                        WHEN MAX(d.type) = 'fixed' THEN ROUND(CAST(p.price - MAX(d.value) AS numeric), 2)
                        ELSE p.price
                    END as price
                "),
                    'im.images',
                    DB::raw("MAX(d.type) as discount_type"),
                    DB::raw("MAX(d.value) as discount_value"),
                    DB::raw("MAX(d.percentage) as discount_percentage"),
                    DB::raw("
                    CASE
                        WHEN MAX(d.id::text) IS NULL THEN NULL
                        ELSE p.price
                    END as original_price
                ")
                )
                ->whereRaw("p.status = 'Y'")
                ->whereRaw("iv.status = 'Y'")
                ->groupBy('i.id', 'i.title', 'iv.id', 'iv.value', 'im.images', 'p.price');
        }

        // ── Filter logic (shared for both) ──
        $tableAlias = (!empty($post['roleid']) && $post['roleid'] == "550e8400-e29b-41d4-a716-446655440002")
            ? 'it' : 'i';

        if (!empty($categoryIds) && !empty($subcategory_id)) {
            $query->join('category_items as ci', 'ci.itemid', '=', "{$tableAlias}.id")
                ->whereIn('ci.categoryid', array_merge($categoryIds, [$subcategory_id]));
        } elseif (!empty($categoryIds) && !empty($categoryId)) {
            $query->join('category_items as ci', 'ci.itemid', '=', "{$tableAlias}.id")
                ->whereIn('ci.categoryid', array_merge($categoryIds, [$categoryId]));
        } elseif (!empty($categoryIds)) {
            $query->join('category_items as ci', 'ci.itemid', '=', "{$tableAlias}.id")
                ->whereIn('ci.categoryid', $categoryIds);
        } elseif (!empty($categoryId) && !empty($subcategory_id)) {
            $query->join('category_items as ci', 'ci.itemid', '=', "{$tableAlias}.id")
                ->whereIn('ci.categoryid', [$categoryId, $subcategory_id]);
        } elseif (!empty($subcategory_id)) {
            $query->join('category_items as ci', 'ci.itemid', '=', "{$tableAlias}.id")
                ->where('ci.categoryid', $subcategory_id);
        } elseif (!empty($categoryId)) {
            $query->join('category_items as ci', 'ci.itemid', '=', "{$tableAlias}.id")
                ->where('ci.categoryid', $categoryId);
        }

        $data = $query->paginate($perPage);

        if ($data->isEmpty()) {
            return response()->json([
                'type'    => 'error',
                'message' => 'No Item found.',
                'result'  => $this->paginationMeta($data)
            ]);
        }

        $productIds = collect($data->items())
            ->pluck('productid')
            ->unique()
            ->toArray();

        $allVariations = DB::table('itemvariations as iv')
            ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
            ->select(
                'iv.id as variationid',
                'iv.item_id as productid',
                'iv.value as name',
                'p.price'
            )
            ->whereIn('iv.item_id', $productIds)
            ->where('iv.status', 'Y')
            ->where('p.status', 'Y')
            ->get()
            ->groupBy('productid');

        $wholesalerDetails = DB::table('wholesaler_price_details as wd')
            ->join('wholesaler_prices as wp', 'wp.id', '=', 'wd.wholesalermasterid')
            ->where('wd.status', 'Y')
            ->where('wp.status', 'Y')
            ->where('wp.orgid', $orgId)
            ->select(
                'wd.wholesalermasterid',
                'wd.min_qty',
                'wd.max_qty',
                'wd.price'
            )
            ->orderBy('wd.min_qty', 'asc')
            ->get()
            ->groupBy('wholesalermasterid')
            ->map(function ($details) {
                return $details->map(function ($detail) {
                    return [
                        'price'   => $detail->price,
                        'min_qty' => $detail->min_qty,
                        'max_qty' => $detail->max_qty,
                    ];
                })->values();
            });

        if (!empty($post['roleid']) && $post['roleid'] == '550e8400-e29b-41d4-a716-446655440002') {

            $items = collect($data->items())->map(function ($row) use ($allVariations, $wholesalerDetails) {
                return [
                    'productid'        => $row->productid,
                    'title'            => $row->title,
                    'variationid'      => $row->variationid,
                    'value'            => $row->value,
                    'price'            => $row->price,
                    'images'           => $row->images
                        ? array_map(fn($img) => url('storage/items/' . trim($img)), explode(',', $row->images))
                        : [],
                    'wholesaler_price' => !empty($row->wholesaler_price_id)
                        ? $wholesalerDetails->get($row->wholesaler_price_id, collect([]))->values()
                        : [],
                    'variations'       => $allVariations[$row->productid] ?? [],
                ];
            });
        } else {

            $items = collect($data->items())->map(function ($row) use ($allVariations) {
                return [
                    'productid'           => $row->productid,
                    'title'               => $row->title,
                    'variationid'         => $row->variationid,
                    'value'               => $row->value,
                    'price'               => $row->price,
                    'original_price'      => $row->original_price ?? null,
                    'discount_type'       => $row->discount_type ?? null,
                    'discount_value'      => $row->discount_value ?? null,
                    'discount_percentage' => $row->discount_percentage ?? null,
                    'images'              => $row->images
                        ? array_map(fn($img) => url('storage/items/' . trim($img)), explode(',', $row->images))
                        : [],
                    'variations'          => $allVariations[$row->productid] ?? [],
                ];
            });
        }

        return response()->json([
            'type'    => 'success',
            'message' => $hasOrders
                ? 'Recommended items fetched successfully.'
                : 'No order history found. Showing all items.',
            'result'  => [
                'is_personalized' => $hasOrders,
                'data'            => $items,
                'pagination'      => $this->paginationMeta($data),
            ],
        ]);
    }

    // public function getByProductCode(Request $request, $product_code)
    // {
    //     try {
    //         $item = DB::table('items as i')
    //             ->leftJoin('brands as b', 'b.id', '=', 'i.brand_id')
    //             ->leftJoin(DB::raw("(
    //                 SELECT item_id, string_agg(image, ',' ORDER BY order_number ASC) as images
    //                 FROM item_images
    //                 GROUP BY item_id
    //                 ) as img"), 'img.item_id', '=', 'i.id')
    //             ->where('i.status', 'Y')
    //             ->where(function ($q) use ($product_code) {
    //                 $q->where('i.product_code', $product_code)
    //                  ->orWhere('i.company_product_code', $product_code);
    //                 })
    //             ->select(
    //                 'i.id as productid',
    //                 'i.title',
    //                 'i.product_code',
    //                 'i.company_product_code',
    //                 'i.description',
    //                 'i.type',
    //                 'b.name as brand',
    //                 'img.images'
    //             )
    //             ->first();

    //         if (!$item) {
    //             return response()->json([
    //                 'type'    => 'error',
    //                 'message' => 'Product not found.',
    //                 'result'  => null,
    //             ], 404);
    //         }

    //         $item->images = $item->images
    //             ? array_map(
    //                 fn($img) => url('storage/items/' . trim($img)),
    //                 explode(',', $item->images)
    //             )
    //             : [];

    //         return response()->json([
    //             'type'    => 'success',
    //             'message' => 'Product fetched successfully.',
    //             'result'  => $item,
    //         ], 200);
    //     } catch (QueryException $e) {
    //         return response()->json(['type' => 'error', 'message' => 'Database error.'], 500);
    //     } catch (Exception $e) {
    //         return response()->json(['type' => 'error', 'message' => $e->getMessage()], 400);
    //     }
    // }

    public function getByProductCode(Request $request, $product_code)
    {
        try {
            // 1. Try matching at the item level first
            $item = DB::table('items as i')
                ->leftJoin('brands as b', 'b.id', '=', 'i.brand_id')
                ->leftJoin(DB::raw("(
                SELECT item_id, string_agg(image, ',' ORDER BY order_number ASC) as images
                FROM item_images
                GROUP BY item_id
            ) as img"), 'img.item_id', '=', 'i.id')
                ->where('i.status', 'Y')
                ->where(function ($q) use ($product_code) {
                    $q->where('i.product_code', $product_code)
                        ->orWhere('i.company_product_code', $product_code);
                })
                ->select(
                    'i.id as productid',
                    'i.title',
                    'i.product_code',
                    'i.company_product_code',
                    'i.description',
                    'i.type',
                    'b.name as brand',
                    'img.images'
                )
                ->first();

            // 2. If no item-level match, try matching at the variation level
            $variationMatch = null;
            if (!$item) {
                $variationMatch = DB::table('itemvariations as iv')
                    ->join('items as i', 'i.id', '=', 'iv.item_id')
                    ->leftJoin('brands as b', 'b.id', '=', 'i.brand_id')
                    ->leftJoin(DB::raw("(
                    SELECT item_id, string_agg(image, ',' ORDER BY order_number ASC) as images
                    FROM item_images
                    GROUP BY item_id
                ) as img"), 'img.item_id', '=', 'i.id')
                    ->where('i.status', 'Y')
                    ->where('iv.status', 'Y')
                    ->where(function ($q) use ($product_code) {
                        $q->where('iv.product_code', $product_code)
                            ->orWhere('iv.company_product_code', $product_code);
                    })
                    ->select(
                        'i.id as productid',
                        'i.title',
                        'iv.id as variationid',
                        'iv.value as variation_value',
                        'iv.product_code',
                        'iv.company_product_code',
                        'i.description',
                        'i.type',
                        'b.name as brand',
                        'img.images'
                    )
                    ->first();
            }

            $result = $item ?? $variationMatch;

            if (!$result) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'Product not found.',
                    'result'  => null,
                ], 404);
            }

            $result->images = $result->images
                ? array_map(
                    fn($img) => url('storage/items/' . trim($img)),
                    explode(',', $result->images)
                )
                : [];

            return response()->json([
                'type'    => 'success',
                'message' => 'Product fetched successfully.',
                'result'  => $result,
            ], 200);
        } catch (QueryException $e) {
            \Log::error('getByProductCode error: ' . $e->getMessage());
            return response()->json(['type' => 'error', 'message' => 'Database error.'], 500);
        } catch (Exception $e) {
            return response()->json(['type' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /* =============================================================
 | PRIVATE HELPERS
 ============================================================= */
    private function batchImages(array $itemIds)
    {
        if (empty($itemIds)) return collect();

        return DB::table('item_images')
            ->whereIn('item_id', $itemIds)
            ->select('item_id', 'image')
            ->get()
            ->groupBy('item_id')
            ->map(fn($group) => $group->map(fn($img) => [
                'image' => $img->image,
            ]));
    }

    private function paginationMeta($data): array
    {
        return [
            'current_page' => $data->currentPage(),
            'last_page'    => $data->lastPage(),
            'per_page'     => $data->perPage(),
            'total'        => $data->total(),
            'has_more'     => $data->hasMorePages(),
            'next_page'    => $data->hasMorePages() ? $data->currentPage() + 1 : null,
            'prev_page'    => $data->currentPage() > 1 ? $data->currentPage() - 1 : null,
        ];
    }
    /* =========================================================
     | 2. LATEST ITEMS
     |    — items sorted by created_at DESC, paginated.
     |      Optional: filter by category.
     ========================================================= */



    public function latest(Request $request)
    {
        $request->validate([
            'per_page'       => 'sometimes|integer|min:1|max:50',
            'category_id'    => 'sometimes|string|nullable',
            'tab_id'         => 'sometimes|string|nullable',
            'subcategory_id' => 'sometimes|string|nullable',
            'brand_id'       => 'sometimes|string|nullable',

        ]);

        $perPage        = $request->input('per_page', 15);
        $categoryId     = $request->input('category_id');
        $tabId          = $request->input('tab_id');
        $subcategory_id = $request->input('subcategory_id');
        $brandId        = $request->input('brand_id');
        $categoryIds    = [];
        $roleId         = $request->header('roleid');
        // dd($roleId);
        $isWholesaler   = !empty($roleId) && $roleId == "550e8400-e29b-41d4-a716-446655440002";
        // dd($isWholesaler);
        // ── Resolve tab ──
        if (!empty($tabId) && empty($categoryId)) {
            $tab = DB::table('home_tabs')
                ->where('status', 'Y')
                ->where(function ($q) use ($tabId) {
                    $q->where('id', $tabId)
                        ->orWhereRaw('LOWER(tab_name) = ?', [strtolower($tabId)]);
                })
                ->first();

            if (!$tab) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'Tab not found or inactive.',
                    'result'  => []
                ]);
            }

            if (strtolower($tab->tab_name) !== 'all') {
                $categoryIds = DB::table('home_tab_categories')
                    ->where('home_tab_id', $tab->id)
                    ->pluck('category_id')
                    ->toArray();

                if (empty($tabCategoryIds)) {
                    return response()->json([
                        'type'    => 'error',
                        'message' => 'No categories assigned to this tab.',
                        'result'  => []
                    ]);
                }
                // expand: if a tab category is a parent, include its children too
                $categoryIds = $this->expandCategoryIds($tabCategoryIds);
            }
        }

        // ── Build query ──
        if ($isWholesaler) {
            $query = DB::table('items as i')
                ->join('itemvariations as iv', 'iv.item_id', '=', 'i.id')

                ->leftJoin('wholesaler_prices as wp', function ($join) {
                    $join->whereColumn('wp.itemid', 'i.id')
                        ->whereColumn('wp.variation_id', 'iv.id')
                        ->where('wp.status', 'Y');
                })

                ->leftJoin(
                    DB::raw("
            (
                SELECT
                    item_id,
                    STRING_AGG(image::text, ',') AS images
                FROM item_images
                GROUP BY item_id
            ) AS im
        "),
                    'im.item_id',
                    '=',
                    'i.id'
                )

                ->select(
                    'i.id as productid',
                    'iv.id as variationid',
                    'i.title as title',
                    'i.brand_id as brand_id',
                    'im.images',
                    'wp.id as wholesaler_price_id',
                    'iv.created_at'
                )

                ->where('iv.status', 'Y')

                ->groupBy(
                    'i.id',
                    'iv.id',
                    'i.title',
                    'i.brand_id',
                    'im.images',
                    'wp.id',
                    'iv.created_at'
                );
        } else {
            $query = DB::table('items as i')
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
                ->leftJoin(
                    DB::raw('discounts as d'),
                    function ($join) {
                        $join->whereRaw("
            (
                d.applies_to = 'entire'
                AND d.status = 'Y'
                AND CURRENT_DATE BETWEEN d.starts_at AND d.ends_at
            )
            OR (
                d.applies_to = 'item'
                AND d.item_id = i.id
                AND d.status = 'Y'
                AND CURRENT_DATE BETWEEN d.starts_at AND d.ends_at
            )
            OR (
                d.applies_to = 'variation'
                AND d.variation_id = iv.id
                AND d.status = 'Y'
                AND CURRENT_DATE BETWEEN d.starts_at AND d.ends_at
            )
        ");
                    }
                )
                ->select(
                    'i.id as productid',
                    'iv.id as variationid',
                    'i.title as title',
                    'i.brand_id as brand_id',
                    DB::raw("
            CASE
                WHEN MAX(d.id::text) IS NULL THEN p.price
                WHEN MAX(d.type) = 'percentage' THEN ROUND(CAST(p.price - (p.price * MAX(d.percentage) / 100) AS numeric), 2)
                WHEN MAX(d.type) = 'fixed' THEN ROUND(CAST(p.price - MAX(d.value) AS numeric), 2)
                ELSE p.price
            END as price
        "),
                    'im.images',
                    DB::raw("MAX(d.type) as discount_type"),
                    DB::raw("MAX(d.value) as discount_value"),
                    DB::raw("MAX(d.percentage) as discount_percentage"),
                    DB::raw("
            CASE
                WHEN MAX(d.id::text) IS NULL THEN NULL
                ELSE p.price
            END as original_price
        ")
                )
                ->where('p.status', 'Y')
                ->where('iv.status', 'Y')
                ->groupBy('i.id', 'i.title', 'iv.id', 'iv.value', 'im.images', 'p.price');
        }
        // dd($query);
        // ── Filter logic (shared for both) ──
        // if (!empty($categoryIds) && !empty($subcategory_id)) {
        //     $query->join('category_items as ci', 'ci.itemid', '=', 'i.id')
        //         ->join('sub_category_items as sci', 'sci.itemid', '=', 'i.id')
        //         ->whereIn('ci.categoryid', $categoryIds)
        //         ->where('sci.subcategoryid', $subcategory_id);
        // } elseif (!empty($categoryIds) && !empty($categoryId)) {
        //     $query->join('category_items as ci', 'ci.itemid', '=', 'i.id')
        //         ->where('ci.categoryid', $categoryId);
        // } elseif (!empty($categoryIds)) {
        //     $query->join('category_items as ci', 'ci.itemid', '=', 'i.id')
        //         ->whereIn('ci.categoryid', $categoryIds);
        // } elseif (!empty($categoryId) && !empty($subcategory_id)) {
        //     $query->join('category_items as ci', 'ci.itemid', '=', 'i.id')
        //         ->join('sub_category_items as sci', 'sci.itemid', '=', 'i.id')
        //         ->where('ci.categoryid', $categoryId)
        //         ->where('sci.subcategoryid', $subcategory_id);
        // } elseif (!empty($subcategory_id)) {
        //     $query->join('sub_category_items as sci', 'sci.itemid', '=', 'i.id')
        //         ->where('sci.subcategoryid', $subcategory_id);
        // } elseif (!empty($categoryId)) {
        //     $query->join('category_items as ci', 'ci.itemid', '=', 'i.id')
        //         ->where('ci.categoryid', $categoryId);
        // }
        // ── Filter logic (shared for both) ──
        if (!empty($categoryIds) && !empty($subcategory_id)) {
            // tab categories + subcategory (child category) filter
            $query->join('category_items as ci', 'ci.itemid', '=', 'i.id')
                ->whereIn('ci.categoryid', array_merge($categoryIds, [$subcategory_id]));
        } elseif (!empty($categoryIds) && !empty($categoryId)) {
            // tab categories + specific category
            $query->join('category_items as ci', 'ci.itemid', '=', 'i.id')
                ->whereIn('ci.categoryid', array_merge($categoryIds, [$categoryId]));
        } elseif (!empty($categoryIds)) {
            // tab categories only
            $query->join('category_items as ci', 'ci.itemid', '=', 'i.id')
                ->whereIn('ci.categoryid', $categoryIds);
        } elseif (!empty($categoryId) && !empty($subcategory_id)) {
            // specific parent category + child category filter
            $query->join('category_items as ci', 'ci.itemid', '=', 'i.id')
                ->whereIn('ci.categoryid', [$categoryId, $subcategory_id]);
        } elseif (!empty($subcategory_id)) {
            // child category only (was sub_category_items, now just category_items)
            $query->join('category_items as ci', 'ci.itemid', '=', 'i.id')
                ->where('ci.categoryid', $subcategory_id);
        } elseif (!empty($categoryId)) {
            // parent category only
            $query->join('category_items as ci', 'ci.itemid', '=', 'i.id')
                ->where('ci.categoryid', $categoryId);
        }

        // ── Brand filter ──
        if (!empty($brandId)) {
            $query->where('i.brand_id', $brandId);
        }
        // ── GroupBy ──
        if ($isWholesaler) {
            $query->groupBy('i.id', 'iv.id', 'i.title', 'i.brand_id', 'im.images', 'wp.id', 'iv.created_at');
        } else {
            $query->groupBy('i.id', 'p.price', 'iv.id', 'i.title', 'i.brand_id', 'im.images', 'iv.created_at');
        }

        $query->orderBy('iv.created_at', 'desc');
        $items = $query->paginate($perPage);

        if ($items->isEmpty()) {
            return response()->json([
                'type'    => 'error',
                'message' => 'No items found.',
                'result'  => $this->paginateResponse($items)
            ]);
        }

        $baseUrl    = url('storage/items');
        $productIds = collect($items->items())->pluck('productid')->unique()->toArray();

        // ── All variations ──
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

        // ── Wholesaler details (only fetch if wholesaler) ──
        $wholesalerDetails = collect();
        if ($isWholesaler) {
            $wholesalerPriceIds = collect($items->items())
                ->pluck('wholesaler_price_id')
                ->filter()
                ->unique()
                ->toArray();

            $wholesalerDetails = DB::table('wholesaler_price_details as wd')
                ->join('wholesaler_prices as wp', 'wp.id', '=', 'wd.wholesalermasterid')
                ->where('wd.status', 'Y')
                ->where('wp.status', 'Y')
                ->whereIn('wd.wholesalermasterid', $wholesalerPriceIds)
                ->select('wd.wholesalermasterid', 'wd.min_qty', 'wd.max_qty', 'wd.price')
                ->orderBy('wd.min_qty', 'asc')
                ->get()
                ->groupBy('wholesalermasterid')
                ->map(function ($details) {
                    return $details->map(fn($d) => [
                        'price'   => $d->price,
                        'min_qty' => $d->min_qty,
                        'max_qty' => $d->max_qty,
                    ])->values();
                });
        }

        // ── Transform ──
        $items->getCollection()->transform(function ($item) use ($baseUrl, $allVariations, $wholesalerDetails, $isWholesaler) {
            $item->images     = $item->images
                ? array_map(fn($img) => $baseUrl . '/' . trim($img), explode(',', $item->images))
                : [];
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

    public function search(Request $request)
    {
        try {
            $search  = $request->get('search');
            $perPage = $request->get('per_page', 10);
            $page    = $request->get('page', 1);

            if (empty($search)) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'Search keyword is required',
                    'result'  => [
                        'search' => [
                            'data'       => [],
                            'pagination' => [
                                'current_page' => (int)$page,
                                'next_page'    => null,
                                'prev_page'    => null,
                                'last_page'    => 1,
                                'per_page'     => (int)$perPage,
                                'total'        => 0,
                                'has_more'     => false,
                            ]
                        ]
                    ]
                ]);
            }

            // ── Items ──────────────────────────────────────────────
            $items = DB::table('items as it')
                ->join('itemvariations as iv', 'iv.item_id', '=', 'it.id')
                ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
                // ->where('it.orgid', $profile['orgid'])
                ->where(function ($q) use ($search) {
                    $q->whereRaw("CONCAT(it.title, ' ', iv.value) ILIKE ?", ["%{$search}%"])
                        ->orWhere('it.description', 'LIKE', "%{$search}%");
                })
                ->select(
                    'it.id',
                    'p.price',
                    DB::raw("CONCAT(it.title, ' ', iv.value) as title"),
                    DB::raw("'item' as type")
                )
                ->get();

            foreach ($items as $item) {
                $image        = DB::table('item_images')
                    ->where('item_id', $item->id)
                    ->value('image as images');
                $item->image  = $image
                    ? request()->getSchemeAndHttpHost() . '/storage/items/' . $image
                    : null;
            }

            // ── Variations ─────────────────────────────────────────
            $variations = DB::table('itemvariations as iv')
                ->join('items as it', 'it.id', '=', 'iv.item_id')
                ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
                // ->where('iv.orgid', $profile['orgid'])
                ->where(function ($q) use ($search) {
                    // $q->where('iv.attribute', 'LIKE', "%{$search}%")
                    //     ->orWhere('iv.value', 'LIKE', "%{$search}%")
                    //     ->orWhere('it.title', 'LIKE', "%{$search}%");
                    //    (change)
                    $q->whereRaw("CONCAT(it.title, ' ', iv.value) ILIKE ?", ["%{$search}%"])
                        ->orWhere('iv.attribute', 'ILIKE', "%{$search}%");
                })
                ->select(
                    'iv.id as variationid',
                    'iv.item_id as productid',
                    'p.price',
                    DB::raw("CONCAT(it.title, ' ', iv.value) as title")
                    // DB::raw("'variation' as type")
                )
                ->get();

            foreach ($variations as $v) {
                $image       = DB::table('item_images')
                    ->where('item_id', $v->productid)
                    ->value('image as images');
                $v->images    = $image
                    ? request()->getSchemeAndHttpHost() . '/storage/items/' . $image  // ← change here
                    : null;
            }


            // // ── Categories ─────────────────────────────────────────
            // $categories = DB::table('categories as c')
            //     // ->where('c.orgid', $profile['orgid'])
            //     ->where('c.title', 'LIKE', "%{$search}%")
            //     ->select(
            //         'c.id',
            //         'c.title',
            //         DB::raw("NULL as description"),
            //         DB::raw("'category' as type"),
            //         DB::raw("CONCAT('http://127.0.0.1:8000/storage/categories/', c.image) as image")
            //     )
            //     ->get();

            // ── Merge & Paginate ───────────────────────────────────
            $merged = $items
                ->merge($variations)
                // ->merge($categories)
                ->values();

            $total = $merged->count();

            if ($total === 0) {
                return response()->json([
                    'type'    => 'error',
                    'message' => 'No results found.',
                    'result'  => [
                        'data'       => [],
                        'pagination' => [
                            'current_page' => (int)$page,
                            'next_page'    => null,
                            'prev_page'    => null,
                            'last_page'    => 1,
                            'per_page'     => (int)$perPage,
                            'total'        => 0,
                            'has_more'     => false,
                        ]
                    ]
                ]);
            }

            $pagedData = $merged
                ->slice(($page - 1) * $perPage, $perPage)
                ->values();

            $lastPage  = (int)ceil($total / $perPage);

            $pagination = [
                'current_page' => (int)$page,
                'next_page'    => ($page * $perPage < $total) ? $page + 1 : null,
                'prev_page'    => ($page > 1) ? $page - 1 : null,
                'last_page'    => $lastPage,
                'per_page'     => (int)$perPage,
                'total'        => $total,
                'has_more'     => ($page * $perPage < $total),
            ];

            return response()->json([
                'type'   => 'success',
                'result' => [
                    'data'       => $pagedData,
                    'pagination' => $pagination,
                ]
            ]);
        } catch (QueryException $e) {
            \Log::error('Search DB Error: ' . $e->getMessage());
            return apiResponseQuery('error', 'Something went wrong. Please try again later.', null, 500);
        } catch (\Exception $e) {
            \Log::error('Search Error: ' . $e->getMessage());
            return apiResponseQuery('error', 'Something went wrong. Please try again later.', null, 500);
        }
    }

    // public function getDetails(ItemDetailRequest $request)
    public function getDetails(Request $request, $variationid)
    {
        try {

            $type = 'success';
            $message = 'Product detail fetched successfully.';

            $profile = [
                'userid' => null,
                'orgid'  => null
            ];

            // 🔥 SAFE JWT HANDLING (IMPORTANT FIX)
            try {
                if ($request->bearerToken()) {
                    $payload = JWTAuth::parseToken()->getPayload();
                    $profile = $payload->get('profile') ?? $profile;
                }
            } catch (Exception $e) {
                // ❗ ignore token errors → treat as guest user
                $profile = [
                    'userid' => null,
                    'orgid'  => null
                ];
            }

            $post = [
                'variationid' => $variationid,
                'orgid'       => $profile['orgid'],
                'userid'      => $profile['userid'],
            ];

            $data = Item::getData($post);

            // ✅ EMPTY CASE
            if (!$data) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Product not found',
                    'details' => null
                ], 404);
            }

            return response()->json([
                'type' => 'success',
                'message' => $message,
                'details' => $data
            ], 200);
        } catch (QueryException $e) {

            return response()->json([
                'type' => 'error',
                'message' => 'Database error occurred',
                'details' => null
            ], 500);
        } catch (Exception $e) {

            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage(),
                'details' => null
            ], 400);
        }
    }

    public function getUserOrderHistory(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');

            $post = [
                'orgid'    => $profile['orgid'],
                'userid'   => $profile['userid'],
                'page'     => $request->get('page', 1),
                'per_page' => $request->get('per_page', 10),
            ];

            $getData = Item::getUserOrderHistory($post);

            if (collect($getData['data'])->isEmpty()) {
                return response()->json([
                    'type'    => 'success',
                    'message' => 'No order history found',
                    'data'       => [],
                    'pagination' => $getData['pagination'],
                ], 200);
            }

            return response()->json([
                'type'       => 'success',
                'message'    => 'Order history fetched successfully',
                'data'       => $getData['data'],        // ← your exact data key
                'pagination' => $getData['pagination'],  // ← pagination beside it
            ], 200);
        } catch (QueryException $e) {
            return apiResponseQuery('error', 'Something went wrong. Please try again later.', null, 500);
        } catch (\Exception $e) {
            return apiResponse('error', $e->getMessage(), null, 500);
        }
    }


    public function searchHistory(Request $request)
    {
        try {
            $payload = JWTAuth::parseToken()->getPayload();
            $profile = $payload->get('profile');
            $message = 'Recommendation fetch successfully';
            $post['orgid'] = $profile['orgid'];
            $post['userid'] = $profile['userid'];

            $post['page']     = $request->get('page', 1);
            $post['per_page'] = $request->get('per_page', 10);

            $getData = Item::getUserRecommendation($post);

            if (collect($getData['data'])->isEmpty()) {
                return response()->json([
                    'type'            => 'success',
                    'message'         => 'No recommendations found',
                    'recommendations' => [],
                    'pagination'      => $getData['pagination'],
                ], 200);
            }

            return response()->json([
                'type'            => 'success',
                'message'         => $message,
                'recommendations' => $getData['data'],
                'pagination'      => $getData['pagination'],
            ], 200);
        } catch (QueryException $e) {

            return response()->json([
                'type' => 'error',
                'message' => 'Something went wrong',
                'recommendations' => null
            ], 500);
        } catch (Exception $e) {

            return response()->json([
                'type' => 'error',
                'message' => $e->getMessage(),
                'recommendations' => null
            ], 400);
        }
    }
}