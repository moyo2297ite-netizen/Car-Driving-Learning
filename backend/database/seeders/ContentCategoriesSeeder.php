<?php

namespace Database\Seeders;

use App\Models\ContentCategory;
use Illuminate\Database\Seeder;

class ContentCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'دروس القيادة الأساسية', 'is_sequential' => true],
            ['name' => 'إشارات المرور', 'is_sequential' => false],
            ['name' => 'قطع السيارة وصيانتها', 'is_sequential' => false],
            ['name' => 'نصائح عامة', 'is_sequential' => false],
        ];

        foreach ($categories as $category) {
            ContentCategory::query()->firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
