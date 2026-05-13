<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;


class Store extends Model
{

    public $incrementing = false;
    protected $keyType = 'string';

    //function to save
    public static function saveData($post)
    {
        try {

            $dataArray = [
                'name'    => $post['name'],
                'phone'   => $post['phone'],
                'address' => $post['address'],
                'email'   => $post['email'],
                'city'   => $post['city'],
                'country'   => $post['country'],
                'orgid'   => $post['orgid'],
            ];

            if (!empty($post['id'])) {

                $dataArray['updated_at'] = Carbon::now();

                $store = DB::table('stores')
                    ->where('id', $post['id'])
                    ->update($dataArray);

                if (!$store) {
                    throw new Exception("Couldn't update store", 1);
                }
            } else {

                $dataArray['id'] = (string) Str::uuid();
                $dataArray['created_at'] = Carbon::now();

                DB::table('stores')->insert($dataArray);

                $orgId = $dataArray['id'];

                if (!$orgId) {
                    throw new Exception("Couldn't save organization", 1);
                }
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }


    //function to get list of store
    public static function list($post)
    {
        try {
            $get = $_GET;
            foreach ($get as $key => $value) {
                $get[$key] = trim(strtolower(htmlspecialchars($get[$key], ENT_QUOTES)));
            }
            $cond = " status = 'Y'";
            if ($get['sSearch_1']) {
                $cond .= "and lower(name) like'%" . $get['sSearch_1'] . "%'";
            }

            if ($get['sSearch_3']) {
                $cond .= "and lower(email) like'%" . $get['sSearch_3'] . "%'";
            }


            if ($get['sSearch_2']) {
                $cond .= "and lower(phone) like'%" . $get['sSearch_2'] . "%'";
            }



            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = Store::selectRaw("(SELECT count(*) FROM stores where {$cond}) AS totalrecs,name,email, id as id, phone, address,country, city")
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


    //function to get data of store
    public static function getData($post)
    {
        $result = DB::table('stores as o')
            ->where('o.id', $post['id'])
            ->first();
        return  $result;
    }


    //function to delete store data
    public static function deleteData($post)
    {
        try {
            $store = Store::find($post['id']);

            if (!$store) {
                return false;
            }

            $storeupdate = [
                'status' => 'N',
                'updated_at' => now(),
            ];

            $result = DB::table('stores')->where('id', $post['id'])->update($storeupdate);

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}