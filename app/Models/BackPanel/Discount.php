<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
            DB::beginTransaction();

            // Auto-generate title since field is hidden in form
            $title = $post['title'] ?? null;
            if (empty($title)) {
                $type = $post['type'] ?? 'discount';
                $value = match ($type) {
                    'percentage' => ($post['percentage'] ?? '') . '% off',
                    'fixed'      => 'Rs ' . ($post['value'] ?? '') . ' off',
                    'coupon'     => 'Coupon: ' . ($post['coupon_code'] ?? ''),
                    default      => ucfirst($type) . ' discount',
                };
                $title = ucfirst($type) . ' — ' . $value;
            }

            $dataArray = [
                'title'                => $title,
                'type'                 => $post['type'],
                'percentage'           => $post['percentage']           ?? null,
                'coupon_code'          => $post['coupon_code']          ?? null,
                'value'                => $post['value']                ?? null,
                'applies_to'           => $post['applies_to'],
                'item_id'              => $post['item_id']              ?? null,
                'variation_id'         => $post['variation_id']         ?? null,
                'min_requirement'      => $post['min_requirement']      ?? 'none',
                'min_value'            => $post['min_value']            ?? null,
                'usage_limit_type'     => $post['usage_limit_type']     ?? 'once',
                'usage_limit'          => $post['usage_limit']          ?? null,
                'usage_limit_per_user' => $post['usage_limit_per_user'] ?? null,
                'discount_type'        => $post['discount_type']        ?? null,
                'starts_at'            => $post['starts_at'],
                'ends_at'              => $post['ends_at'],
                'orgid'                => $post['orgid']                ?? null,
                'postedby'             => $post['userid'],
            ];

            // ── UPDATE ────────────────────────────────────────────────────
            if (!empty($post['id'])) {
                $dataArray['updatedby']  = $post['userid'];
                $dataArray['updated_at'] = Carbon::now();

                DB::table('discounts')
                    ->where('id', $post['id'])
                    ->update($dataArray);

            // ── INSERT ────────────────────────────────────────────────────
            } else {
                $dataArray['id']         = (string) Str::uuid();
                $dataArray['status']     = 'Y';
                $dataArray['used_count'] = 0;
                $dataArray['updatedby']  = $post['userid'];
                $dataArray['created_at'] = Carbon::now();
                $dataArray['updated_at'] = Carbon::now();

                $inserted = DB::table('discounts')->insert($dataArray);

                if (!$inserted) {
                    throw new \Exception("Couldn't save discount.");
                }
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
            if (!Discount::where(['id' => $post['id']])->update($updateArray)) {
                throw new Exception("Couldn't Delete Data. Please try again", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}