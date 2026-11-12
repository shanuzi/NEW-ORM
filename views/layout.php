<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Student Manager</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
  <a class="brand" href="index.php?resource=students">Student<span>.</span>Manager</a>
  <nav>
    <a href="index.php?resource=students">Students</a>
    <a href="index.php?resource=teachers">Teachers</a>
  </nav>
  <?php if ($flash): ?><div class="flash"><?= e($flash) ?></div><?php endif; ?>
  <div class="content">
    <?= $content ?>
  </div>
</div>
</body>
</html>
