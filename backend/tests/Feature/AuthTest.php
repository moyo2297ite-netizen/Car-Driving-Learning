<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_register_with_transmission_type(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test Student',
            'phone' => '0999123456',
            'email' => 'student@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'transmission_type' => 'automatic',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', 'student')
            ->assertJsonPath('user.transmission_type', 'automatic');

        $this->assertDatabaseHas('users', [
            'phone' => '0999123456',
            'role' => 'student',
        ]);
    }

    public function test_registration_requires_transmission_type(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test Student',
            'email' => 'student@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('transmission_type');
    }

    public function test_user_can_login_and_access_protected_route(): void
    {
        User::factory()->create([
            'phone' => '0999555555',
            'email' => 'user@test.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::Student,
            'transmission_type' => 'manual',
        ]);

        $login = $this->postJson('/api/login', [
            'phone' => '0999555555',
            'password' => 'password123',
        ])->assertOk();

        $token = $login->json('token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'user@test.com');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'phone' => '0999555556',
            'email' => 'user@test.com',
            'password' => Hash::make('password123'),
            'role' => UserRole::Student,
        ]);

        $this->postJson('/api/login', [
            'phone' => '0999555556',
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }

    public function test_role_middleware_blocks_unauthorized_role(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);

        $this->actingAs($student, 'sanctum')
            ->getJson('/api/dashboard/admin')
            ->assertForbidden();
    }
}
