<?php
declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Teacher extends Model
{
    protected static string $table = 'teachers';
    protected static array $fillable = ['name', 'email', 'department'];

    public $id;
    public $name;
    public $email;
    public $department;
    public $created_at;

    /** @return Student[] students advised by this teacher */
    public function students(): array
    {
        return $this->hasMany(Student::class, 'teacher_id');
    }
}
