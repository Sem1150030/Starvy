# Authoring Hierarchy Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the content-authoring data model for Starvy — `Subject → Course → Lesson → Quiz → Question → Option` — with migrations, Eloquent models, factories, a subject seeder, and PHPUnit tests.

**Architecture:** Six one-to-many-linked Eloquent models. A `Subject` is a fixed seeded category (no theme data — palette lives in CSS keyed by `slug`). A `Course` belongs to a subject and is owned by a `User`; its visibility is a string-backed PHP enum `CourseStatus` (`Public`/`Private`/`Draft`), and `is_published` is a derived accessor (not a column). Everything below a course cascades on delete. No question `type` column — a question owns options, each with an `is_correct` boolean.

**Tech Stack:** Laravel 13, PHP 8.4, PHPUnit (not Pest), Laravel Pint. Models follow the repo's existing style: PHP `#[Fillable]` attributes, `@property` docblocks, a `casts()` method.

---

## Conventions for every task

- **Migrations** use explicit ordered filenames (`2026_06_13_0000NN_create_*_table.php`) so foreign keys resolve in order. Create them by hand with the Write tool at the given path — do **not** use `php artisan make:migration` (it stamps a different timestamp).
- **Run a single test class** with: `php artisan test --filter=ClassName`
- **Pint** before each commit on touched files: `vendor/bin/pint <files>`
- Commit messages end with the trailer:
  ```
  Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>
  ```
- DB is fresh; `RefreshDatabase` migrates on each test run.

---

## Task 1: `CourseStatus` enum

**Files:**
- Create: `app/Enums/CourseStatus.php`
- Test: `tests/Unit/Enums/CourseStatusTest.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit\Enums;

use App\Enums\CourseStatus;
use PHPUnit\Framework\TestCase;

class CourseStatusTest extends TestCase
{
    public function test_cases_have_expected_string_values(): void
    {
        $this->assertSame('public', CourseStatus::Public->value);
        $this->assertSame('private', CourseStatus::Private->value);
        $this->assertSame('draft', CourseStatus::Draft->value);
    }

    public function test_label_returns_human_friendly_text(): void
    {
        $this->assertSame('Public', CourseStatus::Public->label());
        $this->assertSame('Private', CourseStatus::Private->label());
        $this->assertSame('Draft', CourseStatus::Draft->label());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=CourseStatusTest`
Expected: FAIL — `Class "App\Enums\CourseStatus" not found`.

- [ ] **Step 3: Write the enum**

`app/Enums/CourseStatus.php`:
```php
<?php

namespace App\Enums;

enum CourseStatus: string
{
    case Public = 'public';
    case Private = 'private';
    case Draft = 'draft';

    /**
     * Human-friendly label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Public => 'Public',
            self::Private => 'Private',
            self::Draft => 'Draft',
        };
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=CourseStatusTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Commit**

```bash
vendor/bin/pint app/Enums/CourseStatus.php tests/Unit/Enums/CourseStatusTest.php
git add app/Enums/CourseStatus.php tests/Unit/Enums/CourseStatusTest.php
git commit -m "Add CourseStatus enum

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

## Task 2: `Subject` model, migration, factory

**Files:**
- Create: `database/migrations/2026_06_13_000001_create_subjects_table.php`
- Create: `app/Models/Subject.php`
- Create: `database/factories/SubjectFactory.php`
- Test: `tests/Feature/Models/SubjectTest.php`

- [ ] **Step 1: Write the migration**

`database/migrations/2026_06_13_000001_create_subjects_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
```

- [ ] **Step 2: Write the factory**

`database/factories/SubjectFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
```

- [ ] **Step 3: Write the failing test**

`tests/Feature/Models/SubjectTest.php`:
```php
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
```

- [ ] **Step 4: Run test to verify it fails**

Run: `php artisan test --filter=SubjectTest`
Expected: FAIL — `Class "App\Models\Subject" not found`.

- [ ] **Step 5: Write the model**

`app/Models/Subject.php`:
```php
<?php

namespace App\Models;

use Database\Factories\SubjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug', 'position'])]
class Subject extends Model
{
    /** @use HasFactory<SubjectFactory> */
    use HasFactory;

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=SubjectTest`
Expected: PASS (1 test).

> Note: `courses()` references `Course`, created in Task 3. PHP resolves the class name lazily (only when the relationship is called), so this test passes without `Course` existing yet.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint app/Models/Subject.php database/factories/SubjectFactory.php database/migrations/2026_06_13_000001_create_subjects_table.php tests/Feature/Models/SubjectTest.php
git add app/Models/Subject.php database/factories/SubjectFactory.php database/migrations/2026_06_13_000001_create_subjects_table.php tests/Feature/Models/SubjectTest.php
git commit -m "Add Subject model, migration, and factory

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

