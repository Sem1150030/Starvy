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
