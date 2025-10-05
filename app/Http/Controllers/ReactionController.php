<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;

class ReactionController extends Controller
{
   /**
     * List all reactions
     */
    public function index()
    {
        return response()->json(Reaction::all());
    }

    public function saveReaction(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
              'post_id' => 'required|integer', 
              'post_uuidOrUind' => 'required|string|max:100',
              'reaction' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();

            $data = Reaction::create([
                'post_id'       => $validated['post_id'],
                'post_uuidOrUind'=> $validated['post_uuidOrUind'],
                'reaction'         => $validated['reaction'],
                'code'          => "702"
                // 'role_code'     => $user->role_code,
                // 'fullname'      => $user->fullname,
            ]);
         
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reaction saved successfully',
                'data' => $data,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed.',
                'success' => false,
                'errors'  => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Something went wrong while saving.',
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getReactionPost(string $post_id)
    {
        $data = DB::select('SELECT code,getFullname(code) AS fullname,getUserprofilepic(code) AS photo_pic,
            post_id,reaction,create_at FROM reactionPost WHERE post_id = ? AND reaction !="Unlike"', [$post_id]);

        $grouped = [];
        foreach ($data as $item) {
            $type = $item->reaction;
    
            if (!isset($grouped[$type])) {
                $grouped[$type] = [
                    'reaction' => $type,
                    'count' => 0,
                    'person' => []
                ];
            }
            $grouped[$type]['count']++;
            $grouped[$type]['person'][] = [
                "code" => $item->code,
                "fullname" =>$item->fullname,
                "photo_pic"=> $item->photo_pic ?? 'https://lightgreen-pigeon-122992.hostingersite.com/storage/app/public/uploads/DEFAULTPROFILE/DEFAULTPROFILE.png' 
            ];
        }
        $react = array_values($grouped);
        $result = [
            'count' => count($data),
            'reaction' => $data,
            'react' => $react
        ];
    
        return response()->json($result);
    }


    public function storexx(Request $request)
    {
            $request->validate([
            'post_id' => 'required|integer', // changed to integer
            'post_uuidOrUind' => 'required|string|max:100',
            'reaction' => 'nullable|string|max:255',
        ]);
        $currentUserCode = Auth::user()->code;
        $reaction = Reaction::create($request->all());

        try {
            $reaction = Reaction::create([
                'code' => $currentUserCode,
                'data' => $reaction,
            ]);
        } catch (\Exception $e) {
              return response()->json($e);
        }


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
