<?php

namespace App\Models\BackPanel;

use App\Jobs\SendUserPasswordMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Driver extends Model
{
    ///function to get driver list
    public static function getDrivers($post)
    {
        try {
            $users = User::role('Driver')
                ->join('profiles', 'profiles.user_id', '=', 'users.id')
                ->where('profiles.status', 'Y')
                ->where('profiles.orgid', $post['orgid'])
                ->select(
                    'users.id as id',
                    DB::raw("CONCAT(profiles.first_name, ' ', COALESCE(profiles.middle_name, ''), ' ', profiles.last_name) as name"),
                )
                ->get();
            return $users;
        } catch (Exception $e) {
            throw $e;
        }
    }

    //function ro save driver
    public static function saveData($post)
    {
        try {
            if (empty($post['id'])) {
                $plainPassword = !empty($post['password'])
                    ? $post['password']
                    : Str::random(6);
                $imageName     = null;

                if (!empty($post['image']) && $post['image'] instanceof \Illuminate\Http\UploadedFile) {

                    $file = $post['image'];

                    $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                    $file->move(public_path('/storage/driver'), $imageName);
                }

                $newUuid  = (string) Str::uuid();
                $userData = [
                    'id'         => $newUuid,
                    'name'       => $post['username'],
                    'email'      => $post['email'],
                    'phone'      => $post['phone'],
                    'password' => bcrypt($plainPassword),
                    'created_at' => Carbon::now(),
                ];

                if (!DB::table('users')->insert($userData)) {
                    throw new Exception("Couldn't create user");
                }

                $firstOrg = $post['orgid'];


                DB::table('userorganizations')->insert([
                    'id'         => (string) Str::uuid(),
                    'userid'     => $newUuid,
                    'orgid'      => $firstOrg,
                    'created_at' => Carbon::now(),
                ]);

                $user = \App\Models\User::find($newUuid);

                $role = Role::findOrFail($post['roles']);
                $user->assignRole($role);

                $profileData = [
                    'id'                  => (string) Str::uuid(),
                    'user_id'             => $newUuid,
                    'username'            => $post['username'],
                    'first_name'          => $post['first_name'],
                    'middle_name'         => $post['middle_name'] ?? null,
                    'last_name'           => $post['last_name'],
                    'phone'               => $post['phone'],
                    'address'             => $post['address'],
                    'gender'              => $post['gender'],
                    'type'                => $post['type'],
                    'company_name'        => $post['company_name'] ?? null,
                    'tax_number'          => $post['tax_number'] ?? null,
                    'registration_number' => $post['registration_number'] ?? null,
                    'orgid'               => $firstOrg,
                    'created_at'          => Carbon::now(),
                ];

                if ($imageName) {
                    $profileData['image'] = $imageName;
                }

                DB::table('profiles')->insert($profileData);

                // SendUserPasswordMail::dispatch([
                //     'name' => $post['username'],
                //     'email' => $post['email'],
                //     'password' => $plainPassword,
                // ]);

            } else {
                $imageName = null;

                if (!empty($post['image']) && $post['image'] instanceof \Illuminate\Http\UploadedFile) {

                    $oldImage = DB::table('profiles')->where('user_id', $post['id'])->value('image');
                    if ($oldImage) {
                        $oldPath = public_path('/storage/driver/' . $oldImage);
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    $file      = $post['image'];
                    $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('/storage/driver'), $imageName);
                }

                $userData = [
                    'name'       => $post['username'],
                    'email'      => $post['email'],
                    'phone'      => $post['phone'],
                    'updated_at' => Carbon::now(),
                ];

                if (!DB::table('users')->where('id', $post['id'])->update($userData)) {
                    throw new Exception("Couldn't update user");
                }

                $profileData = [
                    'username'            => $post['username'],
                    'first_name'          => $post['first_name'],
                    'middle_name'         => $post['middle_name'] ?? null,
                    'last_name'           => $post['last_name'],
                    'phone'               => $post['phone'],
                    'address'             => $post['address'],
                    'gender'              => $post['gender'],
                    'type'                => $post['type'],
                    'company_name'        => $post['company_name'] ?? null,
                    'tax_number'          => $post['tax_number'] ?? null,
                    'registration_number' => $post['registration_number'] ?? null,
                    'updated_at'          => Carbon::now(),
                ];

                if ($imageName) {
                    $profileData['image'] = $imageName;
                }

                DB::table('profiles')->where('user_id', $post['id'])->update($profileData);
            }

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
