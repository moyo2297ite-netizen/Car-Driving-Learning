<?php

namespace App\Models;

use App\Enums\AvailabilitySlotStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AvailabilitySlot extends Model
{
    protected $fillable = [
        'instructor_id',
        'start_time',
        'end_time',
        'status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'status' => AvailabilitySlotStatus::class,
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'slot_id');
    }

    public function isBookable(): bool
    {
        return $this->status === AvailabilitySlotStatus::Approved;
    }
}
