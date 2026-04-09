<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;

class Order extends Model
{
    public static function list($post)
    {
        try {
            $get = $_GET;
            foreach ($get as $key => $value) {
                $get[$key] = trim(strtolower(htmlspecialchars($get[$key], ENT_QUOTES)));
            }
            $cond = "1=1";

            if ($get['sSearch_1']) {
                $cond .= "and lower(p.username) like'%" . $get['sSearch_1'] . "%'";
            }

            if ($get['sSearch_3']) {
                $cond .= "and lower(i.title) like'%" . $get['sSearch_3'] . "%'";
            }
            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = Order::from('orders as o')
                ->join('profiles as p', 'p.id', '=', 'o.customer_id')
                ->join('itemvariations as iv', 'iv.id', '=', 'o.variation_id')
                ->join('items as i', 'i.id', '=', 'o.item_id')
                ->selectRaw("
                                (SELECT COUNT(*) FROM sub_categories WHERE {$cond}) as totalrecs,
                                o.id,
                                o.qty,
                                o.price,
                                p.username,
                                p.phone,
                                i.title
                            ")
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderby('o.id', 'desc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderby('o.id', 'desc')->get();
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
        $result =  Order::from('orders as o')
            ->join('profiles as p', 'p.id', '=', 'o.customer_id')
            ->join('itemvariations as iv', 'iv.id', '=', 'o.variation_id')
            ->join('items as i', 'i.id', '=', 'o.item_id')
            ->join('categories as c', 'c.id', '=', 'i.category_id')
            ->join('sub_categories as s', 's.id', '=', 'i.subcategory_id')
            ->where('o.id', $post['id'])
            ->select(
                'o.id',
                'o.qty',
                'o.price',
                'p.username',
                'p.phone',
                'c.title as categorytitle',
                's.title as subcategorytitle',
                'i.title'
            )->first();

        return  $result;
    }
}
