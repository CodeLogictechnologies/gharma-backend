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
        'title',
        'slug',
        'description',
        'type',
        'status',
        'orgid',
        'postedby',
        'updatedby',
        'product_code',
        'company_product_code',
        'hs_code',
        'is_wholesale',
        'vat_status',
        'vat_percent',
        'excise_status',
        'excise_type',
        'excise_percentage',
        'excise_value',
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

    public static function resolveVatPercent(array $post): float
    {
        if (empty($post['vat_status'])) {
            return 0;
        }

        $requested     = (float) ($post['vat_percent'] ?? config('vat.default'));
        $taxableRates  = array_map('floatval', config('vat.taxable'));

        return in_array($requested, $taxableRates, true)
            ? $requested
            : (float) config('vat.default');
    }

    public static function list(array $post)
    {
        $orgid   = $post['orgid'] ?? '';
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
            ->leftJoin('categories as s',            's.id',       '=', 'sci.subcategoryid')
            ->selectRaw("
                items.id,
                items.title,
                items.description,
                items.status,
                items.type,
                items.brand_id,
                items.product_code,
                items.company_product_code,
                items.created_at
            ")
            ->where('items.status', 'Y')
            ->where('items.orgid', $orgid)
            ->distinct();
        if ($search1 !== '') {
            $query->whereRaw("LOWER(items.title) LIKE ?", ["%{$search1}%"]);
        }
        if ($search2 !== '') {
            $query->whereRaw("LOWER(c.title) LIKE ?", ["%{$search2}%"]);
        }
        if ($search3 !== '') {
            $query->whereRaw("LOWER(s.title) LIKE ?", ["%{$search3}%"]);
        }

        $totalrecs     = self::from('items')->where('status', 'Y')->where('orgid', $orgid)->count();
        $filteredCount = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query->getQuery())
            ->count();

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
            ->join('categories as s', 's.id', '=', 'sci.subcategoryid')
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

    public static function saveData($post)
    {
        try {
            DB::beginTransaction();

            $dataArray = [
                'title'                => $post['title'],
                'brand_id'             => $post['brand'],
                'threshold'            => '1',
                'type'                 => $post['type']                 ?? null,
                'description'          => $post['description']          ?? null,
                'product_code'         => $post['product_code']         ?? null,
                'company_product_code' => $post['company_product_code'] ?? null,
                'hs_code'              => !empty($post['hs_code']) ? $post['hs_code'] : null,
                'is_wholesale'         => !empty($post['is_wholesale']) ? 'Y' : 'N',
                'vat_status'           => !empty($post['vat_status']) ? 'Y' : 'N',
                'vat_percent'          => self::resolveVatPercent($post),
                'excise_status'        => !empty($post['excise_status']) ? 'Y' : 'N',
                'excise_type'          => !empty($post['excise_status']) ? ($post['excise_type'] ?? null) : null,
                'excise_percentage'    => (!empty($post['excise_status']) && ($post['excise_type'] ?? '') === 'percentage')
                    ? ($post['excise_percentage'] ?? null) : null,
                'excise_value'         => (!empty($post['excise_status']) && ($post['excise_type'] ?? '') === 'fixed')
                    ? ($post['excise_value'] ?? null) : null,
                'postedby'             => $post['userid'],
                'orgid'                => $post['orgid']                ?? null,
            ];

            if (!empty($post['id'])) {

                $itemId = $post['id'];

                $dataArray['updatedby']  = $post['userid'];
                $dataArray['updated_at'] = Carbon::now();

                DB::table('items')->where('id', $itemId)->update($dataArray);

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

                // ── Sub Sub Category (delete old links, then re-insert current selection) ──
                DB::table('sub_sub_category_items')->where('itemid', $itemId)->delete();

                if (!empty($post['sub_sub_categories'])) {
                    $subSubCategoryRows = [];
                    foreach ($post['sub_sub_categories'] as $subSubCategoryId) {
                        $subSubCategoryRows[] = [
                            'id'               => (string) Str::uuid(),
                            'orgid'            => $post['orgid'] ?? null,
                            'subsubcategoryid' => $subSubCategoryId,
                            'itemid'           => $itemId,
                            'status'           => 'Y',
                            'postedby'         => $post['userid'],
                            'updatedby'        => $post['userid'],
                            'created_at'       => Carbon::now(),
                            'updated_at'       => Carbon::now(),
                        ];
                    }
                    DB::table('sub_sub_category_items')->insert($subSubCategoryRows);
                }

                if (!empty($post['variations'])) {
                    $ids                     = [];
                    $attributeCases          = [];
                    $varAttrIdCases          = [];
                    $unitCases               = [];
                    $unitIdCases             = [];
                    $valueCases              = [];
                    $productCodeCases        = [];
                    $companyProductCodeCases = [];
                    $hsCodeCases             = [];
                    $thresholdCases          = [];
                    $discountTypeCases       = [];
                    $discountCases           = [];
                    $discountAmountCases     = [];
                    $priceCases              = [];
                    $existingVariationPrices = [];

                    $attrNameMap = DB::table('variation_attributes')
                        ->where('orgid', $post['orgid'] ?? null)
                        ->where('status', 'Y')
                        ->pluck('name', 'id')
                        ->toArray();

                    $unitNameMap = DB::table('units')
                        ->where('orgid', $post['orgid'] ?? null)
                        ->where('status', 'Y')
                        ->pluck('unit_name', 'id')
                        ->toArray();

                    foreach ($post['variations'] as $variation) {
                        if (empty($variation['value'])) continue;

                        $status = (($variation['status'] ?? 'active') === 'active') ? 'Y' : 'N';

                        if (!empty($variation['variationid'])) {
                            $id             = $variation['variationid'];
                            $ids[]          = $id;
                            $attrId         = $variation['attribute_id'] ?? null;
                            $attrName       = !empty($attrId) ? ($attrNameMap[$attrId] ?? '') : '';
                            $unitId         = $variation['unit_id'] ?? null;
                            $unitName       = !empty($unitId) ? ($unitNameMap[$unitId] ?? '') : '';
                            $threshold      = is_numeric($variation['threshold'] ?? null) ? (int) $variation['threshold'] : 0;
                            $discountType   = in_array($variation['discount_type'] ?? null, ['percentage', 'fixed'], true) ? $variation['discount_type'] : null;
                            $discount       = ($discountType === 'percentage' && is_numeric($variation['discount_percentage'] ?? null))
                                ? (float) $variation['discount_percentage'] : null;
                            $discountAmount = ($discountType === 'fixed' && is_numeric($variation['discount_amount'] ?? null))
                                ? (float) $variation['discount_amount'] : null;
                            $price          = is_numeric($variation['price'] ?? null) ? (float) $variation['price'] : 0;

                            $attributeCases[]          = "WHEN '$id' THEN '" . addslashes($attrName) . "'";
                            $varAttrIdCases[]          = !empty($attrId) ? "WHEN '$id' THEN '$attrId'::uuid" : "WHEN '$id' THEN NULL::uuid";
                            $unitCases[]               = "WHEN '$id' THEN '" . addslashes($unitName) . "'";
                            $unitIdCases[]             = !empty($unitId) ? "WHEN '$id' THEN '$unitId'::uuid" : "WHEN '$id' THEN NULL::uuid";
                            $valueCases[]              = "WHEN '$id' THEN '" . addslashes($variation['value'])               . "'";
                            $productCodeCases[]        = "WHEN '$id' THEN '" . addslashes($variation['product_code']         ?? '') . "'";
                            $companyProductCodeCases[] = "WHEN '$id' THEN '" . addslashes($variation['company_product_code'] ?? '') . "'";
                            $hsCodeCases[]             = "WHEN '$id' THEN " . (!empty($variation['hs_code']) ? "'" . addslashes($variation['hs_code']) . "'" : 'NULL');
                            $thresholdCases[]          = "WHEN '$id' THEN $threshold";
                            $discountTypeCases[]       = "WHEN '$id' THEN " . ($discountType !== null ? "'$discountType'" : 'NULL::varchar');
                            $discountCases[]           = "WHEN '$id' THEN " . ($discount !== null ? $discount : 'NULL::numeric(5,2)');
                            $discountAmountCases[]     = "WHEN '$id' THEN " . ($discountAmount !== null ? $discountAmount : 'NULL::numeric');
                            $priceCases[]              = "WHEN '$id' THEN $price";

                            $existingVariationPrices[$id] = $price;
                        } else {
                            $attrId         = $variation['attribute_id'] ?? null;
                            $attrName       = !empty($attrId) ? ($attrNameMap[$attrId] ?? '') : '';
                            $unitId         = $variation['unit_id'] ?? null;
                            $unitName       = !empty($unitId) ? ($unitNameMap[$unitId] ?? '') : '';
                            $threshold      = is_numeric($variation['threshold'] ?? null) ? (int) $variation['threshold'] : 0;
                            $discountType   = in_array($variation['discount_type'] ?? null, ['percentage', 'fixed'], true) ? $variation['discount_type'] : null;
                            $discount       = ($discountType === 'percentage' && is_numeric($variation['discount_percentage'] ?? null))
                                ? (float) $variation['discount_percentage'] : null;
                            $discountAmount = ($discountType === 'fixed' && is_numeric($variation['discount_amount'] ?? null))
                                ? (float) $variation['discount_amount'] : null;
                            $price          = is_numeric($variation['price'] ?? null) ? (float) $variation['price'] : 0;
                            $varId          = (string) Str::uuid();

                            DB::table('itemvariations')->insert([
                                'id'                     => $varId,
                                'item_id'                => $itemId,
                                'attribute'              => $attrName,
                                'variation_attribute_id' => $attrId ?: null,
                                'unit'                   => $unitName,
                                'unit_id'                => $unitId ?: null,
                                'value'                  => $variation['value'],
                                'threshold'              => $threshold,
                                'discount_type'          => $discountType,
                                'discount'               => $discount,
                                'discount_amount'        => $discountAmount,
                                'price'                  => $price,
                                'product_code'           => $variation['product_code']         ?? null,
                                'company_product_code'   => $variation['company_product_code'] ?? null,
                                'hs_code'                => !empty($variation['hs_code']) ? $variation['hs_code'] : null,
                                'status'                 => $status,
                                'orgid'                  => $post['orgid']                     ?? null,
                                'created_at'             => Carbon::now(),
                                'updated_at'             => Carbon::now(),
                                'postedby'               => $post['userid'],
                                'updatedby'              => $post['userid'],
                            ]);

                            if ($price > 0) {

                                if (!empty($post['price'])) {
                                    $price = (float) $post['price'];
                                }

                                // Discount
                                $discount = 0;
                                if (!empty($post['discount_type'])) {

                                    if ($post['discount_type'] == 'percentage') {
                                        $discount = ($price * (float)$post['discount_percentage']) / 100;
                                    } else {
                                        $discount = (float)($post['discount_amount'] ?? 0);
                                    }
                                }

                                // Excise
                                $excise = 0;
                                if (!empty($post['excise_status'])) {
                                    if ($post['excise_status'] == 1) {
                                        if ($post['excise_type'] == 'percentage') {
                                            $excise = ($price * (float)$post['excise_percentage']) / 100;
                                        } else {
                                            $excise = (float)($post['excise_value'] ?? 0);
                                        }
                                    }
                                }

                                // Before Discount
                                $subTotalBefore = $price + $excise;
                                $vatBefore = 0;

                                if (!empty($post['vat_status'])) {
                                    if ($post['vat_status'] == 1) {
                                        $vatBefore = ($subTotalBefore * (float)$post['vat_percent']) / 100;
                                    }
                                }

                                $priceBeforeDiscount = $subTotalBefore + $vatBefore;

                                // After Discount
                                $subTotalAfter = ($price - $discount) + $excise;
                                $vatAfter = 0;

                                if (!empty($post['vat_status'])) {
                                    if ($post['vat_status'] == 1) {
                                        $vatAfter = ($subTotalAfter * (float)$post['vat_percent']) / 100;
                                    }
                                }

                                $priceAfterDiscount = $subTotalAfter + $vatAfter;


                                $rpExists = DB::table('retailer_prices')
                                    ->where('itemid', $itemId)
                                    ->where('variation_id', $varId)
                                    ->where('status', 'Y')
                                    ->exists();

                                if (!$rpExists) {
                                    DB::table('retailer_prices')->insert([
                                        'id'           => (string) Str::uuid(),
                                        'orgid'        => $post['orgid'] ?? null,
                                        'itemid'       => $itemId,
                                        'variation_id' => $varId,
                                        'price'        => $price,
                                        'price_before_discount' => round($priceBeforeDiscount, 2),
                                        'price_after_discount'  => round($priceAfterDiscount, 2),
                                        'status'       => 'Y',
                                        'postedby'     => $post['userid'],
                                        'updatedby'    => $post['userid'],
                                        'created_at'   => Carbon::now(),
                                        'updated_at'   => Carbon::now(),
                                    ]);
                                }
                            }
                        }
                    }

                    if (!empty($ids)) {
                        $rpVatStatus        = !empty($post['vat_status']) ? 'Y' : 'N';
                        $rpVatPercent        = !empty($post['vat_status']) ? ($post['vat_percent'] ?? null) : null;
                        $rpExciseStatus      = !empty($post['excise_status']) ? 'Y' : 'N';
                        $rpExciseType        = !empty($post['excise_status']) ? ($post['excise_type'] ?? null) : null;
                        $rpExcisePercentage  = (!empty($post['excise_status']) && ($post['excise_type'] ?? '') === 'percentage')
                            ? ($post['excise_percentage'] ?? null) : null;
                        $rpExciseValue       = (!empty($post['excise_status']) && ($post['excise_type'] ?? '') === 'fixed')
                            ? ($post['excise_value'] ?? null) : null;

                        $idsList = "'" . implode("','", $ids) . "'";
                        DB::statement("
                        UPDATE itemvariations SET
                            attribute              = CASE id " . implode(' ', $attributeCases)         . " END,
                            variation_attribute_id = CASE id " . implode(' ', $varAttrIdCases)         . " END,
                            unit                    = CASE id " . implode(' ', $unitCases)              . " END,
                            unit_id                 = CASE id " . implode(' ', $unitIdCases)            . " END,
                            value                  = CASE id " . implode(' ', $valueCases)              . " END,
                            product_code           = CASE id " . implode(' ', $productCodeCases)        . " END,
                            company_product_code   = CASE id " . implode(' ', $companyProductCodeCases) . " END,
                            hs_code                = CASE id " . implode(' ', $hsCodeCases)             . " END,
                            threshold              = CASE id " . implode(' ', $thresholdCases)          . " END,
                            discount_type          = CASE id " . implode(' ', $discountTypeCases)       . " END,
                            discount               = CASE id " . implode(' ', $discountCases)           . " END,
                            discount_amount        = CASE id " . implode(' ', $discountAmountCases)     . " END,
                            price                  = CASE id " . implode(' ', $priceCases)              . " END,
                            updated_at             = NOW(),
                            updatedby              = ?
                        WHERE id IN ($idsList)
                    ", [$post['userid']]);

                        foreach ($existingVariationPrices as $varId => $price) {
                            if ($price <= 0) continue;

                            // Per-variation discount (falls back to item-level discount inputs if variation didn't send its own)
                            $variation = collect($post['variations'])->firstWhere('variationid', $varId);

                            $varDiscountType = $variation['discount_type'] ?? null;
                            $varDiscount = 0;
                            if ($varDiscountType === 'percentage') {
                                $varDiscount = ($price * (float)($variation['discount_percentage'] ?? 0)) / 100;
                            } elseif ($varDiscountType === 'fixed') {
                                $varDiscount = (float)($variation['discount_amount'] ?? 0);
                            }

                            // Excise (item-level status/type, applied to this variation's price)
                            $varExcise = 0;
                            if ($rpExciseStatus === 'Y') {
                                if ($rpExciseType === 'percentage') {
                                    $varExcise = ($price * (float)$rpExcisePercentage) / 100;
                                } else {
                                    $varExcise = (float)$rpExciseValue;
                                }
                            }

                            // Before discount
                            $varSubTotalBefore = $price + $varExcise;
                            $varVatBefore = ($rpVatStatus === 'Y') ? ($varSubTotalBefore * (float)$rpVatPercent) / 100 : 0;
                            $varPriceBeforeDiscount = $varSubTotalBefore + $varVatBefore;

                            // After discount
                            $varSubTotalAfter = ($price - $varDiscount) + $varExcise;
                            $varVatAfter = ($rpVatStatus === 'Y') ? ($varSubTotalAfter * (float)$rpVatPercent) / 100 : 0;
                            $varPriceAfterDiscount = $varSubTotalAfter + $varVatAfter;

                            $existing = DB::table('retailer_prices')
                                ->where('itemid', $itemId)
                                ->where('variation_id', $varId)
                                ->where('status', 'Y')
                                ->first();

                            $payload = [
                                'price'                 => $price,
                                'price_before_discount' => round($varPriceBeforeDiscount, 2),
                                'price_after_discount'  => round($varPriceAfterDiscount, 2),
                                'updatedby'             => $post['userid'],
                                'updated_at'            => Carbon::now(),
                            ];

                            if ($existing) {
                                DB::table('retailer_prices')->where('id', $existing->id)->update($payload);
                            } else {
                                $payload['id']           = (string) Str::uuid();
                                $payload['orgid']        = $post['orgid'] ?? null;
                                $payload['itemid']       = $itemId;
                                $payload['variation_id'] = $varId;
                                $payload['status']       = 'Y';
                                $payload['postedby']     = $post['userid'];
                                $payload['created_at']   = Carbon::now();
                                DB::table('retailer_prices')->insert($payload);
                            }
                        }
                    }
                }

                $keptImageIds = array_filter((array) ($post['kept_image_ids'] ?? []));

                $existingImageIds = DB::table('item_images')
                    ->where('item_id', $itemId)
                    ->pluck('id')
                    ->toArray();

                $toDelete = array_diff($existingImageIds, $keptImageIds);

                if (!empty($toDelete)) {
                    $filenames = DB::table('item_images')
                        ->whereIn('id', $toDelete)
                        ->pluck('image');

                    foreach ($filenames as $filename) {
                        Storage::disk('public')->delete('items/' . $filename);
                    }

                    DB::table('item_images')->whereIn('id', $toDelete)->delete();
                }

                if (!empty($post['image_order'])) {
                    foreach ($post['image_order'] as $order => $imageId) {
                        DB::table('item_images')
                            ->where('id', $imageId)
                            ->where('item_id', $itemId)
                            ->update(['order_number' => (int) $order + 1]);
                    }
                }

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

                // ── Sub Sub Category (new item — no existing rows to delete) ──
                if (!empty($post['sub_sub_categories'])) {
                    $subSubCategoryRows = [];
                    foreach ($post['sub_sub_categories'] as $subSubCategoryId) {
                        $subSubCategoryRows[] = [
                            'id'               => (string) Str::uuid(),
                            'orgid'            => $post['orgid'] ?? null,
                            'subsubcategoryid' => $subSubCategoryId,
                            'itemid'           => $itemId,
                            'status'           => 'Y',
                            'postedby'         => $post['userid'],
                            'updatedby'        => $post['userid'],
                            'created_at'       => Carbon::now(),
                            'updated_at'       => Carbon::now(),
                        ];
                    }
                    DB::table('sub_sub_category_items')->insert($subSubCategoryRows);
                }

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

                if (!empty($post['variations'])) {
                    $variationRows = [];
                    $priceRows     = [];

                    $attrNameMap = DB::table('variation_attributes')
                        ->where('orgid', $post['orgid'] ?? null)
                        ->where('status', 'Y')
                        ->pluck('name', 'id')
                        ->toArray();

                    $unitNameMap = DB::table('units')
                        ->where('orgid', $post['orgid'] ?? null)
                        ->where('status', 'Y')
                        ->pluck('unit_name', 'id')
                        ->toArray();

                    foreach ($post['variations'] as $variation) {
                        if (empty($variation['value'])) continue;

                        $status         = (($variation['status'] ?? 'active') === 'active') ? 'Y' : 'N';
                        $attrId         = $variation['attribute_id'] ?? null;
                        $attrName       = !empty($attrId) ? ($attrNameMap[$attrId] ?? '') : '';
                        $unitId         = $variation['unit_id'] ?? null;
                        $unitName       = !empty($unitId) ? ($unitNameMap[$unitId] ?? '') : '';
                        $varId          = (string) Str::uuid();
                        $price          = is_numeric($variation['price'] ?? null) ? (float) $variation['price'] : 0;
                        $discountType   = in_array($variation['discount_type'] ?? null, ['percentage', 'fixed'], true) ? $variation['discount_type'] : null;
                        $discount       = ($discountType === 'percentage' && is_numeric($variation['discount_percentage'] ?? null))
                            ? (float) $variation['discount_percentage'] : null;
                        $discountAmount = ($discountType === 'fixed' && is_numeric($variation['discount_amount'] ?? null))
                            ? (float) $variation['discount_amount'] : null;

                        $variationRows[] = [
                            'id'                     => $varId,
                            'item_id'                => $itemId,
                            'attribute'              => $attrName,
                            'variation_attribute_id' => $attrId ?: null,
                            'unit'                   => $unitName,
                            'unit_id'                => $unitId ?: null,
                            'value'                  => $variation['value'],
                            'threshold'              => $variation['threshold']          ?? 0,
                            'discount_type'          => $discountType,
                            'discount'               => $discount,
                            'discount_amount'        => $discountAmount,
                            'price'                  => $price,
                            'product_code'           => $variation['product_code']         ?? null,
                            'company_product_code'   => $variation['company_product_code'] ?? null,
                            'hs_code'                => !empty($variation['hs_code']) ? $variation['hs_code'] : null,
                            'status'                 => $status,
                            'orgid'                  => $post['orgid']                   ?? null,
                            'created_at'             => Carbon::now(),
                            'updated_at'             => Carbon::now(),
                            'postedby'               => $post['userid'],
                            'updatedby'              => $post['userid'],
                        ];

                        if ($price > 0) {
                            $priceRows[] = [
                                'id'           => (string) Str::uuid(),
                                'orgid'        => $post['orgid'] ?? null,
                                'itemid'       => $itemId,
                                'variation_id' => $varId,
                                'price'        => $price,
                                'status'       => 'Y',
                                'postedby'     => $post['userid'],
                                'updatedby'    => $post['userid'],
                                'created_at'   => Carbon::now(),
                                'updated_at'   => Carbon::now(),
                            ];
                        }
                    }

                    if (!empty($variationRows)) {
                        $variationInserted = DB::table('itemvariations')->insert($variationRows);
                        if (!$variationInserted) {
                            throw new Exception("Couldn't save item variations.");
                        }
                    }

                    if (!empty($priceRows)) {
                        DB::table('retailer_prices')->insert($priceRows);
                    }
                }
            }

            DB::commit();
            return $itemId;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function deleteItem($post)
    {
        try {
            $id = $post['id'] ?? null;
            if (empty($id)) {
                throw new Exception('Item ID is required.');
            }

            DB::beginTransaction();

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
            ->leftJoin('categories as s',       's.id',       '=', 'sci.subcategoryid')
            ->leftJoin('brands as b',               'b.id',       '=', 'i.brand_id')
            ->where('i.id', $id)
            ->select('i.*', 'c.title as category_title', 's.title as subcategory_title', 'b.name as brand')
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

    public static function getItem($post, $inStockOnly = false)
    {
        try {
            $query = DB::table('items')
                ->select(
                    'id as itemid',
                    'title as itemname',
                    'product_code',
                    'vat_status',
                    'vat_percent',
                    'excise_status',
                    'excise_type',
                    'excise_percentage',
                    'excise_value',
                    'is_wholesale'
                )
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y');

            if (!empty($post['wholesale_only'])) {
                $query->where('is_wholesale', 'Y');
            }

            if ($inStockOnly) {
                // Items without any variation have no stock/remaining concept in this system
                // (see Inventory > Stock, which is variation-based), so they're excluded here.
                $inStockVariationIds = Itemvariation::inStockVariationIds($post['orgid']);
                $eligibleItemIds = DB::table('itemvariations')
                    ->whereIn('id', $inStockVariationIds)
                    ->pluck('item_id')
                    ->unique();
                $query->whereIn('id', $eligibleItemIds);
            }

            return $query->get();
        } catch (Exception $e) {
            throw $e;
        }
    }
}