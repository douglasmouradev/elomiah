<?php
$ok = flash('success');
$err = flash('error');
?>
<?php if ($ok): ?><div class="container"><div class="alert alert-ok"><?= e($ok) ?></div></div><?php endif; ?>
<?php if ($err): ?><div class="container"><div class="alert alert-err"><?= e($err) ?></div></div><?php endif; ?>
