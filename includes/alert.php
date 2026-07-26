<?php

if (hasFlash()) :

    $flash = getFlash();

?>

<div class="alert alert-<?= strtolower($flash['type']); ?>">

    <?= $flash['message']; ?>

</div>

<?php endif; ?>