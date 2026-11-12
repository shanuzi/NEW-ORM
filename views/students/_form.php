<?php if (!empty($errors)): ?>
  <ul class="errors">
    <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
  </ul>
<?php endif; ?>

<form method="post" action="<?= e($formAction) ?>">
  <?= \Core\Csrf::field() ?>

  <label>Name
    <input type="text" name="name" maxlength="100" required value="<?= e($old['name'] ?? '') ?>">
  </label>
  <label>Email
    <input type="email" name="email" maxlength="150" required value="<?= e($old['email'] ?? '') ?>">
  </label>
  <label>Course
    <input type="text" name="course" maxlength="100" required value="<?= e($old['course'] ?? '') ?>">
  </label>
  <label>Adviser (teacher)
    <select name="teacher_id">
      <option value="">No adviser</option>
      <?php foreach ($teachers as $t): ?>
        <option value="<?= (int) $t->id ?>"
          <?= (string) ($old['teacher_id'] ?? '') === (string) $t->id ? 'selected' : '' ?>>
          <?= e($t->name) ?> (<?= e($t->department) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </label>

  <p>
    <button class="btn primary" type="submit">Save</button>
    <a class="btn" href="index.php?resource=students">Cancel</a>
  </p>
</form>
