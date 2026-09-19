<?php

namespace App\Models;

use App\Enums\TransmissionType;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'transmission_type',
        'teaches_manual',
        'teaches_automatic',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'transmission_type' => TransmissionType::class,
            'teaches_manual' => 'boolean',
            'teaches_automatic' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isSupervisor(): bool
    {
        return $this->role === UserRole::Supervisor;
    }

    public function isInstructor(): bool
    {
        return $this->role === UserRole::Instructor;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function canManageContent(): bool
    {
        return $this->isAdmin() || $this->isSupervisor();
    }

    public function canChatWith(User $other): bool
    {
        if ($this->isStudent() && ($other->isAdmin() || $other->isSupervisor())) {
            return true;
        }

        if ($other->isStudent() && ($this->isAdmin() || $this->isSupervisor())) {
            return true;
        }

        return false;
    }

    public function supportsTransmission(TransmissionType $type): bool
    {
        return match ($type) {
            TransmissionType::Manual => $this->teaches_manual,
            TransmissionType::Automatic => $this->teaches_automatic,
        };
    }

    public function availabilitySlots(): HasMany
    {
        return $this->hasMany(AvailabilitySlot::class, 'instructor_id');
    }

    public function studentBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'student_id');
    }

    public function assignedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'assigned_by');
    }

    public function uploadedContents(): HasMany
    {
        return $this->hasMany(Content::class, 'uploaded_by');
    }

    public function contentProgress(): HasMany
    {
        return $this->hasMany(StudentContentProgress::class, 'student_id');
    }

    public function studentSkills(): HasMany
    {
        return $this->hasMany(StudentSkill::class, 'student_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
}
