<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_account_permanently_via_profile_endpoint(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Secret123!'),
        ]);

        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Evento a eliminar',
            'type' => 'tarea',
            'start_at' => now()->toDateTimeString(),
        ]);

        Notification::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'scheduled_at' => now(),
            'status' => 'pendiente',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/profile', [
                'password' => 'Secret123!',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
        $this->assertDatabaseMissing('notifications', ['event_id' => $event->id]);
    }

    public function test_user_can_request_deletion_verification_code_and_delete_account_with_it(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Secret123!'),
        ]);

        Cache::forget("user_del_cooldown_{$user->id}");

        // Solicitar código de verificación
        $codeRes = $this->actingAs($user)->postJson('/api/account/send-deletion-code', [
            'password' => 'Secret123!',
        ]);

        $codeRes->assertOk()->assertJsonPath('success', true);

        $code = Cache::get("user_del_code_{$user->id}");
        $this->assertNotNull($code);

        // Confirmar baja con contraseña y código
        $response = $this->actingAs($user)
            ->deleteJson('/api/account', [
                'password' => 'Secret123!',
                'verification_code' => $code,
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_account_deletion_rejects_incorrect_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Secret123!'),
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/profile', [
                'password' => 'WrongPassword',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_account_deletion_requires_password_field(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson('/api/profile', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }
}
