<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class TermsPrivacy extends Model
{
    protected $table = 'terms_privacy_policies';

    protected $fillable = [
        'type',
        'description',
        'orgid',
        'postedby',
        'updatedby',
    ];

    public static function savePolicy($post)
    {
        try {
            $existing = DB::table('terms_privacy_policies')
                ->where('type',  $post['type'])
                ->where('orgid', $post['orgid'])
                ->first();

            if ($existing) {
                DB::table('terms_privacy_policies')
                    ->where('id', $existing->id)
                    ->update([
                        'description' => $post['description'],
                        'updatedby'   => $post['userid'],
                        'updated_at'  => Carbon::now(),
                    ]);
            } else {
                DB::table('terms_privacy_policies')->insert([
                    'type'        => $post['type'],
                    'description' => $post['description'],
                    'orgid'       => $post['orgid'],
                    'postedby'    => $post['userid'],
                    'updatedby'   => $post['userid'],
                    'created_at'  => Carbon::now(),
                    'updated_at'  => Carbon::now(),
                ]);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getPolicy($orgid, $type)
    {
        return DB::table('terms_privacy_policies')
            ->where('orgid', $orgid)
            ->where('type',  $type)
            ->first();
    }
}