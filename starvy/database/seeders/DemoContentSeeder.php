<?php

namespace Database\Seeders;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    /**
     * Seed a small, realistic course tree so there is something to look at
     * during development. Idempotent: keyed on the course slug.
     */
    public function run(): void
    {
        $math = Subject::where('slug', 'math')->first();
        $owner = User::where('email', 'test@example.com')->first() ?? User::first();

        if (! $math || ! $owner) {
            return;
        }

        // Only build the tree once — skip if the demo course already exists.
        if (Course::where('slug', 'fractions-fun')->exists()) {
            return;
        }

        $course = Course::create([
            'subject_id' => $math->id,
            'user_id' => $owner->id,
            'title' => 'Fractions fun',
            'slug' => 'fractions-fun',
            'description' => 'Learn what fractions are and how to play with them.',
            'status' => CourseStatus::Public,
        ]);

        $intro = $course->lessons()->create([
            'title' => 'What is a fraction?',
            'body' => <<<'MD'
                A **fraction** shows a part of a whole.

                The number on top is the **numerator** — it tells you how many parts you have.
                The number on the bottom is the **denominator** — it tells you how many equal parts the whole is split into.

                So in `3/4`, you have **3** out of **4** equal parts. 🍰
                MD,
            'position' => 1,
        ]);

        $course->lessons()->create([
            'title' => 'Adding fractions',
            'body' => <<<'MD'
                When two fractions have the **same denominator**, you add the numerators and keep the denominator the same.

                For example: `1/4 + 2/4 = 3/4`.

                The bottom number stays put — only the top numbers add up!
                MD,
            'position' => 2,
        ]);

        $course->lessons()->create([
            'title' => 'Equivalent fractions',
            'body' => <<<'MD'
                Some fractions look different but are worth the **same** amount.

                `1/2` is the same as `2/4` and `3/6` — they all fill up half of the whole. ⭐
                MD,
            'position' => 3,
        ]);

        $quiz = $intro->quizzes()->create([
            'title' => 'Fraction basics quiz',
            'position' => 1,
        ]);

        // Single-correct question.
        $q1 = $quiz->questions()->create([
            'prompt' => 'In the fraction 3/4, which number is the numerator?',
            'position' => 1,
        ]);
        $q1->options()->createMany([
            ['text' => '3', 'is_correct' => true, 'position' => 1],
            ['text' => '4', 'is_correct' => false, 'position' => 2],
            ['text' => '7', 'is_correct' => false, 'position' => 3],
        ]);

        // Multiple-correct question.
        $q2 = $quiz->questions()->create([
            'prompt' => 'Pick all the fractions that are equal to 1/2.',
            'position' => 2,
        ]);
        $q2->options()->createMany([
            ['text' => '2/4', 'is_correct' => true, 'position' => 1],
            ['text' => '3/6', 'is_correct' => true, 'position' => 2],
            ['text' => '1/3', 'is_correct' => false, 'position' => 3],
            ['text' => '2/3', 'is_correct' => false, 'position' => 4],
        ]);

        // True/false question — just a question with two options.
        $q3 = $quiz->questions()->create([
            'prompt' => 'Is 5/5 equal to one whole?',
            'position' => 3,
        ]);
        $q3->options()->createMany([
            ['text' => 'True', 'is_correct' => true, 'position' => 1],
            ['text' => 'False', 'is_correct' => false, 'position' => 2],
        ]);
    }
}
