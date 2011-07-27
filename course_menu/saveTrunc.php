<?php 

require_once('../../config.php');

$courseId = $_REQUEST['courseId'];

$_SESSION['truncated'][$courseId]  = $_REQUEST['truncated'];
$_SESSION['truncWidth'][$courseId] = $_REQUEST['truncWidth'];

echo ' ';

?>