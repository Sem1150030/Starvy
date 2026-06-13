<?php

namespace Tests\Feature\Models;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthoringHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_full_relationship_chain_resolves(): void
    {
        $option = Option::factory()->create();

        $question = $option->question;
        $quiz = $question->quiz;
        $lesson = $quiz->lesson;
        $course = $lesson->course;
        $subject = $course->subject;

        $this->assertInstanceOf(Question::class, $question);
        $this->assertInstanceOf(Quiz::class, $quiz);
        $this->assertInstanceOf(Lesson::class, $lesson);
        $this->assertInstanceOf(Course::class, $course);
        $this->assertInstanceOf(Subject::class, $subject);
    }

    public function test_deleting_a_course_cascades_to_all_descendants(): void
    {
        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);
        $quiz = Quiz::factory()->create(['lesson_id' => $lesson->id]);
        $question = Question::factory()->create(['quiz_id' => $quiz->id]);
        $option = Option::factory()->create(['question_id' => $question->id]);

        $course->delete();

        $this->assertDatabaseMissing('lessons', ['id' => $lesson->id]);
        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
        $this->assertDatabaseMissing('options', ['id' => $option->id]);
    }
}
