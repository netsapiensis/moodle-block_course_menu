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

function xmldb_block_course_menu_upgrade($oldversion, $block)
{
    global $DB, $CFG;

    if ($oldversion <= 2012082702) {

        //remove gradebooks element - this might be present from prev versions
        if (!empty($CFG->block_course_menu_global_config)) { //first, remove it from the global config element
            $config = unserialize($CFG->block_course_menu_global_config);
            $save_it = block_course_menu_append_elements($config);

            if ($save_it) {
                set_config('block_course_menu_global_config', serialize($config));
            }
        }

        //foreach instance, remove gradebook and add new participants and reports links
        foreach ($DB->get_records('block_instances', array('blockname' => 'course_menu')) as $instance) {
            $config = unserialize(base64_decode($instance->configdata));

            $save_it = block_course_menu_append_elements($config);
            if ($save_it) {
                $instance->configdata = base64_encode(serialize($config));
                $DB->update_record('block_instances', $instance);
            }
        }
    } elseif ($oldversion <= 2012101500) {
        //remove gradebooks element - this might be present from prev versions
        if (!empty($CFG->block_course_menu_global_config)) { //first, remove it from the global config element
            $config = unserialize($CFG->block_course_menu_global_config);
            $save_it = block_course_menu_append_elements($config);

            if ($save_it) {
                set_config('block_course_menu_global_config', serialize($config));
            }
        }

        //foreach instance, remove gradebook and add new participants and reports links
        foreach ($DB->get_records('block_instances', array('blockname' => 'course_menu')) as $instance) {
            $config = unserialize(base64_decode($instance->configdata));

            $save_it = block_course_menu_append_elements($config);
            if ($save_it) {
                $instance->configdata = base64_encode(serialize($config));
                $DB->update_record('block_instances', $instance);
            }
        }
    }
    
    return true;
}

function block_course_menu_append_elements(& $config)
{
    $save_it = false;
    foreach ($config->elements as $index => $element) {
        if (!empty($element['id']) && ($element['id'] == 'showgrades' || $element['id'] == 'participantlist'))  {
            array_splice($config->elements, $index, 1);
            $save_it = true;
        }
        if ($element['id'] == 'participants') {
            $participants_found = true;
        }
    }
    if (!isset($participants_found)) {
        //add the new links - participants and reports
        // participants
        $config->elements [] = block_course_menu_create_element(
                'participants', get_string("participants", 'block_course_menu'), '', '', 1, 1, 1
        );

        // reports
        $config->elements [] = block_course_menu_create_element(
                'reports', get_string("reports", 'block_course_menu'), '', '', 1, 1, 1
        );
        $save_it = true;
    }

    return $save_it;
}

function block_course_menu_create_element($id, $name, $url, $icon = "", $canHide = 1, $visible = 1, $expandable = 0)
{
    $elem = array();
    $elem['id'] = $id;
    $elem['name'] = $name;
    $elem['url'] = $url;
    if (is_object($icon)) {
        $icon = $icon->__toString();
    }
    $elem['icon'] = $icon;
    $elem['canHide'] = $canHide;
    $elem['visible'] = $visible;

    $elem['expandable'] = $expandable;

    return $elem;
}