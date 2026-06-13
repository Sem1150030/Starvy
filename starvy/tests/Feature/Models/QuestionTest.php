<?php

namespace Tests\Feature\Models;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_question_belongs_to_a_quiz(): void
    {
        $question = Question::factory()->create();

        $this->assertInstanceOf(Quiz::class, $question->quiz);
    }

    public function test_a_quiz_has_many_questions(): void
    {
        $quiz = Quiz::factory()->create();
        Question::factory()->count(4)->create(['quiz_id' => $quiz->id]);

        $this->assertCount(4, $quiz->questions);
    }
}
