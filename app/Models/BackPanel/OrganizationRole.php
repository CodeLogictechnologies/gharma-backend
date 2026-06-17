<?php

namespace App\Models\BackPanel;

use Illuminate\Database\Eloquent\Model;
use App\Models\Organization;
use App\Models\User;
use Spatie\Permission\Models\Role;

class OrganizationRole extends Model
{
    protected $table = 'organization_roles';

    protected $fillable = [
        'organization_id',
        'role_id',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}