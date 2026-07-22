<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_account_permanently_with_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Secret123!'),
        ]);

        Event::create([
            'user_id' => $user->id,
            'title' => 'Evento 1 de usuario',
            'type' => 'tarea',
            'start_at' => now()->toDateTimeString(),
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/account', [
                'password' => 'Secret123!',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('events', ['user_id' => $user->id]);
    }

    public function test_account_deletion_rejects_incorrect_password(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Secret123!'),
        ]);

        $response = $this->actingAs($user)
            ->deleteJson('/api/account', [
                'password' => 'WrongPassword',
            ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
