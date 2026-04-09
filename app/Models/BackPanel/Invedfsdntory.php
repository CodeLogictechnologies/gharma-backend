<?php

namespace App\Models;

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
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

            $query = DB::table('orders as o')
                ->join('profiles as p',        'p.id',  '=', 'o.customer_id')
                ->join('itemvariations as iv', 'iv.id', '=', 'o.variation_id')
                ->join('items as i',           'i.id',  '=', 'o.item_id')
                ->join('categories as c',      'c.id',  '=', 'i.category_id')
                ->join('sub_categories as s',  's.id',  '=', 'i.subcategory_id')
                ->selectRaw("
        (SELECT COUNT(*) FROM orders as o2
            JOIN itemvariations iv2 ON iv2.id = o2.variation_id
            JOIN items i2           ON i2.id  = o2.item_id
            JOIN categories c2      ON c2.id  = i2.category_id
            JOIN sub_categories s2  ON s2.id  = i2.subcategory_id
            JOIN profiles p2        ON p2.id  = o2.customer_id
            WHERE {$cond}
        ) as totalrecs,
        o.id,
        o.qty          as soldqty,
        o.price,
        p.username,
        p.phone,
        c.title        as categorytitle,
        s.title        as subcategorytitle,
        i.title,
        iv.attribute,
        iv.stock,
        (iv.stock - o.qty) as remainingqty
    ")->whereRaw($cond);


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
        try {
            $id = $post['id'] ?? 0;

            $result = DB::table('orders as o')
                ->join('profiles as p', 'p.id', '=', 'o.customer_id')
                ->join('itemvariations as iv', 'iv.id', '=', 'o.variation_id')
                ->join('items as i', 'i.id', '=', 'o.item_id')
                ->join('categories as c', 'c.id', '=', 'i.category_id')
                ->join('sub_categories as s', 's.id', '=', 'i.subcategory_id')
                ->where('o.id', $id)
                ->select(
                    'o.id',
                    'o.qty as soldqty',
                    'o.price',
                    'p.username',
                    'p.phone',
                    'c.title as categorytitle',
                    's.title as subcategorytitle',
                    'i.title',
                    'iv.attribute',
                    'iv.stock',
                    DB::raw('(iv.stock - o.qty) as remainingqty')
                )
                ->first();

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
