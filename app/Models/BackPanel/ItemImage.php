<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\DB;

class ItemImage extends Model
{
    public static function getDate($post)
    {
        try {
            $result = DB::table('item_images')->where('item_id', $post['id'])->get();
            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
