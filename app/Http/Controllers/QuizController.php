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
                'errors' => json_encode(['easy' => 0, 'medium' => 0, 'hard' => 0]),
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
            $attempt->errors = json_encode($errors);
            $attempt->save();

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
                        foreach ($completedTasks as $attempt) {
                            $grade = 'E';
                            foreach ($gradeScale as $threshold => $letter) {
                                if ($attempt->score >= $threshold) {
                                    $grade = $letter;
                                    break;
                                }
                            }
                            $grades[$attempt->task_number] = $grade;
                            $itsReport .= "- Tugas {$attempt->task_number}: {$attempt->score}/100 ({$grade})" . ($attempt->score < 55 ? " [Gagal]" : "") . "\n";
                        }
                        $itsReport .= "Rata-rata: " . number_format($feedback->average_score, 2) . "\n";
                        if (!empty($failed_tasks)) {
                            $itsReport .= "Tugas gagal: " . implode(', ', $failed_tasks) . "\n";
                            $itsReport .= "Tugas tambahan: {$question_distribution['easy']} easy, {$question_distribution['medium']} medium, {$question_distribution['hard']} hard\n";
                            $itsReport .= "Bobot: Easy " . number_format($question_weights['easy'], 2) . ", Sedang " . number_format($question_weights['medium'], 2) . ", Sulit " . number_format($question_weights['hard'], 2);
                        } else {
                            $itsReport .= "Semua tugas lulus!";
                        }

                        Log::info("Feedback pop-up generated", [
                            'studentId' => $studentId,
                            'courseId' => $course->id,
                            'itsReport' => $itsReport
                        ]);

                        return view('matkul.kuis', [
                            'quiz' => $quiz,
                            'questions' => $questions,
                            'courseCode' => $courseCode,
                            'quizId' => $quizId,
                            'course' => $course,
                            'amount' => $attempt,
                            'attempt' => $attempt,
                            'studentAnswers' => StudentAnswer::where('attempt_id', $attempt->id)->get()->keyBy('question_id'),
                            'score' => $score,
                        ])->with([
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

            return view('matkul.kuis', [
                'quiz' => $quiz,
                'questions' => $questions,
                'courseCode' => $courseCode,
                'quizId' => $quizId,
                'course' => $course,
                'attempt' => $attempt,
                'studentAnswers' => StudentAnswer::where('attempt_id', $attempt->id)->get()->keyBy('question_id'),
                'score' => $score,
            ])->with('quiz_completed', ['quiz_number' => $quiz->task_number, 'score' => $score]);
        } catch (QueryException $e) {
            Log::error("Database error in submitQuiz", [
                'error' => $e->getMessage(),
                'studentId' => $studentId,
                'quizId' => $quizId
            ]);
            return redirect()->back()->with('error', 'Gagal menyimpan jawaban kuis. Hubungi admin.');
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

        // Hitung errors dan task_errors dari StudentAnswer hanya untuk tugas yang gagal
        $errors = ['easy' => 0, 'medium' => 0, 'hard' => 0];
        $task_errors = [];
        $failedTaskNumbers = array_keys($failedTasks);

        if (!empty($failedTaskNumbers)) {
            $attemptIds = $attempts->whereIn('task_number', $failedTaskNumbers)->pluck('id')->toArray();

            $answers = StudentAnswer::whereIn('attempt_id', $attemptIds)
                ->join('questions', 'student_answers.question_id', '=', 'questions.id')
                ->join('student_attempts', 'student_answers.attempt_id', '=', 'student_attempts.id')
                ->select('questions.difficulty', 'student_answers.is_correct', 'student_attempts.task_number')
                ->get();

            foreach ($answers as $answer) {
                if (!$answer->is_correct) {
                    $task = strval($answer->task_number);
                    $difficulty = $answer->difficulty;
                    $errors[$difficulty]++;
                    if (!isset($task_errors[$task])) {
                        $task_errors[$task] = ['easy' => 0, 'medium' => 0, 'hard' => 0];
                    }
                    $task_errors[$task][$difficulty]++;
                }
            }
        }

        Log::info("Errors calculated", ['errors' => $errors, 'task_errors' => $task_errors]);

        // Ambil jumlah soal per kesulitan dari database
        $questions_per_task = ['easy' => 0, 'medium' => 0, 'hard' => 0];
        $question_counts = Question::where('course_id', $courseId)
            ->whereIn('task_number', [1, 2, 3, 4])
            ->select('difficulty')
            ->groupBy('difficulty')
            ->selectRaw('difficulty, COUNT(*) as count')
            ->get()
            ->keyBy('difficulty');

        foreach (['easy', 'medium', 'hard'] as $difficulty) {
            $questions_per_task[$difficulty] = $question_counts->has($difficulty)
                ? $question_counts[$difficulty]->count / 4
                : 0;
        }

        if (array_sum($questions_per_task) == 0) {
            Log::error("No questions found in database", [
                'studentId' => $studentId,
                'courseId' => $courseId
            ]);
            return redirect()->route('course.show', ['courseCode' => strtolower(str_replace('-', '', Course::find($courseId)->course_code))])
                ->with('error', 'Tidak ada soal di database untuk menghitung feedback.');
        }

        // Bayesian Network Calculation
        $num_failed_tasks = count($failedTasks);
        $bnResult = null;

        if ($num_failed_tasks == 0) {
            $bnResult = [
                'distribution' => ['easy' => 10, 'medium' => 5, 'hard' => 5],
                'task_distribution' => [],
                'weights' => ['easy' => 5.0, 'medium' => 10.0, 'hard' => 15.0]
            ];
        } else {
            // 1. Prior: P(Difficulty=d)
            $total_questions = array_sum($questions_per_task);
            $prior = [];
            foreach (['easy', 'medium', 'hard'] as $d) {
                $prior[$d] = $total_questions > 0 ? $questions_per_task[$d] / $total_questions : 0;
            }
            Log::info("BN Prior", ['prior' => $prior]);

            // 2. Total Error
            $total_error = array_sum($errors) ?: 1;
            Log::info("BN Total Error", ['total_error' => $total_error]);

            // 3. Error Rates dan Success Rates
            $error_rates = [];
            $success_rates = [];
            foreach (['easy', 'medium', 'hard'] as $d) {
                $total_attempts = $questions_per_task[$d] * $num_failed_tasks;
                $error_rates[$d] = $total_attempts ? min(0.99, max(0.01, $errors[$d] / $total_attempts)) : 0.01;
                $success_rates[$d] = 1 - $error_rates[$d];
            }
            Log::info("BN Error Rates", ['error_rates' => $error_rates]);
            Log::info("BN Success Rates", ['success_rates' => $success_rates]);

            // 4. Posterior: P(Difficulty=d | Error=False)
            $p_error_false = 0;
            foreach (['easy', 'medium', 'hard'] as $d) {
                $p_error_false += $success_rates[$d] * $prior[$d];
            }
            $success_dist = [];
            foreach (['easy', 'medium', 'hard'] as $d) {
                $success_dist[$d] = ($p_error_false > 0) ? ($success_rates[$d] * $prior[$d]) / $p_error_false : $prior[$d];
            }
            Log::info("BN Posterior Success Dist", ['success_dist' => $success_dist]);

            // 5. Distribusi Awal (20 soal)
            $num_questions = 20;
            $total_success = array_sum($success_dist) ?: 1;
            $questions_per_difficulty = [];
            foreach (['easy', 'medium', 'hard'] as $d) {
                $questions_per_difficulty[$d] = round($num_questions * ($success_dist[$d] / $total_success));
            }
            Log::info("BN Initial Distribution", ['questions_per_difficulty' => $questions_per_difficulty]);

            // 6. Sesuaikan Total Soal
            $total = array_sum($questions_per_difficulty);
            $max_attempts = 5;
            $attempt = 0;
            while ($total != $num_questions || $questions_per_difficulty['easy'] <= $questions_per_difficulty['medium'] || $questions_per_difficulty['medium'] < $questions_per_difficulty['hard']) {
                if ($attempt++ >= $max_attempts) {
                    Log::warning("Max adjustment attempts reached", ['questions_per_difficulty' => $questions_per_difficulty]);
                    break;
                }
                if ($total > $num_questions) {
                    $excess = $total - $num_questions;
                    foreach (['hard', 'medium', 'easy'] as $d) {
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
            Log::info("BN Adjusted Distribution", ['questions_per_difficulty' => $questions_per_difficulty]);

            // 7. Distribusi ke Failed Tasks
            $task_distribution = [];
            foreach (array_keys($failedTasks) as $task) {
                $task_distribution[strval($task)] = ['easy' => 0, 'medium' => 0, 'hard' => 0];
            }
            foreach (['easy', 'medium', 'hard'] as $difficulty) {
                $total_count = $questions_per_difficulty[$difficulty];
                $base_count = intdiv($total_count, $num_failed_tasks);
                $remainder = $total_count % $num_failed_tasks;

                $sorted_tasks = array_keys($failedTasks);
                usort($sorted_tasks, function($a, $b) use ($task_errors, $difficulty) {
                    $error_a = $task_errors[strval($a)][$difficulty] ?? 0;
                    $error_b = $task_errors[strval($b)][$difficulty] ?? 0;
                    return $error_b <=> $error_a;
                });
                Log::info("BN Sorted Tasks for $difficulty", ['sorted_tasks' => $sorted_tasks]);

                foreach ($sorted_tasks as $i => $task) {
                    $task_distribution[strval($task)][$difficulty] = $base_count + ($i < $remainder ? 1 : 0);
                }
            }
            Log::info("BN Task Distribution", ['task_distribution' => $task_distribution]);

            // 8. Hitung Bobot
            $base_weights = ['easy' => 5.0, 'medium' => 10.0, 'hard' => 15.0];
            $weights = [];
            foreach (['easy', 'medium', 'hard'] as $d) {
                $base_questions_d = $questions_per_task[$d] ?: 1;
                $weights[$d] = $base_weights[$d] * min(2, $questions_per_difficulty[$d] / $base_questions_d);
            }
            Log::info("BN Weights", ['weights' => $weights]);

            // 9. Total Distribusi
            $total_distribution = [
                'easy' => array_sum(array_column($task_distribution, 'easy')),
                'medium' => array_sum(array_column($task_distribution, 'medium')),
                'hard' => array_sum(array_column($task_distribution, 'hard'))
            ];
            Log::info("BN Total Distribution", ['total_distribution' => $total_distribution]);

            // 10. Fallback tanpa default
            if ($total_distribution['easy'] + $total_distribution['medium'] + $total_distribution['hard'] != 20 ||
                $total_distribution['easy'] <= $total_distribution['medium'] ||
                $total_distribution['medium'] < $total_distribution['hard']) {
                Log::warning("Invalid BN distribution, using success rate-based fallback", ['total_distribution' => $total_distribution]);
                $total_success_rate = array_sum($success_rates) ?: 1;
                $success_dist = [];
                foreach (['easy', 'medium', 'hard'] as $d) {
                    $success_dist[$d] = max(1, round($num_questions * ($success_rates[$d] / $total_success_rate)));
                }
                $total = array_sum($success_dist);
                if ($total != 20) {
                    $success_dist['easy'] += 20 - $total;
                }
                if ($success_dist['easy'] <= $success_dist['medium']) {
                    $success_dist['easy'] = $success_dist['medium'] + 1;
                    $success_dist['medium'] = max(1, $success_dist['medium'] - 1);
                }
                if ($success_dist['medium'] < $success_dist['hard']) {
                    $success_dist['medium'] = $success_dist['hard'];
                }
                $total_distribution = $success_dist;

                $task_distribution = [];
                foreach (array_keys($failedTasks) as $task) {
                    $task_distribution[strval($task)] = ['easy' => 0, 'medium' => 0, 'hard' => 0];
                }
                foreach (['easy', 'medium', 'hard'] as $difficulty) {
                    $total_count = $total_distribution[$difficulty];
                    $base_count = intdiv($total_count, $num_failed_tasks);
                    $remainder = $total_count % $num_failed_tasks;
                    $sorted_tasks = array_keys($failedTasks);
                    usort($sorted_tasks, function($a, $b) use ($task_errors, $difficulty) {
                        $error_a = $task_errors[strval($a)][$difficulty] ?? 0;
                        $error_b = $task_errors[strval($b)][$difficulty] ?? 0;
                        return $error_b <=> $error_a;
                    });
                    foreach ($sorted_tasks as $i => $task) {
                        $task_distribution[strval($task)][$difficulty] = $base_count + ($i < $remainder ? 1 : 0);
                    }
                }

                $weights = [];
                foreach (['easy', 'medium', 'hard'] as $d) {
                    $base_questions_d = $questions_per_task[$d] ?: 1;
                    $weights[$d] = $base_weights[$d] * min(2, $total_distribution[$d] / $base_questions_d);
                }
            }

            $bnResult = [
                'distribution' => $total_distribution,
                'task_distribution' => $task_distribution,
                'weights' => $weights
            ];
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
            $feedbackText .= "Bobot: Mudah " . number_format($bnResult['weights']['easy'], 2) . ", Sedang " . number_format($bnResult['weights']['medium'], 2) . ", Sulit " . number_format($bnResult['weights']['hard'], 2) . ".\n";
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
                    'task_score' => json_encode($grades),
                    'task_distribution' => json_encode($bnResult['task_distribution'])
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
                'courseId' => $courseId
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
                    Log::error("No failed tasks found", [
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

            $questions = $easy->merge($medium)->merge($hard);
            if ($questions->count() != 10) {
                Log::error("Invalid question count for task 1-4", [
                    'taskNumber' => $taskNumber,
                    'questionCount' => $questions->count()
                ]);
                throw new \Exception("Jumlah soal tidak mencapai 10 untuk tugas {$taskNumber}.");
            }

            return $questions;
        } catch (QueryException $e) {
            Log::error("Database error in getQuestionsForQuiz", [
                'error' => $e->getMessage(),
                'quizId' => $quizId,
                'courseId' => $courseId,
                'taskNumber' => $taskNumber
            ]);
            throw new \Exception("Gagal mengambil soal dari database.");
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