## Task 3: `Course` model, migration, factory (status + is_published)

**Files:**
- Create: `database/migrations/2026_06_13_000002_create_courses_table.php`
- Create: `app/Models/Course.php`
- Create: `database/factories/CourseFactory.php`
- Test: `tests/Feature/Models/CourseTest.php`

- [ ] **Step 1: Write the migration**

`database/migrations/2026_06_13_000002_create_courses_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
```

- [ ] **Step 2: Write the factory**

`database/factories/CourseFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'subject_id' => Subject::factory(),
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 999999),
            'description' => fake()->optional()->paragraph(),
            'status' => CourseStatus::Draft,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CourseStatus::Public,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CourseStatus::Private,
        ]);
    }
}
```

- [ ] **Step 3: Write the failing test**

`tests/Feature/Models/CourseTest.php`:
```php
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
```

- [ ] **Step 4: Run test to verify it fails**

Run: `php artisan test --filter=CourseTest`
Expected: FAIL — `Class "App\Models\Course" not found`.

- [ ] **Step 5: Write the model**

`app/Models/Course.php`:
```php
<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $subject_id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property CourseStatus $status
 * @property bool $is_published
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['subject_id', 'user_id', 'title', 'slug', 'description', 'status'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => CourseStatus::class,
        ];
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * A course is published when it is no longer a draft.
     */
    protected function isPublished(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->status !== CourseStatus::Draft,
        );
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=CourseTest`
Expected: PASS (3 tests).

> Note: `lessons()` references `Lesson` (Task 4); resolved lazily, so this passes now.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint app/Models/Course.php database/factories/CourseFactory.php database/migrations/2026_06_13_000002_create_courses_table.php tests/Feature/Models/CourseTest.php
git add app/Models/Course.php database/factories/CourseFactory.php database/migrations/2026_06_13_000002_create_courses_table.php tests/Feature/Models/CourseTest.php
git commit -m "Add Course model with status enum and derived is_published

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

## Task 4: `Lesson` model, migration, factory

**Files:**
- Create: `database/migrations/2026_06_13_000003_create_lessons_table.php`
- Create: `app/Models/Lesson.php`
- Create: `database/factories/LessonFactory.php`
- Test: `tests/Feature/Models/LessonTest.php`

- [ ] **Step 1: Write the migration**

`database/migrations/2026_06_13_000003_create_lessons_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('body')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
```

- [ ] **Step 2: Write the factory**

`database/factories/LessonFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => fake()->sentence(3),
            'body' => fake()->paragraphs(2, true),
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
```

- [ ] **Step 3: Write the failing test**

`tests/Feature/Models/LessonTest.php`:
```php
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
```

- [ ] **Step 4: Run test to verify it fails**

Run: `php artisan test --filter=LessonTest`
Expected: FAIL — `Class "App\Models\Lesson" not found`.

- [ ] **Step 5: Write the model**

`app/Models/Lesson.php`:
```php
<?php

namespace App\Models;

use Database\Factories\LessonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $course_id
 * @property string $title
 * @property string|null $body
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['course_id', 'title', 'body', 'position'])]
class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=LessonTest`
Expected: PASS (2 tests).

> Note: `quizzes()` references `Quiz` (Task 5); resolved lazily.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint app/Models/Lesson.php database/factories/LessonFactory.php database/migrations/2026_06_13_000003_create_lessons_table.php tests/Feature/Models/LessonTest.php
git add app/Models/Lesson.php database/factories/LessonFactory.php database/migrations/2026_06_13_000003_create_lessons_table.php tests/Feature/Models/LessonTest.php
git commit -m "Add Lesson model, migration, and factory

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

## Task 5: `Quiz` model, migration, factory

**Files:**
- Create: `database/migrations/2026_06_13_000004_create_quizzes_table.php`
- Create: `app/Models/Quiz.php`
- Create: `database/factories/QuizFactory.php`
- Test: `tests/Feature/Models/QuizTest.php`

- [ ] **Step 1: Write the migration**

`database/migrations/2026_06_13_000004_create_quizzes_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
```

- [ ] **Step 2: Write the factory**

`database/factories/QuizFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'title' => fake()->sentence(2),
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
```

- [ ] **Step 3: Write the failing test**

`tests/Feature/Models/QuizTest.php`:
```php
<?php

namespace Tests\Feature\Models;

use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_quiz_belongs_to_a_lesson(): void
    {
        $quiz = Quiz::factory()->create();

        $this->assertInstanceOf(Lesson::class, $quiz->lesson);
    }

    public function test_a_lesson_has_many_quizzes(): void
    {
        $lesson = Lesson::factory()->create();
        Quiz::factory()->count(3)->create(['lesson_id' => $lesson->id]);

        $this->assertCount(3, $lesson->quizzes);
    }
}
```

