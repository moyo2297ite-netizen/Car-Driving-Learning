<?php

namespace Database\Seeders;

use App\Enums\ContentType;
use App\Enums\UserRole;
use App\Models\Content;
use App\Models\ContentCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

// محتوى تجريبي واحد بكويز، بالفئة المتسلسلة — عشان تقدر تجرّب شاشة
// "حل الكويز" وقفل/فتح المحتوى فورًا بدون ما تحتاج تضيفه يدويًا.
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $category = ContentCategory::query()->where('name', 'دروس القيادة الأساسية')->first();
        $admin = User::query()->where('role', UserRole::Admin)->first();

        if (! $category || ! $admin) {
            return;
        }

        $content = Content::query()->firstOrCreate(
            ['category_id' => $category->id, 'title' => 'التعرف على لوحة القيادة'],
            [
                'uploaded_by' => $admin->id,
                'type' => ContentType::Video,
                'url' => 'https://example.com/dashboard-intro.mp4',
                'order_index' => 1,
            ],
        );

        if (! $content->quiz) {
            $quiz = $content->quiz()->create(['pass_score' => 70]);

            $quiz->questions()->createMany([
                [
                    'question' => 'شو بيدل عليه ضوء البنزين الأحمر بلوحة القيادة؟',
                    'options' => ['البنزين قارب يخلص', 'المكيّف شغّال', 'الأنوار مفتوحة'],
                    'correct_answer' => 'البنزين قارب يخلص',
                ],
                [
                    'question' => 'وين تلاقي عداد السرعة عادةً؟',
                    'options' => ['يمين السائق', 'أمام السائق مباشرة', 'بالسقف'],
                    'correct_answer' => 'أمام السائق مباشرة',
                ],
            ]);
        }

        Content::query()->firstOrCreate(
            ['category_id' => $category->id, 'title' => 'بدء التشغيل والإيقاف'],
            [
                'uploaded_by' => $admin->id,
                'type' => ContentType::Video,
                'url' => 'https://example.com/start-stop.mp4',
                'order_index' => 2,
            ],
        );
    }
}
