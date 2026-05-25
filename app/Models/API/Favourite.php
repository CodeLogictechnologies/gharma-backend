<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

use function Laravel\Prompts\title;

class Favourite extends Model
{
    public static function saveData($post)
    {
        try {
            $insertArray = [
                'id'         => (string) Str::uuid(),
                'orgid'      => $post['orgid'],
                'userid'     => $post['userid'],
                'variationid'      => $post['variationid'],
                'created_at' => Carbon::now(),
            ];

            if (!Favourite::insert($insertArray)) {
                throw new \Exception("Couldn't add item to favourite.");
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getListData($post)
    {
        $perPage = isset($post['per_page']) ? (int)$post['per_page'] : 10;
        $page    = isset($post['page'])     ? (int)$post['page']     : 1;
        $offset  = ($page - 1) * $perPage;

        $query = DB::table('favourites as f')
            ->join('itemvariations as iv', 'iv.id', '=', 'f.variationid')
            ->join('items as i', 'i.id', '=', 'iv.item_id')
            ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
            ->where('f.userid', $post['userid'])
            ->where('f.orgid', $post['orgid'])
            ->where('f.status', 'Y')
            ->select(
                DB::raw('MIN(f.id) as favouriteid'),
                'i.id as productid',
                'p.price',
                'iv.id as variationid',
                DB::raw("CONCAT(i.title, ' ', iv.value) as itemname")
            )
            ->groupBy('iv.id', 'i.id', 'i.title', 'iv.value', 'p.price');

        $total   = (clone $query)->count();
        $result  = $query->offset($offset)->limit($perPage)->get();

        if ($result->isEmpty()) {
            return [
                'data'       => [],
                'pagination' => [
                    'current_page' => $page,
                    'last_page'    => 1,
                    'per_page'     => $perPage,
                    'total'        => 0,
                    'has_more'     => false,
                    'next_page'    => null,
                    'prev_page'    => null,
                ]
            ];
        }

        $productIds = $result->pluck('productid')->unique();

        $images = DB::table('item_images')
            ->whereIn('item_id', $productIds)
            ->orderBy('created_at')
            ->get()
            ->groupBy('item_id');

        $result = $result->map(function ($item) use ($images) {
            $firstImage = optional($images[$item->productid]->first())->image ?? null;
            $item->image = $firstImage ? url('uploads/items/' . $firstImage) : null;
            return $item;
        });

        return [
            'data'       => $result,
            'pagination' => [
                'current_page' => $page,
                'last_page'    => $total > 0 ? (int)ceil($total / $perPage) : 1,
                'per_page'     => $perPage,
                'total'        => $total,
                'has_more'     => ($page * $perPage) < $total,
                'next_page'    => ($page * $perPage) < $total ? $page + 1 : null,
                'prev_page'    => $page > 1 ? $page - 1 : null,
            ]
        ];
    }
    public static function deleteFavourite($post)
    {
        try {
            $result = Favourite::where('variationid', $post['variationid'])
                ->where('userid', $post['userid'])
                ->where('orgid', $post['orgid'])
                ->delete();

            if (!$result) {
                throw new Exception('Failed to delete favourite', 1);
            }
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
