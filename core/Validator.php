<?php
declare(strict_types=1);

namespace Core;

use App\Models\Student;
use App\Models\Teacher;

/** Returns an array of error messages (empty array = valid). */
class Validator
{
    public static function student(array $data, ?int $ignoreId = null): array
    {
        $errors = [];
        self::required($errors, $data, 'name', 'Name');
        self::email($errors, $data, Student::class, $ignoreId);
        self::required($errors, $data, 'course', 'Course');

        // Adviser is optional, but if given it must be a real teacher.
        $teacherId = $data['teacher_id'] ?? null;
        if ($teacherId !== null && Teacher::find((int) $teacherId) === null) {
            $errors[] = 'Selected adviser does not exist.';
        }
        return $errors;
    }

    public static function teacher(array $data, ?int $ignoreId = null): array
    {
        $errors = [];
        self::required($errors, $data, 'name', 'Name');
        self::email($errors, $data, Teacher::class, $ignoreId);
        self::required($errors, $data, 'department', 'Department');
        return $errors;
    }

    private static function required(array &$errors, array $data, string $key, string $label): void
    {
        $value = $data[$key] ?? '';
        if ($value === '') {
            $errors[] = "$label is required.";
        } elseif (mb_strlen($value) > 100) {
            $errors[] = "$label must be at most 100 characters.";
        }
    }

    /** @param class-string<Model> $modelClass */
    private static function email(array &$errors, array $data, string $modelClass, ?int $ignoreId): void
    {
        $email = $data['email'] ?? '';
        if ($email === '') {
            $errors[] = 'Email is required.';
            return;
        }
        if (mb_strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is not valid.';
            return;
        }
        foreach ($modelClass::where('email', $email) as $existing) {
            if ($ignoreId === null || (int) $existing->id !== $ignoreId) {
                $errors[] = 'That email is already registered.';
                return;
            }
        }
    }
}
