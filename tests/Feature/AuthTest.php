<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_successfully(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Juan',
            'middle_name' => 'Carlos',
            'last_name' => 'Perez',
            'second_last_name' => 'Gomez',
            'email' => 'juan@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'juan@example.com')
            ->assertJsonPath('data.user.first_name', 'Juan')
            ->assertJsonPath('data.user.last_name', 'Perez')
            ->assertJsonPath('data.user.name', 'Juan Carlos Perez Gomez')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'first_name', 'last_name', 'email', 'name'],
                    'auth_token',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'juan@example.com',
            'first_name' => 'Juan',
            'last_name' => 'Perez',
        ]);
    }

    public function test_register_rejects_weak_password(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Juan',
            'last_name' => 'Perez',
            'email' => 'weak@example.com',
            'password' => 'simple',
            'password_confirmation' => 'simple',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Error de validación.');
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'duplicado@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'first_name' => 'Usuario',
            'last_name' => 'Duplicado',
            'email' => 'duplicado@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Error de validación.');
    }

    public function test_login_returns_sanctum_token_on_success(): void
    {
        User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'login@example.com');
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'wrong@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'Incorrecta123!',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Credenciales inválidas. Verifica tu correo y contraseña.');
    }

    public function test_register_rejects_numbers_in_names(): void
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Juan123',
            'last_name' => 'Perez456',
            'email' => 'con_numeros@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('data.errors.first_name.0', 'El primer nombre solo debe contener letras, no se permiten números.');
    }
}
