<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
      // ✅ Save reaction
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|integer',
            'reaction' => 'required|string|in:like,heart,haha,wow,sad,angry',
        ]);

        $reaction = Reaction::create([
            'post_id' => $request->post_id,
            'reaction' => $request->reaction,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Reaction saved successfully!',
            'data' => $reaction,
        ]);
    }

    // ✅ Get reactions grouped by type for a post
    public function getReactions($postId)
    {
        $counts = Reaction::where('post_id', $postId)
            ->selectRaw('reaction, COUNT(*) as total')
            ->groupBy('reaction')
            ->pluck('total', 'reaction');

        return response()->json([
            'success' => true,
            'post_id' => $postId,
            'reactions' => $counts,
        ]);
    }
}
