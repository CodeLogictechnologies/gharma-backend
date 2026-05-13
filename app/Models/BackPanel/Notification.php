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

class Notification extends Model
{

    public $incrementing = false;
    protected $keyType = 'string';

    public static function saveData($post)
    {
        try {
            $dataArray = [
                'type'    => $post['type'],
                'userid'   => $post['user_id'],
                'title' => $post['title'],
                'message'   => $post['message'],
                'orgid'   => $post['orgid'],
            ];



            if (!empty($post['id'])) {
                $dataArray['updatedby'] = $post['userid'];
                $dataArray['updated_at'] = Carbon::now();

                $vendor = DB::table('notifications')
                    ->where('id', $post['id'])
                    ->update($dataArray);

                if (!$vendor) {
                    throw new Exception("Couldn't update vendor", 1);
                }
            } else {

                $dataArray['id'] = (string) Str::uuid();
                $dataArray['postedby'] = $post['userid'];
                $dataArray['created_at'] = Carbon::now();
                $dataArray['updated_at'] = Carbon::now();

                $vendor =  DB::table('notifications')->insert($dataArray);

                if (!$vendor) {
                    throw new Exception("Couldn't save vendor", 1);
                }
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function list($post)
    {
        try {
            $get = $_GET;
            $cond = "";

            foreach ($get as $key => $value) {
                $get[$key] = trim(strtolower(htmlspecialchars($get[$key], ENT_QUOTES)));
            }

            if (!empty($get['sSearch_1'])) {
                $cond .= " and lower(n.title) like '%" . $get['sSearch_1'] . "%'";
            }
            // if (!empty($get['sSearch_2'])) {
            //     $cond .= " and lower(n.type) like '%" . $get['sSearch_'] . "%'";
            // }
            if (!empty($get['sSearch_4'])) {
                $cond .= " and lower(CONCAT(p.first_name,' ',p.middle_name,' ',p.last_name)) like '%" . $get['sSearch_4'] . "%'";
            }

            if (!empty($get['sSearch_3'])) {
                $cond .= " and lower(n.message) like '%" . $get['sSearch_3'] . "%'";
            }

            // ✅ FIXED condition (was broken before)
            $cond = "n.status = 'Y' 
        and p.status = 'Y' 
        and n.orgid = '" . $post['orgid'] . "' 
        and p.orgid = '" . $post['orgid'] . "' 
        " . $cond;

            $limit = 15;
            $offset = 0;

            if (!empty($get["length"])) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = Notification::from('notifications as n')
                ->join('profiles as p', 'p.user_id', '=', 'n.userid')
                ->selectRaw("
        (SELECT count(*) FROM notifications WHERE {$cond}) AS totalrecs,
        n.title,
        n.message,
        n.id        AS id,
        n.type,
        CONCAT(p.first_name, ' ', COALESCE(p.middle_name, ''), ' ', p.last_name) AS username
    ")
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderBy('n.id', 'asc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderBy('n.id', 'asc')->get();
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
        $result = DB::table('notifications as n')
            ->join('users as u', 'u.id', '=', 'n.userid')
            ->join('profiles as p', 'p.user_id', '=', 'u.id')
            ->select(
                'n.id',
                'n.title',
                'n.message',
                'n.type',
                'u.id as user_id',
                DB::raw("CONCAT(p.first_name,' ',COALESCE(p.middle_name,''),' ',p.last_name) as username")
            )
            ->where('n.id', $post['id'])
            ->where('n.orgid', $post['orgid'])
            ->first();
        return  $result;
    }

    public static function deleteDate($post)
    {
        try {
            $updateArray = [
                'status' => 'N',
                'updatedby' => $post['userid'],
                'updated_at' => Carbon::now(),
            ];
            if (!Notification::where(['id' => $post['id']])->where(['orgid' => $post['orgid']])->update($updateArray)) {
                throw new Exception("Couldn't Delete Data. Please try again", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getVendor($post)
    {
        try {
            $data = DB::table('vendors')->select('id as vendorid', 'name as vendorname')->where('orgid', $post['orgid'])->get();
            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
