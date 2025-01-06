<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('author')->latest()->paginate(10);
        return response()->json($posts);
    }

    public function show($slug)
    {
        $post = Post::where('slug', $slug)->with('author')->firstOrFail();
        return response()->json($post);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $post = Post::create([
            'title' => $validatedData['title'],
            'slug' => Str::slug($validatedData['title'], '-'),
            'content' => $validatedData['content'],
            'user_id' => auth()->id(),
        ]);

        return response()->json($post, 201);
    }

    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
        ]);

        $post->update($validatedData);

        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        if ($post->user_id !== auth()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(null, 204);
    }
}

