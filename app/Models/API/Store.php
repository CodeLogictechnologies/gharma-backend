<?php

namespace App\Models\API;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Database\QueryException;

class Store extends Model
{
    public $incrementing = false;
    protected $keyType   = 'string';

    public static function getList()
{
    try {
        $stores = DB::table('stores')
            ->where('status', 'Y')
            ->select(
                'id',
                'name',
                'phone',
                'email',
                'address',
                'city',
                'country',
                'latitude',
                'longitude'
            )
            ->get();

        return $stores->map(function ($store) {
            return [
                'coordinates' => [
                    'latitude'  => $store->latitude  ? (float) $store->latitude  : null,
                    'longitude' => $store->longitude ? (float) $store->longitude : null,
                ],
            ];
        });

    } catch (Exception $e) {
        throw $e;
    }
}

// getDetail() stays the same — no changes needed
public static function getDetail($post)
{
    try {
        $store = DB::table('stores')
            ->where('id', $post['id'])
            ->where('status', 'Y')
            ->select(
                'id',
                'name',
                'phone',
                'email',
                'address',
                'city',
                'country',
                'latitude',
                'longitude'
            )
            ->first();

        if (!$store) {
            throw new Exception('Store not found.');
        }

        return [
            'id'          => $store->id,
            'name'        => $store->name,
            'phone'       => $store->phone,
            'email'       => $store->email,
            'address'     => $store->address,
            'city'        => $store->city,
            'country'     => $store->country,
            'coordinates' => [
                'latitude'  => $store->latitude  ? (float) $store->latitude  : null,
                'longitude' => $store->longitude ? (float) $store->longitude : null,
            ],
        ];

    } catch (Exception $e) {
        throw $e;
    }
}
}