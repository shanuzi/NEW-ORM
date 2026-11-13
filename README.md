# SPELEC2A CRUD Application (Core PHP, Custom ORM, PSR-4)

A student and teacher manager built with Core PHP 8+, MySQL/MariaDB and PDO. No frameworks.
Composer is used **only** for PSR-4 autoloading.

## Requirements
- PHP 8.0 or newer with the `pdo_mysql` extension
- MySQL or MariaDB
- Composer (optional but recommended)

## Setup
1. **Create the database:** import `database/schema.sql`
   - phpMyAdmin: Import tab, choose the file, Go
   - or CLI: `mysql -u root -p < database/schema.sql`
2. **Configure the connection:** edit `config/database.php` (host, dbname, user, pass).
3. **Generate the autoloader:** run `composer dump-autoload` in the project folder.
   (If you skip this, `public/index.php` falls back to `core/autoload.php`,
   which uses the same PSR-4 mapping.)
4. **Run the app:**
   ```
   php -S localhost:8000 -t public
   ```
   Open http://localhost:8000
   (URLs look like `index.php?resource=teachers&action=edit&id=1`)

> Note: `schema.sql` drops and recreates the `students` and `teachers` tables.

## Features
- Create, Read, Update, Delete for **students** and **teachers**, plus search by name
- Each student can have an adviser (teacher): one-to-many relationship in the ORM
- Server-side validation (required, length, email format, unique email)
- Prepared statements (PDO), output escaping, CSRF tokens, POST-only deletes
- Friendly 404/405/500 pages (raw errors are never shown)

## Structure
```
app/Controllers   request handling (StudentController, TeacherController)
app/Models        table mappings (Student, Teacher)
core/             ORM (Model), Database, Validator, Csrf, base Controller
config/           database settings
public/           index.php (front controller / router)
views/            PHP templates
database/         schema.sql
docs/             ORM and PSR-4 documentation
```

## Adding another model
1. Create the table in SQL.
2. Add `app/Models/Book.php` extending `Core\Model` with `$table` and `$fillable`.
3. Add a controller and views.

Pagination means showing the list in small chunks (say 10 students per page) instead of all rows at once, with page links at the bottom.

The idea is simple: the page number comes from the URL (`?page=2`), and MySQL's `LIMIT` and `OFFSET` pick which rows to fetch.

| Page | OFFSET | Rows shown |
|---|---|---|
| 1 | 0 | 1-10 |
| 2 | 10 | 11-20 |
| 3 | 20 | 21-30 |

The formula is `offset = (page - 1) * perPage`.

In your project, `Student::all()` and `Student::where()` fetch every row, so you add three small changes.

**1. Model: add a `paginate()` method in `core/Model.php`**

```php
public static function paginate(int $page = 1, int $perPage = 10, string $search = ''): array
{
    $pdo    = Database::connection();
    $where  = '';
    $params = [];
    if ($search !== '') {
        $where    = ' WHERE name LIKE ?';
        $params[] = '%' . $search . '%';
    }

    // 1. count total rows, so we know how many pages exist
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM ' . static::$table . $where);
    $stmt->execute($params);
    $total = (int) $stmt->fetchColumn();

    $pages  = max(1, (int) ceil($total / $perPage));
    $page   = min(max(1, $page), $pages);      // keep page in a valid range
    $offset = ($page - 1) * $perPage;

    // 2. fetch only this page's rows
    $sql = 'SELECT * FROM ' . static::$table . $where
         . ' ORDER BY ' . static::$primaryKey . ' DESC'
         . ' LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return [
        'items' => $stmt->fetchAll(PDO::FETCH_CLASS, static::class),
        'page'  => $page,
        'pages' => $pages,
        'total' => $total,
    ];
}
```

**2. Controller: update `index()` in `StudentController`**

```php
public function index(): void
{
    $search   = trim((string) ($_GET['q'] ?? ''));
    $page     = (int) ($_GET['page'] ?? 1);
    $result   = Student::paginate($page, 10, $search);
    $students = $result['items'];
    $this->render('students/index', compact('students', 'search', 'result'));
}
```

**3. View: add page links under the table in `views/students/index.php`**

```php
<?php if ($result['pages'] > 1): ?>
  <p class="pagination">
    <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
      <a class="btn <?= $i === $result['page'] ? 'primary' : '' ?>"
         href="index.php?resource=students&q=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
    <?php endfor; ?>
  </p>
<?php endif; ?>
```

Putting `q=` in the link keeps the search active while you flip pages. Do the same three steps for `TeacherController` and `views/teachers/index.php`. The model method is shared, so that part is already done.

If your teacher asks why you did it this way, you can say:
- **`COUNT(*)`** tells you how many pages there are.
- **`LIMIT`/`OFFSET`** loads only the rows you need, so the page stays fast with thousands of records.
- **`min(max(1, $page), $pages)`** stops someone typing `?page=9999` or `?page=-5` in the URL.
- **Casting to `(int)`** keeps `LIMIT` and `OFFSET` safe from SQL injection, since those can't be bound as normal parameters here.

I can also add Prev/Next buttons or a "Page 2 of 5" label if you want.

#Paginate Teachers

public function index(): void
{
    $search   = trim((string) ($_GET['q'] ?? ''));
    $page     = (int) ($_GET['page'] ?? 1);
    $result   = Teacher::paginate($page, 10, $search);
    $teachers = $result['items'];
    $this->render('teachers/index', compact('teachers', 'search', 'result'));
}
