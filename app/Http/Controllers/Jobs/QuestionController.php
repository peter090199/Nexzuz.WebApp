<?php

namespace App\Http\Controllers\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Jobs\Question;

class QuestionController extends Controller
{
    public function addQuestions(Request $request)
    {
        try {
            $user = Auth::user();

            // Validate the incoming request
            $validated = $request->validate([
                'job_id' => 'required|integer',
                'question_text' => 'required|string|max:255',
                'job_name' => 'required|string',
            ]);
       
            // Save question to database
            $question = Question::create([
                'question_text' => $validated['question_text'],
                'job_id' => $validated['job_id'],
                'job_name' => $validated['job_name'],
                'role_code' => $user->role_code,
                'code' => $user->code,
                'fullname' => $user->fullname,
                'company' => $user->company,
                
            ]);

            // Return JSON response
               return response()->json([
                'success' => true,
                'message' => 'Question added successfully',
            ], 201);


        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getQuestionsById($jobId)
    {
        try {
            $questions = Question::where('job_id', $jobId)->get();
            return response()->json([
                'success' => true,
                'questions' => $questions
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


}
