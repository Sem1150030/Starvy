<?php

namespace Tests\Feature;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoContentSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seedPrerequisites(): User
    {
        $this->seed(SubjectSeeder::class);

        return User::factory()->create(['email' => 'test@example.com']);
    }

    public function test_it_seeds_a_full_course_tree_owned_by_the_test_user(): void
    {
        $owner = $this->seedPrerequisites();

        $this->seed(DemoContentSeeder::class);

        $course = Course::where('slug', 'fractions-fun')->first();

        $this->assertNotNull($course);
        $this->assertSame($owner->id, $course->user_id);
        $this->assertSame('math', $course->subject->slug);
        $this->assertSame(CourseStatus::Public, $course->status);
        $this->assertCount(3, $course->lessons);

        $quiz = $course->lessons->firstWhere('title', 'What is a fraction?')->quizzes->first();
        $this->assertNotNull($quiz);
        $this->assertCount(3, $quiz->questions);
    }

    public function test_it_seeds_a_question_with_multiple_correct_options(): void
    {
        $this->seedPrerequisites();
        $this->seed(DemoContentSeeder::class);

        $quiz = Course::where('slug', 'fractions-fun')->first()
            ->lessons->firstWhere('title', 'What is a fraction?')
            ->quizzes->first();

        $multiCorrect = $quiz->questions->first(
            fn ($question) => $question->options->where('is_correct', true)->count() > 1
        );

        $this->assertNotNull($multiCorrect, 'Expected a question with more than one correct option.');
    }

    public function test_it_is_idempotent(): void
    {
        $this->seedPrerequisites();

        $this->seed(DemoContentSeeder::class);
        $this->seed(DemoContentSeeder::class);

        $this->assertSame(1, Course::where('slug', 'fractions-fun')->count());
    }
}
