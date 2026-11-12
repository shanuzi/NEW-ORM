<?php
declare(strict_types=1);

namespace Core;

use InvalidArgumentException;
use PDO;

/**
 * Base class of the lightweight ORM.
 * Child models declare $table and $fillable; they inherit everything else.
 * All queries use PDO prepared statements.
 */
abstract class Model
{
    protected static string $table;
    protected static string $primaryKey = 'id';
    /** Columns that may be written by create()/update(). */
    protected static array $fillable = [];

    private const OPERATORS = ['=', '!=', '<', '>', '<=', '>=', 'LIKE'];

    /** @return static[] */
    public static function all(?string $orderBy = null, string $direction = 'ASC'): array
    {
        $sql = 'SELECT * FROM ' . static::$table;
        if ($orderBy !== null) {
            static::assertColumn($orderBy);
            $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY $orderBy $dir";
        }
        return Database::connection()->query($sql)
            ->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    public static function find(int $id): ?static
    {
        $sql = 'SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ? LIMIT 1';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::class);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** @return static[] */
    public static function where(string $column, mixed $value, string $op = '='): array
    {
        static::assertColumn($column);
        if (!in_array($op, self::OPERATORS, true)) {
            throw new InvalidArgumentException("Invalid operator: $op");
        }
        $sql = 'SELECT * FROM ' . static::$table . " WHERE $column $op ?";
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    /** Insert a row; returns the new id. */
    public static function create(array $data): int
    {
        $data = static::onlyFillable($data);
        if ($data === []) {
            throw new InvalidArgumentException('No valid columns to insert.');
        }
        $cols  = implode(', ', array_keys($data));
        $marks = implode(', ', array_fill(0, count($data), '?'));
        $sql   = 'INSERT INTO ' . static::$table . " ($cols) VALUES ($marks)";
        $pdo   = Database::connection();
        $pdo->prepare($sql)->execute(array_values($data));
        return (int) $pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $data = static::onlyFillable($data);
        if ($data === []) {
            throw new InvalidArgumentException('No valid columns to update.');
        }
        $set = implode(', ', array_map(fn ($c) => "$c = ?", array_keys($data)));
        $sql = 'UPDATE ' . static::$table . " SET $set WHERE " . static::$primaryKey . ' = ?';
        return Database::connection()->prepare($sql)
            ->execute([...array_values($data), $id]);
    }

    public static function delete(int $id): bool
    {
        $sql = 'DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ?';
        return Database::connection()->prepare($sql)->execute([$id]);
    }

    /**
     * Many-to-one: this row holds the foreign key.
     * Example: $student->teacher() => belongsTo(Teacher::class, 'teacher_id')
     */
    protected function belongsTo(string $related, string $foreignKey): ?Model
    {
        $id = $this->$foreignKey;
        return $id ? $related::find((int) $id) : null;
    }

    /**
     * One-to-many: the other table holds the foreign key.
     * Example: $teacher->students() => hasMany(Student::class, 'teacher_id')
     * @return Model[]
     */
    protected function hasMany(string $related, string $foreignKey): array
    {
        return $related::where($foreignKey, $this->{static::$primaryKey});
    }

    /** Keep only columns the model allows (mass-assignment protection). */
    private static function onlyFillable(array $data): array
    {
        return array_intersect_key($data, array_flip(static::$fillable));
    }

    /** Column names cannot be bound as parameters, so whitelist them. */
    private static function assertColumn(string $column): void
    {
        $allowed = array_merge(static::$fillable, [static::$primaryKey, 'created_at']);
        if (!in_array($column, $allowed, true)) {
            throw new InvalidArgumentException("Invalid column: $column");
        }
    }
}
