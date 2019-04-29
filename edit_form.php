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

class block_course_menu_edit_form extends block_edit_form
{

    function definition_after_data()
    {

        $mform = $this->_form;
        if ($mform->getElementType('bui_contexts') == 'select') {
            $el = $mform->getElement('bui_contexts');
            $el->removeOption(2); // Remove 'whole site' option
        }
    }

    protected function specific_definition($mform)
    {
        global $CFG, $PAGE;
        
        $mform->addElement('header', 'configheader', get_string('blockgeneralsettings', $this->block->blockname));

        $options = array(
            block_course_menu::TRIM_RIGHT => get_string('trimmoderight', $this->block->blockname),
            block_course_menu::TRIM_LEFT => get_string('trimmodeleft', $this->block->blockname),
            block_course_menu::TRIM_CENTER => get_string('trimmodecenter', $this->block->blockname)
        );
        $mform->addElement('select', 'config_trimmode', get_string('trimmode', $this->block->blockname), $options);
        $mform->setType('config_trimmode', PARAM_INT);

        $mform->addElement('text', 'config_trimlength', get_string('trimlength', $this->block->blockname));
        $mform->setDefault('config_trimlength', block_course_menu::DEFAULT_TRIM_LENGTH);
        $mform->setType('config_trimlength', PARAM_INT);

        $yesnooptions = array(1 => get_string('yes'), 0 => get_string('no'));
        $mform->addElement('select', 'config_expandableTree', get_string('expandable_tree', $this->block->blockname), $yesnooptions);
        $mform->setDefault('config_expandableTree', block_course_menu::EXPANDABLE_TREE);
        $mform->setType('config_expandableTree', PARAM_INT);

        $mform->addElement('header', 'configheader', get_string('chapters', $this->block->blockname));
        $mform->addElement('hidden', 'config_chapEnable', '', array('id' => 'id_config_chapEnable'));
        $mform->setDefault('config_chapEnable', 0);
        $mform->setType('config_chapEnable', PARAM_INT);
        
        $mform->addElement('hidden', 'config_subChapEnable', '', array('id' => 'id_config_subChapEnable'));
        $mform->setDefault('config_subChapEnable', 0);
        $mform->setType('config_subChapEnable', PARAM_INT);
        
        $mform->addElement('hidden', 'config_subChaptersCount', '', array('id' => 'id_config_subChaptersCount'));
        $mform->setType('config_subChaptersCount', PARAM_INT);
        
        $mform->addElement('html', $this->block->config_chapters());

        $mform->addElement('header', 'configheader', get_string('elements', $this->block->blockname));
        $mform->addElement('html', $this->block->config_elements());

        $mform->addElement('header', 'configheader', get_string('links', $this->block->blockname));
        $mform->addElement('hidden', 'config_linksEnable', '', array('id' => 'id_config_linksEnable'));
        $mform->setDefault('config_linksEnable', 0);
        $mform->setType('config_linksEnable', PARAM_INT);
        
        $mform->addElement('html', $this->block->config_links());
        
        $PAGE->requires->yui_module(array('moodle-block_course_menu-settings'), 'M.block_course_menu_settings.instance', 
                array(
                    $this->block->get_settings_util_js(), 
                    $this->block->get_config(), 
                    $this->block->get_section_names(),
                    $this->block->is_site_level()), null, true);
        
    }

}