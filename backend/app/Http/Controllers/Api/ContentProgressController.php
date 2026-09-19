<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentContentProgressResource;
use App\Models\User;
use App\Services\ContentProgressService;
use Illuminate\Http\Request;

class ContentProgressController extends Controller
{
    public function __construct(private readonly ContentProgressService $progress) {}

    public function mine(Request $request)
    {
        return StudentContentProgressResource::collection(
            $this->progress->studentProgressReport($request->user())
        );
    }

    public function forStudent(User $student)
    {
        abort_unless($student->isStudent(), 404);

        return StudentContentProgressResource::collection(
            $this->progress->studentProgressReport($student)
        );
    }
}
