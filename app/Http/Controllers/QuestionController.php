<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;

class QuestionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'question_text' => 'required|string',
            'options' => 'required|array|min:2',
            'correct_answer' => 'required|string|in:' . implode(',', $request->options),
        ]);

        Question::create([
            'question_text' => $request->question_text,
            'options' => json_encode($request->options),
            'correct_answer' => $request->correct_answer,
        ]);

        return response()->json(['message' => 'Question added successfully!']);
    }

    public function index()
    {
        return response()->json(Question::all());
    }
}
