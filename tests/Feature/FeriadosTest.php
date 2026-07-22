<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FeriadosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FeriadosTest extends TestCase
{
    use RefreshDatabase;

    public function test_feriados_service_fetches_and_saves_holidays(): void
    {
        Http::fake([
            'api.feriados.io/v1/*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'date' => '2026-07-20',
                        'name' => 'Día de la Independencia',
                        'is_movable' => false,
                    ],
                    [
                        'date' => '2026-08-07',
                        'name' => 'Batalla de Boyacá',
                        'is_movable' => false,
                    ],
                ],
            ], 200),
        ]);

        $service = new FeriadosService();
        $holidays = $service->syncHolidays(2026, 'co');

        $this->assertCount(2, $holidays);
        $this->assertDatabaseHas('holidays', [
            'name' => 'Día de la Independencia',
            'date' => '2026-07-20',
        ]);
    }

    public function test_authenticated_user_can_get_holidays_api(): void
    {
        Http::fake([
            'api.feriados.io/v1/*' => Http::response([
                'success' => true,
                'data' => [
                    [
                        'date' => '2026-07-20',
                        'name' => 'Día de la Independencia',
                        'is_movable' => false,
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/holidays?year=2026');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.holidays.0.name', 'Día de la Independencia');
    }
}
