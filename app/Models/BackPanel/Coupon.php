<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Exception;

class Coupon extends Model
{
    protected $table = 'coupons';

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

    public static function saveData(array $post): bool
    {
        try {
            DB::beginTransaction();

            $title = 'Coupon: ' . strtoupper($post['coupon_code'] ?? '');

            $dataArray = [
                'title'                => $title,
                'coupon_code'          => strtoupper($post['coupon_code']),
                'discount_type'        => $post['discount_type']        ?? 'percentage',
                'percentage'           => ($post['discount_type'] ?? '') === 'percentage' ? ($post['percentage'] ?? null) : null,
                'value'                => ($post['discount_type'] ?? '') === 'fixed'      ? ($post['value']      ?? null) : null,
                'applies_to'           => $post['applies_to'],
                'item_id'              => $post['item_id']              ?? null,
                'variation_id'         => $post['variation_id']         ?? null,
                'min_requirement'      => $post['min_requirement']      ?? 'none',
                'min_value'            => $post['min_value']            ?? null,
                'usage_limit_type'     => $post['usage_limit_type']     ?? 'once',
                'usage_limit'          => $post['usage_limit']          ?? null,
                'usage_limit_per_user' => $post['usage_limit_per_user'] ?? null,
                'starts_at'            => $post['starts_at'],
                'ends_at'              => $post['ends_at'],
                'orgid'                => $post['orgid']                ?? null,
                'postedby'             => $post['userid'],
            ];

            if (!empty($post['id'])) {
                $dataArray['updatedby']  = $post['userid'];
                $dataArray['updated_at'] = Carbon::now();

                DB::table('coupons')->where('id', $post['id'])->update($dataArray);
            } else {
                $dataArray['id']         = (string) Str::uuid();
                $dataArray['status']     = 'Y';
                $dataArray['used_count'] = 0;
                $dataArray['updatedby']  = $post['userid'];
                $dataArray['created_at'] = Carbon::now();
                $dataArray['updated_at'] = Carbon::now();

                if (!DB::table('coupons')->insert($dataArray)) {
                    throw new Exception("Couldn't save coupon.");
                }
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function deleteData(array $post): bool
    {
        try {
            $updated = DB::table('coupons')
                ->where('id', $post['id'])
                ->update([
                    'status'     => 'N',
                    'updated_at' => Carbon::now(),
                ]);

            if (!$updated) {
                throw new Exception("Couldn't delete coupon. Please try again.");
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}