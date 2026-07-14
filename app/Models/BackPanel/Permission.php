<?php

namespace App\Models\BackPanel;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Permission extends SpatiePermission
{
    use HasFactory;
    public $incrementing = false;
    protected $keyType = 'string';

    //function to save permission
    public static function saveData($post)
    {
        try {
            if (!empty($post['id'])) {
                // ── UPDATE ────────────────────────────────────────────
                $dataArray = [
                    'name'       => $post['name'],
                    'guard_name' => 'web',
                ];

                //  update() returns rows affected (0 = nothing changed, still OK)
                DB::table('permissions')->where('id', $post['id'])->update($dataArray);
            } else {
                // ── INSERT ────────────────────────────────────────────
                $dataArray = [
                    'id'         => (string) Str::uuid(),  // ✅ moved here, only for insert
                    'name'       => $post['name'],
                    'guard_name' => 'web',
                ];

                //  use insert(), not where()->insert()
                $inserted = DB::table('permissions')->insert($dataArray);

                if (!$inserted) {
                    throw new Exception("Couldn't save permission", 1);
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
            $cond = "status = 'Y'";

            if ($get['columns'][1]['search']['value'])
                $cond .= " and lower(name) like '%" . $get['columns'][1]['search']['value'] . "%'";


            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = Permission::selectRaw("(SELECT count(*) FROM permissions WHERE {$cond}) AS totalrecs,name, id as id")
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


    // after — renamed, no longer collides with Spatie's internal getPermission()
    public static function getPermissionList()
    {
        $result = DB::table('permissions')->select('id', 'name')->get();
        return $result;
    }
}