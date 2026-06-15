<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Coupon extends Model
{
    public static function getList($post)
    {
        try {
            $query = DB::table('coupons')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->whereNull('deleted_at')
                ->select(
                    'id',
                    // 'title',
                    'coupon_code',
                    'discount_type',
                    'percentage',
                    'value'
                    // 'applies_to',
                    // 'item_id',
                    // 'variation_id',
                    // 'min_requirement',
                    // 'min_value',
                    // 'usage_limit_type',
                    // 'usage_limit',
                    // 'usage_limit_per_user',
                    // 'used_count',
                    // 'starts_at',
                    // 'ends_at',
                    // 'status',
                    // 'created_at'
                )
                ->orderBy('created_at', 'desc');

            // Optional filters
            if (!empty($post['coupon_code'])) {
                $query->where('coupon_code', 'ilike', '%' . $post['coupon_code'] . '%');
            }

            if (!empty($post['discount_type'])) {
                $query->where('discount_type', $post['discount_type']);
            }

            if (!empty($post['applies_to'])) {
                $query->where('applies_to', $post['applies_to']);
            }

            return $query->get();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}