<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class LoyaltySetup extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    //function to save loyalty
    public static function saveData($post)
    {
        try {
            $dataArray = [
                'minprice' => $post['minprice'],
                'maxprice' => $post['maxprice'],
                'percentage' => $post['percentage'],
                'status' => 'Y',
                'orgid' => $post['orgid'],
                'userid' => $post['userid'],
                'postedby' => $post['userid']
            ];


            if (!empty($post['id'])) {
                $dataArray['updated_at'] = Carbon::now();
                $dataArray['updatedby'] = $post['userid'];

                if (!LoyaltySetup::where('id', $post['id'])->update($dataArray)) {
                    throw new \Exception("Couldn't update Records");
                }
            } else {

                $dataArray['id'] = (string) Str::uuid();

                $dataArray['created_at'] = Carbon::now();

                if (!LoyaltySetup::insert($dataArray)) {
                    throw new \Exception("Couldn't Save Records");
                }
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //function to get list of loyaties
    public static function list($post)
    {
        try {
            $get = $post;

            $sorting = !empty($get['order'][0]['dir']) ? $get['order'][0]['dir'] : 'asc';

            // Sanitize search inputs
            foreach ($get['columns'] as $key => $value) {
                $get['columns'][$key]['search']['value'] = trim(strtolower(htmlspecialchars($value['search']['value'], ENT_QUOTES)));
            }

            $orgid = $post['orgid'];

            // Build WHERE condition as string
            $cond = "status = 'Y' and orgid = '{$orgid}'";


            if (!empty($get['columns'][1]['search']['value'])) {
                $search = $get['columns'][1]['search']['value'];
                $cond .= " and lower(minprice) like '%{$search}%'";
            }
            if (!empty($get['columns'][2]['search']['value'])) {
                $search = $get['columns'][2]['search']['value'];
                $cond .= " and lower(maxprice) like '%{$search}%'";
            }

            if (!empty($get['columns'][3]['search']['value'])) {
                $search = $get['columns'][3]['search']['value'];
                $cond .= " and lower(percentage) like '%{$search}%'";
            }

            $limit = !empty($get["length"]) ? (int)$get['length'] : 15;
            $offset = !empty($get["start"]) ? (int)$get["start"] : 0;

            $query = LoyaltySetup::selectRaw("(SELECT count(*) FROM loyalty_setups WHERE {$cond}) AS totalrecs, minprice, maxprice, percentage, id")
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderBy('id', 'desc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderBy('id', 'desc')->get();
            }

            if ($result) {
                $ndata = $result;
                $ndata['totalrecs'] = @$result[0]->totalrecs ?? 0;
                $ndata['totalfilteredrecs'] = @$result[0]->totalrecs ?? 0;
            } else {
                $ndata = [];
            }

            return $ndata;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //delete function
    public static function deleteLoyalty($post)
    {
        try {
            if (!LoyaltySetup::where(['id' => $post['id']])->delete()) {
                throw new Exception("Couldn't Delete Data. Please try again", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
