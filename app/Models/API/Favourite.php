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
        $result = DB::table('favourites as f')
            ->join('itemvariations as iv', 'iv.id', '=', 'f.variationid')
            ->join('items as i', 'i.id', '=', 'iv.item_id')
            ->join('retailer_prices as p', 'p.variation_id', '=', 'iv.id')
            ->where('f.userid', $post['userid'])
            ->where('f.orgid', $post['orgid'])
            ->where('f.status', 'Y')
            ->select(
                DB::raw('MIN(f.id) as favouriteid'), // ✅ avoid duplicate ids
                'i.id as productid',
                'p.price',
                'iv.id as variationid',
                DB::raw("CONCAT(i.title, ' ', iv.value) as itemname")
            )
            ->groupBy('iv.id', 'i.id', 'i.title', 'iv.value', 'p.price') // ✅ remove duplicates
            ->get();


        if ($result->isEmpty()) {
            throw new \Exception('No favourite item found.');
        }

        // ✅ Get product IDs
        $productIds = $result->pluck('productid')->unique();

        // ✅ Get ONLY FIRST image per product
        $images = DB::table('item_images')
            ->whereIn('item_id', $productIds)
            ->orderBy('created_at') // first image
            ->get()
            ->groupBy('item_id');

        // ✅ Attach ONLY first image
        $result = $result->map(function ($item) use ($images) {

            $firstImage = optional($images[$item->productid]->first())->image ?? null;

            $item->image = $firstImage
                ? url('uploads/items/' . $firstImage)
                : null;

            return $item;
        });

        return $result;
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