<?php

namespace App\Models\BackPanel;

use App\Models\BackPanel\Category;
use App\Models\BackPanel\Organization;
use App\Models\BackPanel\SubCategory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Item extends Model
{
    protected $fillable = [
        'category_id',
        'subcategory_id',
        'product_code',
        'company_product_code',
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

    /* ─────────────────────────────────────────────────────────────────
       RELATIONSHIPS
    ───────────────────────────────────────────────────────────────── */

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

    /* ─────────────────────────────────────────────────────────────────
       ACCESSORS
    ───────────────────────────────────────────────────────────────── */

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

    /* ─────────────────────────────────────────────────────────────────
       SCOPES
    ───────────────────────────────────────────────────────────────── */

    public function scopeActive($query)
    {
        return $query->where('status', 'Y');
    }

    /* ─────────────────────────────────────────────────────────────────
       LIST (DataTable)
    ───────────────────────────────────────────────────────────────── */

    public static function list(array $post)
    {
        $search1 = trim(strtolower($post['sSearch_1'] ?? ''));
        $search2 = trim(strtolower($post['sSearch_2'] ?? ''));
        $search3 = trim(strtolower($post['sSearch_3'] ?? ''));

        $limit  = (int) ($post['iDisplayLength'] ?? 15);
        $offset = (int) ($post['iDisplayStart']  ?? 0);

        $query = self::query()
            ->from('items')
            ->leftJoin('category_items as ci',     'ci.itemid',  '=', 'items.id')
            ->leftJoin('categories as c',           'c.id',       '=', 'ci.categoryid')
            ->leftJoin('sub_category_items as sci', 'sci.itemid', '=', 'items.id')
            ->leftJoin('sub_categories as s',       's.id',       '=', 'sci.subcategoryid')
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

        $totalrecs     = self::from('items')->where('status', 'Y')->count();
        $filteredCount = (clone $query)->count();

        $sortDir = ($post['sSortDir_0'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('items.id', $sortDir);

        if ($limit > -1) {
            $query->offset($offset)->limit($limit);
        }

        $result  = $query->get();
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

        $brands = DB::table('brands')
            ->select('id', 'name')
            ->get()
            ->keyBy('id');

        $result = $result->map(function ($item) use ($categories, $subcategories, $brands) {
            $item->categories = isset($categories[$item->id])
                ? $categories[$item->id]->pluck('title')->values()
                : collect([]);

            $item->subcategories = isset($subcategories[$item->id])
                ? $subcategories[$item->id]->pluck('title')->values()
                : collect([]);

            $item->brand = isset($brands[$item->brand_id])
                ? $brands[$item->brand_id]->name
                : '—';

            return $item;
        });

        $result['totalrecs']         = $totalrecs;
        $result['totalfilteredrecs'] = $filteredCount;

        return $result;
    }

    /* ─────────────────────────────────────────────────────────────────
       SAVE (INSERT + UPDATE)
    ───────────────────────────────────────────────────────────────── */

    public static function saveData($post)
    {
        try {
            DB::beginTransaction();

            $dataArray = [
                'title'       => $post['title'],
                'product_code'         => $post['product_code'],
                'company_product_code' => $post['company_product_code'],
                'brand_id'    => $post['brand'],
                'threshold'   => '1',
                'type'        => $post['type']        ?? null,
                'description' => $post['description'] ?? null,
                'postedby'    => $post['userid'],
                'orgid'       => $post['orgid']        ?? null,
            ];

            /* ══════════════════════════════════════
               UPDATE
            ══════════════════════════════════════ */
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
                    $productCodeCases        = [];  // ← ADD THIS
                    $companyProductCodeCases = [];
                    // $stockCases     = [];
                    $thresholdCases = [];
                    $statusCases    = [];

                    foreach ($post['variations'] as $variation) {
                        if (empty($variation['value'])) continue;

                        $status = ($variation['status'] === 'active') ? 'Y' : 'N';

                        if (!empty($variation['variationid'])) {
                            $id    = $variation['variationid'];
                            $ids[] = $id;
                            $productCodeCases[]         = "WHEN '$id' THEN '" . addslashes($variation['product_code'])         . "'";
                            $companyProductCodeCases[]  = "WHEN '$id' THEN '" . addslashes($variation['company_product_code']) . "'";

                            $attributeCases[] = "WHEN '$id' THEN '" . addslashes($variation['name'])       . "'";
                            $valueCases[]     = "WHEN '$id' THEN '" . addslashes($variation['value'])      . "'";
                            $priceCases[]     = "WHEN '$id' THEN "  . floatval($variation['price']   ?? 0);
                            // $stockCases[]     = "WHEN '$id' THEN "  . intval($variation['stock']     ?? 0);
                            $thresholdCases[] = "WHEN '$id' THEN "  . intval($variation['threshold'] ?? 0);
                            $statusCases[]    = "WHEN '$id' THEN '$status'";
                        } else {
                            DB::table('itemvariations')->insert([
                                'id'         => (string) Str::uuid(),
                                'item_id'    => $itemId,
                                'attribute'  => $variation['name'],
                                'value'      => $variation['value'],
                                'threshold'  => $variation['threshold'] ?? 0,
                                'product_code'         => $variation['product_code']         ?? null,
                                'company_product_code' => $variation['company_product_code'] ?? null,
                                'price'      => $variation['price']     ?? 0,
                                // 'stock'      => $variation['stock']     ?? 0,
                                'status'     => $status,
                                'orgid'      => $post['orgid']          ?? null,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now(),
                                'postedby'   => $post['userid'],
                                'updatedby'  => $post['userid'],
                            ]);
                        }
                    }

                    if (!empty($ids)) {
                        $idsList = "'" . implode("','", $ids) . "'";
                        DB::statement("
        UPDATE itemvariations SET
            attribute            = CASE id " . implode(' ', $attributeCases)          . " END,
            value                = CASE id " . implode(' ', $valueCases)              . " END,
            product_code         = CASE id " . implode(' ', $productCodeCases)        . " END,
            company_product_code = CASE id " . implode(' ', $companyProductCodeCases) . " END,
            price                = CASE id " . implode(' ', $priceCases)              . " END,
            threshold            = CASE id " . implode(' ', $thresholdCases)          . " END,
            status               = CASE id " . implode(' ', $statusCases)             . " END,
            updated_at           = NOW(),
            updatedby            = ?
        WHERE id IN ($idsList)
    ", [$post['userid']]);
                    }
                }

                // ── DELETE removed images ────────────────────────────────
                // kept_image_ids[] contains the IDs the user left on the form.
                // Any DB image NOT in that list was removed by the user — delete it.
                $keptImageIds = array_filter((array) ($post['kept_image_ids'] ?? []));

                $existingImageIds = DB::table('item_images')
                    ->where('item_id', $itemId)
                    ->pluck('id')
                    ->toArray();

                $toDelete = array_diff($existingImageIds, $keptImageIds);

                if (!empty($toDelete)) {
                    // Remove physical files from storage
                    $filenames = DB::table('item_images')
                        ->whereIn('id', $toDelete)
                        ->pluck('image');

                    foreach ($filenames as $filename) {
                        Storage::disk('public')->delete('items/' . $filename);
                    }

                    DB::table('item_images')->whereIn('id', $toDelete)->delete();
                }

                // ── Save image order for kept images ─────────────────────
                if (!empty($post['image_order'])) {
                    foreach ($post['image_order'] as $order => $imageId) {
                        DB::table('item_images')
                            ->where('id', $imageId)
                            ->where('item_id', $itemId)
                            ->update(['order_number' => (int) $order + 1]);
                    }
                }

                // ── Upload new images added during edit ──────────────────
                if (!empty($post['images'])) {
                    $maxOrder = DB::table('item_images')
                        ->where('item_id', $itemId)
                        ->max('order_number') ?? 0;

                    $imageRows = [];
                    foreach ($post['images'] as $index => $file) {
                        $imageName   = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                        $file->storeAs('items', $imageName, 'public');
                        $imageRows[] = [
                            'id'           => (string) Str::uuid(),
                            'item_id'      => $itemId,
                            'image'        => $imageName,
                            'order_number' => $maxOrder + $index + 1,
                            'orgid'        => $post['orgid'] ?? null,
                            'created_at'   => Carbon::now(),
                            'updated_at'   => Carbon::now(),
                            'postedby'     => $post['userid'],
                            'updatedby'    => $post['userid'],
                        ];
                    }
                    DB::table('item_images')->insert($imageRows);
                }

                /* ══════════════════════════════════════
               INSERT
            ══════════════════════════════════════ */
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
                    foreach ($post['images'] as $index => $file) {
                        $imageName   = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                        $file->storeAs('items', $imageName, 'public');
                        $imageRows[] = [
                            'id'           => (string) Str::uuid(),
                            'item_id'      => $itemId,
                            'image'        => $imageName,
                            'order_number' => $index + 1,
                            'orgid'        => $post['orgid'] ?? null,
                            'created_at'   => Carbon::now(),
                            'updated_at'   => Carbon::now(),
                            'postedby'     => $post['userid'],
                            'updatedby'    => $post['userid'],
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
                            'product_code'         => $variation['product_code']         ?? null, // ← ADD
                            'company_product_code' => $variation['company_product_code'] ?? null,
                            'price'      => $variation['price']     ?? 0,
                            // 'stock'      => $variation['stock']     ?? 0,
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

    /* ─────────────────────────────────────────────────────────────────
       DELETE
    ───────────────────────────────────────────────────────────────── */

    public static function deleteItem($post)
    {
        try {
            $id = $post['id'] ?? null;
            if (empty($id)) {
                throw new Exception('Item ID is required.');
            }

            DB::beginTransaction();

            // Soft-delete the item
            DB::table('items')
                ->where('id', $id)
                ->update([
                    'status'     => 'N',
                    'updated_at' => Carbon::now(),
                ]);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /* ─────────────────────────────────────────────────────────────────
       GET SINGLE ITEM (View)
    ───────────────────────────────────────────────────────────────── */

    public static function getData($post)
    {
        $id = $post['id'] ?? null;

        if (empty($id)) {
            throw new Exception('Item ID is required.');
        }

        $item = DB::table('items as i')
            ->leftJoin('category_items as ci',     'ci.itemid',  '=', 'i.id')
            ->leftJoin('categories as c',           'c.id',       '=', 'ci.categoryid')
            ->leftJoin('sub_category_items as sci', 'sci.itemid', '=', 'i.id')
            ->leftJoin('sub_categories as s',       's.id',       '=', 'sci.subcategoryid')
            ->where('i.id', $id)
            ->select('i.*', 'c.title as category_title', 's.title as subcategory_title')
            ->first();

        if (!$item) {
            throw new Exception('Item not found.');
        }

        $item->images = DB::table('item_images')
            ->where('item_id', $id)
            ->orderByRaw('CASE WHEN order_number IS NULL OR order_number = 0 THEN 1 ELSE 0 END')
            ->orderBy('order_number')
            ->orderBy('created_at')
            ->get();

        $item->variations = DB::table('itemvariations')
            ->where('item_id', $id)
            ->orderBy('id')
            ->get();

        return $item;
    }

    /* ─────────────────────────────────────────────────────────────────
       GET ITEMS (Dropdown)
    ───────────────────────────────────────────────────────────────── */

    public static function getItem($post)
    {
        try {
            return DB::table('items')
                ->select('id as itemid', 'title as itemname')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
