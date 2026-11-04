<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Automation;
use App\Models\Device;
use App\Models\Room;
use App\Models\Scene;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed database khusus miniatur rumah sekolah (6 Ruangan, masing-masing 1 Lampu).
     * 1. Ruang Tamu
     * 2. Ruang Keluarga
     * 3. Kamar 1
     * 4. Kamar 2
     * 5. Dapur
     * 6. WC / Kamar Mandi
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        ActivityLog::truncate();
        Automation::truncate();
        Device::truncate();
        Scene::truncate();
        Room::truncate();

        Schema::enableForeignKeyConstraints();

        // 1. Buat 6 Ruangan Maket
        $ruangTamu = Room::create([
            'name' => 'Ruang Tamu',
            'slug' => 'ruang-tamu',
            'icon' => 'fa-couch',
            'floor' => 'Depan',
        ]);

        $ruangKeluarga = Room::create([
            'name' => 'Ruang Keluarga',
            'slug' => 'ruang-keluarga',
            'icon' => 'fa-tv',
            'floor' => 'Tengah',
        ]);

        $kamar1 = Room::create([
            'name' => 'Kamar 1',
            'slug' => 'kamar-1',
            'icon' => 'fa-bed',
            'floor' => 'Utama',
        ]);

        $kamar2 = Room::create([
            'name' => 'Kamar 2',
            'slug' => 'kamar-2',
            'icon' => 'fa-bed',
            'floor' => 'Anak',
        ]);

        $dapur = Room::create([
            'name' => 'Dapur',
            'slug' => 'dapur',
            'icon' => 'fa-utensils',
            'floor' => 'Belakang',
        ]);

        $wc = Room::create([
            'name' => 'WC / Kamar Mandi',
            'slug' => 'wc',
            'icon' => 'fa-bath',
            'floor' => 'Belakang',
        ]);

        // 2. Buat 1 Lampu untuk Masing-Masing Ruangan (Total 6 Lampu LED)
        Device::create([
            'room_id' => $ruangTamu->id,
            'name' => 'Lampu Ruang Tamu',
            'type' => 'light',
            'is_on' => true,
            'value' => 100,
            'settings' => ['pin' => 'D1 / GPIO5', 'component' => 'LED Ruang Tamu', 'key' => 'ruang_tamu'],
            'power_watts' => 5.0,
            'is_online' => true,
        ]);

        Device::create([
            'room_id' => $ruangKeluarga->id,
            'name' => 'Lampu Ruang Keluarga',
            'type' => 'light',
            'is_on' => true,
            'value' => 100,
            'settings' => ['pin' => 'D2 / GPIO4', 'component' => 'LED Ruang Keluarga', 'key' => 'ruang_keluarga'],
            'power_watts' => 5.0,
            'is_online' => true,
        ]);

        Device::create([
            'room_id' => $kamar1->id,
            'name' => 'Lampu Kamar 1',
            'type' => 'light',
            'is_on' => false,
            'value' => 100,
            'settings' => ['pin' => 'D5 / GPIO14', 'component' => 'LED Kamar 1', 'key' => 'kamar_1'],
            'power_watts' => 5.0,
            'is_online' => true,
        ]);

        Device::create([
            'room_id' => $kamar2->id,
            'name' => 'Lampu Kamar 2',
            'type' => 'light',
            'is_on' => false,
            'value' => 100,
            'settings' => ['pin' => 'D6 / GPIO12', 'component' => 'LED Kamar 2', 'key' => 'kamar_2'],
            'power_watts' => 5.0,
            'is_online' => true,
        ]);

        Device::create([
            'room_id' => $dapur->id,
            'name' => 'Lampu Dapur',
            'type' => 'light',
            'is_on' => false,
            'value' => 100,
            'settings' => ['pin' => 'D7 / GPIO13', 'component' => 'LED Dapur', 'key' => 'dapur'],
            'power_watts' => 5.0,
            'is_online' => true,
        ]);

        Device::create([
            'room_id' => $wc->id,
            'name' => 'Lampu WC',
            'type' => 'light',
            'is_on' => false,
            'value' => 100,
            'settings' => ['pin' => 'D8 / GPIO15', 'component' => 'LED WC', 'key' => 'wc'],
            'power_watts' => 5.0,
            'is_online' => true,
        ]);

        // 3. Log Awal
        ActivityLog::create([
            'device_name' => 'Maket IoT Rumah',
            'action' => 'Simulator Maket 6 Ruangan siap digunakan',
            'type' => 'success',
            'user_or_source' => 'Sistem Maket',
        ]);
    }
}
