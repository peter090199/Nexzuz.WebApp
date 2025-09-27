<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
   /**
     * List all reactions
     */
    public function index()
    {
        return response()->json(Reaction::all());
    }

    /**
     * Store a new reaction
     */
    public function store(Request $request)
    {
            $request->validate([
            'post_id' => 'required|integer', // changed to integer
            'post_uuidOrUind' => 'required|string|max:100',
            'reaction' => 'nullable|string|max:255',
        ]);

        $reaction = Reaction::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Reaction created successfully!',
            'data' => $reaction,
        ], 201);
    }

    /**
     * Show a single reaction
     */
    public function show($id)
    {
        $reaction = Reaction::find($id);

        if (!$reaction) {
            return response()->json(['message' => 'Reaction not found'], 404);
        }

        return response()->json($reaction);
    }

    /**
     * Update a reaction
     */
    public function update(Request $request, $id)
    {
        $reaction = Reaction::find($id);

        if (!$reaction) {
            return response()->json(['message' => 'Reaction not found'], 404);
        }

        $request->validate([
            'code' => 'sometimes|string|in:like,heart,haha,wow,sad,angry',
            'reaction' => 'nullable|string|max:255',
        ]);

        $reaction->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Reaction updated successfully!',
            'data' => $reaction,
        ]);
    }

    /**
     * Delete a reaction
     */
    public function destroy($id)
    {
        $reaction = Reaction::find($id);

        if (!$reaction) {
            return response()->json(['message' => 'Reaction not found'], 404);
        }

        $reaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reaction deleted successfully!',
        ]);
    }
}
