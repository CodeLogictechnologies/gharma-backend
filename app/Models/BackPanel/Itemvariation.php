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
}
