<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Facades\DB;

class Permission extends Model
{
    use HasFactory;

    //function to save permission
    public static function saveData($post)
    {
        try {
            $dataArray = [
                'name' => $post['name'],
                'guard_name' => 'web',
            ];

            if (!empty($post['id'])) {
                $permission = DB::table('permissions')->where('id', $post['id'])->update($dataArray);
                if (!$permission) {
                    throw new Exception("Couldn't update permissions", 1);
                }
            } else {
                $permission = DB::table('permissions')->where('id', $post['id'])->insert($dataArray);
                if (!$permission) {
                    throw new Exception("Couldn't save permissions", 1);
                }
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    //function to list permission
    public static function list($post)
    {
        try {
            $get = $post;
            foreach ($get['columns'] as $key => $value) {
                $get['columns'][$key]['search']['value'] = trim(strtolower(htmlspecialchars($value['search']['value'], ENT_QUOTES)));
            }
            $cond = "1=1";

            if ($get['columns'][1]['search']['value'])
                $cond .= " and lower(name) like '%" . $get['columns'][1]['search']['value'] . "%'";


            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = Permission::selectRaw("(SELECT count(*) FROM permissions) AS totalrecs,name, id as id")
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


    public static function getPermission()
    {
        $result = DB::table('permissions')->select('id', 'name')->get();

        return $result;
    }
}