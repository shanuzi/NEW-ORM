# ORM Design and PSR-4 Implementation

## ORM design
The ORM is one abstract base class, `Core\Model`. A child model only describes
itself; all behavior is inherited.

```php
class Student extends Model {
    protected static string $table = 'students';
    protected static array $fillable = ['name', 'email', 'course'];
}
```

**Model mapping.** `$table` links a class to a table. Rows fetched with
`PDO::FETCH_CLASS` become objects of the model class (e.g. `Student`).

**Methods.** `all()`, `find($id)`, `where($col, $value, $op)`, `create($data)`,
`update($id, $data)`, `delete($id)`.

**Late static binding.** Methods are static and use `static::$table`, so a call
like `Student::all()` resolves to the `Student` table, not to the parent class.

**Security decisions**
- Every value goes through PDO prepared statements (`?` placeholders).
- Column names and operators cannot be bound, so `where()` and `all()` check
  them against a whitelist (`$fillable`, the primary key, `created_at`; and a
  fixed operator list). Anything else throws `InvalidArgumentException`.
- `$fillable` stops mass assignment: keys not listed are dropped before
  `create()` and `update()` build SQL.
- `Database` uses `ERRMODE_EXCEPTION` and disables emulated prepares.

**Relationships.** `Student` has a nullable `teacher_id` foreign key to `Teacher`.
The base `Model` provides two helpers built on `find()` and `where()`:
- `belongsTo(Related::class, 'fk')` - many-to-one (`$student->teacher()`)
- `hasMany(Related::class, 'fk')` - one-to-many (`$teacher->students()`)

The database enforces integrity with `FOREIGN KEY ... ON DELETE SET NULL`:
deleting a teacher keeps their students and clears their adviser. Known
trade-off: loading the relation inside a list loop runs one query per row
(the N+1 problem), acceptable at this scale.

**Separation of concerns.** Model = data access, Controller = flow,
Validator = rules, Views = display.

## PSR-4 implementation
PSR-4 maps a namespace prefix to a directory. The rest of the class name maps
to a path.

| Namespace prefix | Directory |
|---|---|
| `App\`  | `app/`  |
| `Core\` | `core/` |

Examples:
- `App\Models\Student` -> `app/Models/Student.php`
- `App\Controllers\StudentController` -> `app/Controllers/StudentController.php`
- `App\Models\Teacher` -> `app/Models/Teacher.php`
- `Core\Model` -> `core/Model.php`

Configured in `composer.json` under `autoload.psr-4`; `composer dump-autoload`
generates `vendor/autoload.php`, which `public/index.php` loads. No `require`
calls are needed for classes. A small helper file (`core/helpers.php`, with the
`e()` escape function) is loaded through Composer's `files` autoload option.
