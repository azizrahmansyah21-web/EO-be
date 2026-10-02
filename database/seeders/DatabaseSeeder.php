<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Super Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@agungtoyota.co.id'],
            [
                'name' => 'Arya Pratama, S.Kom',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'branch' => 'Sutomo Hub Pekanbaru',
                'phone' => '+62 812-7561-9011',
                'quota_allocated' => 0,
            ]
        );

        // 2. Create Sales Consultants
        $sales1 = User::updateOrCreate(
            ['nik' => 'SC-STM-041'],
            [
                'name' => 'Doni Saputra',
                'email' => 'doni.saputra@agungtoyota.co.id',
                'password' => Hash::make('password123'),
                'role' => 'sales',
                'branch' => 'Cabang Sutomo',
                'phone' => '+62 812-3456-7890',
                'quota_allocated' => 50,
            ]
        );

        $sales2 = User::updateOrCreate(
            ['nik' => 'SC-ARK-012'],
            [
                'name' => 'Rina Anggraini',
                'email' => 'rina.anggraini@agungtoyota.co.id',
                'password' => Hash::make('password123'),
                'role' => 'sales',
                'branch' => 'Cabang Arengka',
                'phone' => '+62 813-7890-1234',
                'quota_allocated' => 50,
            ]
        );

        // 3. Create Main Event
        $event = Event::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Toyota Customer Gathering & Weekend Expo 2025',
                'subtitle' => 'ANNUAL GATHERING & SPK EXPO',
                'event_date' => '2025-03-15',
                'event_time' => 'Pukul 09:00 – 15:00 WIB',
                'venue' => 'Grand Mercure Ballroom Lt. 3',
                'address' => 'Jl. Sudirman No. 45, Pekanbaru, Riau',
                'maps_url' => 'https://maps.google.com/?q=Grand+Mercure+Pekanbaru',
                'image_url' => '/toyota-event.jpg',
                'status' => 'active',
                'quota_target' => 300,
            ]
        );

        // 4. Create Sample Guests with Pre-configured Tokens
        Guest::updateOrCreate(
            ['token' => 'TKN-88319B-JKT'],
            [
                'event_id' => $event->id,
                'sales_id' => $sales1->id,
                'name' => 'Hendra Wijaya, S.E.',
                'company' => 'PT Mega Nusantara Logistik',
                'title' => 'Direktur Utama',
                'phone' => '6281275619011',
                'vip' => true,
                'vip_tier' => 'VVIP',
                'pax' => 1,
                'status' => 'attended',
                'claimed_at' => now()->subMinutes(25),
                'souvenir_claimed' => false,
                'snack_claimed' => false,
                'car_model' => 'Innova Zenix HEV',
            ]
        );

        Guest::updateOrCreate(
            ['token' => 'TKT-AGUNG-2025-0891'],
            [
                'event_id' => $event->id,
                'sales_id' => $sales1->id,
                'name' => 'Hendra Wijaya, S.E.',
                'company' => 'PT Mega Nusantara Logistik',
                'title' => 'Direktur Utama',
                'phone' => '6281288997721',
                'vip' => true,
                'vip_tier' => 'VVIP',
                'pax' => 1,
                'status' => 'confirmed',
                'claimed_at' => null,
                'souvenir_claimed' => false,
                'snack_claimed' => false,
                'car_model' => 'Innova Zenix HEV',
            ]
        );

        Guest::updateOrCreate(
            ['token' => 'TKN-44219A-JKT'],
            [
                'event_id' => $event->id,
                'sales_id' => $sales1->id,
                'name' => 'Bambang Soeharto',
                'company' => 'PT Duta Mandiri',
                'title' => 'Komisaris',
                'phone' => '6281244219901',
                'vip' => true,
                'vip_tier' => 'VVIP',
                'pax' => 2,
                'status' => 'attended',
                'claimed_at' => now()->subMinutes(40),
                'souvenir_claimed' => true,
                'souvenir_claimed_at' => now()->subMinutes(35),
                'snack_claimed' => false,
                'car_model' => 'Alphard Hybrid',
            ]
        );

        Guest::updateOrCreate(
            ['token' => 'TKN-88023C-JKT'],
            [
                'event_id' => $event->id,
                'sales_id' => $sales2->id,
                'name' => 'Dr. Nadia Maharani',
                'company' => 'RS Awal Bros',
                'title' => 'Spesialis Anak',
                'phone' => '6281288023114',
                'vip' => true,
                'vip_tier' => 'VIP',
                'pax' => 1,
                'status' => 'attended',
                'claimed_at' => now()->subMinutes(50),
                'souvenir_claimed' => true,
                'souvenir_claimed_at' => now()->subMinutes(45),
                'snack_claimed' => true,
                'snack_claimed_at' => now()->subMinutes(42),
                'car_model' => 'Yaris Cross HEV',
            ]
        );

        Guest::updateOrCreate(
            ['token' => 'TKN-DEMO-001'],
            [
                'event_id' => $event->id,
                'sales_id' => $sales1->id,
                'name' => 'Budi Santoso',
                'company' => 'PT Riau Prima Perkasa',
                'title' => 'General Manager',
                'phone' => '628117765100',
                'vip' => false,
                'vip_tier' => 'REGULAR',
                'pax' => 1,
                'status' => 'confirmed',
                'claimed_at' => null,
                'souvenir_claimed' => false,
                'snack_claimed' => false,
                'car_model' => 'Hilux Rangga',
            ]
        );
    }
}
