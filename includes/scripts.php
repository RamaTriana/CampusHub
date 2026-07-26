<?php

if (isset($pageJS)) {
if (is_array($pageJS)) {
    foreach ($pageJS as $js) {
        echo '<link rel="stylesheet" href="assets/js/' . $js . '">';
    }
} else {
   echo '<script src="assets/js/' . $pageJS . '"></script>';
}

}

?>

</body>
</html>