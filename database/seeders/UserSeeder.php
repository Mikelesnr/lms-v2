<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed System Admin from services.admin config
        if (config('services.admin.email')) {
            User::updateOrCreate(
                ['email' => config('services.admin.email')],
                [
                    'id' => (string) Str::uuid(),
                    'name' => config('services.admin.name', 'System Admin'),
                    'password' => Hash::make(config('services.admin.password')),
                    'role' => UserRole::SYSTEM_ADMIN->value,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Passwords from configuration
        $systemUserPassword = Hash::make(config('services.system_user.password'));
        $userPassword = Hash::make(config('services.user.password'));

        // 2. System Users (Staff, Technician, Accountant)
        $systemUsers = [
            [
                'name' => 'System Staff',
                'email' => 'staff@lms.com',
                'role' => UserRole::SYSTEM_STAFF->value,
            ],
            [
                'name' => 'System Technician',
                'email' => 'technician@lms.com',
                'role' => UserRole::SYSTEM_TECHNICIAN->value,
            ],
            [
                'name' => 'System Accountant',
                'email' => 'account@lms.com',
                'role' => UserRole::SYSTEM_ACCOUNTANT->value,
            ],
        ];

        foreach ($systemUsers as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $data['name'],
                    'password' => $systemUserPassword,
                    'role' => $data['role'],
                    'email_verified_at' => now(),
                ]
            );
        }

        // 3. Instructors (2)
        $instructors = [
            ['name' => 'Instructor One', 'email' => 'instructor1@lms.com'],
            ['name' => 'Instructor Two', 'email' => 'instructor2@lms.com'],
        ];

        foreach ($instructors as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $data['name'],
                    'password' => $userPassword,
                    'role' => UserRole::INSTRUCTOR->value,
                    'email_verified_at' => now(),
                ]
            );
        }

        // 4. Students (4)
        $students = [
            ['name' => 'Student One', 'email' => 'student1@lms.com'],
            ['name' => 'Student Two', 'email' => 'student2@lms.com'],
            ['name' => 'Student Three', 'email' => 'student3@lms.com'],
            ['name' => 'Student Four', 'email' => 'student4@lms.com'],
        ];

        foreach ($students as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'id' => (string) Str::uuid(),
                    'name' => $data['name'],
                    'password' => $userPassword,
                    'role' => UserRole::STUDENT->value,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}