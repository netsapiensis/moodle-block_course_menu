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


defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once 'lib/settingslib.php';

    $block = block_instance('course_menu');

    $settings->add(new admin_setting_configtext('block_course_menu_trimlength', get_string('trimlength', 'block_course_menu'), '', block_course_menu::DEFAULT_TRIM_LENGTH, PARAM_INT, 11));
    $settings->add(new admin_setting_configtext('block_course_menu_sitetitle', get_string('namesitelevel', 'block_course_menu'), get_string('namesiteleveldescription', 'block_course_menu'), block_course_menu::DEFAULT_SITE_LEVEL_TITLE));
    $settings->add(new admin_setting_configcolourpicker('block_course_menu_docked_background', get_string('dockedbg', 'block_course_menu'), get_string('dockedbgdesc', 'block_course_menu'), block_course_menu::DEFAULT_DOCKED_BG));
    $settings->add(new block_cm_admin_setting_confightml('global_config', '', '', '', $block));

}