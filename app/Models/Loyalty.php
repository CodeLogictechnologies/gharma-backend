<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class Loyalty extends Model
{
    public static function getData($post)
    {
        $data = DB::table('loyalties')
            ->where('userid', $post['userid'])
            // ->where('orgid', $post['orgid'])
            ->value('loyaltypoint');

        return $data;
    }
}