<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function show($id)
    {
        $post = Post::findOrFail($id);

        return response()->json(
            [
                'post' => $post,
            ],
            200
        );
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'thumbnail' => 'required',
            'content' => 'required',
            'isPublished' => 'required',
        ]);

        if ($validated->fails()) {
            return response()->json(
                [
                    'message' => "Invalid validation",
                ],
                400
            );
        }

        $post = Post::create([
            'title' => $request->title,
            'thumbnail' => $request->thumbnail,
            'content' => $request->content,
            'isPublished' => $request->isPublished,
            'user_id' => $request->user()->id,
        ]);

        return response()->json(
            [
                'post' => $post,
            ],
            201
        );
    }

    public function update(Request $request, Post $post)
    {
        if ($post->user_id != $request->user()->id) {
            return response()->json([], 403);
        }

        $validated = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'thumbnail' => 'required',
            'content' => 'required',
            'isPublished' => 'required',
        ]);

        $post->update($request->all());

        return response()->json([
            'post' => $post,
        ]);
    }

    public function destroy(Request $request, Post $post)
    {
        if ($post->user_id != $request->user()->id) {
            return response()->json([], 403);
        }

        $post->delete();

        return response()->json([], 204);
    }
}
