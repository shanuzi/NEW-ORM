<h2>Teachers</h2>
<form method="get" action="index.php">
  <input type="hidden" name="resource" value="teachers">
  <input type="text" name="q" placeholder="Search by name" value="<?= e($search) ?>" style="width:55%;padding:.4rem">
  <button class="btn" type="submit">Search</button>
  <a class="btn primary" href="index.php?resource=teachers&action=create">+ Add teacher</a>
</form>

<?php if (!$teachers): ?>
  <p>No teachers found.</p>
<?php else: ?>
<table>
  <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th><th>Advisees</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach ($teachers as $t): ?>
    <?php $advisees = $t->students(); ?>
    <tr>
      <td><?= e($t->id) ?></td>
      <td><?= e($t->name) ?></td>
      <td><?= e($t->email) ?></td>
      <td><?= e($t->department) ?></td>
      <td>
        <?= count($advisees) ?>
        <?php if ($advisees): ?>
          <small>(<?= e(implode(', ', array_map(fn ($s) => $s->name, $advisees))) ?>)</small>
        <?php endif; ?>
      </td>
      <td>
        <a class="btn" href="index.php?resource=teachers&action=edit&id=<?= (int) $t->id ?>">Edit</a>
        <form class="inline" method="post"
              action="index.php?resource=teachers&action=destroy&id=<?= (int) $t->id ?>"
              onsubmit="return confirm('Delete this teacher? Their students will have no adviser.')">
          <?= \Core\Csrf::field() ?>
          <button class="btn danger" type="submit">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
