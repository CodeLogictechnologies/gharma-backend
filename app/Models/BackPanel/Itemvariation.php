<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\DB;

class Itemvariation extends Model
{
    public static function getDate($post)
    {
        try {
            $result = DB::table('itemvariations')->where('item_id', $post['id'])->get();
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getProductCodes($post)
    {
        try {
            return DB::table('itemvariations as iv')
                ->join('items as i', 'i.id', '=', 'iv.item_id')
                ->select(
                    'iv.id as variationid',
                    'iv.item_id as itemid',
                    'iv.product_code',
                    'iv.attribute',
                    'iv.value',
                    'i.title as itemname',
                    'i.is_wholesale'
                )
                ->where('i.orgid', $post['orgid'])
                ->where('i.status', 'Y')
                ->whereNotNull('iv.product_code')
                ->where('iv.product_code', '!=', '')
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
