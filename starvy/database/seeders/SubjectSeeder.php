<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Math', 'slug' => 'math'],
            ['name' => 'Reading', 'slug' => 'reading'],
            ['name' => 'Science', 'slug' => 'science'],
            ['name' => 'Art', 'slug' => 'art'],
            ['name' => 'Music', 'slug' => 'music'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(['slug' => $subject['slug']], $subject);
        }
    }
}
