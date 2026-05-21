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
        $query = DB::table('categories as f')
            ->whereIn('f.status', ['Y', '1', 1])
            ->select(
                'f.id as categoryid',
                'f.title',
                DB::raw("CONCAT('" . url('uploads/categories') . "/', f.image) as image")
            );

        // Filter by orgid only if provided
        if (!empty($post['orgid'])) {
            $query->where('f.orgid', $post['orgid']);
        }

        return $query->get();

    } catch (\Exception $e) {
        throw $e;
    }
}
}