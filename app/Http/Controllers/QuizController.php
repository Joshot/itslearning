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
            if (!$feedback || !$feedback->question_weights) {
                Log::error("Feedback or weights not found for task 5", ['studentId' => $studentId, 'courseId' => $course->id]);
                return redirect()->back()->with('error', 'Data bobot soal tidak tersedia.');
            }
            $weights = json_decode($feedback->question_weights, true);

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
            // Scoring task 1-4 sesuai base weights
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
                        $score += 15;
                    }
                }

                StudentAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $questionId,
                    'selected_option' => $selectedOption,
                    'is_correct' => $isCorrect,
                ]);
            }
        }

        $attempt->score = $score;
        $attempt->save();

        Log::info("Quiz submitted", [
            'studentId' => $studentId,
            'quizId' => $quizId,
            'taskNumber' => $quiz->task_number,
            'score' => $score,
            'answers' => array_keys($answers)
        ]);

        // Cek apakah semua task 1-4 selesai
        if ($quiz->task_number != 5) {
            $completedTasks = StudentAttempt::where('student_id', $studentId)
                ->where('course_id', $course->id)
                ->whereIn('task_number', [1, 2, 3, 4])
                ->count();
            if ($completedTasks == 4) {
                $this->generateFeedback($studentId, $course->id);
            }
        }

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
            if (!$feedback || !$feedback->question_distribution) {
                Log::error("Feedback or question distribution not found", [
                    'studentId' => $studentId,
                    'courseId' => $courseId,
                    'taskNumber' => $taskNumber
                ]);
                throw new \Exception("Feedback atau distribusi soal tidak tersedia untuk tugas tambahan.");
            }

            $distribution = json_decode($feedback->question_distribution, true);
            if (!isset($distribution['easy'], $distribution['medium'], $distribution['hard'])) {
                Log::error("Invalid question distribution", [
                    'studentId' => $studentId,
                    'courseId' => $courseId,
                    'distribution' => $distribution
                ]);
                throw new \Exception("Distribusi soal tidak valid.");
            }

            // Validasi distribusi
            $total_questions = $distribution['easy'] + $distribution['medium'] + $distribution['hard'];
            if ($total_questions != 20 || $distribution['easy'] <= $distribution['medium'] || $distribution['medium'] < $distribution['hard']) {
                Log::error("Invalid distribution", [
                    'studentId' => $studentId,
                    'courseId' => $courseId,
                    'distribution' => $distribution,
                    'total' => $total_questions
                ]);
                throw new \Exception("Distribusi soal tidak memenuhi syarat (easy > medium >= hard, total 20).");
            }

            $failed_tasks = json_decode($feedback->failed_tasks, true) ?? [];
            if (empty($failed_tasks)) {
                Log::error("No failed tasks found for additional quiz", [
                    'studentId' => $studentId,
                    'courseId' => $courseId
                ]);
                throw new \Exception("Tidak ada tugas yang gagal untuk diambil soalnya.");
            }

            $questions = collect();
            foreach (['easy', 'medium', 'hard'] as $difficulty) {
                $count = $distribution[$difficulty] ?? 0;
                if ($count > 0) {
                    $taskQuestions = Question::where('course_id', $courseId)
                        ->whereIn('task_number', $failed_tasks)
                        ->where('difficulty', $difficulty)
                        ->inRandomOrder()
                        ->take($count)
                        ->get();
                    $questions = $questions->merge($taskQuestions);

                    if ($taskQuestions->count() < $count) {
                        Log::error("Insufficient questions for difficulty", [
                            'difficulty' => $difficulty,
                            'required' => $count,
                            'found' => $taskQuestions->count(),
                            'failed_tasks' => $failed_tasks
                        ]);
                        throw new \Exception("Soal tidak cukup untuk kesulitan {$difficulty}.");
                    }
                }
            }

            if ($questions->count() != 20) {
                Log::error("Total questions not equal to 20", [
                    'studentId' => $studentId,
                    'courseId' => $courseId,
                    'totalQuestions' => $questions->count()
                ]);
                throw new \Exception("Jumlah soal tidak mencapai 20 untuk tugas tambahan.");
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

        $bnResult = null;
        try {
            $inputData = json_encode(['errors' => $errors, 'failed_tasks' => array_keys($failedTasks), 'scores' => $failedTasks]);
            $command = escapeshellcmd("python3 " . storage_path('app/calculate_difficulty_weights.py') . " " . escapeshellarg($inputData));
            $output = shell_exec($command . ' 2>&1');
            Log::info("Python script executed", ['command' => $command, 'output' => $output]);
            if ($output && ($decoded = json_decode($output, true))) {
                if (isset($decoded['distribution'], $decoded['task_distribution'], $decoded['weights'])) {
                    // Validasi distribusi
                    $total_questions = $decoded['distribution']['easy'] + $decoded['distribution']['medium'] + $decoded['distribution']['hard'];
                    if ($total_questions != 20 || $decoded['distribution']['easy'] <= $decoded['distribution']['medium'] || $decoded['distribution']['medium'] < $decoded['distribution']['hard']) {
                        Log::error("Invalid BN distribution", [
                            'studentId' => $studentId,
                            'courseId' => $courseId,
                            'distribution' => $decoded['distribution']
                        ]);
                        throw new \Exception("Distribusi BN tidak valid.");
                    }
                    $bnResult = [
                        'distribution' => $decoded['distribution'],
                        'task_distribution' => $decoded['task_distribution'],
                        'weights' => $decoded['weights']
                    ];
                } else {
                    Log::error("Invalid Python script output", ['output' => $output]);
                    throw new \Exception("Output Python tidak valid.");
                }
            } else {
                Log::error("Python script failed to produce output", ['command' => $command, 'output' => $output]);
                throw new \Exception("Skrip Python gagal menghasilkan output.");
            }
        } catch (\Throwable $e) {
            Log::error("Python script error", [
                'error' => $e->getMessage(),
                'studentId' => $studentId,
                'courseId' => $courseId,
                'errors' => $errors,
                'failedTasks' => array_keys($failedTasks)
            ]);
            // Fallback distribusi berdasarkan error
            $total_errors = array_sum($errors) or 1;
            $success_dist = [
                'easy' => max(1, 10 - round(5 * $errors['easy'] / $total_errors)),
                'medium' => max(1, 6 - round(3 * $errors['medium'] / $total_errors)),
                'hard' => max(1, 4 - round(2 * $errors['hard'] / $total_errors))
            ];
            $total = $success_dist['easy'] + $success_dist['medium'] + $success_dist['hard'];
            if ($total != 20) {
                $success_dist['easy'] += 20 - $total;
            }
            $bnResult = [
                'distribution' => $success_dist,
                'task_distribution' => [],
                'weights' => [
                    'easy' => 5.0 * min(2, $success_dist['easy'] / 4),
                    'medium' => 10.0 * min(2, $success_dist['medium'] / 3),
                    'hard' => 15.0 * min(2, $success_dist['hard'] / 3)
                ]
            ];
        }

        $taskDistribution = [];
        if (!empty($failedTasks)) {
            $num_failed_tasks = count($failedTasks);
            foreach ($bnResult['distribution'] as $difficulty => $totalCount) {
                $baseCount = intdiv($totalCount, $num_failed_tasks);
                $remainder = $totalCount % $num_failed_tasks;
                $sorted_tasks = array_keys($failedTasks);
                usort($sorted_tasks, function($a, $b) use ($failedTasks) {
                    return $failedTasks[$a] <=> $failedTasks[$b];
                });

                $remainder_index = 0;
                foreach ($sorted_tasks as $task) {
                    $taskDistribution[$task][$difficulty] = $baseCount;
                    if ($remainder_index < $remainder) {
                        $taskDistribution[$task][$difficulty]++;
                        $remainder_index++;
                    }
                }
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

        try {
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
        } catch (\Throwable $e) {
            Log::error("Failed to save feedback", [
                'error' => $e->getMessage(),
                'studentId' => $studentId,
                'courseId' => $courseId
            ]);
            return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $course->course_code))])
                ->with('error', 'Gagal menyimpan feedback. Hubungi admin.');
        }
    }

    private function createAdditionalQuiz($feedback, $failedTasks, $bnResult)
    {
        $studentId = $feedback->student_id;
        $courseId = $feedback->course_id;
        $task_distribution = $bnResult['task_distribution'] ?? [];
        $course = Course::find($courseId);

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
