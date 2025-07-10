<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $tags = Tag::latest()->paginate(10);
        return \App\Http\Resources\TagResource::collection($tags);
    }

    public function store(StoreTagRequest $request)
    {
        $tag = Tag::create($request->validated());
        return new \App\Http\Resources\TagResource($tag);
    }

    public function show(Tag $tag)
    {
        return new \App\Http\Resources\TagResource($tag);
    }

    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());
        return new \App\Http\Resources\TagResource($tag);
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return response()->noContent();
    }
}
