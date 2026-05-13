<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderHeatmapSeeder extends Seeder
{
    public function run(): void
    {
        // ── All your real address IDs ──────────────────────────────
        $addressIds = [
            '069e4358-5dad-42df-b782-d0bb063c1e1a', // Pokhara - Mahendrapul
            '0bc7ad1f-a614-454d-9881-698033a0dae5', // Pokhara - New Road
            '0e0bfb25-ccbd-40de-9ff5-a965e01344b6', // Kathmandu - New Baneshwor
            '12f9896d-012d-4a0a-a9f2-6137a7b3575a', // Pokhara - Prithvi Chowk
            '16bacf5d-32f9-4e22-94c7-7e59a2e0b6e9', // Pokhara - Lakeside
            '195f5f82-c3b6-4ce8-af2f-9f6de3d31f10', // Lalitpur - Imadol
            '19a8d75a-96da-4c78-8f16-648fc70d93d2', // Pokhara - Lekhnath
            '28a80adc-d6ba-4285-ace9-a32687275e91', // Hetauda - Rapti Road
            '2de88547-da32-44fa-b2f2-ad40327b9b95', // Nepalgunj - Tribhuvan Chowk
            '38387bc9-b685-4787-9b08-ab82e8cc9e0d', // Birtamod
            '3a95d52a-6feb-45a8-9800-9f280cbafef2', // Dhangadhi - Pipalchowk
            '3be21551-380d-48d6-b8e1-075fdf17dc91', // Kathmandu - Thamel
            '444d85b0-668a-4ea8-b9c0-a3e34bb155d1', // Tulsipur - Main Road
            '45098644-921c-44c5-896f-cf8c2b668845', // Butwal - Traffic Chowk
            '47ebc9a7-a3a5-4de5-aeee-4b8708bf6bc9', // Tansen - Palpa
            '61c65117-f0af-49f8-b0f1-2d8fecf1f32e', // Mahendranagar
            '67d40165-0ed5-4b5a-9994-8c65819c52a4', // Kathmandu - Balaju
            '7f891fd9-29dc-4f8f-8a2e-ee59337f2445', // Ilam Bazaar
            '830b2afe-9ceb-4706-9550-0b455a8a0e69', // Bhaktapur - Durbarmarg
            '8492e0e0-4ffa-4fb3-b2f4-7d955720937d', // Itahari
            '913e879f-6444-4fda-b4b2-f9dd4211718c', // Birgunj - Adarshanagar
            '9fea4fb6-1619-4d6a-ae16-503c4a095773', // Janakpur - Ramananad Chowk
            'a22a08a6-84d8-44da-8e37-69315b0bfec0', // Ghorahi
            'a273e728-277f-4e8f-80a3-5aa52b77ef28', // Kathmandu - Budhanilkantha
            'a93d7c6c-2229-4924-a062-014362c1bf8c', // Bhairahawa - Buddha Chowk
            'c2220610-fb79-47d2-88eb-8076a4b1bfc4', // Biratnagar - Traffic Chowk
            'c70dc5ed-45d8-42c2-a952-d3b9da5db4e8', // Damak
            'c9452a62-234d-4a72-9456-d0c2f0e55a3b', // Kathmandu - Bhaktapur
            'd5a8bda2-cf27-446b-a474-b5b6e61fcf25', // Bharatpur - Narayanghat
            'd95c7d40-f6d1-4f13-96f6-03a14542b058', // Lalitpur - Pulchowk
            'df7f8a95-efe6-4cc1-9cb5-ee4f89b6db01', // Kirtipur
            'e45f3c2b-5e7c-427a-97e8-1b1e4c64e55a', // Dharan - Bhanu Chowk
            'e6130f25-c560-4138-8c06-1f215d8a587c', // Taulihawa - Kapilvastu
            'ee725093-da29-4de3-b386-d72697957382', // Chitwan - Ratnanagar
            'f6d32bc7-be24-4300-b368-ef0ee3fc7682', // Damauli - Tanahun
            'f72ab975-3ed3-4966-8aac-a9e4273295cb', // Biratnagar - Rangeli Road
            'f7c3ac18-3c72-4abd-ba4e-61c66a7937f2', // Kathmandu - Patan
        ];

        // ── Fixed IDs from your schema ─────────────────────────────
        $orgId       = DB::table('organizations')->value('id');
        $userId      = DB::table('users')->value('id');
        $variationId = '2cb1ba69-b1ba-470c-8c87-b98aa5d98e77';

        if (!$orgId || !$userId) {
            $this->command->error('No organization or user found. Run DatabaseSeeder first.');
            return;
        }

        // ── Weighted distribution (Kathmandu gets more orders) ─────
        // Weight by index: first addresses (Kathmandu) get higher weight
        $weights = [
            '3be21551-380d-48d6-b8e1-075fdf17dc91' => 120, // Kathmandu - Thamel
            '0e0bfb25-ccbd-40de-9ff5-a965e01344b6' => 100, // Kathmandu - New Baneshwor
            'a273e728-277f-4e8f-80a3-5aa52b77ef28' => 90,  // Kathmandu - Budhanilkantha
            '67d40165-0ed5-4b5a-9994-8c65819c52a4' => 85,  // Kathmandu - Balaju
            'f7c3ac18-3c72-4abd-ba4e-61c66a7937f2' => 80,  // Kathmandu - Patan
            'c9452a62-234d-4a72-9456-d0c2f0e55a3b' => 75,  // Kathmandu - Bhaktapur
            'd95c7d40-f6d1-4f13-96f6-03a14542b058' => 70,  // Lalitpur - Pulchowk
            '195f5f82-c3b6-4ce8-af2f-9f6de3d31f10' => 65,  // Lalitpur - Imadol
            '16bacf5d-32f9-4e22-94c7-7e59a2e0b6e9' => 60,  // Pokhara - Lakeside
            '0bc7ad1f-a614-454d-9881-698033a0dae5' => 55,  // Pokhara - New Road
            '069e4358-5dad-42df-b782-d0bb063c1e1a' => 50,  // Pokhara - Mahendrapul
            '12f9896d-012d-4a0a-a9f2-6137a7b3575a' => 45,  // Pokhara - Prithvi Chowk
            'c2220610-fb79-47d2-88eb-8076a4b1bfc4' => 40,  // Biratnagar - Traffic Chowk
            'f72ab975-3ed3-4966-8aac-a9e4273295cb' => 35,  // Biratnagar - Rangeli Road
            'e45f3c2b-5e7c-427a-97e8-1b1e4c64e55a' => 30,  // Dharan - Bhanu Chowk
            'd5a8bda2-cf27-446b-a474-b5b6e61fcf25' => 25,  // Bharatpur - Narayanghat
            'ee725093-da29-4de3-b386-d72697957382' => 22,  // Chitwan - Ratnanagar
            '913e879f-6444-4fda-b4b2-f9dd4211718c' => 20,  // Birgunj
            '9fea4fb6-1619-4d6a-ae16-503c4a095773' => 18,  // Janakpur
            '28a80adc-d6ba-4285-ace9-a32687275e91' => 15,  // Hetauda
            '45098644-921c-44c5-896f-cf8c2b668845' => 14,  // Butwal
            '2de88547-da32-44fa-b2f2-ad40327b9b95' => 12,  // Nepalgunj
            'a93d7c6c-2229-4924-a062-014362c1bf8c' => 10,  // Bhairahawa
            '8492e0e0-4ffa-4fb3-b2f4-7d955720937d' => 9,   // Itahari
            '830b2afe-9ceb-4706-9550-0b455a8a0e69' => 8,   // Bhaktapur - Durbarmarg
            'df7f8a95-efe6-4cc1-9cb5-ee4f89b6db01' => 7,   // Kirtipur
            '19a8d75a-96da-4c78-8f16-648fc70d93d2' => 6,   // Pokhara - Lekhnath
            'f6d32bc7-be24-4300-b368-ef0ee3fc7682' => 5,   // Damauli
            '444d85b0-668a-4ea8-b9c0-a3e34bb155d1' => 4,   // Tulsipur
            'a22a08a6-84d8-44da-8e37-69315b0bfec0' => 4,   // Ghorahi
            '3a95d52a-6feb-45a8-9800-9f280cbafef2' => 3,   // Dhangadhi
            '61c65117-f0af-49f8-b0f1-2d8fecf1f32e' => 3,   // Mahendranagar
            '7f891fd9-29dc-4f8f-8a2e-ee59337f2445' => 3,   // Ilam Bazaar
            '38387bc9-b685-4787-9b08-ab82e8cc9e0d' => 2,   // Birtamod
            'c70dc5ed-45d8-42c2-a952-d3b9da5db4e8' => 2,   // Damak
            'e6130f25-c560-4138-8c06-1f215d8a587c' => 2,   // Taulihawa
            '47ebc9a7-a3a5-4de5-aeee-4b8708bf6bc9' => 1,   // Tansen
        ];

        // Build weighted pool
        $pool = [];
        foreach ($weights as $addressId => $weight) {
            for ($i = 0; $i < $weight; $i++) {
                $pool[] = $addressId;
            }
        }

        $orderStatuses = ['Pending','Confirmed','Packed','Shipped','Delivered','Delivered','Delivered','Cancelled'];
        $totalOrders   = 1000;
        $quantity      = 2;
        $price         = 500;
        $totalPrice    = $quantity * $price; // 1000

        $this->command->info("Seeding {$totalOrders} orders across " . count($addressIds) . " locations...");
        $bar = $this->command->getOutput()->createProgressBar($totalOrders);

        DB::transaction(function () use (
            $pool, $orgId, $userId, $variationId,
            $orderStatuses, $totalOrders, $quantity, $price, $totalPrice, $bar
        ) {
            for ($i = 0; $i < $totalOrders; $i++) {

                // Pick random address from weighted pool
                $addressId = $pool[array_rand($pool)];
                $status    = $orderStatuses[array_rand($orderStatuses)];

                // Random date within last 12 months
                $createdAt = now()->subDays(rand(0, 365))->subHours(rand(0, 23));

                // ── order_masters ──────────────────────────────────
                $masterId = Str::uuid()->toString();

                DB::table('order_masters')->insert([
                    'id'                      => $masterId,
                    'orgid'                   => $orgId,
                    'userid'                  => $userId,
                    'addressid'               => $addressId,
                    'order_master_total_price' => $totalPrice,
                    'status'                  => 'Y',
                    'order_status'            => $status,
                    'postedby'                => $userId,
                    'created_at'              => $createdAt,
                    'updated_at'              => $createdAt,
                ]);

                // ── order_details ──────────────────────────────────
                DB::table('order_details')->insert([
                    'id'                       => Str::uuid()->toString(),
                    'ordermasterid'            => $masterId,
                    'variation_id'             => $variationId,
                    'userid'                   => $userId,
                    'quantity'                 => $quantity,
                    'price'                    => $price,
                    'order_detail_total_price' => $totalPrice,
                    'status'                   => 'Y',
                    'postedby'                 => $userId,
                    'created_at'               => $createdAt,
                    'updated_at'               => $createdAt,
                ]);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->command->newLine();
        $this->command->info('✅ Done! 1000 orders seeded across Nepal locations.');
        $this->command->table(
            ['Location', 'Orders'],
            collect($weights)
                ->sortDesc()
                ->take(10)
                ->map(fn($w, $id) => [$id, $w])
                ->values()
                ->toArray()
        );
    }
}