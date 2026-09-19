<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentCategory extends Model
{
    protected $fillable = [
        'name',
        'is_sequential',
    ];

    protected function casts(): array
    {
        return [
            'is_sequential' => 'boolean',
        ];
    }

    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'category_id')->orderBy('order_index');
    }
}
