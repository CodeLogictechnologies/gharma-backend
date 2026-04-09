<?php

namespace App\Models\BackPanel;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;;

class RetailerPrice extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    public static function saveData($post)
    {
        try {

            $dataArray = [
                'itemid' => $post['itemid'],
                'price' => $post['price'],
                'variation_id' => $post['variationid'],
                'orgid' => $post['orgid']
            ];


            if (!empty($post['id'])) {
                $dataArray['updatedby'] = $post['userid'];
                $dataArray['updated_at'] = Carbon::now();

                if (!RetailerPrice::where('id', $post['id'])->update($dataArray)) {
                    throw new \Exception("Couldn't update Records");
                }
            } else {

                $dataArray['id'] = (string) Str::uuid();
                $dataArray['postedby'] = $post['userid'];
                $dataArray['created_at'] = Carbon::now();

                if (!RetailerPrice::insert($dataArray)) {
                    throw new \Exception("Couldn't Save Records");
                }
            }

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    //function to list team category
    public static function list($post)
    {
        try {
            $get = $post;

            $sorting = !empty($get['order'][0]['dir']) ? $get['order'][0]['dir'] : 'asc';

            foreach ($get['columns'] as $key => $value) {
                $get['columns'][$key]['search']['value'] = trim(strtolower(htmlspecialchars($value['search']['value'], ENT_QUOTES)));
            }

            $cond = " rp.status = 'Y' ";

            if ($get['columns'][1]['search']['value'])
                $cond .= " and lower(i.title) like '%" . $get['columns'][1]['search']['value'] . "%'";

            $limit = 15;
            $offset = 0;

            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = RetailerPrice::from('retailer_prices as rp')
                ->join('items as i', 'i.id', '=', 'rp.itemid')
                ->join('itemvariations as iv', 'iv.id', '=', 'rp.variation_id')
                ->selectRaw("
                (SELECT count(*) FROM retailer_prices as rp WHERE {$cond}) AS totalrecs,
                rp.id,
                rp.price,
                i.id as itemid,
                i.title,
                iv.id as variationid,
                iv.value
            ")
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderBy('rp.id', 'desc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderBy('rp.id', 'desc')->get();
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

    //restore 
    public static function deleteRetailerPrice($post)
    {
        try {
            $updateArray = [
                'status' => 'N',
                'updatedby' => $post['userid'],
                'updated_at' => Carbon::now(),
            ];
            if (!RetailerPrice::where(['id' => $post['id']])->update($updateArray)) {
                throw new Exception("Couldn't Delete Data. Please try again", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getCategory($post)
    {
        try {
            $result = DB::table('categories')->select('id', 'title')->where('orgid', $post['orgid'])->where('status', 'Y')->get();

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
