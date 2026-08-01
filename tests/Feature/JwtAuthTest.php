<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JwtAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_valid_jwt_token_format(): void
    {
        $user = User::factory()->create([
            'email' => 'jwt_user@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'jwt_user@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'token',
                    'jwt_token',
                    'token_type',
                ],
            ]);

        $jwt = $response->json('data.jwt_token');
        $this->assertNotEmpty($jwt);

        // Verificar que el token tenga el formato JWT de 3 partes separadas por puntos (header.payload.signature)
        $parts = explode('.', $jwt);
        $this->assertCount(3, $parts, 'El token debe ser un JWT valido con 3 partes (header.payload.signature)');

        $payload = JwtService::validateToken($jwt);
        $this->assertNotNull($payload);
        $this->assertEquals($user->id, $payload->sub);
    }

    public function test_protected_routes_accept_jwt_token(): void
    {
        $user = User::factory()->create();
        $jwt = JwtService::generateToken($user);

        $response = $this->withHeader('Authorization', 'Bearer '.$jwt)
            ->getJson('/api/user');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.id', $user->id);
    }

    public function test_protected_routes_reject_invalid_jwt_token(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid.jwt.token')
            ->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }
}
