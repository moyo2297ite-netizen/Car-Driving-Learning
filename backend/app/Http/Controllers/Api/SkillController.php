<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSkillRequest;
use App\Http\Resources\SkillResource;
use App\Models\Skill;

class SkillController extends Controller
{
    public function index()
    {
        return SkillResource::collection(Skill::query()->orderBy('name')->get());
    }

    public function store(StoreSkillRequest $request)
    {
        $skill = Skill::query()->create($request->validated());

        return new SkillResource($skill);
    }

    public function update(StoreSkillRequest $request, Skill $skill)
    {
        $skill->update($request->validated());

        return new SkillResource($skill);
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();

        return response()->json(['message' => 'تم الحذف.']);
    }
}
