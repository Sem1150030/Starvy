<?php

namespace Tests\Feature\Models;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_lesson_belongs_to_a_course(): void
    {
        $lesson = Lesson::factory()->create();

        $this->assertInstanceOf(Course::class, $lesson->course);
    }

    public function test_a_course_has_many_lessons(): void
    {
        $course = Course::factory()->create();
        Lesson::factory()->count(2)->create(['course_id' => $course->id]);

        $this->assertCount(2, $course->lessons);
    }
}
