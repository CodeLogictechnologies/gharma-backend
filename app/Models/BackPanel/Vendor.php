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

class Vendor extends Model
{

    public $incrementing = false;
    protected $keyType = 'string';

    public static function saveData($post)
    {
        try {

            $dataArray = [
                'name'    => $post['name'],
                'phone'   => $post['phone'],
                'address' => $post['address'],
                'email'   => $post['email'],
                'company_name'   => $post['company'],
                'tax_number'   => $post['pan'],
                'registration_number'   => $post['registration_number'],
                'city'   => $post['city'],
                'address'   => $post['address'],
                'orgid'   => $post['orgid'],
                'slug' => Str::slug($post['name']) . '-' . time(),
            ];



            if (!empty($post['id'])) {
                $dataArray['updatedby'] = $post['userid'];
                $dataArray['updated_at'] = Carbon::now();

                $vendor = DB::table('vendors')
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

                $vendor =  DB::table('vendors')->insert($dataArray);

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
            foreach ($get as $key => $value) {
                $get[$key] = trim(strtolower(htmlspecialchars($get[$key], ENT_QUOTES)));
            }
            $cond = " status = 'Y'";
            if ($get['sSearch_1']) {
                $cond .= "and lower(name) like'%" . $get['sSearch_1'] . "%'";
            }

            if ($get['sSearch_2']) {
                $cond .= "and lower(phone) like'%" . $get['sSearch_2'] . "%'";
            }

            if ($get['sSearch_3']) {
                $cond .= "and lower(email) like'%" . $get['sSearch_3'] . "%'";
            }


            // if (!empty($post['type']) && $post['type'] === "trashed") {
            //     $cond = " status = 'N'";
            // }


            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = Vendor::selectRaw("(SELECT count(*) FROM vendors where {$cond}) AS totalrecs,name,email, id as id, phone, address, tax_number, registration_number, company_name")
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
        $result = DB::table('vendors')
            ->select('id', 'address', 'name', 'email', 'phone', 'company_name', 'tax_number', 'registration_number', 'city', 'address')
            ->where('id', $post['id'])
            ->where('orgid', $post['orgid'])
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
            if (!Vendor::where(['id' => $post['id']])->where(['orgid' => $post['orgid']])->update($updateArray)) {
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
