<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Teacher;
use Core\Controller;
use Core\Validator;

class TeacherController extends Controller
{
    protected string $resource = 'teachers';

    public function index(): void
    {
        $search = trim((string) ($_GET['q'] ?? ''));
        $teachers = $search !== ''
            ? Teacher::where('name', '%' . $search . '%', 'LIKE')
            : Teacher::all('id', 'DESC');
        $this->render('teachers/index', compact('teachers', 'search'));
    }

    public function create(): void
    {
        $this->render('teachers/create', ['errors' => [], 'old' => []]);
    }

    public function store(): void
    {
        $this->requireCsrf();
        $data = $this->input();
        $errors = Validator::teacher($data);

        if ($errors) {
            $this->render('teachers/create', ['errors' => $errors, 'old' => $data]);
            return;
        }
        Teacher::create($data);
        $this->flash('Teacher added.');
        $this->redirect();
    }

    public function edit(): void
    {
        $teacher = $this->findOrFail();
        $old = ['name' => $teacher->name, 'email' => $teacher->email, 'department' => $teacher->department];
        $this->render('teachers/edit', ['errors' => [], 'old' => $old, 'teacher' => $teacher]);
    }

    public function update(): void
    {
        $this->requireCsrf();
        $teacher = $this->findOrFail();
        $data = $this->input();
        $errors = Validator::teacher($data, (int) $teacher->id);

        if ($errors) {
            $this->render('teachers/edit', ['errors' => $errors, 'old' => $data, 'teacher' => $teacher]);
            return;
        }
        Teacher::update((int) $teacher->id, $data);
        $this->flash('Teacher updated.');
        $this->redirect();
    }

    public function destroy(): void
    {
        $this->requireCsrf();
        $teacher = $this->findOrFail();
        Teacher::delete((int) $teacher->id); // students keep existing (FK ON DELETE SET NULL)
        $this->flash('Teacher deleted. Their students now have no adviser.');
        $this->redirect();
    }

    private function input(): array
    {
        return [
            'name'       => trim((string) ($_POST['name'] ?? '')),
            'email'      => trim((string) ($_POST['email'] ?? '')),
            'department' => trim((string) ($_POST['department'] ?? '')),
        ];
    }

    private function findOrFail(): Teacher
    {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $teacher = $id ? Teacher::find($id) : null;
        if (!$teacher) {
            $this->notFound('Teacher not found.');
        }
        return $teacher;
    }
}
