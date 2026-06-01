<?php

namespace App\Models;

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
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendUserPasswordMail;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'orgid',
        'provider',
        'provider_id',
        'provider_token',
    ];

    // Required methods for JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        $profile = DB::table('userorganizations')
            ->where('userid', $this->id)
            ->select('userid', 'orgid')
            ->first();

        return [
            'roles'   => $this->getRoleNames(),
            'profile' => $profile,
        ];
    }

    /* ── Helper: get profile row by auth user email ─────────────
     * This bypasses any user_id mismatch by joining on email.
     * Also auto-fixes the broken user_id so future calls work.
     */
    private static function getAuthProfile()
    {
        $authId    = auth()->id();
        $authEmail = auth()->user()->email;

        $profile = DB::table('profiles')
            ->join('users', 'users.email', '=', DB::raw("'" . $authEmail . "'"))
            ->where('profiles.username', auth()->user()->name)
            ->select('profiles.*')
            ->first();

        // Fallback: try direct user_id match first (for correctly linked accounts)
        if (!$profile) {
            $profile = DB::table('profiles')->where('user_id', $authId)->first();
        }

        // Auto-fix broken user_id if found via email but user_id is wrong
        if ($profile && $profile->user_id !== $authId) {
            DB::table('profiles')
                ->where('id', $profile->id)
                ->update(['user_id' => $authId]);
            $profile->user_id = $authId;
        }

        return $profile;
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

            $user->password        = Hash::make($post['password']);
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
        try {
            // Update users table
            DB::table('users')->where('id', auth()->id())->update([
                'name'       => $post['name'],
                'updated_at' => Carbon::now(),
            ]);

            // ✅ Find profile safely (handles user_id mismatch)
            $profile = DB::table('profiles')
                ->join('users', 'users.id', '=', 'profiles.user_id')
                ->where('users.email', auth()->user()->email)
                ->select('profiles.id')
                ->first();

            if ($profile) {
                DB::table('profiles')->where('id', $profile->id)->update([
                    'address'    => $post['address'],
                    'user_id'    => auth()->id(), // ✅ auto-fix mismatch
                    'updated_at' => Carbon::now(),
                ]);
            }

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /* ── update profile image ─────────────────────────────────── */
    public static function saveProfileImage($post)
    {
        try {
            if (empty($post['image']) || !($post['image'] instanceof \Illuminate\Http\UploadedFile)) {
                return true;
            }

            $file     = $post['image'];
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profiles', $fileName, 'public');

            // ✅ Find profile by email join (bypasses user_id mismatch)
            $profile = DB::table('profiles')
                ->join('users', 'users.id', '=', 'profiles.user_id')
                ->where('users.email', auth()->user()->email)
                ->select('profiles.*')
                ->first();

            if (!$profile) {
                // Last resort: try direct user_id match
                $profile = DB::table('profiles')->where('user_id', auth()->id())->first();
            }

            if (!$profile) {
                throw new Exception('Profile not found.');
            }

            // ✅ Delete old image
            if ($profile->image) {
                $oldPath = storage_path('app/public/profiles/' . $profile->image);
                if (File::exists($oldPath)) File::delete($oldPath);
            }

            // ✅ Update by profiles.id (safe) and fix user_id mismatch
            DB::table('profiles')->where('id', $profile->id)->update([
                'image'      => $fileName,
                'user_id'    => auth()->id(), // ✅ auto-fix broken user_id
                'updated_at' => Carbon::now(),
            ]);

            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /* ── save new user ────────────────────────────────────────── */
    public static function saveData($post)
    {
        try {
            DB::beginTransaction();

            $plainPassword = !empty($post['password'])
                ? $post['password']
                : Str::random(6);
            $imageName     = null;

            if (!empty($post['image']) && $post['image'] instanceof \Illuminate\Http\UploadedFile) {
                $file      = $post['image'];
                $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('profiles', $imageName, 'public');
            }

            $newUuid  = (string) Str::uuid();
            $userData = [
                'id'         => $newUuid,
                'name'       => $post['username'],
                'email'      => $post['email'],
                'phone'      => $post['phone'],
                'password' => bcrypt($plainPassword),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];

            if (!empty($post['type']) && $post['type'] == 'user') {
                $userData['user_status'] = 'Approve';
            }

            if (!DB::table('users')->insert($userData)) {
                throw new Exception("Couldn't create user");
            }

            if (!empty($post['type'] == 'user')) {
                $firstOrg = $post['orgid'];
            } else {
                $firstOrg = DB::table('userorganizations')->value('orgid');
                if (!$firstOrg) {
                    throw new Exception("No organization found in userorganizations table.");
                }
            }

            DB::table('userorganizations')->insert([
                'id'         => (string) Str::uuid(),
                'userid'     => $newUuid,
                'orgid'      => $firstOrg,
                'created_at' => Carbon::now(),
            ]);

            $user = \App\Models\User::find($newUuid);

            if (!empty($post['type'])) {
                $post['role'] = $post['type'] == 'retailer' ? 1 : 2;
            }

            $user->assignRole($post['role']);

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
                'updated_at'          => Carbon::now(),
            ];

            if ($imageName) {
                $profileData['image'] = $imageName;
            }

            DB::table('profiles')->insert($profileData);

            DB::commit();

            SendUserPasswordMail::dispatch([
                'name' => $post['username'],
                'email' => $post['email'],
                'password' => $plainPassword,
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /* ── user list ────────────────────────────────────────────── */
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
                ->leftJoin('model_has_roles as mhr', function ($join) {
                    $join->on('mhr.model_id', '=', 'users.id')
                        ->where('mhr.model_type', '=', User::class);
                })
                ->where('profiles.status', 'Y')
                ->where('u.orgid', $post['orgid']);
            if (!empty($post['inactiveuser'])) {
                if (!empty($post['inactiveuser']) && $post['inactiveuser'] == 'Y') {
                    $query->where('users.user_status', '!=', 'Approve');
                } else {
                    $query->where('users.user_status', '=', 'Approve');
                }
            }
            if (!empty($post['role']) && $post['role'] == 4) {
                $query->where('mhr.role_id', 4);
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
        } catch (\Exception $e) {
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
            $userRoles       = DB::table('model_has_roles')->where('model_id', $result->id)->pluck('role_id')->toArray();
            $result->roles   = $userRoles;
            $result->role_names = DB::table('roles')->whereIn('id', $userRoles)->pluck('name')->toArray();
        }

        return $result;
    }

    /* ── get user data (dropdown etc) ────────────────────────── */
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

    /* ── update user (admin panel) ───────────────────────────── */
    public static function updateUser($post)
    {
        try {
            $imageName = null;

            if (!empty($post['image']) && $post['image'] instanceof \Illuminate\Http\UploadedFile) {
                $file      = $post['image'];
                $imageName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                $file->storeAs('profiles', $imageName, 'public');
            }

            if (empty($post['userid'])) {
                throw new Exception("User ID is required");
            }

            $userId = $post['userid'];

            DB::table('users')->where('id', $userId)->update([
                'name'       => $post['username'] ?? '',
                'email'      => $post['email'] ?? '',
                'updated_at' => Carbon::now(),
            ]);

            $oldData = DB::table('profiles')->where('user_id', $userId)->first();

            if ($imageName && $oldData && $oldData->image) {
                $oldPath = storage_path('app/public/profiles/' . $oldData->image);
                if (File::exists($oldPath)) File::delete($oldPath);
            }

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

            return DB::table('profiles')->where('user_id', $userId)->update($profileData);
        } catch (Exception $e) {
            throw $e;
        }
    }
}
