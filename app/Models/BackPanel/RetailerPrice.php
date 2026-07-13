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
            $price = (float) $post['price'];

            // Discount
            $discount = 0;
            if ($post['discount_type'] == 'percentage') {
                $discount = ($price * (float)$post['discount_percentage']) / 100;
            } else {
                $discount = (float)($post['discount_amount'] ?? 0);
            }

            // Excise
            $excise = 0;
            if ($post['excise_status'] == 1) {
                if ($post['excise_type'] == 'percentage') {
                    $excise = ($price * (float)$post['excise_percentage']) / 100;
                } else {
                    $excise = (float)($post['excise_value'] ?? 0);
                }
            }

            // Before Discount
            $subTotalBefore = $price + $excise;
            $vatBefore = 0;

            if ($post['vat_status'] == 1) {
                $vatBefore = ($subTotalBefore * (float)$post['vat_percent']) / 100;
            }

            $priceBeforeDiscount = $subTotalBefore + $vatBefore;

            // After Discount
            $subTotalAfter = ($price - $discount) + $excise;
            $vatAfter = 0;

            if ($post['vat_status'] == 1) {
                $vatAfter = ($subTotalAfter * (float)$post['vat_percent']) / 100;
            }

            $priceAfterDiscount = $subTotalAfter + $vatAfter;

            $dataArray = [
                'itemid'                => $post['itemid'],
                'variation_id'          => $post['variationid'],
                'price'                 => $price,
                'price_before_discount' => round($priceBeforeDiscount, 2),
                'price_after_discount'  => round($priceAfterDiscount, 2),
                'orgid'                 => $post['orgid'],
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

            $variationUpdate = [
                'price'      => $post['price'],
                'updated_at' => Carbon::now(),
            ];

            if (array_key_exists('discount_type', $post)) {
                $discountType = in_array($post['discount_type'] ?? null, ['percentage', 'fixed'], true)
                    ? $post['discount_type'] : null;

                $discountPercentage = ($discountType === 'percentage' && is_numeric($post['discount_percentage'] ?? null))
                    ? $post['discount_percentage'] : null;

                $discountAmount = ($discountType === 'fixed' && is_numeric($post['discount_amount'] ?? null))
                    ? $post['discount_amount'] : null;

                $variationUpdate['discount_type']   = $discountType;
                $variationUpdate['discount']        = $discountPercentage;
                $variationUpdate['discount_amount'] = $discountAmount;
            }

            DB::table('itemvariations')
                ->where('id', $post['variationid'])
                ->update($variationUpdate);

            if (array_key_exists('excise_status', $post) || array_key_exists('vat_status', $post)) {
                $itemUpdate = ['updated_at' => Carbon::now()];

                if (array_key_exists('excise_status', $post)) {
                    $exciseStatus = !empty($post['excise_status']) ? 'Y' : 'N';
                    $exciseType   = $exciseStatus === 'Y' ? ($post['excise_type'] ?? null) : null;

                    $itemUpdate['excise_status']     = $exciseStatus;
                    $itemUpdate['excise_type']       = $exciseType;
                    $itemUpdate['excise_percentage'] = $exciseType === 'percentage' ? ($post['excise_percentage'] ?? null) : null;
                    $itemUpdate['excise_value']      = $exciseType === 'fixed' ? ($post['excise_value'] ?? null) : null;
                }

                if (array_key_exists('vat_status', $post)) {
                    $itemUpdate['vat_status']  = !empty($post['vat_status']) ? 'Y' : 'N';
                    $itemUpdate['vat_percent'] = Item::resolveVatPercent($post);
                }

                DB::table('items')
                    ->where('id', $post['itemid'])
                    ->update($itemUpdate);
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

            if ($get['columns'][2]['search']['value'])
                $cond .= " and lower(iv.value) like '%" . $get['columns'][2]['search']['value'] . "%'";

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
                iv.value,
                rp.price_after_discount,
                rp.price_before_discount,
                iv.discount_type,
                iv.discount,
                iv.discount_amount,
                i.vat_status,
                i.vat_percent,
                i.excise_status,
                i.excise_type,
                i.excise_percentage,
                i.excise_value
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