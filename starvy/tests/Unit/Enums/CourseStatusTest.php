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
