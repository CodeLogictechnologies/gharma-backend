<?php

namespace App\Models\BackPanel;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class Driver extends Model
{
    public static function getDrivers($post)
    {
        try {
            $users = User::role('Driver')->get();
            return $users;
        } catch (Exception $e) {
            throw $e;
        }
    }
}
