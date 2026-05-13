<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class SearchHistory extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    public static function saveData($post)
    {
        try {
            $exists = DB::table('search_histories')
                ->where('orgid', $post['orgid'])
                ->where('userid', $post['userid'])
                ->where('text', $post['search'])
                ->exists();

            if ($exists) {
                return true;
            }

            $saveHistoryArray = [
                'id'         => (string) Str::uuid(),
                'orgid'      => $post['orgid'],
                'userid'     => $post['userid'],
                'text'       => $post['search'],
                'postedby'   => $post['userid'],
                'created_at' => now()
            ];

            $result = DB::table('search_histories')->insert($saveHistoryArray);

            if (!$result) {
                throw new Exception('Could not save search', 1);
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function deleteSearch($post)
    {
        try {
            $result = SearchHistory::where('id', $post['searchid'])
                ->where('userid', $post['userid'])
                ->where('orgid', $post['orgid'])
                ->delete();

            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function deleteSearchBulk($post)
    {
        try {
            $result = SearchHistory::where('userid', $post['userid'])
                ->where('orgid', $post['orgid'])
                ->delete();

            return $result;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
