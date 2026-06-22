<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Exception;

class RecentlyViewed extends Model
{
    protected $table = 'recently_viewed';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'variation_id',
        'org_id',
        'user_id',
    ];

    /**
     * Save (or touch) a recently-viewed record.
     * Guests (no userid) are silently skipped — nothing to save against.
     *
     * id / variation_id / org_id / user_id are all uuid (char(36)) columns
     * here, matching itemvariations.id, orgid, and users.id elsewhere in
     * this app. updateOrInsert() does not auto-generate a uuid primary key
     * the way Eloquent::create() would, so we generate one ourselves only
     * when inserting a brand-new row (the WHEN id IS NULL branch below).
     */
    public static function saveData(array $post): bool
    {
        if (empty($post['userid']) || empty($post['variationid'])) {
            return false;
        }

        try {
            $existing = DB::table('recently_viewed')
                ->where('user_id', $post['userid'])
                ->where('org_id', $post['orgid'] ?? null)
                ->where('variation_id', $post['variationid'])
                ->first();

            if ($existing) {
                DB::table('recently_viewed')
                    ->where('id', $existing->id)
                    ->update([
                        'updated_at' => Carbon::now(),
                    ]);
            } else {
                DB::table('recently_viewed')->insert([
                    'id'           => (string) Str::uuid(),
                    'user_id'      => $post['userid'],
                    'org_id'       => $post['orgid'] ?? null,
                    'variation_id' => $post['variationid'],
                    'created_at'   => Carbon::now(),
                    'updated_at'   => Carbon::now(),
                ]);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Fetch variationid / title / price for a single variation —
     * same join shape as getList() below, just without pagination
     * or the recently_viewed join. Returns null if the variation
     * has no matching itemvariations/retailer_prices row.
     */
    public static function getVariationDetail(string $variationId): ?object
    {
        return DB::table('itemvariations as iv')
            ->where('iv.id', $variationId)
            ->select(
                'iv.id as variationid',
                
            )
            ->first();
    }

    /**
     * Fetch a user's recently viewed items, newest first, with
     * price / images / variations — same shape as Item::getData().
     */
    public static function getList(array $post): array
    {
        $perPage = isset($post['per_page']) ? (int) $post['per_page'] : 10;
        $page    = isset($post['page'])     ? (int) $post['page']     : 1;
        $offset  = ($page - 1) * $perPage;

        $query = DB::table('recently_viewed as rv')
            ->join('itemvariations as iv', 'iv.id', '=', 'rv.variation_id')
            ->join('items as i', 'i.id', '=', 'iv.item_id')
            ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
            ->leftJoin(DB::raw('(
                SELECT item_id, MIN(image) as image
                FROM item_images
                GROUP BY item_id
            ) as im'), 'im.item_id', '=', 'i.id')
            ->where('rv.user_id', $post['userid'])
            ->where('rv.org_id', $post['orgid'])
            // ->where('i.status', 'Y')
            ->select(
                'iv.id as variationid',
                'i.id as productid',
                DB::raw("CONCAT(i.title) as title"),
                'iv.value',
                'p.price',
                DB::raw("CONCAT('" . url('uploads/items') . "/', im.image) as image"),
                'rv.updated_at as viewed_at'
            )
            ->orderBy('rv.updated_at', 'desc');

        $total   = (clone $query)->count();
        $records = $query->offset($offset)->limit($perPage)->get();

        return [
            'data'       => $records,
            'pagination' => [
                'current_page' => $page,
                'last_page'    => $total > 0 ? (int) ceil($total / $perPage) : 1,
                'per_page'     => $perPage,
                'total'        => $total,
                'has_more'     => ($page * $perPage) < $total,
                'next_page'    => ($page * $perPage) < $total ? $page + 1 : null,
                'prev_page'    => $page > 1 ? $page - 1 : null,
            ],
        ];
    }

    /**
     * Delete a single recently-viewed entry for a user.
     */
    // public static function deleteOne(array $post): bool
    // {
    //     if (empty($post['userid']) || empty($post['variationid'])) {
    //         throw new Exception('userid and variationid are required.');
    //     }

    //     return DB::table('recently_viewed')
    //         ->where('user_id', $post['userid'])
    //         ->where('org_id', $post['orgid'] ?? null)
    //         ->where('variation_id', $post['variationid'])
    //         ->delete() > 0;
    // }
}