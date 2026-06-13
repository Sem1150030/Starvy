<?php

namespace Tests\Feature;

use App\Models\Subject;
use Database\Seeders\SubjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_five_fixed_subjects(): void
    {
        $this->seed(SubjectSeeder::class);

        $this->assertSame(5, Subject::count());

        foreach (['math', 'reading', 'science', 'art', 'music'] as $slug) {
            $this->assertDatabaseHas('subjects', ['slug' => $slug]);
        }
    }

    public function test_seeding_twice_does_not_duplicate_subjects(): void
    {
        $this->seed(SubjectSeeder::class);
        $this->seed(SubjectSeeder::class);

        $this->assertSame(5, Subject::count());
    }
}
