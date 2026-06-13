<?php

namespace Tests\Feature\Models;

use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_quiz_belongs_to_a_lesson(): void
    {
        $quiz = Quiz::factory()->create();

        $this->assertInstanceOf(Lesson::class, $quiz->lesson);
    }

    public function test_a_lesson_has_many_quizzes(): void
    {
        $lesson = Lesson::factory()->create();
        Quiz::factory()->count(3)->create(['lesson_id' => $lesson->id]);

        $this->assertCount(3, $lesson->quizzes);
    }
}
