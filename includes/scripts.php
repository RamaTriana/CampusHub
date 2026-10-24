<?php

require_once __DIR__ . '/guard.php';

if (isset($pageJS)) {
if (is_array($pageJS)) {
    foreach ($pageJS as $js) {
        echo '<script src="/public/assets/js/' . $js . '"></script>';
    }
} else {
    echo '<script src="/public/assets/js/' . $pageJS . '"></script>';
}

}

?>

</body>
</html>