<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContentCategoryRequest;
use App\Http\Requests\UpdateContentCategoryRequest;
use App\Http\Resources\ContentCategoryResource;
use App\Models\ContentCategory;

class ContentCategoryController extends Controller
{
    public function index()
    {
        $categories = ContentCategory::query()->with('contents.quiz')->get();

        return ContentCategoryResource::collection($categories);
    }

    public function store(StoreContentCategoryRequest $request)
    {
        $category = ContentCategory::query()->create($request->validated());

        return new ContentCategoryResource($category);
    }

    public function show(ContentCategory $contentCategory)
    {
        return new ContentCategoryResource($contentCategory->load('contents.quiz'));
    }

    public function update(UpdateContentCategoryRequest $request, ContentCategory $contentCategory)
    {
        $contentCategory->update($request->validated());

        return new ContentCategoryResource($contentCategory);
    }

    public function destroy(ContentCategory $contentCategory)
    {
        $contentCategory->delete();

        return response()->json(['message' => 'تم الحذف.']);
    }
}
