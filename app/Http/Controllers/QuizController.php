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
use Illuminate\Database\QueryException;

class QuizController extends Controller
{
    public function startQuiz($courseCode, $quizId)
    {
        $studentId = Auth::guard('student')->id();
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));

        try {
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
        } catch (QueryException $e) {
            Log::error("Database error in startQuiz", [
                'error' => $e->getMessage(),
                'quizId' => $quizId,
                'courseCode' => $formattedCourseCode
            ]);
            return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))])
                ->with('error', 'Gagal mengakses database. Hubungi admin.');
        }
    }

    public function submitQuiz(Request $request, $courseCode, $quizId)
    {
        $studentId = Auth::guard('student')->id();
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));

        try {
            $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();
            $quiz = Quiz::where('id', $quizId)->where('course_code', $formattedCourseCode)->firstOrFail();

            $existingAttempt = StudentAttempt::where('student_id', $studentId)
                ->where('quiz_id', $quizId)
                ->where('course_id', $course->id)
                ->where('task_number', $quiz->task_number)
                ->first();

            if ($existingAttempt) {
                Log::warning("Quiz already attempted on submit", ['studentId' => $studentId, 'quizId' => $quizId]);
                return redirect()->route('kuis.review', ['courseCode' => $courseCode, 'quizId' => $quizId])
                    ->with('error', 'Anda telah mengerjakan kuis ini.');
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
                'errors_easy' => 0,
                'errors_medium' => 0,
                'errors_hard' => 0,
            ]);

            $score = 0;
            $errors = ['easy' => 0, 'medium' => 0, 'hard' => 0];

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
                    } else {
                        $errors[$question->difficulty]++;
                    }

                    StudentAnswer::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                        'selected_option' => $selectedOption,
                        'is_correct' => $isCorrect,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                $score = min(100, round($score, 2));
            } else {
                $correctHardCount = 0;
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
                            $correctHardCount++;
                        }
                    } else {
                        $errors[$question->difficulty]++;
                    }

                    StudentAnswer::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $questionId,
                        'selected_option' => $selectedOption,
                        'is_correct' => $isCorrect,
                    ]);
                }

                if ($correctHardCount == 1) {
                    $score += 20;
                } elseif ($correctHardCount == 2) {
                    $score += 35;
                } elseif ($correctHardCount == 3) {
                    $score += 50;
                }
            }

            $attempt->score = $score;
            $attempt->errors_easy = $errors['easy'];
            $attempt->errors_medium = $errors['medium'];
            $attempt->errors_hard = $errors['hard'];
            $attempt->save();

            // Update feedback average_score for Task 5
            if ($quiz->task_number == 5) {
                $feedback = Feedback::where('student_id', $studentId)
                    ->where('course_id', $course->id)
                    ->first();

                if ($feedback) {
                    $allAttempts = StudentAttempt::where('student_id', $studentId)
                        ->where('course_id', $course->id)
                        ->whereIn('task_number', [1, 2, 3, 4, 5])
                        ->pluck('score', 'task_number')
                        ->toArray();

                    // Calculate new average: (T1 + T2 + T3 + T4 + T5) / 5
                    $totalScore = 0;
                    foreach ([1, 2, 3, 4, 5] as $task) {
                        $totalScore += $allAttempts[$task] ?? 0;
                    }
                    $newAverage = $totalScore / 5;

                    $feedback->update([
                        'average_score' => $newAverage
                    ]);

                    Log::info("Feedback average_score updated for Task 5", [
                        'studentId' => $studentId,
                        'courseId' => $course->id,
                        'feedbackId' => $feedback->id,
                        'newAverage' => $newAverage,
                        'taskScores' => $allAttempts
                    ]);
                } else {
                    Log::error("Feedback not found for average_score update", [
                        'studentId' => $studentId,
                        'courseId' => $course->id,
                        'quizId' => $quizId
                    ]);
                }
            }

            Log::info("Quiz submitted", [
                'studentId' => $studentId,
                'quizId' => $quizId,
                'taskNumber' => $quiz->task_number,
                'score' => $score,
                'errors' => $errors,
                'answers' => array_keys($answers)
            ]);

            if ($quiz->task_number != 5) {
                $completedTasks = StudentAttempt::where('student_id', $studentId)
                    ->where('course_id', $course->id)
                    ->whereIn('task_number', [1, 2, 3, 4])
                    ->get();

                if ($completedTasks->count() == 4) {
                    $this->generateFeedback($studentId, $course->id);
                    $feedback = Feedback::where('student_id', $studentId)->where('course_id', $course->id)->first();
                    if ($feedback) {
                        $failed_tasks = json_decode($feedback->failed_tasks, true) ?? [];
                        $question_distribution = json_decode($feedback->question_distribution, true);
                        $question_weights = json_decode($feedback->question_weights, true);
                        $gradeScale = [
                            85 => 'A', 80 => 'A-', 75 => 'B+', 70 => 'B', 65 => 'B-',
                            60 => 'C+', 55 => 'C', 45 => 'D', 0 => 'E'
                        ];
                        $grades = [];
                        $itsReport = "Laporan ITS:\n";
                        foreach ($completedTasks as $completedTask) {
                            $grade = 'E';
                            foreach ($gradeScale as $threshold => $letter) {
                                if ($completedTask->score >= $threshold) {
                                    $grade = $letter;
                                    break;
                                }
                            }
                            $grades[$completedTask->task_number] = $grade;
                            $itsReport .= "- Tugas {$completedTask->task_number}: {$completedTask->score}/100 ({$grade})" . ($completedTask->score < 55 ? " [Gagal]" : "") . "\n";
                        }
                        $itsReport .= "Rata-rata: " . number_format($feedback->average_score, 2) . "\n";
                        if (!empty($failed_tasks)) {
                            $itsReport .= "Tugas gagal: " . implode(', ', $failed_tasks) . "\n";
                            $itsReport .= "Tugas tambahan: {$question_distribution['easy']} easy, {$question_distribution['medium']} medium, {$question_distribution['hard']} hard\n";
                            $itsReport .= "Bobot: Easy " . number_format($question_weights['easy'], 2) . ", Medium " . number_format($question_weights['medium'], 2) . ", Hard " . number_format($question_weights['hard'], 2);
                        } else {
                            $itsReport .= "Semua tugas lulus!";
                        }

                        Log::info("Feedback pop-up generated", [
                            'studentId' => $studentId,
                            'courseId' => $course->id,
                            'itsReport' => $itsReport
                        ]);

                        return redirect()->route('kuis.review', ['courseCode' => $courseCode, 'quizId' => $quizId])
                            ->with([
                                'quiz_completed' => ['quiz_number' => $quiz->task_number, 'score' => $score],
                                'feedback_popup' => [
                                    'title' => 'Feedback Tugas',
                                    'text' => $itsReport,
                                    'redirect' => route('feedback.show', ['courseCode' => strtolower(str_replace('-', '', $course->course_code))])
                                ]
                            ]);
                    }
                }
            }

            return redirect()->route('kuis.review', ['courseCode' => $courseCode, 'quizId' => $quizId])
                ->with('quiz_completed', ['quiz_number' => $quiz->task_number, 'score' => $score]);
        } catch (QueryException $e) {
            Log::error("Database error in submitQuiz", [
                'error' => $e->getMessage(),
                'studentId' => $studentId,
                'quizId' => $quizId
            ]);
            return redirect()->back()->with('error', 'Gagal menyimpan jawaban kuis. Hubungi admin.');
        }
    }


    public function reviewQuiz($courseCode, $quizId)
    {
        $studentId = Auth::guard('student')->id();
        $formattedCourseCode = strtoupper(preg_replace('/([a-zA-Z]+)(\d+)([a-zA-Z]*)/', '$1$2-$3', $courseCode));

        try {
            $course = Course::where('course_code', $formattedCourseCode)->firstOrFail();
            $quiz = Quiz::where('id', $quizId)->where('course_code', $formattedCourseCode)->firstOrFail();

            // For Task 5, check feedback.additional_quiz_id
            if ($quiz->task_number == 5) {
                $feedback = Feedback::where('course_id', $course->id)
                    ->where('student_id', $studentId)
                    ->where('additional_quiz_id', $quizId)
                    ->first();
                if (!$feedback) {
                    Log::error("No feedback found for Task 5 review", [
                        'studentId' => $studentId,
                        'quizId' => $quizId,
                        'courseId' => $course->id
                    ]);
                    return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))])
                        ->with('error', 'Data kuis tambahan tidak ditemukan.');
                }
            }

            $attempt = StudentAttempt::where('student_id', $studentId)
                ->where('quiz_id', $quizId)
                ->where('course_id', $course->id)
                ->where('task_number', $quiz->task_number)
                ->first();

            if (!$attempt) {
                Log::error("No attempt found for review", [
                    'studentId' => $studentId,
                    'quizId' => $quizId,
                    'courseId' => $course->id,
                    'taskNumber' => $quiz->task_number
                ]);
                return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))])
                    ->with('error', 'Tidak ada data kuis yang ditemukan.');
            }

            // Fetch StudentAnswer records sorted by id
            $studentAnswers = StudentAnswer::where('attempt_id', $attempt->id)
                ->orderBy('id', 'asc')
                ->get()
                ->keyBy('question_id');

            if ($studentAnswers->isEmpty()) {
                Log::error("No student answers found for review", [
                    'studentId' => $studentId,
                    'quizId' => $quizId,
                    'attemptId' => $attempt->id
                ]);
                return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))])
                    ->with('error', 'Tidak ada jawaban yang ditemukan untuk kuis ini.');
            }

            // Get question IDs in the order of StudentAnswer
            $questionIds = $studentAnswers->pluck('question_id')->toArray();

            // Fetch Questions in the same order as question IDs
            $questions = Question::whereIn('id', $questionIds)
                ->where('course_id', $course->id)
                ->orderByRaw('FIELD(id, ' . implode(',', $questionIds) . ')')
                ->get();

            if ($questions->count() != ($quiz->task_number == 5 ? 20 : 10)) {
                Log::warning("Incorrect number of questions for review", [
                    'studentId' => $studentId,
                    'quizId' => $quizId,
                    'taskNumber' => $quiz->task_number,
                    'expected' => $quiz->task_number == 5 ? 20 : 10,
                    'found' => $questions->count()
                ]);
            }

            return view('lecture.reviewtugas', [
                'quiz' => $quiz,
                'questions' => $questions,
                'courseCode' => $courseCode,
                'quizId' => $quizId,
                'course' => $course,
                'attempt' => $attempt,
                'studentAnswers' => $studentAnswers,
                'score' => $attempt->score,
            ]);
        } catch (QueryException $e) {
            Log::error("Database error in reviewQuiz", [
                'error' => $e->getMessage(),
                'studentId' => $studentId,
                'quizId' => $quizId,
                'courseCode' => $formattedCourseCode
            ]);
            return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))])
                ->with('error', 'Gagal mengakses data review. Hubungi admin.');
        } catch (\Exception $e) {
            Log::error("General error in reviewQuiz", [
                'error' => $e->getMessage(),
                'studentId' => $studentId,
                'quizId' => $quizId,
                'courseCode' => $formattedCourseCode
            ]);
            return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', $courseCode))])
                ->with('error', 'Gagal mengakses data review. Hubungi admin.');
        }
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
            60 => 'C+', 55 => 'C', 45 => 'D', 0 => 'E'
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
            if ($score < 55) {
                $failedTasks[$task] = $score;
            }
        }

        Log::info("Failed tasks calculated", [
            'studentId' => $studentId,
            'courseId' => $courseId,
            'failedTasks' => array_keys($failedTasks),
            'scores' => $scores
        ]);

        // Hitung total errors dari kolom errors_easy, errors_medium, errors_hard untuk tugas gagal
        $errors = ['easy' => 0, 'medium' => 0, 'hard' => 0];
        $failedTaskNumbers = array_keys($failedTasks);
        $num_failed_tasks = count($failedTasks);

        if (!empty($failedTaskNumbers)) {
            foreach ($attempts->whereIn('task_number', $failedTaskNumbers) as $attempt) {
                $errors['easy'] += $attempt->errors_easy ?? 0;
                $errors['medium'] += $attempt->errors_medium ?? 0;
                $errors['hard'] += $attempt->errors_hard ?? 0;
            }
        }

        Log::info("Errors calculated", ['errors' => $errors]);

        // Jumlah soal per tugas
        $questions_per_task = [
            'easy' => 4,
            'medium' => 3,
            'hard' => 3
        ];

        // Perhitungan Distribusi Soal dan Bobot untuk T5 menggunakan Bayesian Network
        $num_questions = 20;
        $bnResult = null;

        if ($num_failed_tasks == 0) {
            $bnResult = [
                'distribution' => ['easy' => 13, 'medium' => 3, 'hard' => 4],
                'task_distribution' => [],
                'weights' => ['easy' => 5, 'medium' => 10, 'hard' => 15]
            ];
        } else {
            // Bayesian Network Setup
            $difficulties = ['easy', 'medium', 'hard'];
            $tasks = $failedTaskNumbers;

            // Prior probabilities untuk Difficulty (D)
            $total_questions_per_task = array_sum($questions_per_task);
            $prior_D = [
                'easy' => $questions_per_task['easy'] / $total_questions_per_task,
                'medium' => $questions_per_task['medium'] / $total_questions_per_task,
                'hard' => $questions_per_task['hard'] / $total_questions_per_task
            ];

            // Total attempts per difficulty
            $total_soal_attempts = [
                'easy' => $questions_per_task['easy'] * $num_failed_tasks,
                'medium' => $questions_per_task['medium'] * $num_failed_tasks,
                'hard' => $questions_per_task['hard'] * $num_failed_tasks
            ];

            // Hitung success rates
            $success_rates = [];
            foreach ($difficulties as $d) {
                $error_rate = $errors[$d] / max(1, $total_soal_attempts[$d]);
                $success_rates[$d] = max(0.0001, 1 - $error_rate);
            }

            // Hitung posterior P(S = true | D)
            $total_posterior = 0;
            $posterior_S = [];
            foreach ($difficulties as $d) {
                $posterior_S[$d] = $success_rates[$d] * $prior_D[$d];
                $total_posterior += $posterior_S[$d];
            }

            // Normalisasi posterior
            $distribusi_soal = [];
            foreach ($difficulties as $d) {
                $distribusi_soal[$d] = ($total_posterior > 0) ? $posterior_S[$d] / $total_posterior : (1 / count($difficulties));
            }

            // Jumlah soal berdasarkan posterior
            $questions_per_difficulty = [
                'easy' => round($num_questions * $distribusi_soal['easy']),
                'medium' => round($num_questions * $distribusi_soal['medium']),
                'hard' => round($num_questions * $distribusi_soal['hard'])
            ];

            // Penyesuaian distribusi
            $total = array_sum($questions_per_difficulty);
            $max_attempts = ceil(log($num_failed_tasks * count($difficulties), 2));
            $attempt = 0;
            while ($total != $num_questions || $questions_per_difficulty['easy'] <= $questions_per_difficulty['medium'] || $questions_per_difficulty['medium'] < $questions_per_difficulty['hard']) {
                if ($attempt++ >= $max_attempts) {
                    $questions_per_difficulty = ['easy' => 9, 'medium' => 6, 'hard' => 5];
                    break;
                }
                if ($total > $num_questions) {
                    $excess = $total - $num_questions;
                    foreach (['hard', 'medium'] as $d) {
                        if ($excess <= 0) break;
                        $reduce = min($excess, max(0, $questions_per_difficulty[$d] - 1));
                        $questions_per_difficulty[$d] -= $reduce;
                        $excess -= $reduce;
                    }
                } elseif ($total < $num_questions) {
                    $questions_per_difficulty['easy'] += $num_questions - $total;
                }
                foreach (['easy', 'medium', 'hard'] as $d) {
                    $questions_per_difficulty[$d] = max(1, $questions_per_difficulty[$d]);
                }
                if ($questions_per_difficulty['medium'] < $questions_per_difficulty['hard']) {
                    $questions_per_difficulty['medium'] = $questions_per_difficulty['hard'];
                }
                if ($questions_per_difficulty['easy'] <= $questions_per_difficulty['medium']) {
                    $questions_per_difficulty['easy'] = $questions_per_difficulty['medium'] + 1;
                }
                $total = array_sum($questions_per_difficulty);
            }

            // Validasi question_distribution
            if ($total != 20 || !isset($questions_per_difficulty['easy'], $questions_per_difficulty['medium'], $questions_per_difficulty['hard'])) {
                Log::error("Invalid question distribution before task allocation", [
                    'studentId' => $studentId,
                    'courseId' => $courseId,
                    'questions_per_difficulty' => $questions_per_difficulty,
                    'total' => $total
                ]);
                $questions_per_difficulty = ['easy' => 9, 'medium' => 6, 'hard' => 5];
            }

            // Distribusi ke failed tasks dengan minimal 1 soal per kesulitan per tugas
            $task_distribution = [];
            foreach ($failedTaskNumbers as $task) {
                $task_distribution[strval($task)] = ['easy' => 1, 'medium' => 1, 'hard' => 1];
            }

            // Hitung total soal yang sudah dialokasikan
            $allocated_questions = [
                'easy' => $num_failed_tasks,
                'medium' => $num_failed_tasks,
                'hard' => $num_failed_tasks
            ];

            // Distribusi sisa soal secara merata dengan sisa berdasarkan error
            foreach (['easy', 'medium', 'hard'] as $difficulty) {
                $remaining_count = $questions_per_difficulty[$difficulty] - $allocated_questions[$difficulty];
                if ($remaining_count > 0) {
                    $base_count = floor($remaining_count / $num_failed_tasks);
                    $remainder = $remaining_count % $num_failed_tasks;

                    foreach ($failedTaskNumbers as $task) {
                        $task_distribution[strval($task)][$difficulty] += $base_count;
                        $allocated_questions[$difficulty] += $base_count;
                    }

                    if ($remainder > 0) {
                        $task_errors = [];
                        foreach ($failedTaskNumbers as $task) {
                            $task_errors[$task] = $attempts->where('task_number', $task)->first()->{"errors_$difficulty"} ?? 0;
                        }
                        usort($failedTaskNumbers, function($a, $b) use ($task_errors) {
                            if ($task_errors[$a] == $task_errors[$b]) {
                                return $a <=> $b;
                            }
                            return $task_errors[$b] <=> $task_errors[$a];
                        });

                        for ($i = 0; $i < $remainder; $i++) {
                            $task_distribution[strval($failedTaskNumbers[$i])][$difficulty]++;
                            $allocated_questions[$difficulty]++;
                        }
                    }
                }
            }

            // Validasi distribusi
            $actual_counts = ['easy' => 0, 'medium' => 0, 'hard' => 0];
            foreach ($task_distribution as $task => $dist) {
                foreach (['easy', 'medium', 'hard'] as $difficulty) {
                    $actual_counts[$difficulty] += $dist[$difficulty];
                }
            }

            // Pastikan task_distribution sesuai dengan question_distribution
            foreach (['easy', 'medium', 'hard'] as $difficulty) {
                if ($actual_counts[$difficulty] != $questions_per_difficulty[$difficulty]) {
                    Log::error("Task distribution does not match question distribution", [
                        'studentId' => $studentId,
                        'courseId' => $courseId,
                        'difficulty' => $difficulty,
                        'task_distribution' => $task_distribution,
                        'expected' => $questions_per_difficulty[$difficulty],
                        'actual' => $actual_counts[$difficulty]
                    ]);
                    $questions_per_difficulty = ['easy' => 9, 'medium' => 6, 'hard' => 5];
                    $task_distribution = [];
                    foreach ($failedTaskNumbers as $task) {
                        $task_distribution[strval($task)] = ['easy' => 1, 'medium' => 1, 'hard' => 1];
                    }
                    $allocated_questions = [
                        'easy' => $num_failed_tasks,
                        'medium' => $num_failed_tasks,
                        'hard' => $num_failed_tasks
                    ];
                    foreach (['easy', 'medium', 'hard'] as $difficulty) {
                        $remaining_count = $questions_per_difficulty[$difficulty] - $allocated_questions[$difficulty];
                        if ($remaining_count > 0) {
                            $base_count = floor($remaining_count / $num_failed_tasks);
                            $remainder = $remaining_count % $num_failed_tasks;
                            foreach ($failedTaskNumbers as $task) {
                                $task_distribution[strval($task)][$difficulty] += $base_count;
                                $allocated_questions[$difficulty] += $base_count;
                            }
                            if ($remainder > 0) {
                                $task_errors = [];
                                foreach ($failedTaskNumbers as $task) {
                                    $task_errors[$task] = $attempts->where('task_number', $task)->first()->{"errors_$difficulty"} ?? 0;
                                }
                                usort($failedTaskNumbers, function($a, $b) use ($task_errors) {
                                    if ($task_errors[$a] == $task_errors[$b]) {
                                        return $a <=> $b;
                                    }
                                    return $task_errors[$b] <=> $task_errors[$a];
                                });
                                for ($i = 0; $i < $remainder; $i++) {
                                    $task_distribution[strval($failedTaskNumbers[$i])][$difficulty]++;
                                    $allocated_questions[$difficulty]++;
                                }
                            }
                        }
                    }
                    break;
                }
            }

            Log::info("Task distribution calculated", [
                'studentId' => $studentId,
                'courseId' => $courseId,
                'task_distribution' => $task_distribution,
                'actual_counts' => $actual_counts,
                'question_distribution' => $questions_per_difficulty
            ]);

            // Hitung bobot menggunakan Bayesian Network
            $default_weights = ['easy' => 5, 'medium' => 10, 'hard' => 15];
            $initial_weights = [];
            foreach ($difficulties as $d) {
                $initial_weights[$d] = $default_weights[$d] * ($posterior_S[$d] / max(0.0001, $total_posterior));
            }

            // Total bobot awal
            $total_initial_weight = 0;
            foreach ($difficulties as $d) {
                $total_initial_weight += $initial_weights[$d] * $questions_per_difficulty[$d];
            }

            // Normalisasi bobot agar 20 soal benar = 100
            $weights = [];
            foreach ($difficulties as $d) {
                $weights[$d] = ($total_initial_weight > 0) ? (100 * $initial_weights[$d] / $total_initial_weight) : ($default_weights[$d] / array_sum($default_weights) * 100 / $questions_per_difficulty[$d]);
            }

            // Penyesuaian jika bobot tidak memenuhi syarat easy < medium < hard
            if ($weights['easy'] >= $weights['medium'] || $weights['medium'] >= $weights['hard']) {
                $total_default = ($default_weights['easy'] * $questions_per_difficulty['easy']) +
                    ($default_weights['medium'] * $questions_per_difficulty['medium']) +
                    ($default_weights['hard'] * $questions_per_difficulty['hard']);
                $weights = [
                    'easy' => (100 * $default_weights['easy'] * $questions_per_difficulty['easy']) / $total_default / $questions_per_difficulty['easy'],
                    'medium' => (100 * $default_weights['medium'] * $questions_per_difficulty['medium']) / $total_default / $questions_per_difficulty['medium'],
                    'hard' => (100 * $default_weights['hard'] * $questions_per_difficulty['hard']) / $total_default / $questions_per_difficulty['hard']
                ];
            }

            $bnResult = [
                'distribution' => $questions_per_difficulty,
                'task_distribution' => $task_distribution,
                'weights' => $weights
            ];
        }

        // Validasi bnResult sebelum menyimpan
        if (!is_array($bnResult['distribution']) ||
            !isset($bnResult['distribution']['easy'], $bnResult['distribution']['medium'], $bnResult['distribution']['hard']) ||
            array_sum($bnResult['distribution']) != 20 ||
            !is_array($bnResult['task_distribution'])) {
            Log::error("Invalid bnResult before saving", [
                'studentId' => $studentId,
                'courseId' => $courseId,
                'bnResult' => $bnResult
            ]);
            $bnResult = [
                'distribution' => ['easy' => 9, 'medium' => 6, 'hard' => 5],
                'task_distribution' => $num_failed_tasks > 0 ? [] : [],
                'weights' => ['easy' => 5, 'medium' => 10, 'hard' => 15]
            ];
            // Rebuild task_distribution if needed
            if ($num_failed_tasks > 0) {
                $task_distribution = [];
                foreach ($failedTaskNumbers as $task) {
                    $task_distribution[strval($task)] = ['easy' => 1, 'medium' => 1, 'hard' => 1];
                }
                $allocated_questions = [
                    'easy' => $num_failed_tasks,
                    'medium' => $num_failed_tasks,
                    'hard' => $num_failed_tasks
                ];
                foreach (['easy', 'medium', 'hard'] as $difficulty) {
                    $remaining_count = $bnResult['distribution'][$difficulty] - $allocated_questions[$difficulty];
                    if ($remaining_count > 0) {
                        $base_count = floor($remaining_count / $num_failed_tasks);
                        $remainder = $remaining_count % $num_failed_tasks;
                        foreach ($failedTaskNumbers as $task) {
                            $task_distribution[strval($task)][$difficulty] += $base_count;
                            $allocated_questions[$difficulty] += $base_count;
                        }
                        if ($remainder > 0) {
                            $task_errors = [];
                            foreach ($failedTaskNumbers as $task) {
                                $task_errors[$task] = $attempts->where('task_number', $task)->first()->{"errors_$difficulty"} ?? 0;
                            }
                            usort($failedTaskNumbers, function($a, $b) use ($task_errors) {
                                if ($task_errors[$a] == $task_errors[$b]) {
                                    return $a <=> $b;
                                }
                                return $task_errors[$b] <=> $task_errors[$a];
                            });
                            for ($i = 0; $i < $remainder; $i++) {
                                $task_distribution[strval($failedTaskNumbers[$i])][$difficulty]++;
                                $allocated_questions[$difficulty]++;
                            }
                        }
                    }
                }
                $bnResult['task_distribution'] = $task_distribution;
            }
        }

        // Generate Feedback Text
        $feedbackText = "Halo bro, apa kabar? Nih hasil tugas-tugasmu:\n";
        foreach ($grades as $task => $score) {
            $gradeLetter = 'E';
            foreach ($gradeScale as $threshold => $letter) {
                if ($score >= $threshold) {
                    $gradeLetter = $letter;
                    break;
                }
            }
            $feedbackText .= "- Tugas $task: $score/100 ($gradeLetter)" . (in_array($task, array_keys($failedTasks)) ? " [GAGAL]" : "") . "\n";
        }
        $feedbackText .= "Rata-rata: " . number_format($averageScore, 2) . "\n";
        if (!empty($failedTasks)) {
            $failedTaskNumbers = array_keys($failedTasks);
            $feedbackText .= "Sayang banget, kamu nggak lulus di Tugas " . implode(', ', $failedTaskNumbers) . ". ";
            $feedbackText .= "Tugas tambahan (Tugas 5) dengan 20 soal: {$bnResult['distribution']['easy']} mudah, {$bnResult['distribution']['medium']} sedang, {$bnResult['distribution']['hard']} sulit.\n";
            $feedbackText .= "Bobot: Mudah " . number_format($bnResult['weights']['easy'], 2) . ", Medium " . number_format($bnResult['weights']['medium'], 2) . ", Hard " . number_format($bnResult['weights']['hard'], 2) . ".\n";
            if (!empty($bnResult['task_distribution'])) {
                $feedbackText .= "Calon soal tugas tambahan:\n";
                foreach ($bnResult['task_distribution'] as $task => $dist) {
                    $feedbackText .= "- Tugas $task: {$dist['easy']} easy, {$dist['medium']} medium, {$dist['hard']} hard\n";
                }
            }
        } else {
            $feedbackText .= "Mantap!! semua tugas lulus!";
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
                    'task_distribution' => json_encode($bnResult['task_distribution']),
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
                'weights' => $bnResult['weights'],
                'task_distribution' => $bnResult['task_distribution']
            ]);

            if (!empty($failedTasks)) {
                $this->createAdditionalQuiz($feedback, $failedTasks, $bnResult);
            }
        } catch (\Throwable $e) {
            Log::error("Failed to save feedback", [
                'error' => $e->getMessage(),
                'studentId' => $studentId,
                'courseId' => $courseId,
                'bnResult' => $bnResult
            ]);
            return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', Course::find($courseId)->course_code))])
                ->with('error', 'Gagal menyimpan feedback. Hubungi admin.');
        }
    }

    private function getQuestionsForQuiz($quizId, $courseId, $taskNumber, $studentId)
    {
        try {
            if ($taskNumber == 5) {
                $feedback = Feedback::where('course_id', $courseId)->where('student_id', $studentId)->first();
                if (!$feedback) {
                    Log::error("Feedback not found", [
                        'studentId' => $studentId,
                        'courseId' => $courseId,
                        'taskNumber' => $taskNumber
                    ]);
                    throw new \Exception("Feedback tidak tersedia untuk tugas tambahan.");
                }

                // Handle question_distribution
                $distribution = $feedback->question_distribution;
                if (is_string($distribution)) {
                    $distribution = json_decode($distribution, true);
                }
                if (!is_array($distribution) || !isset($distribution['easy'], $distribution['medium'], $distribution['hard']) ||
                    array_sum($distribution) != 20) {
                    Log::error("Invalid or missing question distribution, using fallback", [
                        'studentId' => $studentId,
                        'courseId' => $courseId,
                        'distribution' => $distribution
                    ]);
                    $distribution = ['easy' => 9, 'medium' => 6, 'hard' => 5];
                }

                // Handle task_distribution
                $task_distribution = $feedback->task_distribution;
                if (is_string($task_distribution)) {
                    $task_distribution = json_decode($task_distribution, true);
                }
                if (!is_array($task_distribution) || empty($task_distribution)) {
                    Log::error("Invalid or missing task distribution, rebuilding from failed_tasks", [
                        'studentId' => $studentId,
                        'courseId' => $courseId,
                        'task_distribution' => $task_distribution
                    ]);
                    $failed_tasks = $feedback->failed_tasks ?? [];
                    if (empty($failed_tasks)) {
                        throw new \Exception("Distribusi tugas tidak tersedia dan tidak ada tugas gagal.");
                    }
                    $task_distribution = [];
                    $num_failed_tasks = count($failed_tasks);
                    foreach ($failed_tasks as $task) {
                        $task_distribution[strval($task)] = ['easy' => 1, 'medium' => 1, 'hard' => 1];
                    }
                    $allocated_questions = ['easy' => $num_failed_tasks, 'medium' => $num_failed_tasks, 'hard' => $num_failed_tasks];
                    foreach (['easy', 'medium', 'hard'] as $difficulty) {
                        $remaining_count = $distribution[$difficulty] - $allocated_questions[$difficulty];
                        if ($remaining_count > 0) {
                            $base_count = floor($remaining_count / $num_failed_tasks);
                            $remainder = $remaining_count % $num_failed_tasks;
                            foreach ($failed_tasks as $task) {
                                $task_distribution[strval($task)][$difficulty] += $base_count;
                                $allocated_questions[$difficulty] += $base_count;
                            }
                            if ($remainder > 0) {
                                $task_errors = [];
                                foreach ($failed_tasks as $task) {
                                    $attempt = StudentAttempt::where('student_id', $studentId)
                                        ->where('course_id', $courseId)
                                        ->where('task_number', $task)
                                        ->first();
                                    $task_errors[$task] = $attempt ? ($attempt->{"errors_$difficulty"} ?? 0) : 0;
                                }
                                usort($failed_tasks, function($a, $b) use ($task_errors) {
                                    if ($task_errors[$a] == $task_errors[$b]) {
                                        return $a <=> $b;
                                    }
                                    return $task_errors[$b] <=> $task_errors[$a];
                                });
                                for ($i = 0; $i < $remainder; $i++) {
                                    $task_distribution[strval($failed_tasks[$i])][$difficulty]++;
                                    $allocated_questions[$difficulty]++;
                                }
                            }
                        }
                    }
                }

                // Validasi task_distribution
                $task_totals = ['easy' => 0, 'medium' => 0, 'hard' => 0];
                foreach ($task_distribution as $task => $dist) {
                    if (!is_array($dist)) {
                        Log::error("Invalid task distribution entry", [
                            'studentId' => $studentId,
                            'courseId' => $courseId,
                            'task' => $task,
                            'dist' => $dist
                        ]);
                        throw new \Exception("Distribusi tugas tidak valid untuk tugas {$task}.");
                    }
                    foreach (['easy', 'medium', 'hard'] as $difficulty) {
                        $count = $dist[$difficulty] ?? 0;
                        if ($count < 1) {
                            Log::error("Invalid task distribution: Minimum 1 question required", [
                                'studentId' => $studentId,
                                'courseId' => $courseId,
                                'task' => $task,
                                'difficulty' => $difficulty,
                                'count' => $count
                            ]);
                            throw new \Exception("Distribusi tugas tidak valid: Minimal 1 soal per kesulitan untuk tugas {$task}.");
                        }
                        $task_totals[$difficulty] += $count;
                    }
                }

                // Pastikan task_totals sesuai dengan question_distribution
                $total_questions = $task_totals['easy'] + $task_totals['medium'] + $task_totals['hard'];
                foreach (['easy', 'medium', 'hard'] as $difficulty) {
                    if ($task_totals[$difficulty] != $distribution[$difficulty]) {
                        Log::error("Task distribution total mismatch", [
                            'studentId' => $studentId,
                            'courseId' => $courseId,
                            'difficulty' => $difficulty,
                            'task_total' => $task_totals[$difficulty],
                            'expected' => $distribution[$difficulty],
                            'task_distribution' => $task_distribution,
                            'question_distribution' => $distribution
                        ]);
                        throw new \Exception("Total soal per kesulitan tidak sesuai dengan distribusi soal untuk {$difficulty}.");
                    }
                }

                if ($total_questions != 20) {
                    Log::error("Invalid total questions in task distribution", [
                        'studentId' => $studentId,
                        'courseId' => $courseId,
                        'total' => $total_questions,
                        'task_distribution' => $task_distribution,
                        'question_distribution' => $distribution
                    ]);
                    throw new \Exception("Total soal tidak mencapai 20 untuk tugas tambahan.");
                }

                $questions = collect();
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
                            if ($taskQuestions->count() < $count) {
                                Log::error("Insufficient questions for task and difficulty", [
                                    'studentId' => $studentId,
                                    'courseId' => $courseId,
                                    'task' => $task,
                                    'difficulty' => $difficulty,
                                    'required' => $count,
                                    'found' => $taskQuestions->count()
                                ]);
                                throw new \Exception("Soal tidak cukup untuk tugas {$task} dengan kesulitan {$difficulty}. Hanya ditemukan {$taskQuestions->count()} dari {$count} soal yang dibutuhkan.");
                            }
                            $questions = $questions->merge($taskQuestions);
                        }
                    }
                }

                if ($questions->count() != 20) {
                    Log::error("Total questions fetched not equal to 20", [
                        'studentId' => $studentId,
                        'courseId' => $courseId,
                        'totalQuestions' => $questions->count(),
                        'task_distribution' => $task_distribution,
                        'question_distribution' => $distribution
                    ]);
                    throw new \Exception("Jumlah soal yang diambil tidak mencapai 20 untuk tugas tambahan. Hanya {$questions->count()} soal ditemukan.");
                }

                return $questions->shuffle();
            }

            // Untuk tugas 1-4: 4 easy, 3 medium, 3 hard
            $questions = Question::where('course_id', $courseId)
                ->where('task_number', $taskNumber)
                ->whereIn('difficulty', ['easy', 'medium', 'hard'])
                ->inRandomOrder()
                ->get()
                ->groupBy('difficulty');

            $easy = $questions->get('easy', collect())->take(4);
            $medium = $questions->get('medium', collect())->take(3);
            $hard = $questions->get('hard', collect())->take(3);

            $allQuestions = $easy->merge($medium)->merge($hard);
            if ($allQuestions->count() != 10) {
                Log::error("Invalid question count for task 1-4", [
                    'studentId' => $studentId,
                    'courseId' => $courseId,
                    'taskNumber' => $taskNumber,
                    'questionCount' => $allQuestions->count()
                ]);
                throw new \Exception("Jumlah soal tidak mencapai 10 untuk tugas {$taskNumber}.");
            }

            return $allQuestions;
        } catch (\Exception $e) {
            Log::error("Error in getQuestionsForQuiz", [
                'error' => $e->getMessage(),
                'quizId' => $quizId,
                'courseId' => $courseId,
                'taskNumber' => $taskNumber,
                'studentId' => $studentId
            ]);
            throw $e;
        }
    }


    private function createAdditionalQuiz($feedback, $failedTasks, $bnResult)
    {
        try {
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
            $questions = collect();
            foreach ($task_distribution as $task => $dist) {
                foreach (['easy', 'medium', 'hard'] as $difficulty) {
                    $count = $dist[$difficulty] ?? 0;
                    if ($count > 0) {
                        $taskQuestions = Question::where('course_id', $courseId)
                            ->where('task_number', $task)
                            ->where('difficulty', $difficulty)
                            ->whereNotIn('id', $questions->pluck('id')->toArray())
                            ->inRandomOrder()
                            ->take($count)
                            ->get();
                        $questions = $questions->merge($taskQuestions);

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
                                ->whereIn('task_number', array_keys($failedTasks))
                                ->where('difficulty', $difficulty)
                                ->whereNotIn('id', $questions->pluck('id')->toArray())
                                ->inRandomOrder()
                                ->take($remaining)
                                ->get();
                            $questions = $questions->merge($fallbackQuestions);

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
                    ->whereIn('task_number', array_keys($failedTasks))
                    ->whereNotIn('id', $questions->pluck('id')->toArray())
                    ->inRandomOrder()
                    ->take($remaining)
                    ->get();
                $questions = $questions->merge($extraQuestions);

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

            if ($questionsCreated != 20) {
                Log::error("Failed to create 20 questions for additional quiz", [
                    'studentId' => $studentId,
                    'courseId' => $courseId,
                    'questionsCreated' => $questionsCreated
                ]);
                throw new \Exception("Gagal membuat 20 soal untuk kuis tambahan.");
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
        } catch (QueryException $e) {
            Log::error("Database error in createAdditionalQuiz", [
                'error' => $e->getMessage(),
                'studentId' => $studentId,
                'courseId' => $courseId
            ]);
            throw new \Exception("Gagal membuat kuis tambahan.");
        }
    }
}
