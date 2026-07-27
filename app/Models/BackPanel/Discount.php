<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BsdateController;

class Discount extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public static function saveData($post)
    {
        try {
            // dd($post);
            DB::beginTransaction();
            $bsdate = new BsdateController;
            $start_date_ad = $bsdate->nep_to_eng($post['starts_at']);
            $end_date_ad = $bsdate->nep_to_eng($post['ends_at']);
            $appliesToId = null;

            switch ($post['applies_to']) {
                case 'item':
                    if (empty($post['item_ids'])) {
                        throw new Exception('Please select at least one item.');
                    }
                    break;

                case 'category':
                    $appliesToId = $post['category_target_id'];
                    break;

                case 'sub_category':
                    $appliesToId = $post['sub_category_target_id'];
                    break;

                case 'sub_sub_category':
                    $appliesToId = $post['sub_sub_category_target_id'];
                    break;

                case 'brand':
                    $appliesToId = $post['brand_target_id'];
                    break;
            }

            if ($post['applies_to'] !== 'item' && empty($appliesToId)) {
                throw new Exception('Please select Applies To.');
            }

            $discountMasterId = (string) Str::uuid();

            $masterData = [
                'id'                    => $discountMasterId,
                'applies_to'            => $post['applies_to'],
                'applies_to_id'         => $appliesToId,
                'min_requirement'       => $post['min_requirement'] ?? 'none',
                'min_value'             => $post['min_value'] ?? null,
                'max_value'             => $post['max_value'] ?? null,
                'usage_limit_type'      => $post['usage_limit_type'] ?? 'once',
                'usage_limit'           => $post['usage_limit'] ?? null,
                'usage_limit_per_user'  => $post['usage_limit_per_user'] ?? null,
                'start_date_bs'            => $post['starts_at'],
                'end_date_bs'              => $post['ends_at'],
                'start_date_ad'            => $start_date_ad,
                'end_date_ad'              => $end_date_ad,
                'orgid'                 => $post['orgid'],
                'status'                => 'Y',
                'used_count'            => 0,
                'created_at'            => now(),
                'updated_at'            => now(),
            ];

            DB::table('discount_masters')->insert($masterData);

            $details = [];

            if (!empty($post['item_ids'])) {

                $itemIds = array_unique($post['item_ids']);

                foreach ($itemIds as $variationId) {

                    $variation = DB::table('itemvariations as iv')
                        ->join('items as i', 'i.id', '=', 'iv.item_id')
                        ->where('iv.id', $variationId)
                        ->where('iv.orgid', $post['orgid'])
                        ->select(
                            'iv.id',
                            'iv.price',
                            'iv.discount_type',
                            'iv.discount_amount',
                            'i.excise_status',
                            'i.excise_type',
                            'i.excise_percentage',
                            'i.excise_value',
                            'i.vat_status',
                            'i.vat_percent'
                        )
                        ->first();

                    if (!$variation) {
                        throw new Exception('Invalid Item Variation.');
                    }

                    $basePrice = (float) $variation->price;

                    $variationDiscount = 0;

                    if (!empty($variation->discount_type)) {

                        if ($variation->discount_type == 'percentage') {

                            $variationDiscount = round(
                                $basePrice * ((float)$variation->discount_amount / 100),
                                2
                            );
                        } elseif ($variation->discount_type == 'fixed') {

                            $variationDiscount = round(
                                (float)$variation->discount_amount,
                                2
                            );
                        }
                    }

                    $priceAfterVariationDiscount = max(
                        0,
                        $basePrice - $variationDiscount
                    );

                    // Excise
                    $exciseAmount = 0;

                    if ($variation->excise_status == 'Y') {

                        if ($variation->excise_type == 'percentage') {

                            $exciseAmount = round(
                                $priceAfterVariationDiscount *
                                    ((float)$variation->excise_percentage / 100),
                                2
                            );
                        } elseif ($variation->excise_type == 'fixed') {

                            $exciseAmount = round(
                                (float)$variation->excise_value,
                                2
                            );
                        }
                    }

                    $priceAfterExcise = round(
                        $priceAfterVariationDiscount + $exciseAmount,
                        2
                    );

                    // VAT
                    $vatAmount = 0;

                    if ($variation->vat_status == 'Y') {

                        $vatAmount = round(
                            $priceAfterExcise *
                                ((float)$variation->vat_percent / 100),
                            2
                        );
                    }

                    $originalAmount = round(
                        $priceAfterExcise + $vatAmount,
                        2
                    );

                    // Discount
                    if ($post['type'] == 'percentage') {

                        $discountType = 'percentage';
                        $discountValue = (float) $post['percentage'];

                        $discountAmount = round(
                            $originalAmount * ($discountValue / 100),
                            2
                        );
                    } else {

                        $discountType = 'amount';
                        $discountValue = (float) $post['value'];

                        $discountAmount = round($discountValue, 2);
                    }

                    $totalAmount = max(
                        0,
                        round($originalAmount - $discountAmount, 2)
                    );

                    $details[] = [
                        'id'                 => (string) Str::uuid(),
                        'discount_master_id' => $discountMasterId,
                        'orgid'              => $post['orgid'],
                        'variation_id'       => $variation->id,
                        'discount_type'      => $discountType,
                        'discount_value'     => $discountValue,
                        'original_amount'    => $originalAmount,
                        'discount_amount'    => $discountAmount,
                        'total_amount'       => $totalAmount,
                        'status'             => 'Y',
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];
                }
            }

            // Insert only once
            if (!empty($details)) {
                DB::table('discount_details')->insert($details);
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {

            DB::rollBack();
            throw $e;
        }
    }

    public static function deleteDate($post)
    {
        try {
            $updateArray = [
                'status'     => 'N',
                'updated_at' => Carbon::now(),
            ];
            if (!Discount::where('id', $post['id'])
                ->where('type', '!=', 'coupon')
                ->where('orgid', $post['orgid'])
                ->update($updateArray)) {
                throw new Exception("Couldn't delete discount. Please try again.", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}