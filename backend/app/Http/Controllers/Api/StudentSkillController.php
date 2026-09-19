<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateStudentSkillRequest;
use App\Http\Resources\StudentSkillResource;
use App\Models\Skill;
use App\Models\User;
use App\Services\SkillService;
use Illuminate\Http\Request;

class StudentSkillController extends Controller
{
    public function __construct(private readonly SkillService $skills) {}

    public function mine(Request $request)
    {
        return StudentSkillResource::collection($this->skills->studentSkills($request->user()));
    }

    public function forStudent(User $student)
    {
        abort_unless($student->isStudent(), 404);

        return StudentSkillResource::collection($this->skills->studentSkills($student));
    }

    public function update(UpdateStudentSkillRequest $request, User $student, Skill $skill)
    {
        $studentSkill = $this->skills->updateSkill(
            $request->user(),
            $student,
            $skill,
            $request->boolean('is_completed'),
        );

        return new StudentSkillResource($studentSkill->load(['skill', 'updatedBy']));
    }

    public function certificateEligibility(Request $request, User $student)
    {
        abort_unless($student->isStudent(), 404);

        return response()->json([
            'eligible' => $this->skills->isCertificateEligible($student),
        ]);
    }
}
