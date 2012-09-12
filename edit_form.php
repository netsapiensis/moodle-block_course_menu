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
    function definition() {
        $mform =& $this->_form;

        // First show fields specific to this type of block.
        $this->specific_definition($mform);

        // Then show the fields about where this block appears.
        $mform->addElement('header', 'whereheader', get_string('wherethisblockappears', 'block'));

        // If the current weight of the block is out-of-range, add that option in.
        $blockweight = $this->block->instance->weight;
        $weightoptions = array();
        if ($blockweight < -block_manager::MAX_WEIGHT) {
            $weightoptions[$blockweight] = $blockweight;
        }
        for ($i = -block_manager::MAX_WEIGHT; $i <= block_manager::MAX_WEIGHT; $i++) {
            $weightoptions[$i] = $i;
        }
        if ($blockweight > block_manager::MAX_WEIGHT) {
            $weightoptions[$blockweight] = $blockweight;
        }
        $first = reset($weightoptions);
        $weightoptions[$first] = get_string('bracketfirst', 'block', $first);
        $last = end($weightoptions);
        $weightoptions[$last] = get_string('bracketlast', 'block', $last);

        $regionoptions = $this->page->theme->get_all_block_regions();

        $parentcontext = get_context_instance_by_id($this->block->instance->parentcontextid);
        
        $mform->addElement('hidden', 'bui_parentcontextid', $parentcontext->id);
        
        $contextoptions = array();
        if ( ($parentcontext->contextlevel == CONTEXT_COURSE && $parentcontext->instanceid == SITEID) ||
             ($parentcontext->contextlevel == CONTEXT_SYSTEM)) {        // Home page
            $contextoptions[0] = get_string('showonfrontpageonly', 'block');
            $contextoptions[1] = get_string('showonfrontpageandsubs', 'block');
            //$contextoptions[2] = get_string('showonentiresite', 'block');
        } else {
            $parentcontextname = print_context_name($parentcontext);
            $contextoptions[0] = get_string('showoncontextonly', 'block', $parentcontextname);
            $contextoptions[1] = get_string('showoncontextandsubs', 'block', $parentcontextname);
        }
        $mform->addElement('select', 'bui_contexts', get_string('contexts', 'block'), $contextoptions);
        
        if ($this->page->pagetype == 'site-index') {   // No need for pagetype list on home page
            $pagetypelist = array('*');
        } else {
            $pagetypelist = matching_page_type_patterns($this->page->pagetype);
        }
        $pagetypeoptions = array();
        foreach ($pagetypelist as $pagetype) {         // Find human-readable names for the pagetypes
            $pagetypeoptions[$pagetype] = $pagetype;
            $pagetypestringname = 'page-'.str_replace('*', 'x',$pagetype);  // Better names MDL-21375
            if (get_string_manager()->string_exists($pagetypestringname, 'pagetype')) {
                $pagetypeoptions[$pagetype] .= ' (' . get_string($pagetypestringname, 'pagetype') . ')';
            }
        }
        $mform->addElement('select', 'bui_pagetypepattern', get_string('restrictpagetypes', 'block'), $pagetypeoptions);

        if ($this->page->subpage) {
            $subpageoptions = array(
                '%@NULL@%' => get_string('anypagematchingtheabove', 'block'),
                $this->page->subpage => get_string('thisspecificpage', 'block', $this->page->subpage),
            );
            $mform->addElement('select', 'bui_subpagepattern', get_string('subpages', 'block'), $subpageoptions);
        }

        $defaultregionoptions = $regionoptions;
        $defaultregion = $this->block->instance->defaultregion;
        if (!array_key_exists($defaultregion, $defaultregionoptions)) {
            $defaultregionoptions[$defaultregion] = $defaultregion;
        }
        $mform->addElement('select', 'bui_defaultregion', get_string('defaultregion', 'block'), $defaultregionoptions);

        $mform->addElement('select', 'bui_defaultweight', get_string('defaultweight', 'block'), $weightoptions);

        // Where this block is positioned on this page.
        $mform->addElement('header', 'whereheader', get_string('onthispage', 'block'));

        $mform->addElement('selectyesno', 'bui_visible', get_string('visible', 'block'));

        $blockregion = $this->block->instance->region;
        if (!array_key_exists($blockregion, $regionoptions)) {
            $regionoptions[$blockregion] = $blockregion;
        }
        $mform->addElement('select', 'bui_region', get_string('region', 'block'), $regionoptions);

        $mform->addElement('select', 'bui_weight', get_string('weight', 'block'), $weightoptions);

        $pagefields = array('bui_visible', 'bui_region', 'bui_weight');
        if (!$this->block->user_can_edit()) {
            $mform->hardFreezeAllVisibleExcept($pagefields);
        }
        if (!$this->page->user_can_edit_blocks()) {
            $mform->hardFreeze($pagefields);
        }

        $this->add_action_buttons();
    }

    protected function specific_definition($mform) {
        global $CFG;
        $mform->addElement('header', 'configheader', get_string('blockgeneralsettings', $this->block->blockname));
        
        $options = array(
            block_course_menu::TRIM_RIGHT   => get_string('trimmoderight', $this->block->blockname),
            block_course_menu::TRIM_LEFT    => get_string('trimmodeleft', $this->block->blockname),
            block_course_menu::TRIM_CENTER  => get_string('trimmodecenter', $this->block->blockname)
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
        $mform->addElement('hidden', 'config_subChapEnable', '', array('id' => 'id_config_subChapEnable'));
        $mform->setDefault('config_subChapEnable', 0);
        $mform->addElement('hidden', 'config_subChaptersCount', '', array('id' => 'id_config_subChaptersCount'));
        $mform->addElement('html', $this->block->config_chapters());

        $mform->addElement('header', 'configheader', get_string('elements', $this->block->blockname));
        $mform->addElement('html', $this->block->config_elements());

        $mform->addElement('header', 'configheader', get_string('links', $this->block->blockname));
        $mform->addElement('hidden', 'config_linksEnable', '', array('id' => 'id_config_linksEnable'));
        $mform->setDefault('config_linksEnable', 0);
        $mform->addElement('html', $this->block->config_links());
    }
    
}