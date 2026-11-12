<h2>Edit teacher #<?= e($teacher->id) ?></h2>
<?php $formAction = 'index.php?resource=teachers&action=update&id=' . (int) $teacher->id; require __DIR__ . '/_form.php'; ?>
