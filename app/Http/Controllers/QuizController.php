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

        $questions = $this->getQuestionsForQuiz($quizId, $course->id, $quiz->task_number);
        if ($questions->count() < 10) {
            Log::error("Insufficient questions", ['quizId' => $quizId, 'questionCount' => $questions->count()]);
            return redirect()->back()->with('error', 'Tidak cukup soal untuk kuis ini.');
        }

        Log::info("Questions fetched", ['questionIds' => $questions->pluck('id')->toArray()]);

        if ($existingAttempt) {
            $studentAnswers = StudentAnswer::where('attempt_id', $existingAttempt->id)->get()->keyBy('question_id');
            return view('matkul.kuis', [
                'quiz' => $quiz,
                'questions' => $questions,
                'courseCode' => $courseCode,
                'quizId' => $quizId,
                'course' => $course,
                'attempt' => $existingAttempt,
                'studentAnswers' => $studentAnswers,
                'score' => $existingAttempt->score,
            ]);
        }

        return view('matkul.kuis', compact('quiz', 'questions', 'courseCode', 'quizId', 'course'));
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
            return redirect()->back()->with('error', 'Anda telah mengerjakan kuis ini.');
        }

        $answers = $request->input('answers', []);
        $questions = $this->getQuestionsForQuiz($quizId, $course->id, $quiz->task_number);

        if (count($answers) < $questions->count()) {
            Log::warning("Incomplete answers", ['answered' => count($answers), 'required' => $questions->count()]);
            return redirect()->back()->with('error', 'Harap isi semua jawaban sebelum mengirimkan kuis.');
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
            $score += 20;
        }
        if ($hardCorrectCount >= 2) {
            $score += 15;
        }
        if ($hardCorrectCount >= 3) {
            $score += 15;
        }

        $attempt->score = $score;
        $attempt->save();

        Log::info("Quiz submitted", [
            'studentId' => $studentId,
            'quizId' => $quizId,
            'score' => $score,
            'hardCorrect' => $hardCorrectCount,
            'answers' => $answers,
        ]);

        $studentAnswers = StudentAnswer::where('attempt_id', $attempt->id)->get()->keyBy('question_id');
        return view('matkul.kuis', [
            'quiz' => $quiz,
            'questions' => $questions,
            'courseCode' => $courseCode,
            'quizId' => $quizId,
            'course' => $course,
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
