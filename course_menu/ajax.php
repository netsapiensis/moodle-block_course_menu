<?php
require_once('../../config.php');

$name = $_REQUEST['element_name'];
$action = $_REQUEST['action'];

$courseId = $_REQUEST['courseId'];

if ($action == "add") {
    
    if (!in_array($name, $_SESSION['yui_tree'][$courseId]['expanded_elements'])) {
        $_SESSION['yui_tree'][$courseId]['expanded_elements'][] = $name;
    }

} elseif ($action == "remove") {

    foreach ($_SESSION['yui_tree'][$courseId]['expanded_elements'] as $k => $v)
        if ($name == $v) {
            unset($_SESSION['yui_tree'][$courseId]['expanded_elements'][$k]);
            break;
        }
}

?>
