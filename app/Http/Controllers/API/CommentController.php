<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;

class CommentController extends Controller
{
    public function show(Comment $comment)
    {
        return response()->json(
            [
                'comment' => $comment,
            ],
            200
        );
    }

    public function store(Request $request, Post $post)
    {
        if (!$post) {
            return response()->json([], 404);
        }

        $validate = Validator::make($request->all(), [
            'comment' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json(
                [
                    'message' => "Invalid validation",
                ],
                400
            );
        }

        $comment = Comment::create([
            'comment' => $request->comment,
            'post_id' => $post->id,
            'user_id' => $request->user()->id
        ]);

        return response()->json(
            [
                'comment' => $comment,
            ],
            201
        );
    }

    public function update(Request $request, Post $post, Comment $comment)
    {
        if (!$post && !$comment) {
            return response()->json([], 404);
        }

        if ($comment->user_id != $request->user()->id) {
            return response()->json([], 403);
        }

        $validated = Validator::make($request->all(), [
            'comment' => 'required',
        ]);

        if ($validated->fails()) {
            return response()->json(
                [
                    'message' => "Invalid validation",
                ],
                400
            );
        }

        $comment->update($request->all());

        return response()->json([
            'comment' => $comment,
        ]);
    }

    public function destroy(Request $request, Post $post, Comment $comment)
    {
        if (!$post && !$comment) {
            return response()->json([], 404);
        }

        if ($comment->user_id != $request->user()->id) {
            return response()->json([], 403);
        }

        $comment->delete();

        return response()->json([], 204);
    }
}
