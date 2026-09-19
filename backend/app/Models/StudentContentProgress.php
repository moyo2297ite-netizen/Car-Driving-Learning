<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentContentProgress extends Model
{
    protected $table = 'student_content_progress';

    protected $fillable = [
        'student_id',
        'content_id',
        'completed',
        'quiz_score',
    ];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
