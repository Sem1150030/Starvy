# Authoring hierarchy — design

**Date:** 2026-06-13
**Status:** Approved for planning
**Scope:** The content-authoring data model only. Student activity (attempts, scoring, stars/streaks/progress), AI generation, and Scout/Meilisearch indexing are explicitly out of scope and will get their own specs.

## Purpose

Starvy is content-first: the thing teachers and students create and browse is structured learning content, not standalone quizzes. This spec defines the authoring backbone — the tables and models that hold that content — so later work (student attempts, rewards, search, AI) has a stable foundation to hang off.

## Decisions

These were settled during brainstorming and drive the design:

- **Content-first, not assessment-first.** A quiz is one activity *inside* a lesson, not the root of the tree.
- **A course is the top of the user-created tree.** Courses are free-form — a teacher *or* a student can create one. Courses are categorized by a subject.
- **Subjects are a fixed, seeded set** (Math, Reading, Science, Art, Music). They identify and group courses; they carry **no theme/color data** — palette is handled entirely in the styling layer (CSS variables / config keyed by the subject `slug`).
- **No question `type` column.** A question owns options; each option has an `is_correct` boolean. One correct option → single-answer. Several correct → multiple-answer. True/false is just a question with two options ("True", "False"). The data carries everything.
- **Course `status` is a PHP enum** (`Public` / `Private` / `Draft`), mirroring the `UserRole` pattern (string-backed enum, defined in PHP, stored as a string column — not a DB-level enum).
- **`is_published` is a derived accessor**, not a stored column: `true` when `status !== Draft`. This keeps `$course->is_published` available in code without a second column that could drift from `status`.

## Entity hierarchy

```
Subject (seeded, themed in CSS only)
  └─< Course   (belongs to Subject; owned by a User — Teacher or Student)
        └─< Lesson   (ordered; holds the teaching content)
              └─< Quiz     (a lesson can have several)
                    └─< Question
                          └─< Option  (text + is_correct boolean)
```

Every "contains" edge is a one-to-many. Deletes cascade down the tree.

## Schema

### subjects
| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | e.g. "Math" |
| slug | string, unique | e.g. `math` — join key to the CSS palette |
| position | unsigned int | ordering of subject tiles |
| timestamps | | |

Seeded with the 5 subjects from CLAUDE.md (Math, Reading, Science, Art, Music). No color columns — theming is a frontend concern keyed off `slug`.

### courses
| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| subject_id | FK → subjects | `cascadeOnDelete` |
| user_id | FK → users | the owner (Teacher or Student) |
| title | string | |
| slug | string, unique | for routing / future search |
| description | text, nullable | |
| status | string, default `draft` | cast to `App\Enums\CourseStatus` |
| timestamps | | |

No `is_published` column — it is a derived accessor (see Models).

### lessons
| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| course_id | FK → courses | `cascadeOnDelete` |
| title | string | |
| body | longText | freeform teaching content |
| position | unsigned int | ordering within the course |
| timestamps | | |

### quizzes
| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| lesson_id | FK → lessons | `cascadeOnDelete` |
| title | string | |
| position | unsigned int | ordering within the lesson |
| timestamps | | |

### questions
| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| quiz_id | FK → quizzes | `cascadeOnDelete` |
| prompt | text | the question text |
| position | unsigned int | ordering within the quiz |
| timestamps | | |

### options
| column | type | notes |
|--------|------|-------|
| id | bigint PK | |
| question_id | FK → questions | `cascadeOnDelete` |
| text | string | |
| is_correct | boolean, default false | cast to bool |
| position | unsigned int | ordering within the question |
| timestamps | | |

## Enum

`App\Enums\CourseStatus` — string-backed PHP enum, same conventions as `App\Enums\UserRole`:

```php
enum CourseStatus: string
{
    case Public  = 'public';
    case Private = 'private';   // visible to students of a connected class (class subsystem is future work)
    case Draft   = 'draft';     // author-only, default

    public function label(): string; // "Public" / "Private" / "Draft"
}
```

Default for a new course is `Draft`.

## Models

Six Eloquent models following the repo's existing style (PHP `#[Fillable]` attributes, `@property` docblocks, `casts()`):

- **Subject** — `hasMany(Course)`.
- **Course** — `belongsTo(Subject)`, `belongsTo(User, 'user_id')` exposed as `owner()`, `hasMany(Lesson)`. Casts `status` to `CourseStatus`. Exposes a derived `is_published` accessor returning `status !== CourseStatus::Draft`.
- **Lesson** — `belongsTo(Course)`, `hasMany(Quiz)`.
- **Quiz** — `belongsTo(Lesson)`, `hasMany(Question)`.
- **Question** — `belongsTo(Quiz)`, `hasMany(Option)`.
- **Option** — `belongsTo(Question)`. Casts `is_correct` to bool.

Models stay thin — relationships and casts only. Convenience query helpers (e.g. `Question::correctOptions()`) are deferred until the student-activity spec actually needs them (YAGNI).

## Seeding

A `SubjectSeeder` inserts the 5 fixed subjects with their slugs and positions, wired into `DatabaseSeeder`. (`make reset` runs `migrate:fresh --seed`, so this gives a usable dataset.)

## Testing (PHPUnit)

- Model factories for all six entities, with states for nesting (e.g. a course factory that can create lessons → quizzes → questions → options).
- Tests asserting:
  - The full relationship chain wires up (`subject → course → lesson → quiz → question → option`).
  - Cascade deletes remove descendants.
  - `Course::status` casts to `CourseStatus`; default is `Draft`.
  - `Course::is_published` accessor is `false` for `Draft`, `true` for `Public`/`Private`.
  - `Option::is_correct` casts to bool; a question can have multiple correct options.
  - `SubjectSeeder` produces the 5 expected subjects.

## Out of scope (future specs)

- **Student activity:** `QuizAttempt`, `Answer`, scoring.
- **Rewards/progress:** stars, streaks, completion tracking.
- **Classes/enrollment:** the "connected class" that `CourseStatus::Private` refers to.
- **AI generation** of courses/quizzes (OpenAI).
- **Search:** Scout/Meilisearch indexing. (`slug` columns are laid now so routing/search slot in cleanly.)
