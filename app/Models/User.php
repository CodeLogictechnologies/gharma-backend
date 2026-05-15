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
        $profile = DB::table('userorganizations')
            ->where('userid', $this->id)
            ->select('userid', 'orgid')
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
            $imageName     = null;
            // Handle image upload
            if (!empty($post['image']) && $post['image'] instanceof \Illuminate\Http\UploadedFile) {
                $file      = $post['image'];
                $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/profiles'), $imageName);
            }

            $userData = [
                'name'       => $post['username'],
                'email'      => $post['email'],
                'phone'      => $post['phone'],
                'password'   => bcrypt($plainPassword),
                'updated_at' => Carbon::now(),
            ];


            // ---------- CREATE USER ----------
            $newUuid = (string) Str::uuid();

            $userData['id']         = $newUuid;
            $userData['created_at'] = Carbon::now();
            if (!empty($post['type']) && $post['type'] == 'user') {
                $userData['user_status'] = 'Approve';
            }
            $inserted = DB::table('users')->insert($userData);

            if (!$inserted) {
                throw new Exception("Couldn't create user");
            }
            $post['userorgid'] = (string) Str::uuid();

            if (!empty($post['type'] == 'user')) {
                $firstOrg = $post['orgid'];
            } else {
                $firstOrg = DB::table('userorganizations')->value('orgid');

                if (!$firstOrg) {
                    throw new Exception("No organization found in userorganizations table.");
                }
            }

            $userOrgArray = [
                'id'         => $post['userorgid'],
                'userid'     => $newUuid,
                'orgid'      => $firstOrg,
                'created_at' => Carbon::now(),
            ];


            $user = DB::table('userorganizations')->insert($userOrgArray);

            if (!empty($post['type'])) {
                $user = \App\Models\User::find($newUuid);
                if ($post['type'] == 'retailer') {
                    $post['role'] = 1;
                } else {
                    $post['role'] = 2;
                }
            }
            // Assign role
            $user->assignRole($post['role']); // role name or role ID (if configured)

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
                'type'      => $post['type'],
                'company_name' => $post['company_name'] ?? null,
                'tax_number' => $post['tax_number'] ?? null,
                'registration_number' => $post['registration_number'] ?? null,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
                'orgid'      => 'fef1a6d6-72c6-4591-8f25-e1f477ad2c58',

            ];

            if ($imageName) {
                $profileData['image'] = $imageName;
            }

            DB::table('profiles')->insert($profileData);


            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    //function to get user list
    public static function list($post)
    {
        try {
            $get = $post;

            foreach ($get as $key => $value) {
                $get[$key] = trim(strtolower($value));
            }

            $limit  = !empty($get["length"]) ? (int)$get["length"] : 15;
            $offset = !empty($get["start"]) ? (int)$get["start"] : 0;

            $query = User::query()
                ->select(
                    'users.id',
                    'users.user_status',
                    'users.name',
                    'users.email',
                    'profiles.first_name',
                    'profiles.middle_name',
                    'profiles.last_name',
                    'profiles.username',
                    'profiles.gender',
                    'profiles.phone',
                    'profiles.address',
                    'profiles.image',
                    'profiles.type',
                    'profiles.status'
                )
                ->join('profiles', 'profiles.user_id', '=', 'users.id')
                ->join('userorganizations as u', 'u.userid', '=', 'users.id')
                ->where('u.orgid', $post['orgid']);

            // ✅ Status condition
            if (!empty($post['inactiveuser']) && $post['inactiveuser'] == 'Y') {
                $query->where('users.user_status', '!=', 'Approve');
            } else {
                $query->where('users.user_status', '=', 'Approve');
            }

            // ✅ Search filters (safe)
            if (!empty($get['sSearch_1'])) {
                $query->whereRaw('LOWER(users.name) LIKE ?', ['%' . $get['sSearch_1'] . '%']);
            }

            if (!empty($get['sSearch_2'])) {
                $query->whereRaw('LOWER(users.email) LIKE ?', ['%' . $get['sSearch_2'] . '%']);
            }

            // ✅ Clone query for count
            $total = (clone $query)->count();

            // ✅ Pagination
            if ($limit > -1) {
                $result = $query->orderBy('users.id', 'asc')
                    ->offset($offset)
                    ->limit($limit)
                    ->get();
            } else {
                $result = $query->orderBy('users.id', 'asc')->get();
            }

            // ✅ Proper return structure
            return [
                'data' => $result,
                'totalrecs' => $total,
                'totalfilteredrecs' => $total
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // Fetch user and profile info
    public static function getData($post)
    {
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
            ->where('u.orgid', $post['orgid'])
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
            $result->role_names = $roleNames;
        }

        return $result;
    }

    //function to get user data
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

    //funciton to get user data
    public static function getDataUser($post)
    {
        try {
            $result = DB::table('users as u')
                ->leftJoin('profiles as p', 'p.user_id', '=', 'u.id')
                ->select(
                    // 'u.id as id',
                    'u.name as username',
                    'u.email',
                    'p.first_name',
                    'p.middle_name',
                    'p.last_name',
                    'p.username as profile_username',
                    'p.gender',
                    'p.phone',
                    'p.address',
                    DB::raw("CONCAT('" . url('uploads/profiles') . "/', p.image) as image"),
                    'p.status'
                )
                ->where('u.id', $post['userid'])
                ->first();

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }

    //function to update user
    public static function updateUser($post)
    {

        try {

            $imageName = null;

            // ── upload image ─────────────────────────────
            if (!empty($post['image']) && $post['image'] instanceof \Illuminate\Http\UploadedFile) {

                $file = $post['image'];

                $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();

                $file->move(public_path('uploads/profiles'), $imageName);
            }

            if (empty($post['userid'])) {
                throw new Exception("User ID is required");
            }

            $userId = $post['userid'];

            // ── update users table ───────────────────────
            $userData = [
                'name'       => $post['username'] ?? '',
                'email'      => $post['email'] ?? '',
                'updated_at' => Carbon::now(),
            ];

            DB::table('users')->where('id', $userId)->update($userData);

            // ── get old profile ─────────────────────────
            $oldData = DB::table('profiles')->where('user_id', $userId)->first();

            if ($imageName && $oldData && $oldData->image) {
                $oldPath = public_path('uploads/profiles/' . $oldData->image);

                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            // ── update profile ───────────────────────────
            $profileData = [
                'username'    => $post['username'] ?? '',
                'first_name'  => $post['first_name'] ?? '',
                'middle_name' => $post['middle_name'] ?? null,
                'last_name'   => $post['last_name'] ?? '',
                'phone'       => $post['phone'] ?? '',
                'address'     => $post['address'] ?? '',
                'gender'      => $post['gender'] ?? '',
                'updated_at'  => Carbon::now(),
            ];

            if ($imageName) {
                $profileData['image'] = $imageName;
            }

            $result = DB::table('profiles')->where('user_id', $userId)->update($profileData);

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
