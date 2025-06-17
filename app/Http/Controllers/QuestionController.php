<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_option' => 'required|in:A,B,C,D',
            'task_number' => 'nullable|in:1,2,3,4',
            'course_id' => 'required|exists:courses,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Optional image validation
        ]);

        $data = $request->only([
            'question_text',
            'option_a',
            'option_b',
            'option_c',
            'option_d',
            'correct_option',
            'task_number',
            'course_id',
            'difficulty',
        ]);

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('questions', 'public');
        }

        Question::create($data);

        return response()->json(['message' => 'Soal berhasil ditambahkan!']);
    }

    public function index()
    {
        return response()->json(Question::all());
    }
}
