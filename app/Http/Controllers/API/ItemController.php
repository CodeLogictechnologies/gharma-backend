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

    public function recommended(Request $request)
    {
        $payload = JWTAuth::parseToken()->getPayload();
        $profile = $payload->get('profile');

        $perPage = (int) $request->get('per_page', 10);
        $userId  = $profile['userid'];
        $orgId   = $profile['orgid'];

        $hasOrders = DB::table('order_details')
            ->where('userid', $userId)
            ->exists();

        // ✅ KEEP YOUR OLD QUERY (NO od USED HERE)
        $data = DB::table('itemvariations as iv')
            ->join('items as it', 'it.id', '=', 'iv.item_id')
            ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')

            ->leftJoinSub(
                DB::table('item_images')
                    ->select('item_id', DB::raw('GROUP_CONCAT(image) as images'))
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
                DB::raw("CONCAT(it.title) as title"),
                'iv.id as variationid',
                'iv.value',
                'img.images',
                'p.price'
            )

            ->orderBy('iv.created_at', 'asc')
            ->paginate($perPage);

        if ($data->isEmpty()) {
            return response()->json([
                'type' => 'error',
                'message' => 'No Item found.',
                'result' => $this->paginationMeta($data)
            ]);
        }

        // ✅ GET PRODUCT IDS
        $productIds = collect($data->items())
            ->pluck('productid')
            ->unique()
            ->toArray();

        // ✅ FETCH VARIATIONS (ONE QUERY ONLY)
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

        // ✅ FORMAT RESPONSE
        $items = collect($data->items())->map(function ($row) use ($allVariations) {

            return [
                'productid'   => $row->productid,
                'title'       => $row->title,
                'variationid' => $row->variationid,
                'value'       => $row->value,
                'price'       => $row->price,

                // images
                'images' => $row->images
                    ? array_map(function ($img) {
                        return url('uploads/items/' . $img);
                    }, explode(',', $row->images))
                    : [],

                // ✅ ADD VARIATIONS HERE
                'variations' => $allVariations[$row->productid] ?? [],
            ];
        });

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
            'per_page'    => 'sometimes|integer|min:1|max:50',
        ]);
        // $payload = JWTAuth::parseToken()->getPayload();
        // $profile = $payload->get('profile');
        $perPage = $request->input('per_page', 15);
        $items = DB::table('items as i')
            ->join('itemvariations as iv', 'iv.item_id', '=', 'i.id')
            ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
            ->leftJoin(DB::raw('(
        SELECT 
            item_id,
            GROUP_CONCAT(image) as images
        FROM item_images
        GROUP BY item_id
    ) as im'), 'im.item_id', '=', 'i.id')
            ->select(
                'i.id as productid',
                'iv.id as variationid',
                DB::raw("CONCAT(i.title) as title"),
                'p.price',
                'im.images'
            )
            ->where('p.status', 'Y')
            ->where('iv.status', 'Y')
            ->orderBy('i.created_at', 'desc')
            ->paginate($perPage);

        $baseUrl = url('uploads/items');



        $items->getCollection()->transform(function ($item) use ($baseUrl) {

            // format images
            $item->images = $item->images
                ? array_map(function ($img) use ($baseUrl) {
                    return $baseUrl . '/' . $img;
                }, explode(',', $item->images))
                : [];

            // fetch variations for THIS item
            $item->variations = DB::table('itemvariations as iv')
                ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
                ->select(
                    'iv.id as variationid',
                    DB::raw("CONCAT(iv.value) as name"),
                    'p.price'
                )
                ->where('iv.item_id', $item->productid)
                ->where('p.status', 'Y')
                ->get();

            return $item;
        });
        if ($items->isEmpty()) {
            return response()->json([
                'type' => 'error',
                'message' => 'No Item found.',
                'result' => $this->paginateResponse($items)
            ]);
        }

        return response()->json([
            'type' => 'success',
            'message' => 'Items fetched successfully',
            'result' => $this->paginateResponse($items)
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
                ->join('itemvariations as iv', 'iv.id', '=', 'iv.item_id')
                ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
                // ->where('it.orgid', $profile['orgid'])
                ->where(function ($q) use ($search) {
                    $q->where('it.title', 'LIKE', "%{$search}%")
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
                    ? 'http://127.0.0.1:8000/uploads/items/' . $image
                    : null;
            }

            // ── Variations ─────────────────────────────────────────
            $variations = DB::table('itemvariations as iv')
                ->join('items as it', 'it.id', '=', 'iv.item_id')
                ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
                // ->where('iv.orgid', $profile['orgid'])
                ->where(function ($q) use ($search) {
                    $q->where('iv.attribute', 'LIKE', "%{$search}%")
                        ->orWhere('iv.value', 'LIKE', "%{$search}%")
                        ->orWhere('it.title', 'LIKE', "%{$search}%");
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
                    ? 'http://127.0.0.1:8000/uploads/items/' . $image
                    : null; // clean up from response
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
            //         DB::raw("CONCAT('http://127.0.0.1:8000/uploads/categories/', c.image) as image")
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

            $post['orgid'] = $profile['orgid'];
            $post['userid'] = $profile['userid'];

            $getData = Item::getUserOrderHistory($post);

            if ($getData->isEmpty()) {
                return response()->json([
                    'type'  => 'success',
                    'message' => 'No order history found',
                    'data'    => []
                ], 200);
            }

            return response()->json([
                'type'  => 'success',
                'message' => 'Order history fetched successfully',
                'data'    => $getData
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

            $getData = Item::getUserRecommendation($post);

            if ($getData->isEmpty()) {
                return response()->json([
                    'type'  => 'success',
                    'message' => 'No order history found',
                    'recommendations'    => []
                ], 200);
            }
            return response()->json([
                'type' => 'success',
                'message' => $message,
                'recommendations' => $getData
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