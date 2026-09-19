<?php

namespace App\Services;

use App\Exceptions\DomainException;
use App\Models\Skill;
use App\Models\StudentSkill;
use App\Models\User;
use Illuminate\Support\Collection;

class SkillService
{
    public function updateSkill(User $instructor, User $student, Skill $skill, bool $isCompleted): StudentSkill
    {
        if (! $instructor->isInstructor()) {
            throw new DomainException('تحديث المهارات متاح للمدربين فقط.');
        }

        if (! $student->isStudent()) {
            throw new DomainException('المستخدم المحدد ليس طالباً.');
        }

        return StudentSkill::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'skill_id' => $skill->id,
            ],
            [
                'is_completed' => $isCompleted,
                'updated_by' => $instructor->id,
                'skill_updated_at' => now(),
            ],
        );
    }

    public function isCertificateEligible(User $student): bool
    {
        $totalSkills = Skill::query()->count();

        if ($totalSkills === 0) {
            return false;
        }

        $completedCount = StudentSkill::query()
            ->where('student_id', $student->id)
            ->where('is_completed', true)
            ->count();

        return $completedCount >= $totalSkills;
    }

    public function studentSkills(User $student): Collection
    {
        return StudentSkill::query()
            ->with('skill')
            ->where('student_id', $student->id)
            ->get();
    }
}
