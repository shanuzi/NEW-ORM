<h2>Edit student #<?= e($student->id) ?></h2>
<?php $formAction = 'index.php?resource=students&action=update&id=' . (int) $student->id; require __DIR__ . '/_form.php'; ?>
