<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContentRequest;
use App\Http\Requests\UpdateContentRequest;
use App\Http\Resources\ContentResource;
use App\Models\Content;
use App\Services\ContentProgressService;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(private readonly ContentProgressService $progress) {}

    public function store(StoreContentRequest $request)
    {
        $data = $request->validated();

        $data['order_index'] ??= (Content::query()->where('category_id', $data['category_id'])->max('order_index') ?? 0) + 1;

        $content = Content::query()->create([
            ...$data,
            'uploaded_by' => $request->user()->id,
        ]);

        return new ContentResource($content);
    }

    public function show(Request $request, Content $content)
    {
        $user = $request->user();

        if ($user->isStudent() && ! $this->progress->canAccess($user, $content)) {
            return response()->json(['message' => 'يجب إكمال المحتوى السابق أولاً.'], 403);
        }

        return new ContentResource($content->load('quiz.questions'));
    }

    public function update(UpdateContentRequest $request, Content $content)
    {
        $content->update($request->validated());

        return new ContentResource($content);
    }

    public function destroy(Content $content)
    {
        $content->delete();

        return response()->json(['message' => 'تم الحذف.']);
    }

    public function markViewed(Request $request, Content $content)
    {
        $progress = $this->progress->markViewed($request->user(), $content);

        return response()->json(['completed' => $progress->completed]);
    }
}