- [ ] **Step 4: Run test to verify it fails**

Run: `php artisan test --filter=QuizTest`
Expected: FAIL — `Class "App\Models\Quiz" not found`.

- [ ] **Step 5: Write the model**

`app/Models/Quiz.php`:
```php
<?php

namespace App\Models;

use Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $lesson_id
 * @property string $title
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['lesson_id', 'title', 'position'])]
class Quiz extends Model
{
    /** @use HasFactory<QuizFactory> */
    use HasFactory;

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=QuizTest`
Expected: PASS (2 tests).

> Note: Laravel pluralizes `Quiz` to `quizzes` automatically, matching the table name. `questions()` references `Question` (Task 6); resolved lazily.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint app/Models/Quiz.php database/factories/QuizFactory.php database/migrations/2026_06_13_000004_create_quizzes_table.php tests/Feature/Models/QuizTest.php
git add app/Models/Quiz.php database/factories/QuizFactory.php database/migrations/2026_06_13_000004_create_quizzes_table.php tests/Feature/Models/QuizTest.php
git commit -m "Add Quiz model, migration, and factory

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

## Task 6: `Question` model, migration, factory

**Files:**
- Create: `database/migrations/2026_06_13_000005_create_questions_table.php`
- Create: `app/Models/Question.php`
- Create: `database/factories/QuestionFactory.php`
- Test: `tests/Feature/Models/QuestionTest.php`

- [ ] **Step 1: Write the migration**

`database/migrations/2026_06_13_000005_create_questions_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->text('prompt');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
```

- [ ] **Step 2: Write the factory**

`database/factories/QuestionFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'prompt' => fake()->sentence().'?',
            'position' => fake()->numberBetween(0, 10),
        ];
    }
}
```

- [ ] **Step 3: Write the failing test**

`tests/Feature/Models/QuestionTest.php`:
```php
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
```

- [ ] **Step 4: Run test to verify it fails**

Run: `php artisan test --filter=QuestionTest`
Expected: FAIL — `Class "App\Models\Question" not found`.

- [ ] **Step 5: Write the model**

`app/Models/Question.php`:
```php
<?php

namespace App\Models;

use Database\Factories\QuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $quiz_id
 * @property string $prompt
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['quiz_id', 'prompt', 'position'])]
class Question extends Model
{
    /** @use HasFactory<QuestionFactory> */
    use HasFactory;

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=QuestionTest`
Expected: PASS (2 tests).

> Note: `options()` references `Option` (Task 7); resolved lazily.

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint app/Models/Question.php database/factories/QuestionFactory.php database/migrations/2026_06_13_000005_create_questions_table.php tests/Feature/Models/QuestionTest.php
git add app/Models/Question.php database/factories/QuestionFactory.php database/migrations/2026_06_13_000005_create_questions_table.php tests/Feature/Models/QuestionTest.php
git commit -m "Add Question model, migration, and factory

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

## Task 7: `Option` model, migration, factory

**Files:**
- Create: `database/migrations/2026_06_13_000006_create_options_table.php`
- Create: `app/Models/Option.php`
- Create: `database/factories/OptionFactory.php`
- Test: `tests/Feature/Models/OptionTest.php`

- [ ] **Step 1: Write the migration**

`database/migrations/2026_06_13_000006_create_options_table.php`:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('options');
    }
};
```

- [ ] **Step 2: Write the factory**

`database/factories/OptionFactory.php`:
```php
<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Option>
 */
class OptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'text' => fake()->words(3, true),
            'is_correct' => false,
            'position' => fake()->numberBetween(0, 10),
        ];
    }

    public function correct(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_correct' => true,
        ]);
    }
}
```

- [ ] **Step 3: Write the failing test**

`tests/Feature/Models/OptionTest.php`:
```php
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
```

- [ ] **Step 4: Run test to verify it fails**

Run: `php artisan test --filter=OptionTest`
Expected: FAIL — `Class "App\Models\Option" not found`.

- [ ] **Step 5: Write the model**

`app/Models/Option.php`:
```php
<?php

namespace App\Models;

