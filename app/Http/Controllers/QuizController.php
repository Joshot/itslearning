<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\StudentAnswer;
use App\Models\StudentAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    public function startQuiz($courseCode, $quizId)
    {
        $studentId = Auth::guard('student')->id();
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));

        $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();
        $quiz = Quiz::where('id', $quizId)->where('course_code', $formattedCourseCode)->first();

        if (!$quiz) {
            Log::error("Quiz not found", ['quizId' => $quizId, 'courseCode' => $formattedCourseCode]);
            return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))])
                ->with('error', 'Quiz tidak ditemukan.');
        }

        $existingAttempt = StudentAttempt::where('student_id', $studentId)
            ->where('quiz_id', $quizId)
            ->where('course_id', $course->id)
            ->where('task_number', $quiz->task_number)
            ->first();

        if ($existingAttempt) {
            Log::warning("Quiz already attempted", ['studentId' => $studentId, 'quizId' => $quizId]);
            return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))])
                ->with('error', 'Anda sudah mengerjakan kuis ini.');
        }

        $questions = $this->getQuestionsForQuiz($quizId, $course->id, $quiz->task_number);
        if ($questions->count() < 10) {
            Log::error("Insufficient questions", ['quizId' => $quizId, 'questionCount' => $questions->count()]);
            return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))])
                ->with('error', 'Tidak cukup soal untuk kuis ini.');
        }

        return view('matkul.kuis', compact('questions', 'courseCode', 'quizId', 'course', 'quiz'));
    }

    public function submitQuiz(Request $request, $courseCode, $quizId)
    {
        $studentId = Auth::guard('student')->id();
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));

        $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();
        $quiz = Quiz::where('id', $quizId)->where('course_code', $formattedCourseCode)->firstOrFail();

        $existingAttempt = StudentAttempt::where('student_id', $studentId)
            ->where('quiz_id', $quizId)
            ->where('course_id', $course->id)
            ->where('task_number', $quiz->task_number)
            ->first();

        if ($existingAttempt) {
            Log::warning("Quiz already attempted on submit", ['studentId' => $studentId, 'quizId' => $quizId]);
            return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))])
                ->with('error', 'Anda sudah mengerjakan kuis ini.');
        }

        $answers = $request->input('answers', []);
        $questions = $this->getQuestionsForQuiz($quizId, $course->id, $quiz->task_number);

        if (count($answers) < $questions->count()) {
            Log::warning("Incomplete answers", ['answered' => count($answers), 'required' => $questions->count()]);
            return redirect()->back()->with('error', 'Harap jawab semua soal sebelum mengirimkan kuis.');
        }

        $attempt = StudentAttempt::create([
            'student_id' => $studentId,
            'quiz_id' => $quizId,
            'course_id' => $course->id,
            'task_number' => $quiz->task_number,
            'score' => 0,
        ]);

        $score = 0;
        $hardCorrectCount = 0;

        foreach ($answers as $questionId => $selectedOption) {
            $question = Question::find($questionId);
            if (!$question) {
                Log::warning("Question not found", ['questionId' => $questionId]);
                continue;
            }

            $isCorrect = $question->correct_option == $selectedOption;
            if ($isCorrect) {
                if ($question->difficulty == 'easy') {
                    $score += 5;
                } elseif ($question->difficulty == 'medium') {
                    $score += 10;
                } elseif ($question->difficulty == 'hard') {
                    $hardCorrectCount++;
                }
            }

            StudentAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $questionId,
                'selected_option' => $selectedOption,
                'is_correct' => $isCorrect,
            ]);
        }

        // Hard scoring: 20 for first, 15 for each additional
        if ($hardCorrectCount >= 1) {
            $score += 20; // First hard correct
        }
        if ($hardCorrectCount >= 2) {
            $score += 15; // Second hard correct
        }
        if ($hardCorrectCount == 3) {
            $score += 15; // Third hard correct
        }

        $attempt->score = $score;
        $attempt->save();

        Log::info("Quiz submitted", [
            'studentId' => $studentId,
            'quizId' => $quizId,
            'score' => $score,
            'hardCorrect' => $hardCorrectCount,
        ]);

        $studentAnswers = StudentAnswer::where('attempt_id', $attempt->id)->get()->keyBy('question_id');
        return view('matkul.kuis', [
            'questions' => $questions,
            'courseCode' => $courseCode,
            'quizId' => $quizId,
            'course' => $course,
            'quiz' => $quiz,
            'attempt' => $attempt,
            'studentAnswers' => $studentAnswers,
            'score' => $score,
        ]);
    }

    private function getQuestionsForQuiz($quizId, $courseId, $taskNumber)
    {
        $easy = Question::where('difficulty', 'easy')
            ->where('course_id', $courseId)
            ->where('task_number', $taskNumber)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $medium = Question::where('difficulty', 'medium')
            ->where('course_id', $courseId)
            ->where('task_number', $taskNumber)
            ->inRandomOrder()
            ->take(3)
            ->get();

        $hard = Question::where('difficulty', 'hard')
            ->where('course_id', $courseId)
            ->where('task_number', $taskNumber)
            ->inRandomOrder()
            ->take(3)
            ->get();

        return $easy->merge($medium)->merge($hard);
    }
}
