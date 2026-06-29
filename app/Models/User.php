<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\File;
use App\Jobs\SendUserPasswordMail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles, HasUuids;

    public $incrementing = false;
    protected $keyType   = 'string';

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'orgid',
        'provider',
        'provider_id',
        'provider_token',
        'first_time_login',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // public function getJWTCustomClaims()
    // {
    //     $profile = DB::table('userorganizations')
    //         ->where('userid', $this->id)
    //         ->select('userid', 'orgid')
    //         ->first();

    //     return [
    //         'roles'   => $this->getRoleNames(),
    //         'profile' => $profile,
    //     ];
    // }
    public function getJWTCustomClaims()
{
    try {
        $profile = DB::table('userorganizations')
            ->where('userid', $this->id)
            ->select('userid', 'orgid')
            ->first();

        return [
            'roles'   => $this->getRoleNames(),
            'profile' => $profile,
        ];
    } catch (\Exception $e) {
        $profile = DB::table('userorganizations')
            ->where('userid', $this->id)
            ->select('userid', 'orgid')
            ->first();

        return [
            'roles'   => [],
            'profile' => $profile,
        ];
    }
}

    /* ── Helper: store a single image file ───────────────────── */
    private static function storeImage($file, string $folder = 'profiles', string $prefix = ''): string
    {
        $fileName = time() . ($prefix ? '_' . $prefix : '') . '_' . Str::random(10)
            . '.' . $file->getClientOriginalExtension();
        $file->storeAs($folder, $fileName, 'public');
        return $fileName;
    }

    /* ── Helper: delete an image file safely ─────────────────── */
    private static function deleteImage(string $folder, ?string $fileName): void
    {
        if (!$fileName) return;
        $path = storage_path('app/public/' . $folder . '/' . $fileName);
        if (File::exists($path)) File::delete($path);
    }

    /* ── update password ──────────────────────────────────────── */
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

            $user->password         = Hash::make($post['password']);
            $user->first_time_login = 1;

            if (!$user->save()) {
                throw new Exception('Password is not updated.');
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ── update profile info ──────────────────────────────────── */
    public static function updatedata($post)
    {
        // try {
        DB::table('users')->where('id', auth()->id())->update([
            'name'       => $post['name'],
            'updated_at' => Carbon::now(),
        ]);

        $profile = DB::table('profiles')
            ->join('users', 'users.id', '=', 'profiles.user_id')
            ->where('users.email', auth()->user()->email)
            ->select('profiles.id')
            ->first();

        if ($profile) {
            DB::table('profiles')->where('id', $profile->id)->update([
                'address'    => $post['address'],
                'user_id'    => auth()->id(),
                'updated_at' => Carbon::now(),
            ]);
        }

        return true;
        // } catch (Exception $e) {
        //     throw $e;
        // }
    }

    /* ── update profile image ─────────────────────────────────── */
    public static function saveProfileImage($post)
    {
        // try {
        if (empty($post['image']) || !($post['image'] instanceof \Illuminate\Http\UploadedFile)) {
            return true;
        }

        $profile = DB::table('profiles')
            ->join('users', 'users.id', '=', 'profiles.user_id')
            ->where('users.email', auth()->user()->email)
            ->select('profiles.*')
            ->first();

        if (!$profile) {
            $profile = DB::table('profiles')->where('user_id', auth()->id())->first();
        }

        if (!$profile) {
            $newProfileId = (string) Str::uuid();
            DB::table('profiles')->insert([
                'id'         => $newProfileId,
                'user_id'    => auth()->id(),
                'username'   => auth()->user()->name,
                'first_name' => auth()->user()->name,
                'last_name'  => '',
                'status'     => 'Y',
                'orgid'      => session('orgid'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $profile = DB::table('profiles')->where('id', $newProfileId)->first();
        }

        self::deleteImage('profiles', $profile->image);

        $fileName = self::storeImage($post['image']);

        DB::table('profiles')->where('id', $profile->id)->update([
            'image'      => $fileName,
            'user_id'    => auth()->id(),
            'updated_at' => Carbon::now(),
        ]);

        return true;
        // } catch (Exception $e) {
        //     throw $e;
        // }
    }

    /* ── save new user ────────────────────────────────────────── */
    public static function saveData($post)
    {
        try {
            DB::beginTransaction();

            $plainPassword = !empty($post['password']) ? $post['password'] : Str::random(6);

            // Profile image
            $imageName = null;
            if (!empty($post['image']) && $post['image'] instanceof \Illuminate\Http\UploadedFile) {
                $imageName = self::storeImage($post['image']);
            }

            // PAN image
            $panImage = null;
            if (!empty($post['pan_image']) && $post['pan_image'] instanceof \Illuminate\Http\UploadedFile) {
                $panImage = self::storeImage($post['pan_image'], 'profiles', 'pan');
            }

            // Registration number image
            $registrationImage = null;
            if (!empty($post['registration_number_image']) && $post['registration_number_image'] instanceof \Illuminate\Http\UploadedFile) {
                $registrationImage = self::storeImage($post['registration_number_image'], 'profiles', 'reg');
            }

            $newUuid  = (string) Str::uuid();
            $userData = [
                'id'         => $newUuid,
                'name'       => $post['username'],
                'email'      => $post['email'],
                'phone'      => $post['phone'],
                'password'   => bcrypt($plainPassword),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            if (!empty($post['type']) && $post['type'] == 'user') {
                $userData['user_status'] = 'Approve';
            }

            if (!DB::table('users')->insert($userData)) {
                throw new Exception("Couldn't create user");
            }

            $firstOrg = ($post['type'] == 'user')
                ? $post['orgid']
                : DB::table('userorganizations')->value('orgid');

            if (!$firstOrg) {
                throw new Exception("No organization found.");
            }

            DB::table('userorganizations')->insert([
                'id'         => (string) Str::uuid(),
                'userid'     => $newUuid,
                'orgid'      => $firstOrg,
                'created_at' => Carbon::now(),
            ]);

            $user = User::find($newUuid);

            // Assign roles — support array of role IDs from admin form
            if (!empty($post['roles']) && is_array($post['roles'])) {
                $roleNames = DB::table('roles')
                    ->whereIn('id', $post['roles'])
                    ->pluck('name')
                    ->toArray();
                if (!empty($roleNames)) {
                    $user->syncRoles($roleNames);
                }
            } elseif (!empty($post['role'])) {
                $user->assignRole($post['role']);
            }

            if (!empty($post['type'])) {
                $post['role'] = $post['type'] == 'retailer' ? '550e8400-e29b-41d4-a716-446655440003' : '550e8400-e29b-41d4-a716-446655440002';
            }


            $profileData = [
                'id'                         => (string) Str::uuid(),
                'user_id'                    => $newUuid,
                'username'                   => $post['username'],
                'first_name'                 => $post['first_name'],
                'middle_name'                => $post['middle_name'] ?? null,
                'last_name'                  => $post['last_name'],
                'phone'                      => $post['phone'],
                'address'                    => $post['address'],
                'gender'                     => $post['gender'],
                'type'                       => $post['type'],
                'status'              => 'Y',             // ← add this
                'company_name'               => $post['company_name'] ?? null,
                'tax_number'                 => $post['tax_number'] ?? null,
                'pan_number'                 => $post['pan_number'] ?? null,
                'registration_number'        => $post['registration_number'] ?? null,
                'orgid'                      => $firstOrg,
                'created_at'                 => Carbon::now(),
                'updated_at'                 => Carbon::now(),
            ];

            if ($imageName)        $profileData['image']                     = $imageName;
            if ($panImage)         $profileData['pan_image']                 = $panImage;
            if ($registrationImage) $profileData['registration_number_image'] = $registrationImage;

            DB::table('profiles')->insert($profileData);

            DB::commit();

            SendUserPasswordMail::dispatch([
                'name'     => $post['username'],
                'email'    => $post['email'],
                'password' => $plainPassword,
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /* ── update user (admin panel) ───────────────────────────── */
    public static function updateUser($post)
    {
        try {
            if (empty($post['userid'])) {
                throw new Exception("User ID is required");
            }

            $userId  = $post['userid'];
            $oldData = DB::table('profiles')->where('user_id', $userId)->first();

            // Profile image
            $imageName = null;
            if (!empty($post['image']) && $post['image'] instanceof \Illuminate\Http\UploadedFile) {
                self::deleteImage('profiles', $oldData->image ?? null);
                $imageName = self::storeImage($post['image']);
            }

            // PAN image
            $panImage = null;
            if (!empty($post['pan_image']) && $post['pan_image'] instanceof \Illuminate\Http\UploadedFile) {
                self::deleteImage('profiles', $oldData->pan_image ?? null);
                $panImage = self::storeImage($post['pan_image'], 'profiles', 'pan');
            }

            // Registration image
            $registrationImage = null;
            if (!empty($post['registration_number_image']) && $post['registration_number_image'] instanceof \Illuminate\Http\UploadedFile) {
                self::deleteImage('profiles', $oldData->registration_number_image ?? null);
                $registrationImage = self::storeImage($post['registration_number_image'], 'profiles', 'reg');
            }

            DB::table('users')->where('id', $userId)->update([
                'name'       => $post['username'] ?? '',
                'phone'      => $post['phone'] ?? '',

                'updated_at' => Carbon::now(),
            ]);

            $profileData = [
                'username'                   => $post['username'] ?? '',
                'first_name'                 => $post['first_name'] ?? '',
                'middle_name'                => $post['middle_name'] ?? null,
                'last_name'                  => $post['last_name'] ?? '',
                'phone'                      => $post['phone'] ?? '',
                'address'                    => $post['address'] ?? '',
                'gender'                     => $post['gender'] ?? '',
                'company_name'               => $post['company_name'] ?? null,
                'tax_number'                 => $post['tax_number'] ?? null,
                'registration_number'        => $post['registration_number'] ?? null,
                'pan_number'                 => $post['pan_number'] ?? null,
                'updated_at'                 => Carbon::now(),
            ];

            if ($imageName)         $profileData['image']                     = $imageName;
            if ($panImage)          $profileData['pan_image']                 = $panImage;
            if ($registrationImage) $profileData['registration_number_image'] = $registrationImage;

            return DB::table('profiles')->where('user_id', $userId)->update($profileData);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ── user list ────────────────────────────────────────────── */
    public static function list($post)
    {
        try {
            $get = $post;
            foreach ($get as $key => $value) {
                $get[$key] = is_string($value) ? trim(strtolower($value)) : $value;
            }

            $limit  = !empty($get["length"]) ? (int)$get["length"] : 15;
            $offset = !empty($get["start"])  ? (int)$get["start"]  : 0;

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
                ->leftJoin('model_has_roles as mhr', function ($join) {
                    $join->on('mhr.model_id', '=', 'users.id')
                        ->where('mhr.model_type', '=', User::class);
                })
                ->where('profiles.status', 'Y')
                ->where('u.orgid', $post['orgid']);

            if (!empty($post['inactiveuser']) && $post['inactiveuser'] == 'Y') {
                $query->where('users.user_status', '!=', 'Approve');
            } else if (empty($post['inactiveuser'])) {
                $query->where('users.user_status', '=', 'Approve');
            }

            if (!empty($post['role']) && ($post['role'] == '550e8400-e29b-41d4-a716-446655440004')) {
                $query->where('mhr.role_id', '550e8400-e29b-41d4-a716-446655440004');
            }

            if (!empty($get['sSearch_1'])) {
                $query->whereRaw('LOWER(users.name) LIKE ?', ['%' . $get['sSearch_1'] . '%']);
            }

            if (!empty($get['sSearch_2'])) {
                $query->whereRaw('LOWER(users.email) LIKE ?', ['%' . $get['sSearch_2'] . '%']);
            }

            $total = (clone $query)->count();

            $result = $limit > -1
                ? $query->orderBy('users.id', 'asc')->offset($offset)->limit($limit)->get()
                : $query->orderBy('users.id', 'asc')->get();

            return [
                'data'              => $result,
                'totalrecs'         => $total,
                'totalfilteredrecs' => $total,
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ── get single user data ─────────────────────────────────── */
    public static function getData($post)
    {
        $result = DB::table('users as u')
            ->join('profiles as p', 'p.user_id', '=', 'u.id')
            ->join('userorganizations as uo', 'uo.userid', '=', 'u.id')
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
            ->where('uo.orgid', $post['orgid'])
            ->where('u.id', $post['id'])
            ->first();

        if ($result) {
            $userRoles          = DB::table('model_has_roles')->where('model_id', $result->id)->pluck('role_id')->toArray();
            $result->roles      = $userRoles;
            $result->role_names = DB::table('roles')->whereIn('id', $userRoles)->pluck('name')->toArray();
        }

        return $result;
    }

    /* ── get user data (dropdown) ─────────────────────────────── */
    public static function getUserData($post)
    {
        try {
            return DB::table('users as u')
                ->join('profiles as p', 'p.user_id', '=', 'u.id')
                ->where('p.status', 'Y')
                ->where('p.orgid', $post['orgid'])
                ->select('u.id', DB::raw("CONCAT(p.first_name, ' ', p.middle_name, ' ', p.last_name) as username"))
                ->get();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ── get data user (API/frontend) ────────────────────────── */
    public static function getDataUser($post)
    {
        try {
            return DB::table('users as u')
                ->leftJoin('profiles as p', 'p.user_id', '=', 'u.id')
                ->select(
                    'u.name as username',
                    'u.email',
                    'p.first_name',
                    'p.middle_name',
                    'p.last_name',
                    'p.username as profile_username',
                    'p.gender',
                    'p.phone',
                    'p.address',
                    DB::raw("CONCAT('" . url('storage/profiles') . "/', p.image) as image"),
                    'p.status'
                )
                ->where('u.id', $post['userid'])
                ->first();
        } catch (Exception $e) {
            throw $e;
        }
    }
}
