<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillsSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            'ركن متوازي',
            'ركن عمودي',
            'الرجوع للخلف بخط مستقيم',
            'التحكم بالفرامل',
            'القيادة على الطريق السريع',
            'تبديل المسارات بأمان',
            'قراءة إشارات المرور',
            'الانعطاف عند التقاطعات',
        ];

        foreach ($skills as $name) {
            Skill::query()->firstOrCreate(['name' => $name]);
        }
    }
}
