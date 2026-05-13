<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use App\Models\Common;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\File;

class Discount extends Model
{

    public $incrementing = false;
    protected $keyType = 'string';



    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public static function saveData($post)
    {
        try {
            DB::beginTransaction();

            $dataArray = [
                'title'                => $post['title'],
                'type'                 => $post['type'],
                'percentage'           => $post['percentage']           ?? null,
                'value'                => $post['value']                ?? null,
                'applies_to'           => $post['applies_to'],
                'item_id'              => $post['item_id']              ?? null,
                'variation_id'         => $post['variation_id']         ?? null,
                'min_requirement'      => $post['min_requirement']      ?? 'none',
                'min_value'            => $post['min_value']            ?? null,
                'usage_limit_type'     => $post['usage_limit_type']     ?? 'once',
                'usage_limit'          => $post['usage_limit']          ?? null,
                'usage_limit_per_user' => $post['usage_limit_per_user'] ?? null,
                'discount_type' => $post['discount_type'] ?? null,
                'starts_at'            => $post['starts_at'],
                'ends_at'              => $post['ends_at'],
                'orgid'                => $post['orgid']                ?? null,
                'postedby'             => $post['userid'],
            ];

            // ── UPDATE ─────────────────────────────────────────
            if (!empty($post['id'])) {
                $dataArray['updatedby']  = $post['userid'];
                $dataArray['updated_at'] = Carbon::now();

                DB::table('discounts')
                    ->where('id', $post['id'])
                    ->update($dataArray);

                // ── INSERT ─────────────────────────────────────────
            } else {
                $dataArray['id']         = (string) Str::uuid();
                $dataArray['status']     = 'Y';
                $dataArray['used_count'] = 0;
                $dataArray['updatedby']  = $post['userid'];
                $dataArray['created_at'] = Carbon::now();
                $dataArray['updated_at'] = Carbon::now();

                $inserted = DB::table('discounts')->insert($dataArray);

                if (!$inserted) {
                    throw new \Exception("Couldn't save discount.");
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    public static function list($post)
    {
        try {
            $get = $_GET;
            foreach ($get as $key => $value) {
                $get[$key] = trim(strtolower(htmlspecialchars($get[$key], ENT_QUOTES)));
            }

            $cond = " status = 'Y'";

            if ($get['sSearch_1']) {
                $cond .= "and lower(title) like'%" . $get['sSearch_1'] . "%'";
            }
            if ($get['sSearch_2']) {
                $cond .= "and lower(type) like'%" . $get['sSearch_2'] . "%'";
            }
            if ($get['sSearch_3']) {
                $cond .= "and lower(applies_to) like'%" . $get['sSearch_3'] . "%'";
            }

            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = Discount::selectRaw("(SELECT count(*) FROM discounts where {$cond}) AS totalrecs,title,type, id as id, applies_to, min_requirement, starts_at, ends_at")
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderBy('id', 'asc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderBy('id', 'asc')->get();
            }
            if ($result) {
                $ndata = $result;
                $ndata['totalrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
                $ndata['totalfilteredrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
            } else {
                $ndata = array();
            }
            return $ndata;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getData($post)
    {
        $result = DB::table('organizations as o')
            ->join('users as u', 'u.orgid', '=', 'o.id')
            ->select('o.id as id', 'u.id as userid', 'o.address', 'o.name', 'o.email', 'o.phone', 'o.logo', 'u.name as username')
            ->where('o.id', $post['id'])
            ->first();
        return  $result;
    }

    public static function deleteDate($post)
    {
        try {
            $updateArray = [
                'status' => 'N',
                'updated_at' => Carbon::now(),
            ];
            if (!Discount::where(['id' => $post['id']])->update($updateArray)) {
                throw new Exception("Couldn't Delete Data. Please try again", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}