<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_message_supervisor(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/messages', ['receiver_id' => $supervisor->id, 'body' => 'مرحباً'])
            ->assertCreated()
            ->assertJsonPath('data.body', 'مرحباً');
    }

    public function test_student_cannot_message_instructor_directly(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);
        $instructor = User::factory()->create(['role' => UserRole::Instructor]);

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/messages', ['receiver_id' => $instructor->id, 'body' => 'مرحباً'])
            ->assertUnprocessable();
    }

    public function test_conversation_returns_messages_in_order_and_marking_read_works(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);

        $first = $this->actingAs($student, 'sanctum')
            ->postJson('/api/messages', ['receiver_id' => $supervisor->id, 'body' => 'أول رسالة'])
            ->json('data');

        $this->actingAs($supervisor, 'sanctum')
            ->postJson('/api/messages', ['receiver_id' => $student->id, 'body' => 'رد المشرف'])
            ->assertCreated();

        $conversation = $this->actingAs($student, 'sanctum')
            ->getJson("/api/messages/with/{$supervisor->id}")
            ->assertOk();

        $this->assertCount(2, $conversation->json('data'));

        $this->actingAs($supervisor, 'sanctum')
            ->postJson("/api/messages/{$first['id']}/read")
            ->assertOk()
            ->assertJsonPath('data.read_at', fn ($value) => $value !== null);
    }

    public function test_supervisor_sees_student_threads(): void
    {
        $student = User::factory()->create(['role' => UserRole::Student, 'transmission_type' => 'manual']);
        $supervisor = User::factory()->create(['role' => UserRole::Supervisor]);

        $this->actingAs($student, 'sanctum')
            ->postJson('/api/messages', ['receiver_id' => $supervisor->id, 'body' => 'مرحباً']);

        $threads = $this->actingAs($supervisor, 'sanctum')
            ->getJson('/api/messages/threads')
            ->assertOk();

        $this->assertCount(1, $threads->json('data'));
        $this->assertEquals($student->id, $threads->json('data.0.id'));
    }
}
