<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class OrganizationAccess extends Model
{
    use HasFactory;

    protected $table = 'organization_permissions';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'org_id',
        'permission_id',
    ];

    //function to save organization permissions
    // public static function saveData($post)
    // {
    //     try {
    //     $orgId = $post['id'] ?? null;

    //     if (empty($orgId)) {
    //         throw new Exception('Organization ID is required.', 1);
    //     }

    //     $permissionNames = [];

    //     if (!empty($post['permissions'])) {
    //         $permissionNames = Permission::whereIn('id', $post['permissions'])
    //             ->pluck('name')
    //             ->toArray();
    //     }

    //     self::syncByNames($orgId, $permissionNames);

    //     return true;
    //     } catch (Exception $e) {
    //         throw $e;
    //     }
    // }

    public static function saveData($post)
{
    $orgId = $post['id'] ?? null;

    if (empty($orgId)) {
        throw new Exception('Organization ID is required.', 1);
    }

    $permissionIds = collect($post['permissions'] ?? [])
        ->filter(fn ($id) => \Illuminate\Support\Str::isUuid((string) $id))
        ->unique()
        ->values()
        ->all();

    self::syncByIds($orgId, $permissionIds);

    return true;
}
    //function to get permission IDs assigned to an organization
    public static function getPermissionIds($orgId)
{
    $org = Organization::find($orgId);
    if (!$org) {
        throw new Exception('Organization not found.', 1);
    }
    return $org->permissions()->pluck('id')->toArray();
}
    //function to sync (replace) permissions for an organization by permission names
    public static function syncByNames($orgId, array $permissionNames)
    {
        try {
            $org = Organization::find($orgId);
            if (!$org) {
                throw new Exception('Organization not found.', 1);
            }
            $org->syncPermissions($permissionNames);
        } catch (Exception $e) {
            throw $e;
        }
    }

    //function to sync (replace) permissions for an organization by permission IDs
    public static function syncByIds($orgId, array $permissionIds)
    {
        try {
            DB::transaction(function () use ($orgId, $permissionIds) {
                DB::table('organization_permissions')->where('org_id', $orgId)->delete();

                if (empty($permissionIds)) {
                    return;
                }

                $insertData = array_map(static fn($permissionId) => [
                    'org_id'        => $orgId,
                    'permission_id' => $permissionId,
                ], array_unique($permissionIds));

                DB::table('organization_permissions')->insert($insertData);
            });
        } catch (Exception $e) {
            throw $e;
        }
    }
}
