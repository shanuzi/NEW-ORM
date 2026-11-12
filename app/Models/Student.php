<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Student extends Model
{
    protected static string $table = 'students';
    protected static array $fillable = ['name', 'email', 'course', 'teacher_id'];

    // Columns become public properties when rows are mapped to objects.
    public $id;
    public $name;
    public $email;
    public $course;
    public $teacher_id;
    public $created_at;

    /** The student's adviser, or null if none. */
    public function teacher(): ?Teacher
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
