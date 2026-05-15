<?php

namespace App\Models\BackPanel;

use App\Models\BackPanel\Category;
use App\Models\BackPanel\Organization;
use App\Models\BackPanel\SubCategory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Auth;

class Item extends Model
{
    protected $fillable = [
        'category_id',
        'subcategory_id',
        'title',
        'slug',
        'description',
        'type',
        'status',
        'orgid',
        'postedby',
        'updatedby',
    ];



    protected $casts = [
        'images'           => 'array',
        'extra_attributes' => 'array',
    ];

    public $incrementing = false;
    protected $keyType = 'string';


    public function category()
    {
        return $this->belongsTo(Category::class, 'categories');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'sub_categories');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'orgid');
    }


    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->map(fn($path) => Storage::disk('public')->url($path))
            ->toArray();
    }


    public function getPrimaryImageAttribute(): ?string
    {
        $first = collect($this->images ?? [])->first();
        return $first ? Storage::disk('public')->url($first) : null;
    }


    public function scopeActive($query)
    {
        return $query->where('status', 'Y');
    }


    public static function list(array $post)
    {
        $search1 = trim(strtolower($post['sSearch_1'] ?? ''));
        $search2 = trim(strtolower($post['sSearch_2'] ?? ''));
        $search3 = trim(strtolower($post['sSearch_3'] ?? ''));

        $limit  = (int) ($post['iDisplayLength'] ?? 15);
        $offset = (int) ($post['iDisplayStart'] ?? 0);

        $query = self::query()
            ->from('items')
            ->leftJoin('category_items as ci', 'ci.itemid', '=', 'items.id')
            ->leftJoin('categories as c', 'c.id', '=', 'ci.categoryid')
            ->leftJoin('sub_category_items as sci', 'sci.itemid', '=', 'items.id')
            ->leftJoin('sub_categories as s', 's.id', '=', 'sci.subcategoryid')
            ->selectRaw("
        items.id,
        items.title,
        items.description,
        items.status,
        items.type,
        items.created_at
    ")
            ->where('items.status', 'Y');

        if ($search1 !== '') {
            $query->whereRaw("LOWER(items.title) LIKE ?", ["%{$search1}%"]);
        }

        if ($search2 !== '') {
            $query->whereRaw("LOWER(c.title) LIKE ?", ["%{$search2}%"]);
        }

        if ($search3 !== '') {
            $query->whereRaw("LOWER(s.title) LIKE ?", ["%{$search3}%"]);
        }

        $totalrecs = self::from('items')->where('status', 'Y')->count();

        $filteredCount = (clone $query)->count();

        $sortColIndex = (int) ($post['iSortCol_0'] ?? 1);
        $sortDir      = ($post['sSortDir_0'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy('items.id', $sortDir);

        if ($limit > -1) {
            $query->offset($offset)->limit($limit);
        }

        $result = $query->get();

        $itemIds = $result->pluck('id');

        $categories = DB::table('category_items as ci')
            ->join('categories as c', 'c.id', '=', 'ci.categoryid')
            ->whereIn('ci.itemid', $itemIds)
            ->select('ci.itemid', 'c.title')
            ->get()
            ->groupBy('itemid');

        $subcategories = DB::table('sub_category_items as sci')
            ->join('sub_categories as s', 's.id', '=', 'sci.subcategoryid')
            ->whereIn('sci.itemid', $itemIds)
            ->select('sci.itemid', 's.title')
            ->get()
            ->groupBy('itemid');

        $result = $result->map(function ($item) use ($categories, $subcategories) {

            $item->categories = isset($categories[$item->id])
                ? $categories[$item->id]->pluck('title')->values()
                : [];

            $item->subcategories = isset($subcategories[$item->id])
                ? $subcategories[$item->id]->pluck('title')->values()
                : [];

            return $item;
        });

        $result['totalrecs'] = $totalrecs;
        $result['totalfilteredrecs'] = $filteredCount;

        return $result;
    }

    public static function deleteItem($post)
    {
        try {
            $updateArray = [
                'status' => 'N',
                'updated_at' => Carbon::now(),
            ];
            if (!DB::table('wholesaler_prices')->where(['id' => $post['id']])->update($updateArray)) {
                throw new Exception("Couldn't Delete Data. Please try again", 1);
            }
            if (!DB::table('wholesaler_price_details')->where(['wholesalermasterid' => $post['id']])->update($updateArray)) {
                throw new Exception("Couldn't Delete Data. Please try again", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getData($post)
    {
        $id = $post['id'] ?? null;

        if (empty($id)) {
            throw new Exception('Item ID is required.');
        }

        // ── Core item + category + subcategory names ─────────────────────
        // $item = DB::table('items as i')
        //     ->leftJoin('categories as c',    'c.id', '=', 'i.category_id')
        //     ->leftJoin('sub_categories as s', 's.id', '=', 'i.subcategory_id')
        //     ->where('i.id', $id)
        //     ->select(
        //         'i.*',
        //         'c.title as category_title',
        //         's.title as subcategory_title'
        //     )
        //     ->first();

        $item = DB::table('items as i')
    ->leftJoin('category_items as ci', 'ci.itemid', '=', 'i.id')
    ->leftJoin('categories as c', 'c.id', '=', 'ci.categoryid')
    ->leftJoin('sub_category_items as sci', 'sci.itemid', '=', 'i.id')
    ->leftJoin('sub_categories as s', 's.id', '=', 'sci.subcategoryid')
    ->where('i.id', $id)
    ->select('i.*', 'c.title as category_title', 's.title as subcategory_title')
    ->first();

        if (!$item) {
            throw new Exception('Item not found.');
        }

        // ── Images ───────────────────────────────────────────────────────
        $item->images = DB::table('item_images')
            ->where('item_id', $id)
            ->orderBy('id')
            ->get();

        // ── Variations ───────────────────────────────────────────────────
        $item->variations = DB::table('itemvariations')
            ->where('item_id', $id)
            ->orderBy('id')
            ->get();

        return $item;
    }




    public static function saveData($post)
    {
        try {
            DB::beginTransaction();

            $dataArray = [
                'title'       => $post['title'],
                'brand_id'    => $post['brand'],
                'threshold'   => '1',
                'type'        => $post['type']        ?? null,
                'description' => $post['description'] ?? null,
                'postedby'    => $post['userid'],
                'orgid'       => $post['orgid']        ?? null,
            ];

            // ════════════════════════════════════════
            // UPDATE
            // ════════════════════════════════════════
            if (!empty($post['id'])) {

                $itemId = $post['id'];

                $dataArray['updatedby']  = $post['userid'];
                $dataArray['updated_at'] = Carbon::now();

                DB::table('items')->where('id', $itemId)->update($dataArray);

                // ── Sync category_items pivot ────────────────────────────
                DB::table('category_items')->where('itemid', $itemId)->delete();

                if (!empty($post['categories'])) {
                    $categoryRows = [];
                    foreach ($post['categories'] as $categoryId) {
                        $categoryRows[] = [
                            'id'         => (string) Str::uuid(),
                            'orgid'      => $post['orgid'] ?? null,
                            'categoryid' => $categoryId,
                            'itemid'     => $itemId,
                            'postedby'   => $post['userid'],
                            'updatedby'  => $post['userid'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                    DB::table('category_items')->insert($categoryRows);
                }

                // ── Sync sub_category_items pivot ────────────────────────
                DB::table('sub_category_items')->where('itemid', $itemId)->delete();

                if (!empty($post['sub_categories'])) {
                    $subCategoryRows = [];
                    foreach ($post['sub_categories'] as $subCategoryId) {
                        $subCategoryRows[] = [
                            'id'            => (string) Str::uuid(),
                            'orgid'         => $post['orgid'] ?? null,
                            'subcategoryid' => $subCategoryId,
                            'itemid'        => $itemId,
                            'postedby'      => $post['userid'],
                            'updatedby'     => $post['userid'],
                            'created_at'    => Carbon::now(),
                            'updated_at'    => Carbon::now(),
                        ];
                    }
                    DB::table('sub_category_items')->insert($subCategoryRows);
                }

                // ── Update Variations ────────────────────────────────────
                if (!empty($post['variations'])) {

                    $ids            = [];
                    $attributeCases = [];
                    $valueCases     = [];
                    $priceCases     = [];
                    $stockCases     = [];
                    $thresholdCases = [];
                    $statusCases    = [];

                    foreach ($post['variations'] as $variation) {
                        if (empty($variation['value'])) continue;

                        $status = ($variation['status'] === 'active') ? 'Y' : 'N';

                        if (!empty($variation['variationid'])) {
                            // Existing variation — bulk update
                            $id    = $variation['variationid'];
                            $ids[] = $id;

                            $attributeCases[] = "WHEN '$id' THEN '" . addslashes($variation['name'])      . "'";
                            $valueCases[]     = "WHEN '$id' THEN '" . addslashes($variation['value'])     . "'";
                            $priceCases[]     = "WHEN '$id' THEN "  . floatval($variation['price']  ?? 0);
                            $stockCases[]     = "WHEN '$id' THEN "  . intval($variation['stock']    ?? 0);
                            $thresholdCases[] = "WHEN '$id' THEN "  . intval($variation['threshold'] ?? 0);
                            $statusCases[]    = "WHEN '$id' THEN '$status'";
                        } else {
                            // New variation added during edit
                            DB::table('itemvariations')->insert([
                                'id'         => (string) Str::uuid(),
                                'item_id'    => $itemId,
                                'attribute'  => $variation['name'],
                                'value'      => $variation['value'],
                                'threshold'  => $variation['threshold'] ?? 0,
                                'price'      => $variation['price']     ?? 0,
                                'stock'      => $variation['stock']     ?? 0,
                                'status'     => $status,
                                'orgid'      => $post['orgid']          ?? null,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                                'postedby'   => $post['userid'],
                                'updatedby'  => $post['userid'],
                            ]);
                        }
                    }

                    // Bulk update existing variations
                    if (!empty($ids)) {
                        $idsList = "'" . implode("','", $ids) . "'";

                        DB::statement("
                            UPDATE itemvariations SET
                                attribute  = CASE id " . implode(' ', $attributeCases) . " END,
                                value      = CASE id " . implode(' ', $valueCases)     . " END,
                                price      = CASE id " . implode(' ', $priceCases)     . " END,
                                stock      = CASE id " . implode(' ', $stockCases)     . " END,
                                threshold  = CASE id " . implode(' ', $thresholdCases) . " END,
                                status     = CASE id " . implode(' ', $statusCases)    . " END,
                                updated_at = NOW(),
                                updatedby  = ?
                            WHERE id IN ($idsList)
                        ", [$post['userid']]); // ← passed as binding, no quotes needed
                    }
                }

                // ── New images added during edit ─────────────────────────
                if (!empty($post['images'])) {
                    $imageRows = [];
                    foreach ($post['images'] as $file) {
                        $imageName   = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('uploads/items'), $imageName);
                        $imageRows[] = [
                            'id'         => (string) Str::uuid(),
                            'item_id'    => $itemId,
                            'image'      => $imageName,
                            'orgid'      => $post['orgid'] ?? null,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                            'postedby'   => $post['userid'],
                            'updatedby'  => $post['userid'],
                        ];
                    }
                    DB::table('item_images')->insert($imageRows);
                }

                // ════════════════════════════════════════
                // INSERT
                // ════════════════════════════════════════
            } else {

                $itemId = (string) Str::uuid();

                $dataArray['id']         = $itemId;
                $dataArray['slug']       = Str::slug($post['title']) . '-' . time();
                $dataArray['created_at'] = Carbon::now();
                $dataArray['updated_at'] = Carbon::now();
                $dataArray['updatedby']  = $post['userid'];
                $inserted = DB::table('items')->insert($dataArray);
                if (!$inserted) {
                    throw new Exception("Couldn't save item.");
                }

                // ── Insert category_items pivot ──────────────────────────
                if (!empty($post['categories'])) {
                    $categoryRows = [];
                    foreach ($post['categories'] as $categoryId) {
                        $categoryRows[] = [
                            'id'         => (string) Str::uuid(),
                            'orgid'      => $post['orgid'] ?? null,
                            'categoryid' => $categoryId,
                            'itemid'     => $itemId,
                            'postedby'   => $post['userid'],
                            'updatedby'  => $post['userid'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                    DB::table('category_items')->insert($categoryRows);
                }

                // ── Insert sub_category_items pivot ──────────────────────
                if (!empty($post['sub_categories'])) {
                    $subCategoryRows = [];
                    foreach ($post['sub_categories'] as $subCategoryId) {
                        $subCategoryRows[] = [
                            'id'            => (string) Str::uuid(),
                            'orgid'         => $post['orgid'] ?? null,
                            'subcategoryid' => $subCategoryId,
                            'itemid'        => $itemId,
                            'postedby'      => $post['userid'],
                            'updatedby'     => $post['userid'],
                            'created_at'    => Carbon::now(),
                            'updated_at'    => Carbon::now(),
                        ];
                    }
                    DB::table('sub_category_items')->insert($subCategoryRows);
                }

                // ── Insert Images ────────────────────────────────────────
                if (!empty($post['images'])) {
                    $imageRows = [];
                    foreach ($post['images'] as $file) {
                        $imageName   = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('uploads/items'), $imageName);
                        $imageRows[] = [
                            'id'         => (string) Str::uuid(),
                            'item_id'    => $itemId,
                            'image'      => $imageName,
                            'orgid'      => $post['orgid'] ?? null,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                            'postedby'   => $post['userid'],
                            'updatedby'  => $post['userid'],
                        ];
                    }
                    $imageInserted = DB::table('item_images')->insert($imageRows);
                    if (!$imageInserted) {
                        throw new Exception("Couldn't save item images.");
                    }
                }

                // ── Insert Variations ────────────────────────────────────
                if (!empty($post['variations'])) {
                    $variationRows = [];
                    foreach ($post['variations'] as $variation) {
                        if (empty($variation['value'])) continue;

                        $status = ($variation['status'] === 'active') ? 'Y' : 'N';

                        $variationRows[] = [
                            'id'         => (string) Str::uuid(),
                            'item_id'    => $itemId,
                            'attribute'  => $variation['name'],
                            'value'      => $variation['value'],
                            'threshold'  => $variation['threshold'] ?? 0,
                            'price'      => $variation['price']     ?? 0,
                            'stock'      => $variation['stock']     ?? 0,
                            'status'     => $status,
                            'orgid'      => $post['orgid']          ?? null,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                            'postedby'   => $post['userid'],
                            'updatedby'  => $post['userid'],
                        ];
                    }

                    if (!empty($variationRows)) {
                        $variationInserted = DB::table('itemvariations')->insert($variationRows);
                        if (!$variationInserted) {
                            throw new Exception("Couldn't save item variations.");
                        }
                    }
                }
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function getItem($post)
    {
        try {
            $data = DB::table('items')->select('id as itemid', 'title as itemname')->where('orgid', $post['orgid'])->get();
            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }
}