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

$FIXED_VERSION = 2011051801;

//VERSION problem - until 2011-05-18 the version was: 201100518... (1 extra zero)
global $DB, $CFG;

$blk = $DB->get_record_select("block", "name = 'course_menu' AND version > 20110000000");

if ($blk !== false && is_object($blk)) {
    $_version = $blk->version;
    if (strlen($_version) > 10) {
        $blk->version = $FIXED_VERSION;
        $DB->update_record("block", $blk);
        header("Location: " . $CFG->wwwroot . "/admin/index.php");
        exit();
    }
}

$plugin->version = 2011092901;