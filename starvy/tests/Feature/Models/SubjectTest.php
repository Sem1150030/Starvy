<?php

namespace Tests\Feature\Models;

use App\Models\Subject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_subject_can_be_created(): void
    {
        $subject = Subject::factory()->create(['name' => 'Math', 'slug' => 'math']);

        $this->assertDatabaseHas('subjects', ['slug' => 'math', 'name' => 'Math']);
        $this->assertSame('math', $subject->slug);
    }
}
