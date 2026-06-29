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
                'name'      => $post['name'],
                'phone'     => $post['phone'],
                'address'   => $post['address'],
                'email'     => $post['email'],
                'city'      => $post['city'],
                'country'   => $post['country'],
                'orgid'     => $post['orgid'],
                'latitude'  => !empty($post['latitude'])  ? (float) preg_replace('/[^0-9.\-]/', '', $post['latitude'])  : null,
                'longitude' => !empty($post['longitude']) ? (float) preg_replace('/[^0-9.\-]/', '', $post['longitude']) : null,
                'radius'    => !empty($post['radius'])    ? (float) $post['radius'] : null,

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
    // public static function list($post)
    // {
    //     try {
    //         $get = $_GET;
    //         foreach ($get as $key => $value) {
    //             $get[$key] = trim(strtolower(htmlspecialchars($get[$key], ENT_QUOTES)));
    //         }
    //         $cond = " status = 'Y'";
    //         if ($get['sSearch_1']) {
    //             $cond .= "and lower(name) like'%" . $get['sSearch_1'] . "%'";
    //         }

    //         if ($get['sSearch_3']) {
    //             $cond .= "and lower(email) like'%" . $get['sSearch_3'] . "%'";
    //         }


    //         if ($get['sSearch_2']) {
    //             $cond .= "and lower(phone) like'%" . $get['sSearch_2'] . "%'";
    //         }



    //         $limit = 15;
    //         $offset = 0;
    //         if (!empty($get["length"]) && $get["length"]) {
    //             $limit = $get['length'];
    //             $offset = $get["start"];
    //         }

    //         $query = Store::selectRaw("(SELECT count(*) FROM stores where {$cond}) AS totalrecs,name,email, id as id, phone, address,country, city, latitude, longitude")
    //             ->whereRaw($cond);

    //         if ($limit > -1) {
    //             $result = $query->orderBy('created_at', 'desc')->offset($offset)->limit($limit)->get();
    //         } else {
    //             $result = $query->orderBy('created_at', 'desc')->get();
    //         }
    //         if ($result) {
    //             $ndata = $result;
    //             $ndata['totalrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
    //             $ndata['totalfilteredrecs'] = @$result[0]->totalrecs ? $result[0]->totalrecs : 0;
    //         } else {
    //             $ndata = array();
    //         }
    //         return $ndata;
    //     } catch (Exception $e) {
    //         throw $e;
    //     }
    // }

    public static function list($post)
    {
        try {
            $get = $post;
            foreach ($get as $key => $value) {
                if (is_string($value)) {
                    $get[$key] = trim(strtolower(htmlspecialchars($value, ENT_QUOTES)));
                }
            }

            $cond     = "status = 'Y'";
            $bindings = [];

            if (!empty($get['sSearch_1'])) {
                $cond .= " AND lower(name) LIKE ?";
                $bindings[] = '%' . $get['sSearch_1'] . '%';
            }

            if (!empty($get['sSearch_2'])) {
                $cond .= " AND lower(phone) LIKE ?";
                $bindings[] = '%' . $get['sSearch_2'] . '%';
            }

            if (!empty($get['sSearch_3'])) {
                $cond .= " AND lower(email) LIKE ?";
                $bindings[] = '%' . $get['sSearch_3'] . '%';
            }

            $limit  = !empty($get['iDisplayLength']) ? (int) $get['iDisplayLength'] : 15;
            $offset = !empty($get['iDisplayStart'])  ? (int) $get['iDisplayStart']  : 0;

            // Total unfiltered count (no search conditions)
            $totalrecs = DB::table('stores')
                ->whereRaw("status = 'Y'")
                ->count();

            // Filtered count (with search conditions)
            $totalfilteredrecs = DB::table('stores')
                ->whereRaw($cond, $bindings)
                ->count();

            $query = Store::selectRaw("name, email, id, phone, address, country, city, latitude, longitude")
                ->whereRaw($cond, $bindings)
                ->orderBy('created_at', 'desc');

            $result = ($limit > -1)
                ? $query->offset($offset)->limit($limit)->get()
                : $query->get();

            // Return plain array, not collection with appended keys
            $ndata = $result->all();
            $ndata['totalrecs']         = $totalrecs;
            $ndata['totalfilteredrecs'] = $totalfilteredrecs;

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
            ->select(
                'o.id',
                'o.name',
                'o.phone',
                'o.email',
                'o.address',
                'o.city',
                'o.country',
                'o.latitude',
                'o.longitude',
                'o.radius'

            )
            ->first();

        return $result;
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
