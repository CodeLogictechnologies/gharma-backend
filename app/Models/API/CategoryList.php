<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;

class CategoryList extends Model
{
    public static function getListData($post)
    {
        try {
            $result = DB::table('categories as f')
                ->where('f.orgid', $post['orgid'])
                ->where('f.status', 'Y')
                ->select(
                    'f.id as categortid',
                    'f.title',
                    DB::raw("CONCAT('" . url('uploads/categories') . "/', f.image) as image")
                )
                ->get();
            if (!$result) {
                throw new Exception('No category  found.', 1);
            }
            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}