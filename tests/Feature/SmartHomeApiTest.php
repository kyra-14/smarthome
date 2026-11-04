<?php

namespace Tests\Feature;

use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartHomeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_pwa_manifest_is_accessible(): void
    {
        $response = $this->get('/manifest.json');
        $response->assertStatus(200);
        $this->assertStringContainsString('Smart Maket IoT', $response->getContent());
    }

    public function test_status_returns_6_room_lights(): void
    {
        $response = $this->getJson('/api/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'ruang_tamu',
                'ruang_keluarga',
                'kamar_1',
                'kamar_2',
                'dapur',
                'wc',
                'active_count',
                'total_count',
                'devices',
            ]);

        $this->assertEquals(6, $response->json('total_count'));
    }

    public function test_toggle_light_flips_state(): void
    {
        $device = Device::first();
        $initialState = $device->is_on;

        $response = $this->postJson("/api/lampu/{$device->id}/toggle");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'device' => [
                    'id' => $device->id,
                    'is_on' => !$initialState,
                ],
            ]);

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'is_on' => !$initialState,
        ]);
    }

    public function test_master_switch_turn_all_on_and_off(): void
    {
        // Turn all off
        $responseOff = $this->postJson('/api/lampu/all', ['action' => 'off']);
        $responseOff->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => [
                    'active_count' => 0,
                ],
            ]);

        $this->assertEquals(0, Device::where('is_on', true)->count());

        // Turn all on
        $responseOn = $this->postJson('/api/lampu/all', ['action' => 'on']);
        $responseOn->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => [
                    'active_count' => 6,
                ],
            ]);

        $this->assertEquals(6, Device::where('is_on', true)->count());
    }

    public function test_telemetry_returns_data(): void
    {
        $response = $this->getJson('/api/telemetry');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'timestamp',
                'status' => [
                    'ruang_tamu',
                    'ruang_keluarga',
                    'kamar_1',
                    'kamar_2',
                    'dapur',
                    'wc',
                ],
            ]);
    }
}
