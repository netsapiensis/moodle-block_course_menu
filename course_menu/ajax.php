<?php
/*
 * ---------------------------------------------------------------------------------------------------------------------
 *
 * This file is part of the Course Menu block for Moodle
 *
 * The Course Menu block for Moodle software package is Copyright � 2008 onwards NetSapiensis AB and is provided under
 * the terms of the GNU GENERAL PUBLIC LICENSE Version 3 (GPL). This program is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU
 * General Public License as published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version. This program is distributed in the hope that
 * it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE.
 *
 * See the GNU General Public License for more details. You should have received a copy of the GNU General Public
 * License along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------------------------------------------------------
 */

require_once('../../config.php');
require_login();

$name = required_param('element_name', PARAM_RAW);
$action = required_param('action', PARAM_RAW);
$name = md5(urldecode($name));
$instance_id = required_param('instance', PARAM_INT);

if ($action == "add") {
    if (!in_array($name, $_SESSION['cm_tree'][$instance_id]['expanded_elements'])) {
        $_SESSION['cm_tree'][$instance_id]['expanded_elements'][] = $name;
    }
} elseif ($action == "remove") {
    foreach ($_SESSION['cm_tree'][$instance_id]['expanded_elements'] as $k => $v) {
        if ($name == $v) {
            unset($_SESSION['cm_tree'][$instance_id]['expanded_elements'][$k]);
            break;
        }
    }
}
exit();