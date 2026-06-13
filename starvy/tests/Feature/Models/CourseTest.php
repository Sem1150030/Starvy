<?php

namespace Tests\Feature\Models;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_course_belongs_to_a_subject_and_an_owner(): void
    {
        $course = Course::factory()->create();

        $this->assertInstanceOf(Subject::class, $course->subject);
        $this->assertInstanceOf(User::class, $course->owner);
    }

    public function test_status_casts_to_the_enum_and_defaults_to_draft(): void
    {
        $course = Course::factory()->create();

        $this->assertInstanceOf(CourseStatus::class, $course->fresh()->status);
        $this->assertSame(CourseStatus::Draft, $course->status);
    }

    public function test_is_published_is_derived_from_status(): void
    {
        $draft = Course::factory()->create();
        $public = Course::factory()->published()->create();
        $private = Course::factory()->private()->create();

        $this->assertFalse($draft->is_published);
        $this->assertTrue($public->is_published);
        $this->assertTrue($private->is_published);
    }
}
