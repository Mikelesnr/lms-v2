<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Models\StudentAnswer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GradingService
{
    /**
     * Submit a quiz attempt and automatically grade multiple choice questions.
     *
     * @param User $student
     * @param string $lessonId
     * @param array<int, array{question_id: string, question_option_id?: string, text_answer?: string}> $answers
     * @return QuizAttempt
     */
    public function submitQuiz(User $student, string $lessonId, array $answers): QuizAttempt
    {
        return DB::transaction(function () use ($student, $lessonId, $answers) {
            $attempt = QuizAttempt::create([
                'user_id' => $student->id,
                'lesson_id' => $lessonId,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            $totalScore = 0;
            $maxScore = 0;
            $hasPendingManualGrading = false;

            foreach ($answers as $answerData) {
                /** @var Question $question */
                $question = Question::with('options')->findOrFail($answerData['question_id']);
                $maxScore += $question->points;

                if ($question->type === 'multiple_choice') {
                    $selectedOption = null;
                    if (!empty($answerData['question_option_id'])) {
                        $selectedOption = QuestionOption::where('question_id', $question->id)
                            ->find($answerData['question_option_id']);
                    }

                    $isCorrect = $selectedOption?->is_correct ?? false;
                    $pointsAwarded = $isCorrect ? (float) $question->points : 0.0;
                    $totalScore += $pointsAwarded;

                    StudentAnswer::create([
                        'quiz_attempt_id' => $attempt->id,
                        'user_id' => $student->id,
                        'question_id' => $question->id,
                        'question_option_id' => $selectedOption?->id,
                        'text_answer' => null,
                        'is_correct' => $isCorrect,
                        'points_awarded' => $pointsAwarded,
                        'graded_at' => now(),
                    ]);
                } else {
                    // Open-ended (code snippet / essay) -> Requires instructor grading
                    $hasPendingManualGrading = true;

                    StudentAnswer::create([
                        'quiz_attempt_id' => $attempt->id,
                        'user_id' => $student->id,
                        'question_id' => $question->id,
                        'question_option_id' => null,
                        'text_answer' => $answerData['text_answer'] ?? null,
                        'is_correct' => null, // Pending evaluation
                        'points_awarded' => 0.0,
                    ]);
                }
            }

            $attempt->update([
                'total_score' => $totalScore,
                'max_score' => $maxScore,
                'status' => $hasPendingManualGrading ? 'in_progress' : 'graded',
            ]);

            return $attempt;
        });
    }

    /**
     * Submit an open-ended stand-alone assignment (outside of a multi-question quiz).
     */
    public function submitAssignment(User $student, string $questionId, string $textAnswer): StudentAnswer
    {
        $question = Question::findOrFail($questionId);

        if ($question->type !== 'open_ended') {
            throw new InvalidArgumentException('Only open-ended questions can be submitted as individual assignments.');
        }

        return StudentAnswer::create([
            'quiz_attempt_id' => null,
            'user_id' => $student->id,
            'question_id' => $question->id,
            'question_option_id' => null,
            'text_answer' => $textAnswer,
            'is_correct' => null,
            'points_awarded' => 0.0,
        ]);
    }

    /**
     * Manual grading action performed by an Instructor or Staff member.
     */
    public function gradeOpenEndedAnswer(
        StudentAnswer $answer,
        User $instructor,
        float $pointsAwarded,
        ?string $feedback = null
    ): StudentAnswer {
        return DB::transaction(function () use ($answer, $instructor, $pointsAwarded, $feedback) {
            $question = $answer->question;

            if ($pointsAwarded < 0 || $pointsAwarded > $question->points) {
                throw new InvalidArgumentException("Points awarded must be between 0 and {$question->points}.");
            }

            $isCorrect = $pointsAwarded >= ($question->points / 2);

            $answer->update([
                'points_awarded' => $pointsAwarded,
                'is_correct' => $isCorrect,
                'feedback' => $feedback,
                'graded_by' => $instructor->id,
                'graded_at' => now(),
            ]);

            // If part of a quiz attempt, recalculate attempt totals
            if ($answer->quiz_attempt_id) {
                $attempt = QuizAttempt::where('id', $answer->quiz_attempt_id)->firstOrFail();
                
                $newTotalScore = (float) $attempt->answers()->sum('points_awarded');
                $pendingAnswersCount = $attempt->answers()->whereNull('is_correct')->count();

                $attempt->update([
                    'total_score' => $newTotalScore,
                    'status' => $pendingAnswersCount === 0 ? 'graded' : 'in_progress',
                ]);
            }

            return $answer;
        });
    }
}