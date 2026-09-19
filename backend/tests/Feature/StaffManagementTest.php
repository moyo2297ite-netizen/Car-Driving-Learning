<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_list_and_delete_instructor(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $created = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/staff', [
                'name' => 'مدرب جديد',
                'phone' => '0999777888',
                'email' => 'new-instructor@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'instructor',
                'teaches_manual' => true,
                'teaches_automatic' => false,
            ])
            ->assertCreated()
            ->json('data');

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/staff')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/staff/{$created['id']}")
            ->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $created['id']]);
    }

    public function test_admin_cannot_create_staff_with_student_role(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/staff', [
                'name' => 'محاولة',
                'email' => 'x@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'role' => 'student',
            ])
            ->assertUnprocessable();
    }

    public function test_supervisor_cannot_manage_staff(): void
    {
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);

        $this->actingAs($supervisor, 'sanctum')
            ->getJson('/api/staff')
            ->assertForbidden();
    }

    public function test_staff_endpoint_cannot_delete_a_student(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/staff/{$student->id}")
            ->assertUnprocessable();

        $this->assertDatabaseHas('users', ['id' => $student->id]);
    }
}
