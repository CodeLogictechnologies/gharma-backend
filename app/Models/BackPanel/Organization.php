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

use Illuminate\Support\Facades\File;

class Organization extends Model
{

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    //function to save organization
    public static function saveData($post)
    {
        try {

            $dataArray = [
                'name'    => $post['name'],
                'phone'   => $post['phone'],
                'address' => $post['address'],
                'email'   => $post['email'],
            ];

            $imageName = null;

            if (!empty($post['image'])) {
                $file = $post['image'];
                $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/organizations'), $imageName);
            }
            if ($imageName) {
                $dataArray['logo'] = $imageName;
            }

            if (!empty($post['id'])) {
                $oldData = Organization::find($post['id']);

                // Delete old image if new uploaded
                if ($imageName && $oldData && $oldData->image) {
                    $oldPath = public_path('uploads/organizations/' . $oldData->image);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
                $dataArray['updated_at'] = Carbon::now();

                $organization = DB::table('organizations')
                    ->where('id', $post['id'])
                    ->update($dataArray);

                if (!$organization) {
                    throw new Exception("Couldn't update organization", 1);
                }

                $user = DB::table('users')
                    ->where('id', $post['userid'])
                    ->update([
                        'email'      => $post['email'],
                        'name'   => $post['username'],
                        'updated_at' => Carbon::now(),
                    ]);

                if (!$user) {
                    throw new Exception("Couldn't update user", 1);
                }
            } else {

                $plainPassword = Str::random(6);
                $dataArray['id'] = (string) Str::uuid();

                $dataArray['created_at'] = Carbon::now();
                $dataArray['updated_at'] = Carbon::now();

                DB::table('organizations')->insert($dataArray);

                $orgId = $dataArray['id'];

                if (!$orgId) {
                    throw new Exception("Couldn't save organization", 1);
                }
                $post['userid'] = (string) Str::uuid();

                $userArray = [
                    'id'      => $post['userid'],
                    'email'      => $post['email'],
                    'name'       => $post['name'],
                    'password'   => Hash::make($plainPassword),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];

                $user = DB::table('users')->insert($userArray);

                $post['userorgid'] = (string) Str::uuid();


                if (!$user) {
                    throw new Exception("Couldn't save user", 1);
                }

                $userOrgArray = [
                    'id'      => $post['userorgid'],
                    'userid'      => $post['userid'],
                    'orgid'      => $orgId,
                    'created_at' => Carbon::now(),
                ];

                $user = DB::table('userorganizations')->insert($userOrgArray);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    //function got list of organizations
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

            if ($get['sSearch_3']) {
                $cond .= "and lower(email) like'%" . $get['sSearch_3'] . "%'";
            }


            if ($get['sSearch_2']) {
                $cond .= "and lower(phone) like'%" . $get['sSearch_2'] . "%'";
            }


            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = Organization::selectRaw("(SELECT count(*) FROM organizations where {$cond}) AS totalrecs,name,email, id as id, phone, address, logo,active, created_at")
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


    //function to get data of an organization
    public static function getData($post)
    {
        try {
            $result = DB::table('organizations as o')
                ->join('userorganizations as uo', 'uo.orgid', '=', 'o.id')
                ->join('users as u', 'u.id', '=', 'uo.userid')
                ->select('o.id as id', 'u.id as userid', 'o.address', 'o.name', 'o.email', 'o.phone', 'o.logo', 'u.name as username')
                ->where('o.id', $post['id'])
                ->first();
            return  $result;
        } catch (Exception $e) {
            throw $e;
        }
    }

    //function to delete an organization
    public static function deleteDate($post)
    {
        try {
            $updateArray = [
                'status' => 'N',
                'updated_at' => Carbon::now(),
            ];
            if (!Organization::where(['id' => $post['id']])->update($updateArray)) {
                throw new Exception("Couldn't Delete Data. Please try again", 1);
            }
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
}