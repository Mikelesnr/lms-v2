<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Staff and System Admins can view any course; instructors can only view their own.
     */
    public function view(User $user, Course $course): bool
    {
        return $this->isStaffOrAdmin($user) || $course->user_id === $user->id;
    }

    /**
     * Instructors can only update their own courses; Staff can edit/curate any course.
     */
    public function update(User $user, Course $course): bool
    {
        return $this->isStaffOrAdmin($user) || $course->user_id === $user->id;
    }

    public function delete(User $user, Course $course): bool
    {
        return $this->isStaffOrAdmin($user) || $course->user_id === $user->id;
    }

    private function isStaffOrAdmin(User $user): bool
    {
        return in_array($user->role, [
            UserRole::SYSTEM_ADMIN,
            UserRole::SYSTEM_STAFF,
            UserRole::SYSTEM_TECHNICIAN,
        ]);
    }
}