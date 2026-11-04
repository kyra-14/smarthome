<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Device;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmartHomeController extends Controller
{
    /**
     * Tampilan Utama Smart Maket IoT 6 Ruangan (PWA Mobile & Web).
     * 1. Ruang Tamu (1 Lampu)
     * 2. Ruang Keluarga (1 Lampu)
     * 3. Kamar 1 (1 Lampu)
     * 4. Kamar 2 (1 Lampu)
     * 5. Dapur (1 Lampu)
     * 6. WC / Toilet (1 Lampu)
     */
    public function index(): View
    {
        $rooms = Room::with('devices')->get();
        $devices = Device::with('room')->get();

        $lampuRuangTamu = $devices->firstWhere('name', 'Lampu Ruang Tamu');
        $lampuRuangKeluarga = $devices->firstWhere('name', 'Lampu Ruang Keluarga');
        $lampuKamar1 = $devices->firstWhere('name', 'Lampu Kamar 1');
        $lampuKamar2 = $devices->firstWhere('name', 'Lampu Kamar 2');
        $lampuDapur = $devices->firstWhere('name', 'Lampu Dapur');
        $lampuWc = $devices->firstWhere('name', 'Lampu WC');

        $activeCount = $devices->where('is_on', true)->count();
        $totalCount = $devices->count();
        $recentLogs = ActivityLog::latest()->take(6)->get();

        return view('dashboard', compact(
            'rooms',
            'devices',
            'lampuRuangTamu',
            'lampuRuangKeluarga',
            'lampuKamar1',
            'lampuKamar2',
            'lampuDapur',
            'lampuWc',
            'activeCount',
            'totalCount',
            'recentLogs'
        ));
    }

    /**
     * Toggle status saklar lampu tertentu (ON / OFF).
     */
    public function toggleDevice(Request $request, Device $device): JsonResponse
    {
        $device->is_on = !$device->is_on;
        $device->save();

        $statusStr = $device->is_on ? 'DINYALAKAN' : 'DIMATIKAN';

        $log = ActivityLog::create([
            'device_id' => $device->id,
            'device_name' => $device->name,
            'action' => "{$device->name} {$statusStr}",
            'type' => $device->is_on ? 'success' : 'info',
            'user_or_source' => 'Remote HP / Web',
        ]);

        return response()->json([
            'success' => true,
            'device' => $device,
            'log' => $log,
            'status' => $this->buildStatusData(),
        ]);
    }

    /**
     * Saklar Master: Nyalakan Semua atau Matikan Semua Lampu.
     */
    public function toggleAll(Request $request): JsonResponse
    {
        $action = $request->input('action', 'on'); // 'on' atau 'off'
        $state = ($action === 'on');

        Device::where('type', 'light')->update(['is_on' => $state]);

        $msg = $state 
            ? 'Semua 6 lampu maket berhasil DINYALAKAN' 
            : 'Semua 6 lampu maket berhasil DIMATIKAN';

        $log = ActivityLog::create([
            'device_name' => 'Saklar Master',
            'action' => $msg,
            'type' => $state ? 'success' : 'warning',
            'user_or_source' => 'Tombol Cepat HP',
        ]);

        return response()->json([
            'success' => true,
            'message' => $msg,
            'log' => $log,
            'status' => $this->buildStatusData(),
        ]);
    }

    /**
     * REST API untuk mikrokontroler fisik (ESP8266 / ESP32 Arduino Sketch).
     * Format JSON ringkas untuk 6 pin LED.
     */
    public function getStatus(): JsonResponse
    {
        return response()->json($this->buildStatusData());
    }

    /**
     * Telemetry Polling (Real-time sync HP & Web).
     */
    public function telemetry(): JsonResponse
    {
        return response()->json([
            'timestamp' => now()->format('H:i:s'),
            'status' => $this->buildStatusData(),
        ]);
    }

    /**
     * Helper membuat array status 6 lampu.
     */
    private function buildStatusData(): array
    {
        $devices = Device::all();

        $rt = $devices->firstWhere('name', 'Lampu Ruang Tamu');
        $rk = $devices->firstWhere('name', 'Lampu Ruang Keluarga');
        $k1 = $devices->firstWhere('name', 'Lampu Kamar 1');
        $k2 = $devices->firstWhere('name', 'Lampu Kamar 2');
        $dp = $devices->firstWhere('name', 'Lampu Dapur');
        $wc = $devices->firstWhere('name', 'Lampu WC');

        return [
            'ruang_tamu' => ($rt && $rt->is_on) ? 1 : 0,
            'ruang_keluarga' => ($rk && $rk->is_on) ? 1 : 0,
            'kamar_1' => ($k1 && $k1->is_on) ? 1 : 0,
            'kamar_2' => ($k2 && $k2->is_on) ? 1 : 0,
            'dapur' => ($dp && $dp->is_on) ? 1 : 0,
            'wc' => ($wc && $wc->is_on) ? 1 : 0,
            'active_count' => $devices->where('is_on', true)->count(),
            'total_count' => $devices->count(),
            'devices' => $devices->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'is_on' => (bool)$d->is_on,
                'pin' => $d->settings['pin'] ?? '',
                'key' => $d->settings['key'] ?? '',
            ]),
        ];
    }
}
