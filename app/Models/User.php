<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\BackPanel\NewsEvent;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\AssignOp\Concat;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\File;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',               // ✅ add id
        'name',
        'email',
        'password',
        'orgid',
        'provider',
        'provider_id',
        'provider_token',
    ];

    // public function post()
    // {
    //     return $this->hasMany(NewsEvent::class)->where('status', 'Y');
    // }
    // Required methods for JWT
    public function getJWTIdentifier()
    {
        return $this->getKey(); // usually the user ID
    }


    public function getJWTCustomClaims()
    {
        $profile = DB::table('profiles')
            ->where('user_id', $this->id)
            ->select('user_id', 'first_name')
            ->first();

        return [
            'roles' => $this->getRoleNames(),
            'profile' => $profile,
        ];
    }

    /* update password-start */
    public static function updatepassword($post)
    {
        try {
            $user = User::where('id', auth()->id())->first();

            if (!Hash::check($post['current_password'], $user->password)) {
                throw new Exception('The current password is incorrect.');
            }

            if ($post['password'] !== $post['confirm_password']) {
                throw new Exception('The new password and confirm password do not match.');
            }

            $user->password = Hash::make($post['password']);
            $user->first_time_login = 1;
            if (!$user->save()) {
                throw new Exception('Password is not updated.');
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    /* update password-end */


    /*update profile-start */
    public static function updatedata($post)
    {
        try {
            $updateArray = [
                'name' => $post['name'],
                // 'email' => $post['email'],
                'address' => $post['address'],
            ];

            $updateArray['updated_at'] = Carbon::now();

            if (!User::where('id', 1)->update($updateArray)) {
                throw new Exception("Couldn't Save Records", 1);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    /*update profile-end*/


    /*update profile image-start*/
    public static function saveProfileImage($post)
    {
        try {
            $dataArray = [];

            if (!empty($post['image'])) {
                $fileName =  Common::uploadFile('profile', $post['image']);
                if (!$fileName) {
                    return false;
                }
                $dataArray['image'] = $fileName;
            }

            $dataArray['updated_at'] = Carbon::now();
            if (!User::where('id', 1)->update($dataArray)) {
                throw new Exception("Couldn't update Records", 1);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }
    /*update profile image-end*/

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function saveData($post)
    {
        try {
            DB::beginTransaction();

            $plainPassword = Str::random(6);
            $imageName     = null; // ✅ Initialize to avoid undefined variable error

            // Handle image upload
            if (!empty($post['image']) && $post['image'] instanceof \Illuminate\Http\UploadedFile) {
                $file      = $post['image'];
                $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/profiles'), $imageName);
            }

            $userData = [
                'name'       => $post['username'],
                'email'      => $post['email'],
                'orgid'      => 'a20a5adb-1679-474d-a30c-3455d030d8e6',
                'updated_at' => Carbon::now(),
            ];

            if (!empty($post['id'])) {
                // ---------- UPDATE USER ----------
                $userId  = $post['id'];
                $oldData = DB::table('profiles')->where('user_id', $userId)->first(); // ✅ fetch from profiles

                // Delete old image if new one uploaded
                if ($imageName && $oldData && $oldData->image) {
                    $oldPath = public_path('uploads/profiles/' . $oldData->image);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }

                // Update users table
                DB::table('users')->where('id', $userId)->update($userData);

                // Update profile
                $profileData = [
                    'username'    => $post['username'],
                    'first_name'  => $post['first_name'],
                    'middle_name' => $post['middle_name'] ?? null,
                    'last_name'   => $post['last_name'],
                    'phone'       => $post['phone'],
                    'address'     => $post['address'],
                    'gender'      => $post['gender'],
                    'updated_at'  => Carbon::now(),
                ];

                // ✅ Only update image if new one was uploaded
                if ($imageName) {
                    $profileData['image'] = $imageName;
                }

                DB::table('profiles')->where('user_id', $userId)->update($profileData);

                // Update roles
                DB::table('model_has_roles')->where('model_id', $userId)->delete();
                // if (!empty($post['roles'])) {
                //     $roleData = [];
                //     foreach ($post['roles'] as $roleId) {
                //         $roleData[] = [
                //             'role_id'    => $roleId,
                //             'model_type' => 'App\\Models\\User',
                //             'model_id'   => $userId,
                //         ];
                //     }
                //     DB::table('model_has_roles')->insert($roleData);
                // }
            } else {
                // ---------- CREATE USER ----------
                $newUuid = (string) Str::uuid(); // ✅ Store UUID separately to reuse

                $userData['id']         = $newUuid;
                $userData['created_at'] = Carbon::now();
                $userData['password']   = Hash::make($plainPassword);

                $inserted = DB::table('users')->insert($userData); // ✅ insert() returns bool

                if (!$inserted) {
                    throw new Exception("Couldn't create user");
                }
                $newProfileId = (string) Str::uuid(); // ✅ Store UUID separately to reuse

                // Insert profile
                $profileData = [
                    'id'     => $newProfileId, // ✅ use stored UUID, not $userData['id'] after insert
                    'user_id'     => $newUuid, // ✅ use stored UUID, not $userData['id'] after insert
                    'username'    => $post['username'],
                    'first_name'  => $post['first_name'],
                    'middle_name' => $post['middle_name'] ?? null,
                    'last_name'   => $post['last_name'],
                    'phone'       => $post['phone'],
                    'address'     => $post['address'],
                    'gender'      => $post['gender'],
                    'created_at'  => Carbon::now(),
                    'updated_at'  => Carbon::now(),
                    'orgid'      => 'a20a5adb-1679-474d-a30c-3455d030d8e6',

                ];

                if ($imageName) {
                    $profileData['image'] = $imageName;
                }

                DB::table('profiles')->insert($profileData);
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function list($post)
    {
        try {
            $get = $post;
            foreach ($get as $key => $value) {
                $get[$key] = trim(strtolower(htmlspecialchars($get[$key], ENT_QUOTES)));
            }
            $cond = " status = 'Y'";

            // if (!empty($post['type']) && $post['type'] === "trashed") {
            //     $cond = " status = 'N'";
            // }


            if ($get['sSearch_1']) {
                $cond .= "and lower(users.name) like'%" . $get['sSearch_1'] . "%'";
            }

            if ($get['sSearch_3']) {
                $cond .= "and lower(users.email) like'%" . $get['sSearch_3'] . "%'";
            }
            $limit = 15;
            $offset = 0;
            if (!empty($get["length"]) && $get["length"]) {
                $limit = $get['length'];
                $offset = $get["start"];
            }

            $query = User::selectRaw("
        (SELECT count(*) FROM users) AS totalrecs,
        users.id,
        users.user_status,
        users.name,
        users.email,
        profiles.first_name,
        profiles.middle_name,
        profiles.last_name,
        profiles.username,
        profiles.gender,
        profiles.phone,
        profiles.address,
        profiles.image,
        profiles.status
    ")
                ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
                ->whereRaw($cond);

            if ($limit > -1) {
                $result = $query->orderBy('users.id', 'asc')->offset($offset)->limit($limit)->get();
            } else {
                $result = $query->orderBy('users.id', 'asc')->get();
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
        // Fetch user and profile info
        $result = DB::table('users as u')
            ->join('profiles as p', 'p.user_id', '=', 'u.id')
            ->select(
                'u.id as id',
                'u.name as username',
                'u.email',
                'p.first_name',
                'p.middle_name',
                'p.last_name',
                'p.username as profile_username',
                'p.gender',
                'p.phone',
                'p.address',
                'p.image',
                'p.status'
            )
            ->where('u.id', $post['id'])
            ->first();

        if ($result) {
            // Get role IDs
            $userRoles = DB::table('model_has_roles')
                ->where('model_id', $result->id)
                ->pluck('role_id')
                ->toArray();

            // Attach role IDs
            $result->roles = $userRoles;

            // Get role names
            $roleNames = DB::table('roles')
                ->whereIn('id', $userRoles)
                ->pluck('name')
                ->toArray();

            // Attach role names
            $result->role_names = $roleNames; // <-- now you can use $user->role_names in Blade
        }

        return $result;
    }
    public static function getUserData($post)
    {
        try {
            $data = DB::table('users as u')
                ->join('profiles as p', 'p.user_id', '=', 'u.id')
                ->where('p.status', 'Y')
                ->where('p.orgid', $post['orgid'])
                ->select(
                    'u.id',
                    DB::raw("CONCAT(p.first_name, ' ', p.middle_name, ' ', p.last_name) as username")
                )
                ->get();

            return $data;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
