<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_is_cast_to_the_enum(): void
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(UserRole::class, $user->fresh()->role);
    }

    public function test_new_users_default_to_student(): void
    {
        $user = User::factory()->create();

        $this->assertSame(UserRole::Student, $user->role);
        $this->assertTrue($user->isStudent());
    }

    public function test_role_helpers_reflect_the_assigned_role(): void
    {
        $teacher = User::factory()->teacher()->create();
        $admin = User::factory()->admin()->create();

        $this->assertTrue($teacher->isTeacher());
        $this->assertFalse($teacher->isStudent());
        $this->assertTrue($teacher->hasRole(UserRole::Teacher));

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->hasRole(UserRole::Student));
    }

    public function test_role_is_persisted_as_its_string_value(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
    }
}
