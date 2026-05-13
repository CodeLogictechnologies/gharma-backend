<?php

// ══════════════════════════════════════════════════════════════════════════════
// MIGRATION: add latitude/longitude to user_addresses (skip if already exists)
// File: database/migrations/2024_01_01_000001_add_coordinates_to_user_addresses.php
// ══════════════════════════════════════════════════════════════════════════════

// NOTE: Your migration already has latitude & longitude columns:
//   $table->decimal('latitude', 10, 8)->nullable();
//   $table->decimal('longitude', 11, 8)->nullable();
// So NO extra migration is needed. ✅


// ══════════════════════════════════════════════════════════════════════════════
// SEEDER: populate real Nepal lat/lng into test user_addresses
// File: database/seeders/NepalAddressCoordinateSeeder.php
// Run with: php artisan db:seed --class=NepalAddressCoordinateSeeder
// ══════════════════════════════════════════════════════════════════════════════

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NepalAddressCoordinateSeeder extends Seeder
{
    /**
     * Real Nepal city/locality coordinates for testing.
     * Each entry: [address_name, latitude, longitude, type]
     */
    private array $nepalLocations = [
        // Kathmandu Valley
        ['Kathmandu - Thamel',        27.7158,  85.3123, 'other'],
        ['Kathmandu - New Baneshwor', 27.6939,  85.3414, 'work'],
        ['Kathmandu - Patan',         27.6674,  85.3242, 'home'],
        ['Kathmandu - Bhaktapur',     27.6710,  85.4298, 'home'],
        ['Kathmandu - Budhanilkantha', 27.7718,  85.3625, 'home'],
        ['Lalitpur - Pulchowk',       27.6783,  85.3178, 'campus'],
        ['Lalitpur - Imadol',         27.6610,  85.3470, 'home'],
        ['Kirtipur',                  27.6774,  85.2795, 'home'],
        ['Bhaktapur - Durbarmarg',    27.6720,  85.4302, 'other'],
        ['Kathmandu - Balaju',        27.7391,  85.2986, 'work'],

        // Pokhara
        ['Pokhara - Lakeside',        28.2073,  83.9556, 'other'],
        ['Pokhara - New Road',        28.2337,  83.9916, 'work'],
        ['Pokhara - Mahendrapul',     28.2361,  83.9938, 'home'],
        ['Pokhara - Prithvi Chowk',   28.2009,  83.9854, 'home'],

        // Eastern Nepal
        ['Biratnagar - Traffic Chowk', 26.4625,  87.2818, 'home'],
        ['Biratnagar - Rangeli Road', 26.4700,  87.2650, 'work'],
        ['Dharan - Bhanu Chowk',      26.8150,  87.2846, 'home'],
        ['Itahari',                   26.6597,  87.2817, 'home'],
        ['Damak',                     26.6500,  87.7000, 'other'],
        ['Ilam Bazaar',               26.9103,  87.9239, 'home'],
        ['Birtamod',                  26.6480,  87.9998, 'home'],

        // Central Terai
        ['Birgunj - Adarshanagar',    27.0104,  84.8773, 'home'],
        ['Janakpur - Ramananad Chowk', 26.7288,  85.9241, 'home'],
        ['Hetauda - Rapti Road',      27.4167,  85.0333, 'work'],
        ['Bharatpur - Narayanghat',   27.6833,  84.4333, 'home'],
        ['Chitwan - Ratnanagar',      27.6000,  84.3500, 'campus'],

        // Western Nepal
        ['Butwal - Traffic Chowk',    27.7006,  83.4481, 'home'],
        ['Bhairahawa - Buddha Chowk', 27.5010,  83.4506, 'home'],
        ['Tansen - Palpa',            27.8669,  83.5472, 'other'],
        ['Nepalgunj - Tribhuvan Chowk', 28.0500, 81.6167, 'home'],
        ['Tulsipur - Main Road',      28.1306,  82.2972, 'home'],
        ['Ghorahi',                   28.0431,  82.4956, 'home'],

        // Far-Western Nepal
        ['Dhangadhi - Pipalchowk',    28.6833,  80.5833, 'home'],
        ['Mahendranagar',             28.9690,  80.1763, 'home'],

        // Hilly regions
        ['Pokhara - Lekhnath',        28.1667,  84.0167, 'home'],
        ['Damauli - Tanahun',         27.9639,  84.3058, 'home'],
        ['Taulihawa - Kapilvastu',    27.5500,  83.0583, 'home'],
    ];

    public function run(): void
    {
        // Option A: Update existing user_addresses rows that have no coordinates
        // (matches by address_name partial match – adjust to your data)
        foreach ($this->nepalLocations as [$name, $lat, $lng, $type]) {
            DB::table('user_addresses')
                ->where('address_name', 'like', '%' . explode(' - ', $name)[0] . '%')
                ->whereNull('latitude')
                ->update([
                    'latitude'  => $lat,
                    'longitude' => $lng,
                    'type'      => $type,
                    'updated_at' => now(),
                ]);
        }

        // Option B: Insert brand new test addresses linked to first user & org
        // Uncomment this block if you want fresh test data

        $orgId  = DB::table('organizations')->value('id');
        $userId = DB::table('users')->value('id');

        if (!$orgId || !$userId) {
            $this->command->warn('No organization or user found. Skipping seeder.');
            return;
        }

        foreach ($this->nepalLocations as [$name, $lat, $lng, $type]) {
            // Don't duplicate
            $exists = DB::table('user_addresses')
                ->where('address_name', $name)
                ->where('userid', $userId)
                ->exists();

            if (!$exists) {
                DB::table('user_addresses')->insert([
                    'id'           => Str::uuid(),
                    'orgid'        => $orgId,
                    'userid'       => $userId,
                    'address_name' => $name,
                    'latitude'     => $lat,
                    'longitude'    => $lng,
                    'type'         => $type,
                    'status'       => 'Y',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        $this->command->info('Inserted ' . count($this->nepalLocations) . ' Nepal test addresses.');
    }
}
