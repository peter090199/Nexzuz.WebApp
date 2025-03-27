<?php

namespace App\Http\Controllers;

use App\Models\PostMessage;
use Illuminate\Http\Request;
use App\Events\PostCreated;

class MessageController extends Controller
{
    public function showForm()
    {
        return view('user.post');
    }

    public function save(Request $request)
    {
        $post_data = $request->validate([
            'title' => 'required|string',
            'author' => 'required|string' // Fixed "reuired" to "required"
        ]);

        PostMessage::create($post_data);

        $data = [
            'title' => $post_data['title'],
            'author' => $post_data['author']
        ];

        event(new PostCreated($data));

        return redirect()->back()->with('success', 'Post created successfully.');
    }
}
