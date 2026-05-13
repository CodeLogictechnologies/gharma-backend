<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'guard_name',
        'order_number',
        'status',
    ];



    //function to save role
    public static function saveData($post)
    {
        try {
            if (!empty($post['id'])) {

                $role = Role::findOrFail($post['id']);
                $role->update([
                    'name'         => $post['name'],
                    'guard_name'   => 'web',
                    'updated_at'   => Carbon::now(),
                ]);
            } else {
                $role = Role::create([
                    'name'         => $post['name'],
                    'guard_name'   => 'web',
                    'created_at'   => Carbon::now(),
                ]);
            }

            if (!empty($post['permissions'])) {

                $permissions = Permission::whereIn('id', $post['permissions'])
                    ->pluck('name')
                    ->toArray();

                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    //function to list role
    public static function list($post)
    {
        try {
            $get = $post;
            $sorting = !empty($get['order'][0]['dir']) ? $get['order'][0]['dir'] : 'asc';
            // $orderby = " order_number " . $sorting;

            foreach ($get['columns'] as $key => $value) {
                $get['columns'][$key]['search']['value'] = trim(strtolower(htmlspecialchars($value['search']['value'], ENT_QUOTES)));
            }

            $cond = " status = 'Y'";

            if ($get['columns'][1]['search']['value'])
                $cond .= " and lower(name) like '%" . $get['columns'][1]['search']['value'] . "%'";

            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = Role::with('permissions')
                ->selectRaw("(SELECT count(*) FROM roles WHERE {$cond}) AS totalrecs, name, id")
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
        } catch (Exception $e) {
            throw $e;
        }
    }

    public static function getRole()
    {
        try {
            $roles = DB::table('roles')->get();
            return $roles;
        } catch (Exception $e) {
            throw $e;
        }
    }
}