<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\StudentAnswer;
use App\Models\StudentAttempt;
use App\Models\Feedback;
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

        $questions = $this->getQuestionsForQuiz($quizId, $course->id, $quiz->task_number, $studentId);
        $requiredQuestions = $quiz->task_number == 5 ? 20 : 10;
        if ($questions->count() < $requiredQuestions) {
            Log::error("Insufficient questions", [
                'quizId' => $quizId,
                'taskNumber' => $quiz->task_number,
                'questionCount' => $questions->count(),
                'required' => $requiredQuestions
            ]);
            return redirect()->route('feedback.show', ['courseCode' => $courseCode])
                ->with('error', 'Tidak cukup soal untuk kuis ini. Hubungi admin.');
        }

        Log::info("Questions fetched", [
            'quizId' => $quizId,
            'taskNumber' => $quiz->task_number,
            'questionIds' => $questions->pluck('id')->toArray(),
            'questionCount' => $questions->count()
        ]);

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
        $questions = $this->getQuestionsForQuiz($quizId, $course->id, $quiz->task_number, $studentId);
        $requiredQuestions = $quiz->task_number == 5 ? 20 : 10;

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
        if ($quiz->task_number == 5) {
            $feedback = Feedback::where('course_id', $course->id)->where('student_id', $studentId)->first();
            $weights = $feedback && $feedback->question_weights
                ? json_decode($feedback->question_weights, true)
                : ['easy' => 2.8, 'medium' => 5.6, 'hard' => 8.3];

            foreach ($answers as $questionId => $selectedOption) {
                $question = Question::find($questionId);
                if (!$question) {
                    Log::warning("Question not found", ['questionId' => $questionId]);
                    continue;
                }

                $isCorrect = $question->correct_option == $selectedOption;
                if ($isCorrect) {
                    $score += $weights[$question->difficulty] ?? 0;
                }

                StudentAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $questionId,
                    'selected_option' => $selectedOption,
                    'is_correct' => $isCorrect,
                ]);
            }

            $score = min(100, round($score, 2));
        } else {
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

            if ($hardCorrectCount >= 1) {
                $score += 20;
            }
            if ($hardCorrectCount >= 2) {
                $score += 15;
            }
            if ($hardCorrectCount >= 3) {
                $score += 15;
            }
        }

        $attempt->score = $score;
        $attempt->save();

        if ($quiz->task_number != 5) {
            $this->generateFeedback($studentId, $course->id);
        }

        Log::info("Quiz submitted", [
            'studentId' => $studentId,
            'quizId' => $quizId,
            'taskNumber' => $quiz->task_number,
            'score' => $score,
            'answers' => array_keys($answers)
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
        ])->with('quiz_completed', ['quiz_number' => $quiz->task_number, 'score' => $score]);
    }

    private function getQuestionsForQuiz($quizId, $courseId, $taskNumber, $studentId)
    {
        if ($taskNumber == 5) {
            $feedback = Feedback::where('course_id', $courseId)->where('student_id', $studentId)->first();
            $distribution = $feedback && $feedback->question_distribution
                ? json_decode($feedback->question_distribution, true)
                : ['easy' => 9, 'medium' => 6, 'hard' => 5];

            $questions = collect();
            foreach (['easy', 'medium', 'hard'] as $difficulty) {
                $count = $distribution[$difficulty] ?? 0;
                if ($count > 0) {
                    $taskQuestions = Question::where('course_id', $courseId)
                        ->where('task_number', 5)
                        ->where('difficulty', $difficulty)
                        ->inRandomOrder()
                        ->take($count)
                        ->get();
                    $questions = $questions->merge($taskQuestions);

                    if ($taskQuestions->count() < $count) {
                        $remaining = $count - $taskQuestions->count();
                        $fallbackQuestions = Question::where('course_id', $courseId)
                            ->whereIn('task_number', [2, 4])
                            ->where('difficulty', $difficulty)
                            ->inRandomOrder()
                            ->take($remaining)
                            ->get();
                        $questions = $questions->merge($fallbackQuestions);
                    }
                }
            }

            if ($questions->count() < 20) {
                $remaining = 20 - $questions->count();
                $extraQuestions = Question::where('course_id', $courseId)
                    ->whereIn('task_number', [2, 4])
                    ->inRandomOrder()
                    ->take($remaining)
                    ->get();
                $questions = $questions->merge($extraQuestions);
            }

            return $questions->shuffle();
        }

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

    private function generateFeedback($studentId, $courseId)
    {
        $attempts = StudentAttempt::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->whereIn('task_number', [1, 2, 3, 4])
            ->orderBy('task_number')
            ->get();

        if ($attempts->count() !== 4) {
            Log::info("Feedback not generated: Not enough attempts", [
                'studentId' => $studentId,
                'courseId' => $courseId,
                'attemptsCount' => $attempts->count()
            ]);
            return;
        }

        $scores = $attempts->pluck('score', 'task_number')->toArray();
        $averageScore = array_sum($scores) / 4;

        $gradeScale = [
            85 => 'A', 80 => 'A-', 75 => 'B+', 70 => 'B', 65 => 'B-',
            60 => 'C+', 50 => 'C', 45 => 'D', 0 => 'E'
        ];
        $grades = [];
        $failedTasks = [];
        foreach ($scores as $task => $score) {
            $grade = 'E';
            foreach ($gradeScale as $threshold => $letter) {
                if ($score >= $threshold) {
                    $grade = $letter;
                    break;
                }
            }
            $grades[$task] = $score;
            if ($score < 50) {
                $failedTasks[$task] = $score;
            }
        }

        $errors = ['easy' => 0, 'medium' => 0, 'hard' => 0];
        foreach ($attempts as $attempt) {
            $answers = StudentAnswer::where('attempt_id', $attempt->id)
                ->join('questions', 'student_answers.question_id', '=', 'questions.id')
                ->select('questions.difficulty', 'student_answers.is_correct')
                ->get();

            foreach ($answers as $answer) {
                if (!$answer->is_correct) {
                    $errors[$answer->difficulty]++;
                }
            }
        }

        $bnResult = [
            'distribution' => ['easy' => 9, 'medium' => 6, 'hard' => 5],
            'task_distribution' => [],
            'weights' => ['easy' => 2.8, 'medium' => 5.6, 'hard' => 8.3]
        ];
        try {
            $inputData = json_encode(['errors' => $errors, 'failed_tasks' => array_keys($failedTasks), 'scores' => $failedTasks]);
            $command = escapeshellcmd("python3 " . storage_path('app/calculate_difficulty_weights.py') . " " . escapeshellarg($inputData));
            $output = shell_exec($command . ' 2>&1');
            if ($output && ($decoded = json_decode($output, true))) {
                $bnResult['distribution'] = $decoded['distribution'] ?? $bnResult['distribution'];
                $bnResult['weights'] = $decoded['weights'] ?? $bnResult['weights'];
            } else {
                Log::error("Python script failed", ['command' => $command, 'output' => $output]);
            }
        } catch (\Throwable $e) {
            Log::error("Python script error", ['error' => $e->getMessage()]);
        }

        $worstTask = null;
        $lowestScore = 100;
        foreach ($failedTasks as $task => $score) {
            if ($score < $lowestScore) {
                $lowestScore = $score;
                $worstTask = $task;
            }
        }

        $taskDistribution = [];
        if (count($failedTasks) == 2) {
            $task1 = array_keys($failedTasks)[0];
            $task2 = array_keys($failedTasks)[1];
            foreach ($bnResult['distribution'] as $difficulty => $totalCount) {
                $baseCount = intdiv($totalCount, 2);
                $remainder = $totalCount % 2;

                $task1Count = $baseCount;
                $task2Count = $baseCount;

                if ($remainder) {
                    if ($worstTask == $task2) {
                        $task2Count++;
                    } else {
                        $task1Count++;
                    }
                }

                $taskDistribution[$task1][$difficulty] = $task1Count;
                $taskDistribution[$task2][$difficulty] = $task2Count;
            }
        }

        $bnResult['task_distribution'] = $taskDistribution;

        $feedbackText = "Halo bro, apa kabar? Nih hasil tugas-tugasmu:\n";
        foreach ($grades as $task => $score) {
            $gradeLetter = 'E';
            foreach ($gradeScale as $threshold => $letter) {
                if ($score >= $threshold) {
                    $gradeLetter = $letter;
                    break;
                }
            }
            $feedbackText .= "- Tugas $task: $score/100 ($gradeLetter)\n";
        }
        $feedbackText .= "Rata-rata: " . number_format($averageScore, 2) . "\n";
        if (!empty($failedTasks)) {
            $failedTaskNumbers = array_keys($failedTasks);
            $feedbackText .= "Sayang banget, kamu nggak lulus di Tugas " . implode(', ', $failedTaskNumbers) . ". ";
            $feedbackText .= "Tenang, ada tugas tambahan (Tugas 5) dengan 20 soal: {$bnResult['distribution']['easy']} mudah, {$bnResult['distribution']['medium']} sedang, {$bnResult['distribution']['hard']} sulit.\n";
            $feedbackText .= "Bobot: Mudah " . number_format($bnResult['weights']['easy'], 2) . ", Sedang " . number_format($bnResult['weights']['medium'], 2) . ", Sulit " . number_format($bnResult['weights']['hard'], 2) . ".";
        } else {
            $feedbackText .= "Mantap bro, semua tugas lulus! 😎";
        }

        $feedback = Feedback::updateOrCreate(
            ['student_id' => $studentId, 'course_id' => $courseId],
            [
                'description' => $feedbackText,
                'average_score' => $averageScore,
                'failed_tasks' => json_encode(array_keys($failedTasks)),
                'question_distribution' => json_encode($bnResult['distribution']),
                'question_weights' => json_encode($bnResult['weights']),
                'task_score' => json_encode($grades)
            ]
        );

        Log::info("Feedback saved", [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'feedbackId' => $feedback->id,
            'failedTasks' => array_keys($failedTasks),
            'averageScore' => $averageScore,
            'distribution' => $bnResult['distribution'],
            'weights' => $bnResult['weights']
        ]);

        if (!empty($failedTasks)) {
            $this->createAdditionalQuiz($feedback, $failedTasks, $bnResult);
        }
    }

    private function createAdditionalQuiz($feedback, $failedTasks, $bnResult)
    {
        $studentId = $feedback->student_id;
        $courseId = $feedback->course_id;
        $task_distribution = $bnResult['task_distribution'] ?? [];
        $course = Course::find($courseId);

        // Cek apakah quiz untuk task_number 5 sudah ada untuk course ini
        $existingQuiz = Quiz::where('course_code', $course->course_code)
            ->where('task_number', 5)
            ->first();

        if ($existingQuiz) {
            Log::info("Additional quiz already exists", [
                'studentId' => $studentId,
                'courseCode' => $course->course_code,
                'quizId' => $existingQuiz->id
            ]);
            Feedback::where('id', $feedback->id)
                ->update(['additional_quiz_id' => $existingQuiz->id]);
            return;
        }

        // Buat quiz baru untuk task_number 5
        $quiz = Quiz::create([
            'course_code' => $course->course_code,
            'task_number' => 5,
            'title' => 'Tugas Tambahan',
            'start_time' => now(),
            'end_time' => now()->addDays(7),
        ]);

        $questionsCreated = 0;
        foreach ($task_distribution as $task => $dist) {
            foreach (['easy', 'medium', 'hard'] as $difficulty) {
                $count = $dist[$difficulty] ?? 0;
                if ($count > 0) {
                    $taskQuestions = Question::where('course_id', $courseId)
                        ->where('task_number', $task)
                        ->where('difficulty', $difficulty)
                        ->inRandomOrder()
                        ->take($count)
                        ->get();

                    foreach ($taskQuestions as $question) {
                        Question::create([
                            'course_id' => $courseId,
                            'task_number' => 5,
                            'difficulty' => $question->difficulty,
                            'question_text' => $question->question_text,
                            'option_a' => $question->option_a,
                            'option_b' => $question->option_b,
                            'option_c' => $question->option_c,
                            'option_d' => $question->option_d,
                            'correct_option' => $question->correct_option
                        ]);
                        $questionsCreated++;
                    }

                    if ($taskQuestions->count() < $count) {
                        $remaining = $count - $taskQuestions->count();
                        $fallbackQuestions = Question::where('course_id', $courseId)
                            ->where('difficulty', $difficulty)
                            ->whereNotIn('task_number', [5])
                            ->inRandomOrder()
                            ->take($remaining)
                            ->get();
                        foreach ($fallbackQuestions as $question) {
                            Question::create([
                                'course_id' => $courseId,
                                'task_number' => 5,
                                'difficulty' => $question->difficulty,
                                'question_text' => $question->question_text,
                                'option_a' => $question->option_a,
                                'option_b' => $question->option_b,
                                'option_c' => $question->option_c,
                                'option_d' => $question->option_d,
                                'correct_option' => $question->correct_option
                            ]);
                            $questionsCreated++;
                        }
                    }
                }
            }
        }

        // Tambah soal tambahan kalau kurang dari 20
        if ($questionsCreated < 20) {
            $remaining = 20 - $questionsCreated;
            $extraQuestions = Question::where('course_id', $courseId)
                ->whereNotIn('task_number', [5])
                ->inRandomOrder()
                ->take($remaining)
                ->get();
            foreach ($extraQuestions as $question) {
                Question::create([
                    'course_id' => $courseId,
                    'task_number' => 5,
                    'difficulty' => $question->difficulty,
                    'question_text' => $question->question_text,
                    'option_a' => $question->option_a,
                    'option_b' => $question->option_b,
                    'option_c' => $question->option_c,
                    'option_d' => $question->option_d,
                    'correct_option' => $question->correct_option
                ]);
                $questionsCreated++;
            }
        }

        Feedback::where('id', $feedback->id)
            ->update(['additional_quiz_id' => $quiz->id]);

        Log::info("Additional quiz created", [
            'studentId' => $studentId,
            'courseCode' => $course->course_code,
            'quizId' => $quiz->id,
            'questionCount' => $questionsCreated,
            'failedTasks' => array_keys($failedTasks),
            'taskDistribution' => $task_distribution
        ]);
    }
}
