<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'task_number' => 'required|in:1,2,3,4',
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_option' => 'required|in:A,B,C,D',
            'difficulty' => 'required|in:easy,medium,hard',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            $data = $validator->validated();

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('questions', 'public');
                $data['image'] = $path;
            }

            Question::create($data);

            return response()->json(['message' => 'Soal berhasil ditambahkan']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menambahkan soal: ' . $e->getMessage()], 500);
        }
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'task_number' => 'required|in:1,2,3,4', // Explicitly require task_number
            'excel_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            if ($error === 'The task_number field is required.') {
                return response()->json(['error' => 'Pilih tugas (1-4) sebelum mengimpor.'], 422);
            }
            return response()->json(['error' => $error], 422);
        }

        try {
            $file = $request->file('excel_file');
            $courseId = $request->input('course_id');
            $taskNumber = $request->input('task_number');

            // Parse CSV manually
            $csvData = array_map('str_getcsv', file($file->getPathname()));
            $header = array_map('trim', array_shift($csvData));

            $expectedHeader = ['Question', 'Option A', 'Option B', 'Option C', 'Option D', 'Correct Option', 'Difficulty'];
            if ($header !== $expectedHeader) {
                return response()->json(['error' => 'Format CSV tidak sesuai. Header harus: ' . implode(', ', $expectedHeader)], 422);
            }

            $questions = [];
            $errors = [];
            $rowNumber = 2;

            foreach ($csvData as $row) {
                if (count($row) !== 7) {
                    $errors[] = "Baris $rowNumber: Jumlah kolom tidak sesuai (harus 7 kolom).";
                    $rowNumber++;
                    continue;
                }

                $data = [
                    'question_text' => trim($row[0]),
                    'option_a' => trim($row[1]),
                    'option_b' => trim($row[2]),
                    'option_c' => trim($row[3]),
                    'option_d' => trim($row[4]),
                    'correct_option' => strtoupper(trim($row[5])),
                    'difficulty' => strtolower(trim($row[6])),
                    'course_id' => $courseId,
                    'task_number' => $taskNumber,
                    'image' => null,
                ];

                $validator = Validator::make($data, [
                    'question_text' => 'required|string',
                    'option_a' => 'required|string',
                    'option_b' => 'required|string',
                    'option_c' => 'required|string',
                    'option_d' => 'required|string',
                    'correct_option' => 'required|in:A,B,C,D',
                    'difficulty' => 'required|in:easy,medium,hard',
                    'course_id' => 'required|exists:courses,id',
                    'task_number' => 'required|in:1,2,3,4',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Baris $rowNumber: " . $validator->errors()->first();
                    $rowNumber++;
                    continue;
                }

                $questions[] = $data;
                $rowNumber++;
            }

            if (!empty($errors)) {
                return response()->json(['error' => implode('; ', $errors)], 422);
            }

            DB::transaction(function () use ($questions) {
                foreach ($questions as $question) {
                    Question::create($question);
                }
            });

            return response()->json(['message' => 'Soal berhasil diimpor']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengimpor soal: ' . $e->getMessage()], 500);
        }
    }
}
