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
        'images'           => 'array',   // auto encode/decode JSON
        'extra_attributes' => 'array',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    // ── Relationships ─────────────────────────────────────────────────────

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
 
    // ── Accessors ─────────────────────────────────────────────────────────

    /**
     * Full public URLs for every stored image path.
     * Usage: $item->image_urls
     */
    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->map(fn($path) => Storage::disk('public')->url($path))
            ->toArray();
    }

    /**
     * First path = primary image URL.
     * Usage: $item->primary_image
     */
    public function getPrimaryImageAttribute(): ?string
    {
        $first = collect($this->images ?? [])->first();
        return $first ? Storage::disk('public')->url($first) : null;
    }

    // ── Scopes ────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'Y');
    }




    // ============================================================
    // In Item.php (Model) — replace your list() static method with this
    // ============================================================

    public static function list(array $post)
    {
        // Sanitise DataTable column search values
        $columns = $post['columns'] ?? [];
        foreach ($columns as &$col) {
            $col['search']['value'] = trim(strtolower(
                htmlspecialchars($col['search']['value'] ?? '', ENT_QUOTES)
            ));
        }
        unset($col);

        // ── Base condition ─────────────────────────────────────────────────
        $conditions = ["items.status = 'Y'"];

        // Column[1] search → item title
        if (!empty($columns[1]['search']['value'])) {
            $val          = $columns[1]['search']['value'];
            $conditions[] = "lower(items.title) like '%{$val}%'";
        }

        // Column[2] search → category name
        if (!empty($columns[2]['search']['value'])) {
            $val          = $columns[2]['search']['value'];
            $conditions[] = "lower(c.name) like '%{$val}%'";
        }

        $where  = implode(' AND ', $conditions);
        $limit  = (int) ($post['length'] ?? 15);
        $offset = (int) ($post['start']  ?? 0);

        // ── Query ──────────────────────────────────────────────────────────
        $query = self::query()
            ->from('items')
            ->join('categories as c',     'items.category_id',     '=', 'c.id')
            ->join('sub_categories as s', 'items.subcategory_id', '=', 's.id')
            ->join('brands as b', 'b.id', '=', 'items.brand_id')
            ->selectRaw("
            items.id,
            items.title,
            items.description,
            items.status,
            items.type,
            b.name,
            items.created_at,
            c.title  AS category_name,
            s.title  AS sub_category_name,
            (SELECT COUNT(*) FROM items WHERE status = 'Y') AS totalrecs
        ")
            ->whereRaw($where);



        // Total filtered count (before pagination)
        $filteredCount = (clone $query)->count();

        // Apply pagination
        if ($limit > -1) {
            $query->orderBy('items.id', 'desc')->offset($offset)->limit($limit);
        } else {
            $query->orderBy('items.id', 'desc');
        }

        $result = $query->get();

        // Attach totals as collection properties via a Collection macro workaround
        $totalrecs = $result->first()->totalrecs ?? 0;

        $result['totalrecs']         = $totalrecs;
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
            if (!Item::where(['id' => $post['id']])->update($updateArray)) {
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
        $item = DB::table('items as i')
            ->leftJoin('categories as c',    'c.id', '=', 'i.category_id')
            ->leftJoin('sub_categories as s', 's.id', '=', 'i.subcategory_id')
            ->where('i.id', $id)
            ->select(
                'i.*',
                'c.title as category_title',
                's.title as subcategory_title'
            )
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
                'category_id'   => '1e9f9787-5c92-47d8-ab19-d466329eae88',
                'subcategory_id'   => 'c256c64d-70a0-4c55-9b39-5645b6d580ae',
                'type'        => $post['type']        ?? null,
                'description' => $post['description'] ?? null,
                'postedby'    => Auth::id(),
                'orgid'       => $post['orgid']        ?? null,
            ];

            // ════════════════════════════════════════
            // UPDATE
            // ════════════════════════════════════════
            if (!empty($post['id'])) {

                $itemId = $post['id'];

                $dataArray['updatedby']  = Auth::id();
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
                            'postedby'   => Auth::id(),
                            'updatedby'  => Auth::id(),
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
                            'postedby'      => Auth::id(),
                            'updatedby'     => Auth::id(),
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
                                'postedby'   => Auth::id(),
                                'updatedby'  => Auth::id(),
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
                            updatedby  = " . Auth::id() . "
                        WHERE id IN ($idsList)
                    ");
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
                            'postedby'   => Auth::id(),
                            'updatedby'  => Auth::id(),
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
                $dataArray['updatedby']  = Auth::id();

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
                            'postedby'   => Auth::id(),
                            'updatedby'  => Auth::id(),
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
                            'postedby'      => Auth::id(),
                            'updatedby'     => Auth::id(),
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
                            'postedby'   => Auth::id(),
                            'updatedby'  => Auth::id(),
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
                            'postedby'   => Auth::id(),
                            'updatedby'  => Auth::id(),
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
