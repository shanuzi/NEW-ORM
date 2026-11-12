<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use Core\Controller;
use Core\Validator;

class StudentController extends Controller
{
    protected string $resource = 'students';

    public function index(): void
    {
        $search = trim((string) ($_GET['q'] ?? ''));
        $students = $search !== ''
            ? Student::where('name', '%' . $search . '%', 'LIKE')
            : Student::all('id', 'DESC');
        $this->render('students/index', compact('students', 'search'));
    }

    public function create(): void
    {
        $this->render('students/create', $this->formData([], []));
    }

    public function store(): void
    {
        $this->requireCsrf();
        $data = $this->input();
        $errors = Validator::student($data);

        if ($errors) {
            $this->render('students/create', $this->formData($errors, $data));
            return;
        }
        Student::create($data);
        $this->flash('Student added.');
        $this->redirect();
    }

    public function edit(): void
    {
        $student = $this->findOrFail();
        $old = [
            'name' => $student->name, 'email' => $student->email,
            'course' => $student->course, 'teacher_id' => $student->teacher_id,
        ];
        $this->render('students/edit', $this->formData([], $old) + ['student' => $student]);
    }

    public function update(): void
    {
        $this->requireCsrf();
        $student = $this->findOrFail();
        $data = $this->input();
        $errors = Validator::student($data, (int) $student->id);

        if ($errors) {
            $this->render('students/edit', $this->formData($errors, $data) + ['student' => $student]);
            return;
        }
        Student::update((int) $student->id, $data);
        $this->flash('Student updated.');
        $this->redirect();
    }

    public function destroy(): void
    {
        $this->requireCsrf();
        $student = $this->findOrFail();
        Student::delete((int) $student->id);
        $this->flash('Student deleted.');
        $this->redirect();
    }

    private function input(): array
    {
        $teacherId = filter_var($_POST['teacher_id'] ?? '', FILTER_VALIDATE_INT);
        return [
            'name'       => trim((string) ($_POST['name'] ?? '')),
            'email'      => trim((string) ($_POST['email'] ?? '')),
            'course'     => trim((string) ($_POST['course'] ?? '')),
            // '' (no adviser) -> NULL; MySQL rejects '' for an INT column.
            'teacher_id' => $teacherId === false ? null : $teacherId,
        ];
    }

    private function formData(array $errors, array $old): array
    {
        return ['errors' => $errors, 'old' => $old, 'teachers' => Teacher::all('name')];
    }

    private function findOrFail(): Student
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $student = $id ? Student::find($id) : null;
        if (!$student) {
            $this->notFound('Student not found.');
        }
        return $student;
    }
}
