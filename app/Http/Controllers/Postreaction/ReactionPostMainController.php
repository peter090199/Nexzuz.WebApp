<?php

namespace App\Http\Controllers\PostReaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReactionPostMainController extends Controller
{
    
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

}