use Database\Factories\OptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $question_id
 * @property string $text
 * @property bool $is_correct
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['question_id', 'text', 'is_correct', 'position'])]
class Option extends Model
{
    /** @use HasFactory<OptionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `php artisan test --filter=OptionTest`
Expected: PASS (3 tests).

- [ ] **Step 7: Commit**

```bash
vendor/bin/pint app/Models/Option.php database/factories/OptionFactory.php database/migrations/2026_06_13_000006_create_options_table.php tests/Feature/Models/OptionTest.php
git add app/Models/Option.php database/factories/OptionFactory.php database/migrations/2026_06_13_000006_create_options_table.php tests/Feature/Models/OptionTest.php
git commit -m "Add Option model, migration, and factory

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

## Task 8: Full-chain & cascade-delete integration test

**Files:**
- Test: `tests/Feature/Models/AuthoringHierarchyTest.php`

This task adds no production code — it proves the whole tree wires up and cascades. If it fails, fix the relevant migration/model from earlier tasks.

- [ ] **Step 1: Write the failing test**

`tests/Feature/Models/AuthoringHierarchyTest.php`:
```php
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
```

- [ ] **Step 2: Run test to verify it passes**

Run: `php artisan test --filter=AuthoringHierarchyTest`
Expected: PASS (2 tests). The chain and cascades were built in Tasks 2–7, so this should pass immediately. If `test_deleting_a_course_cascades...` fails, a child migration is missing `cascadeOnDelete()` — fix that migration and re-run `php artisan migrate:fresh` (or rely on `RefreshDatabase`).

- [ ] **Step 3: Commit**

```bash
vendor/bin/pint tests/Feature/Models/AuthoringHierarchyTest.php
git add tests/Feature/Models/AuthoringHierarchyTest.php
git commit -m "Add authoring hierarchy integration test

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

## Task 9: `SubjectSeeder` and wiring

**Files:**
- Create: `database/seeders/SubjectSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/SubjectSeederTest.php`

- [ ] **Step 1: Write the failing test**

`tests/Feature/SubjectSeederTest.php`:
```php
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
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=SubjectSeederTest`
Expected: FAIL — `Class "Database\Seeders\SubjectSeeder" not found`.

- [ ] **Step 3: Write the seeder**

`database/seeders/SubjectSeeder.php`:
```php
<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'Math', 'slug' => 'math', 'position' => 1],
            ['name' => 'Reading', 'slug' => 'reading', 'position' => 2],
            ['name' => 'Science', 'slug' => 'science', 'position' => 3],
            ['name' => 'Art', 'slug' => 'art', 'position' => 4],
            ['name' => 'Music', 'slug' => 'music', 'position' => 5],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(['slug' => $subject['slug']], $subject);
        }
    }
}
```

- [ ] **Step 4: Wire it into `DatabaseSeeder`**

Modify `database/seeders/DatabaseSeeder.php` — add the seeder call at the top of `run()`:
```php
    public function run(): void
    {
        $this->call(SubjectSeeder::class);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=SubjectSeederTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Commit**

```bash
vendor/bin/pint database/seeders/SubjectSeeder.php database/seeders/DatabaseSeeder.php tests/Feature/SubjectSeederTest.php
git add database/seeders/SubjectSeeder.php database/seeders/DatabaseSeeder.php tests/Feature/SubjectSeederTest.php
git commit -m "Add SubjectSeeder seeding the five fixed subjects

Co-Authored-By: Claude Fable 5 <noreply@anthropic.com>"
```

---

## Task 10: Final verification

- [ ] **Step 1: Run the new test suite end-to-end**

Run: `php artisan test --filter='Subject|Course|Lesson|Quiz|Question|Option|AuthoringHierarchy|CourseStatus'`
Expected: All green (≈20 tests).

- [ ] **Step 2: Verify a fresh migrate + seed works**

Run: `php artisan migrate:fresh --seed`
Expected: All migrations run; `subjects` table holds 5 rows; no errors. (This is what `make reset` runs.)

- [ ] **Step 3: Pint check across everything touched**

Run: `vendor/bin/pint --test app/ database/ tests/`
Expected: PASS (no style issues). If it reports fixes needed, run `vendor/bin/pint app/ database/ tests/` and commit the formatting.

> Note: The pre-existing 32 failures in the full `php artisan test` run (`No application encryption key has been specified` / `Vite manifest not found`) are an environment gap unrelated to this work — do not try to fix them as part of this plan.

---

## Notes for the implementer

- **`Quiz` pluralization:** Laravel correctly maps `Quiz` → `quizzes`, matching the migration table name. No `$table` override needed.
- **Lazy relationship resolution:** Each model's "downward" relationship (`Subject::courses`, `Course::lessons`, etc.) references a class defined in a later task. PHP only resolves the class string when the relationship method runs, so per-task tests pass before the referenced model exists.
- **Mass assignment:** Models use the `#[Fillable([...])]` attribute (matching the existing `User` model) listing every writable column, including foreign keys, so factories and app code can assign them.
- **`course.slug`** is globally unique (per the spec). The factory appends a random suffix to avoid collisions in tests.
