<?php

namespace App\Models;

use App\Enums\ContentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Content extends Model
{
    protected $fillable = [
        'category_id',
        'uploaded_by',
        'title',
        'type',
        'url',
        'order_index',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContentType::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'category_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    public function studentProgress(): HasMany
    {
        return $this->hasMany(StudentContentProgress::class);
    }
}
