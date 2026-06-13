<?php

namespace Tests\Feature\Models;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_option_belongs_to_a_question(): void
    {
        $option = Option::factory()->create();

        $this->assertInstanceOf(Question::class, $option->question);
    }

    public function test_is_correct_casts_to_boolean(): void
    {
        $option = Option::factory()->correct()->create();

        $this->assertIsBool($option->fresh()->is_correct);
        $this->assertTrue($option->is_correct);
    }

    public function test_a_question_can_have_multiple_correct_options(): void
    {
        $question = Question::factory()->create();
        Option::factory()->count(2)->correct()->create(['question_id' => $question->id]);
        Option::factory()->count(2)->create(['question_id' => $question->id]);

        $this->assertCount(4, $question->options);
        $this->assertCount(2, $question->options->where('is_correct', true));
    }
}
