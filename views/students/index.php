<h2>Students</h2>
<form method="get" action="index.php">
  <input type="hidden" name="resource" value="students">
  <input type="text" name="q" placeholder="Search by name" value="<?= e($search) ?>" style="width:55%;padding:.4rem">
  <button class="btn" type="submit">Search</button>
  <a class="btn primary" href="index.php?resource=students&action=create">+ Add student</a>
</form>

<?php if (!$students): ?>
  <p>No students found.</p>
<?php else: ?>
<table>
  <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Course</th><th>Adviser</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($students as $s): ?>
    <tr>
      <td><?= e($s->id) ?></td>
      <td><?= e($s->name) ?></td>
      <td><?= e($s->email) ?></td>
      <td><?= e($s->course) ?></td>
      <td><?= e($s->teacher()?->name ?? '—') ?></td>
      <td>
        <a class="btn" href="index.php?resource=students&action=edit&id=<?= (int) $s->id ?>">Edit</a>
        <form class="inline" method="post"
              action="index.php?resource=students&action=destroy&id=<?= (int) $s->id ?>"
              onsubmit="return confirm('Delete this student?')">
          <?= \Core\Csrf::field() ?>
          <button class="btn danger" type="submit">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
