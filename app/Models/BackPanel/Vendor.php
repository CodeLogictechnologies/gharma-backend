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
                'name'                => $post['name'],
                'phone'               => $post['phone'],
                'address'             => $post['address'],
                'email'               => $post['email'],
                'company_name'        => $post['company'],
                'tax_number'          => $post['pan'],
                'registration_number' => $post['registration_number'],
                'city'                => $post['city'],
                'orgid'               => $post['orgid'],
                'slug'                => Str::slug($post['name']) . '-' . time(),
            ];

            if (!empty($post['pan_image'])) {
                $dataArray['pan_image'] = $post['pan_image'];
            }
            if (!empty($post['registration_number_image'])) {
                $dataArray['registration_number_image'] = $post['registration_number_image'];
            }

            if (!empty($post['id'])) {
                $dataArray['updatedby'] = $post['userid'];
                $dataArray['updated_at'] = Carbon::now();

                DB::table('vendors')->where('id', $post['id'])->update($dataArray);
            } else {
                $dataArray['id']         = (string) Str::uuid();
                $dataArray['postedby']   = $post['userid'];
                $dataArray['created_at'] = Carbon::now();
                $dataArray['updated_at'] = Carbon::now();

                DB::table('vendors')->insert($dataArray);
            }

            return true;
        } catch (Exception $e) {
            throw $e; // let controller handle rollback
        }
    }

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

    //         if ($get['sSearch_2']) {
    //             $cond .= "and lower(phone) like'%" . $get['sSearch_2'] . "%'";
    //         }

    //         if ($get['sSearch_3']) {
    //             $cond .= "and lower(email) like'%" . $get['sSearch_3'] . "%'";
    //         }


    //         // if (!empty($post['type']) && $post['type'] === "trashed") {
    //         //     $cond = " status = 'N'";
    //         // }


    //         $limit = 15;
    //         $offset = 0;
    //         if (!empty($get["length"]) && $get["length"]) {
    //             $limit = $get['length'];
    //             $offset = $get["start"];
    //         }

    //         $query = Vendor::selectRaw("(SELECT count(*) FROM vendors where {$cond}) AS totalrecs,name,email, id as id, phone, address, tax_number, registration_number, company_name")
    //             ->whereRaw($cond);

    //         if ($limit > -1) {
    //             $result = $query->orderBy('id', 'asc')->offset($offset)->limit($limit)->get();
    //         } else {
    //             $result = $query->orderBy('id', 'asc')->get();
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
            $cond     = " status = 'Y'";
            $bindings = [];

            // Old-style sSearch params (sAjaxSource sends these)
            if (!empty($post['sSearch_1'])) {
                $val      = strtolower(trim($post['sSearch_1']));
                $cond    .= " and lower(name) like ?";
                $bindings[] = "%{$val}%";
            }

            if (!empty($post['sSearch_2'])) {
                $val      = strtolower(trim($post['sSearch_2']));
                $cond    .= " and lower(phone) like ?";
                $bindings[] = "%{$val}%";
            }

            if (!empty($post['sSearch_3'])) {
                $val      = strtolower(trim($post['sSearch_3']));
                $cond    .= " and lower(email) like ?";
                $bindings[] = "%{$val}%";
            }

            $limit  = isset($post['iDisplayLength']) ? (int) $post['iDisplayLength'] : 15;
            $offset = isset($post['iDisplayStart'])  ? (int) $post['iDisplayStart']  : 0;

            $totalrecs = DB::table('vendors')
                ->whereRaw($cond, $bindings)
                ->count();

            $query = Vendor::selectRaw("name, email, id, phone, address, tax_number, registration_number, company_name")
                ->whereRaw($cond, $bindings)
                ->orderBy('created_at', 'desc');

            $filteredCount = (clone $query)->count();

            if ($limit > -1) {
                $result = $query->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->get();
            }

            $ndata                      = $result;
            $ndata['totalrecs']         = $totalrecs;
            $ndata['totalfilteredrecs'] = $filteredCount;

            return $ndata;
        } catch (Exception $e) {
            throw $e;
        }
    }
    public static function getData($post)
    {
        $result = DB::table('vendors')
            ->select('id', 'address', 'name', 'email', 'phone', 'company_name', 'tax_number', 'registration_number', 'city', 'address', 'pan_image', 'registration_number_image')
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
            $data = DB::table('vendors')
                ->select('id as vendorid', 'name as vendorname', 'tax_number as vendorpan')
                ->where('orgid', $post['orgid'])
                ->where('status', 'Y')
                ->get();
            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
