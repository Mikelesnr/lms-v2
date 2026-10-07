<?php

namespace App\Enums;

enum UserRole: string
{
    case SYSTEM_ADMIN = 'admin';
    case SYSTEM_STAFF = 'staff';
    case SYSTEM_TECHNICIAN = 'technician';
    case SYSTEM_ACCOUNTANT = 'accountant';
    case INSTRUCTOR = 'instructor';
    case STUDENT = 'student';
}
