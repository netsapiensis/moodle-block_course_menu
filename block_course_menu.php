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

class block_course_menu extends block_base
{
    /** @var int Trim characters from the right */

    const TRIM_RIGHT = 1;
    /** @var int Trim characters from the left */
    const TRIM_LEFT = 2;
    /** @var int Trim characters from the center */
    const TRIM_CENTER = 3;
    const DEFAULT_TRIM_LENGTH = 20;
    const DEFAULT_SITE_LEVEL_TITLE = 'Menu';
    const DEFAULT_DOCKED_BG = '#00AEEF';
    const EXPANDABLE_TREE = 0;

    private $_docked = 0;
    private $_site_level = 0;
    private $_site_level_elements = array(
        'calendar', 'sitepages', 'myprofile', 'mycourses', 'myprofilesettings'
    );
    private $contentgenerated = false;

    function init()
    {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);   
    }

    function instance_allow_multiple()
    {
        return false;
    }

    function instance_allow_config()
    {
        return true;
    }

    function get_content()
    {
        global $CFG, $USER, $DB, $OUTPUT;

        if ($this->page->course->id == SITEID) {
            if (!empty($CFG->block_course_menu_sitetitle)) {
                $this->title = $CFG->block_course_menu_sitetitle;
            } else {
                $this->title = self::DEFAULT_SITE_LEVEL_TITLE;
            }
            $this->_site_level = 1;
        }

        if ($this->contentgenerated) {
            return $this->content;
        }

        $this->content = new stdClass();

        $this->course = $this->page->course;

        $this->_docked = get_user_preferences('docked_block_instance_' . $this->instance->id, 0);

        //var for keeping expanded elements
        if (!isset($_SESSION['cm_tree'][$this->instance->id]['expanded_elements'])) {
            $_SESSION['cm_tree'][$this->instance->id]['expanded_elements'] = array();
        }
        $sessionVar = $_SESSION['cm_tree'][$this->instance->id]['expanded_elements'];

        $this->check_default_config();

        //newly added elements
        //participatlist -> 2012-10-15
        if (!$this->element_exists('sitepages')) {
            $this->init_default_config();
        }

        $sections = $this->get_sections();

        $this->page->navigation->initialise();
        $navigation = array(clone($this->page->navigation));
        $node_collection = $navigation[0]->children;
        $settings = $this->page->settingsnav->children;

        // displaysection - current section
        $displaysection = optional_param('section', -1, PARAM_INT);

        // section names
        foreach ($sections as $k => $section) {
            $sections[$k]['trimmed_name'] = $this->trim($section['name']);
            $sections[$k]['expanded'] = in_array(md5($sections[$k]['trimmed_name']), $sessionVar);
            foreach ($section['resources'] as $l => $resource) {
                $sections[$k]['resources'][$l]['trimmed_name'] = $this->trim($resource['name']);
            }
        }

        // links
        $links = $this->config->links;

        $sectCount = count($sections);
        $this->check_redo_chaptering($sectCount);

        $chapters = $this->config->chapters;
        $sumSection = 0;
        $found = false;
        foreach ($chapters as $k => $chapter) {
            $chapters[$k]['expanded'] = in_array(md5($chapter['name']), $sessionVar);
            foreach ($chapter['childElements'] as $i => $child) {
                if ($child['type'] == "subchapter") {
                    $chapters[$k]['childElements'][$i]['expanded'] = in_array(md5($child['name']), $sessionVar);
                    $sumSection += $child['count'];
                } else {
                    $sumSection++;
                }
                if ($sumSection >= $displaysection && !$found) {
                    $found = true;
                    $chapters[$k]['expanded'] = true;
                    $chapters[$k]['childElements'][$i]['expanded'] = true;
                }
            }
        }

        $expansionlimit = null;
        if (!empty($this->config->expansionlimit)) {
            $expansionlimit = $this->config->expansionlimit;
            $navigation->set_expansion_limit($this->config->expansionlimit);
        }

        $expandable = array();
        $this->find_expandable($this->page->navigation, $expandable);
        if ($expansionlimit) {
            foreach ($expandable as $key => $node) {
                if ($node['type'] > $expansionlimit && !($expansionlimit == navigation_node::TYPE_COURSE && $node['type'] == $expansionlimit && $node['branchid'] == SITEID)) {
                    unset($expandable[$key]);
                }
            }
        }
        
        $module = array('name' => 'block_course_menu', 'fullpath' => '/blocks/course_menu/course_menu.js', 'requires' => array('core_dock', 'io', 'node', 'dom', 'event-custom', 'json-parse'), 'strings' => array(array('viewallcourses', 'moodle')));
        $limit = 20;

        if (!empty($CFG->block_course_menu_docked_background)) {
            $bg_color = $CFG->block_course_menu_docked_background;
        } else {
            $bg_color = self::DEFAULT_DOCKED_BG;
        }

        $arguments = array(
            $this->instance->id,
            array(
                'expansions' => $expandable,
                'instance' => $this->instance->id,
                'candock' => $this->instance_can_be_docked(),
                'courselimit' => $limit,
                'docked' => $this->_docked,
                'bg_color' => $bg_color
            )
        );
        $this->page->requires->js_init_call('M.block_course_menu.init_add_tree', $arguments, false, $module);

        //render output
        $renderer = $this->page->get_renderer('block_course_menu');
        if ($this->_site_level) {
            $renderer->session = $_SESSION['cm_tree'][$this->instance->id]['expanded_elements'];
        }

        $output = '<div class="block_navigation">';
        $lis = '';
        $linkIndex = 0;
        $first = true;

        foreach ($this->config->elements as $element) {
            $element['name'] = $this->get_name($element['id']);
            $element['children'] = array();
            if ($element['visible']) {
                if (!$this->_site_level || substr($element['id'], 0, 4) == 'link' ||
                        ($this->_site_level && in_array($element['id'], $this->_site_level_elements))) {

                    $icon = $renderer->icon($element['icon'], $element['name'], array('class' => 'smallicon'));
                    switch ($element['id']) {
                        case 'tree': //build chapter / subchapter / topic / week structure
                            $lis .= $renderer->render_chapter_tree($this->instance->id, $this->config, $chapters, $sections, $displaysection, !$first);
                            break;
                        case 'coursemainpage': //this has been removed in Course Menu for Moodle 2.3
                            break;
                        case 'calendar':
                            $element['url'] = "$CFG->wwwroot/calendar/view.php?view=upcoming&course=" . $this->course->id;
                            $lis .= $renderer->render_leaf($element['name'], $icon, array(), $element['url'], false, '', !$first);
                            break;
                        case 'participants':
                            if ($node_collection instanceof navigation_node_collection) {
                                $_node = $node_collection->find($this->page->course->id, global_navigation::TYPE_COURSE)->children->get('participants');
                                //check capabilities
                                // user/index.php expect course context, so get one if page has module context.
                                $currentcontext = $this->page->context->get_course_context(false);
                                if (! (empty($currentcontext) || 
                                        ($this->page->course->id == SITEID && !has_capability('moodle/site:viewparticipants', get_context_instance(CONTEXT_SYSTEM))) ||
                                        !has_capability('moodle/course:viewparticipants', $currentcontext))) {
                                    
                                    $element['url'] = $CFG->wwwroot . '/user/index.php?contextid=' . $currentcontext->id;
                                    $child_node = new navigation_node(array(
                                        'text' => get_string('participantlist', $this->blockname),
                                        'shorttext' => get_string('participantlist', $this->blockname),
                                        'icon' => new pix_icon('i/users', get_string('participantlist', $this->blockname)),
                                        'action' => $element['url']
                                    ));
                                    $_node->add_node($child_node, 0);
                                }
                                $lis .= $renderer->render_navigation_node($_node, $expansionlimit, !$first);
                            }
                            break;
                        case 'reports':
                            if ($node_collection instanceof navigation_node_collection) {
                                $_course = $node_collection->find($this->page->course->id, global_navigation::TYPE_COURSE)->children;
                                $_node = $_course->get(1, global_navigation::TYPE_CONTAINER);
                                if ($_node instanceof navigation_node) {
                                    $lis .= $renderer->render_navigation_node($_node, $expansionlimit, !$first);
                                }
                            }
                            break;
                        default:
                            if (substr($element['id'], 0, 4) == 'link') {
                                preg_match('/link([0-9]+)/', $element['id'], $matches);
                                $index = intval($matches[1]);
                                if ($index && isset($this->config->links[$index])) { //this should always happen
                                    $lis .= $renderer->render_link($this->config->links[$index], $this->course->id, !$first);
                                } else {
                                    $lis .= $renderer->render_link($this->config->links[$linkIndex], $this->course->id, !$first);
                                    $linkIndex++;
                                }
                            } else {
                                //check for special links (navigation, settings)
                                if ($this->is_navigation_element($element['id'])) {
                                    $type = 0;
                                    if ($element['id'] == 'sitepages') {
                                        $type = global_navigation::TYPE_COURSE;
                                    } elseif ($element['id'] == 'myprofile') {
                                        $type = global_navigation::TYPE_USER;
                                    } elseif ($element['id'] == 'mycourses') {
                                        $type = global_navigation::TYPE_ROOTNODE;
                                    }
                                    $all = $node_collection->type($type);
                                    $good = array();

                                    if (is_array($all) && count($all)) {
                                        foreach ($all as $item) {
                                            if ($item->text == get_string($element['id'])) {
                                                $good = $item;
                                                break;
                                            }
                                        }
                                    }
                                    if ($good instanceof navigation_node && $good->children->count()) {
                                        $lis .= $renderer->render_navigation_node($good, $expansionlimit, !$first);
                                    }
                                } elseif ($this->is_settings_element($element['id'])) {
                                    $type = 0;
                                    $key = '';
                                    if ($element['id'] == 'myprofilesettings') {
                                        $type = global_navigation::TYPE_CONTAINER;
                                        $key = 'usercurrentsettings';
                                    } elseif ($element['id'] == 'courseadministration') {
                                        $key = 'courseadministration';
                                        $type = global_navigation::TYPE_COURSE;
                                    }
                                    $all = $settings->type($type);
                                    $s = array();
                                    if (is_array($all) && count($all)) {
                                        foreach ($all as $item) {
                                            if ($item->text == get_string($key)) {
                                                $s = $item;
                                                break;
                                            }
                                        }
                                    }
                                    if ($s instanceof navigation_node && $s->children->count()) {
                                        $lis .= $renderer->render_navigation_node($s, $expansionlimit, !$first);
                                    }
                                }
                            }
                    }
                    $first = false;
                }
            }
        }
        $output .= html_writer::tag('ul', $lis, array('class' => 'block_tree list'));
        $output .= '</div>';

        $this->contentgenerated = true;
        $this->content->text = $output;

        return $this->content;
    }

    public function find_expandable($navigation, array &$expandable)
    {
        foreach ($navigation->children as &$child) {
            if ($child->nodetype == global_navigation::NODETYPE_BRANCH && $child->children->count() == 0 && $child->display) {
                $child->id = 'cm_expandable_branch_' . (count($expandable) + 1);
                $navigation->add_class('canexpand');
                $expandable[] = array('id' => $child->id, 'branchid' => $child->key, 'type' => $child->type);
            }
            $this->find_expandable($child, $expandable);
        }
    }

    function init_default_config($save_it = true)
    {
        global $CFG, $OUTPUT;

        // elements -------------------------------------------------------------------------
        $elements = array();
        $elements[] = $this->create_element("tree", $this->get_name("tree"), '', '', 0);

        // calendar
        $elements[] = $this->create_element(
                "calendar", $this->get_name("calendar"), "", "{$CFG->wwwroot}/blocks/course_menu/icons/cal.gif", 1, 0
        );

        //site pages
        $elements [] = $this->create_element(
                'sitepages', get_string("sitepages"), '', '', 1, $this->_site_level, 1
        );

        //my profile
        $elements [] = $this->create_element(
                'myprofile', get_string("myprofile"), '', '', 1, $this->_site_level, 1
        );

        //my course
        $elements [] = $this->create_element(
                'mycourses', get_string("mycourses"), '', '', 1, $this->_site_level, 1
        );

        //my profile settings
        $elements [] = $this->create_element(
                'myprofilesettings', get_string("myprofilesettings", "{$this->blockname}"), '', '', 1, $this->_site_level, 1
        );

        //course administration
        $elements [] = $this->create_element(
                'courseadministration', get_string("courseadministration", "{$this->blockname}"), '', '', 1, 0, 1
        );

        // participants
        $elements [] = $this->create_element(
                'participants', get_string("participants", "{$this->blockname}"), '', '', 1, 0, 1
        );

        // reports
        $elements [] = $this->create_element(
                'reports', get_string("reports", "{$this->blockname}"), '', '', 1, 0, 1
        );

        // troubleticket
        if (file_exists($CFG->dirroot . '/blocks/trouble_ticket/block_trouble_ticket.php')) {
            $elements[] = $this->create_element(
                    "troubleticket", $this->get_name("troubleticket"), "", "{$CFG->wwwroot}/blocks/trouble_ticket/icons/bug.gif", 1, 0
            );
        }

        $config = new stdClass();
        $config->elements = $elements;

        // sections -------------------------------------------------------------------------
        $sections = $this->get_sections();

        // chaptering -----------------------------------------------------------------------
        $config->chapEnable = 0;
        $config->subChapEnable = 0;
        $config->subChaptersCount = 1;
        $config->chapters = array();

        $chapter = array();
        $chapter['name'] = get_string("chapter", "{$this->blockname}") . " 1";

        $child = array();
        $child['type'] = "subchapter";
        $child['name'] = get_string("subchapter", "{$this->blockname}") . " 1";
        $child['count'] = count($sections);
        $chapter['childElements'] = array($child);

        $config->chapters[] = $chapter;

        // links ----------------------------------------------------------------------------
        $config->linksEnable = 0;
        $config->links = array();

        $config->expandableTree = self::EXPANDABLE_TREE;
        $config->trimmode = self::TRIM_RIGHT;
        $config->trimlength = isset($CFG->block_course_menu_trimlength) ?
                $CFG->block_course_menu_trimlength : self::DEFAULT_TRIM_LENGTH;

        $this->config = $config;
        if ($save_it) {
            $this->save_config_to_db();
        }
    }

    function save_config_to_db()
    {
        global $DB;
        return $DB->set_field('block_instances', 'configdata', base64_encode(serialize($this->config)), array('id' => $this->instance->id));
    }

    function check_default_config()
    {
        global $CFG;

        if (empty($this->config) || !is_object($this->config) || (!$this->_site_level && empty($this->config->chapters))) {
            //try global config
            if ($this->_site_level) {

                $this->init_default_config();
            } elseif (!empty($CFG->block_course_menu_global_config)) {
                $this->config = unserialize($CFG->block_course_menu_global_config);
                // chaptering -----------------------------------------------------------------------
                $this->config->chapEnable = 0;
                $this->config->subChapEnable = 0;
                $this->config->subChaptersCount = 1;
                $this->config->chapters = array();

                $chapter = array();
                $chapter['name'] = get_string("chapter", "{$this->blockname}") . " 1";
                $child = array();
                $child['type'] = "subchapter";
                $child['name'] = get_string("subchapter", "{$this->blockname}") . " 1";
                $child['count'] = count($this->get_sections());
                $chapter['childElements'] = array($child);
                $this->config->chapters[] = $chapter;

                $this->save_config_to_db();
            } else {
                $this->init_default_config();
            }
        }
        $this->remove_deprecated();
    }

    function check_redo_chaptering($sectCount)
    {
        // redo chaptering if the number of the sctions changed
        $sumChapSections = 0;
        $subChapterCount = 0;

        foreach ($this->config->chapters as $k => $chapter) {
            foreach ($chapter['childElements'] as $kk => $child) {
                if ($child['type'] == "subchapter") {
                    $subChapterCount++;
                    $sumChapSections += $child['count'];
                } else {
                    $sumChapSections++;
                }
            }
        }
        $chapCount = count($this->config->chapters);

        if ($sumChapSections != $sectCount) {

            if ($chapCount <= $sectCount) {

                $c = floor($sectCount / $chapCount);

                if (($sectCount - ($c * ($chapCount - 1)) > $c) && ($sectCount - (($c + 1) * ($chapCount - 1)) > 0)) {
                    $c++;
                }

                for ($i = 0; $i < $chapCount; $i++) {
                    $temp = $i < $chapCount - 1 ? $c : $sectCount - ($c * ($chapCount - 1));
                    $j = 0;
                    while ($this->config->chapters[$i]['childElements'][$j]['type'] == "topic") {
                        $j++;
                    }
                    $newChild = $this->config->chapters[$i]['childElements'][$j];
                    $newChild['count'] = $temp;
                    $this->config->chapters[$i]['childElements'] = array();
                    $this->config->chapters[$i]['childElements'][0] = $newChild;
                }
            } else {
                // make 1 section / chapter; eliminate ($chapCount - $sectCount) chapters, from the last ones
                for ($i = 0; $i < $sectCount; $i++) {
                    $j = 0;
                    while ($this->config->chapters[$i]['childElements'][$j]['type'] == "topic") {
                        $j++;
                    }
                    $newChild = $this->config->chapters[$i]['childElements'][$j];
                    $newChild['count'] = 1;
                    $this->config->chapters[$i]['childElements'] = array();
                    $this->config->chapters[$i]['childElements'][0] = $newChild;
                }
                for ($i = $sectCount; $i < $chapCount; $i++) {
                    unset($this->config->chapters[$i]);
                }
                $chapCount = $sectCount;
            }
            $this->config->subChaptersCount = count($this->config->chapters);

            // the number of sections has changed so the chaptering has changed so write the new changes to the database
            $this->save_config_to_db();
        }
    }

    function clearEnters($string)
    {
        $newstring = str_replace(chr(13), ' ', str_replace(chr(10), ' ', $string));
        return $newstring;
    }

    function create_element($id, $name, $url, $icon = "", $canHide = 1, $visible = 1, $expandable = 0)
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

    function get_name($elementId)
    {
        global $DB;
        if (isset($this->instance->pageid)) {
            $course = $DB->get_record('course', array('id' => $this->instance->pageid));
            $format = $course->format;
        } else {
            $format = '';
        }

        switch ($elementId) {
            case 'calendar': return get_string('calendar', 'calendar');
            case 'sectiongroup': return get_string("name" . $format);
            case 'tree':
                if ($format == 'topics') {
                    return get_string('topics', $this->blockname);
                } elseif ($format == 'weeks') {
                    return get_string('weeks', $this->blockname);
                } else {
                    return get_string('topicsweeks', $this->blockname);
                }
            default:
                if (strstr($elementId, "link") !== false) {
                    return get_string("link", $this->blockname);
                }
                if (in_array($elementId, array('sitepages', 'mycourses', 'myprofile'))) {
                    return get_string($elementId);
                }
                return get_string($elementId, $this->blockname);
        }
    }

    function get_sections()
    {
        global $CFG, $USER, $DB, $OUTPUT;

        if (!empty($this->instance) && $this->page->course->id != SITEID) {

            require_once($CFG->dirroot . "/course/lib.php");
            
            $context = get_context_instance(CONTEXT_COURSE, $this->course->id);
            $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);

            $genericName = get_string("sectionname", 'format_' . $this->course->format);
            
            $modinfo = get_fast_modinfo($this->page->course);
            $mods = $modinfo->get_cms();
            
            $allSections = $modinfo->get_section_info_all();

            $sections = array();
            if ($this->course->format != 'social' && $this->course->format != 'scorm') {
                foreach ($allSections as $k => $section) {

                    if ($k <= $this->course->numsections) { // get_all_sections() may return sections that are in the db but not displayed because the number of the sections for this course was lowered - bug [CM-B10]
                        if (!empty($section)) {
                            $newSec = array();
                            $newSec['visible'] = $section->visible;
                            $newSec['uservisible'] = !empty($section->uservisible) ? $section->uservisible : 0;
                            $newSec['availableinfo'] = !empty($section->availableinfo) ? $section->availableinfo : 0;
                            $newSec['id'] = $section->section;
                            $newSec['index'] = $k;

                            if (!empty($section->name)) {
                                $strsummary = trim($section->name);
                            } else {
                                $strsummary = ucwords($genericName) . " " . $k; // just a default name
                            }

                            $strsummary = $this->trim($strsummary);                            
                            $newSec['name'] = $strsummary;
                            $newSec['url'] = course_get_url($this->course, $k);

                            // resources
                            $newSec['resources'] = array();
                            $sectionmods = explode(",", $section->sequence);
                            foreach ($sectionmods as $modnumber) {
                                if (empty($mods[$modnumber])) {
                                    continue;
                                }
                                $mod = $mods[$modnumber];
                                if ($mod->visible or $canviewhidden) {
                                    $instancename = urldecode($modinfo->cms[$modnumber]->name);

                                    if (!empty($CFG->filterall)) {
                                        $instancename = filter_text($instancename, $this->course->id);
                                    }

                                    // don't do anything for labels
                                    if ($mod->modname != 'label') {
                                        // Normal activity
                                        if ($mod->visible or $canviewhidden) {
                                            if (!strlen(trim($instancename))) {
                                                $instancename = $mod->modfullname;
                                            }

                                            $resource = array();
                                            if ($mod->modname != 'resource') {
                                                $resource['name'] = $instancename;
                                                $resource['url'] = "$CFG->wwwroot/mod/$mod->modname/view.php?id=$mod->id";
                                                $icon = $OUTPUT->pix_url("icon", $mod->modname);
                                                if (is_object($icon)) {
                                                    $resource['icon'] = $icon->__toString();
                                                } else {
                                                    $resource['icon'] = '';
                                                }
                                            } else {
                                                require_once($CFG->dirroot . '/mod/resource/lib.php');
                                                $info = resource_get_coursemodule_info($mod);
                                                if (isset($info->icon)) {
                                                    $resource['name'] = $info->name;
                                                    $resource['url'] = "$CFG->wwwroot/mod/$mod->modname/view.php?id=$mod->id";
                                                    $icon = $OUTPUT->pix_url("icon", $mod->modname);
                                                    if (is_object($icon)) {
                                                        $resource['icon'] = $icon->__toString();
                                                    } else {
                                                        $resource['icon'] = '';
                                                    }
                                                } else if (!isset($info->icon)) {
                                                    $resource['name'] = $info->name;
                                                    $resource['url'] = "$CFG->wwwroot/mod/$mod->modname/view.php?id=$mod->id";
                                                    $icon = $OUTPUT->pix_url("icon", $mod->modname);
                                                    if (is_object($icon)) {
                                                        $resource['icon'] = $icon->__toString();
                                                    } else {
                                                        $resource['icon'] = $OUTPUT->pix_url("icon", $mod->modname);
                                                    }
                                                }
                                            }
                                            if ($section->uservisible) {
                                                $newSec['resources'][] = $resource;
                                            }
                                        }
                                    }
                                }
                            }
                            $showsection = $section->uservisible ||
                                    ($section->visible && !$section->available && $section->showavailability);
                            //hide hidden sections from students if the course settings say that - bug #212
                            $coursecontext = get_context_instance(CONTEXT_COURSE, $this->course->id);
                            if (!($section->visible == 0 && !has_capability('moodle/course:viewhiddensections', $coursecontext)) && $showsection) {
                                $sections[] = $newSec;
                            }
                        }
                    }
                }

                // get rid of the first one
                array_shift($sections);
            }

            return $sections;
        }
        return array();
    }

    function get_link_icons()
    {
        global $CFG, $DB, $OUTPUT;

        $icons = array();
        $icons[0]['name'] = get_string('noicon', $this->blockname);
        $icons[0]['img'] = '';
        $icons[1]['name'] = get_string('linkfileorsite', $this->blockname);
        $icons[1]['img'] = "{$CFG->wwwroot}/blocks/course_menu/icons/link.gif";
        $icons[2]['name'] = get_string('displaydirectory', $this->blockname);
        $icons[2]['img'] = "{$CFG->wwwroot}/blocks/course_menu/icons/directory.gif";

        $allmods = $DB->get_records("modules");
        foreach ($allmods as $mod) {
            $icon = array();
            $icon['name'] = get_string("modulename", $mod->name);
            $obj = $OUTPUT->pix_url('icon', $mod->name);
            if (is_object($obj)) {
                $icon['img'] = $obj->__toString();
            } else {
                $icon['img'] = '';
            }

            $icons[] = $icon;
        }

        return $icons;
    }

    public function trim($str)
    {
        $mode = self::TRIM_RIGHT;
        $length = self::DEFAULT_TRIM_LENGTH;
        if (!empty($this->config->trimmode)) {
            $mode = (int) $this->config->trimmode;
        }
        if (!empty($this->config->trimlength)) {
            $length = (int) $this->config->trimlength;
        }

        $str_length = textlib::strlen($str);

        switch ($mode) {
            case self::TRIM_RIGHT :
                if ($str_length > ($length + 3)) {
                    return textlib::substr($str, 0, $length) . '...';
                }
            case self::TRIM_LEFT :
                if ($str_length > ($length + 3)) {
                    return '...' . textlib::substr($str, $str_length - $length);
                }
            case self::TRIM_CENTER :
                if ($str_length > ($length + 3)) {
                    $trimlength = ceil($length / 2);
                    $start = textlib::substr($str, 0, $trimlength);
                    $end = textlib::substr($str, $str_length - $trimlength);
                    $string = $start . '...' . $end;
                    return $string;
                }
        }

        return $str;
    }

    function config_chapters()
    {
        global $CFG, $USER, $OUTPUT;

        $this->course = $this->page->course;
        $this->check_default_config();
        $chapters = $this->config->chapters;
        $sections = $this->get_sections();
        $sectionNames = array();
        foreach ($sections as $section) {
            $sectionNames[] = $section['name'];
        }
        $this->check_redo_chaptering(count($sections));
        ob_start();
        include ("{$CFG->dirroot}/blocks/course_menu/css/styles.php");
        include ("{$CFG->dirroot}/blocks/course_menu/config/chapters.php");
        $cc = ob_get_contents();
        ob_end_clean();
        return $cc;
    }

    function config_elements()
    {
        global $CFG, $USER, $OUTPUT;

        $this->course = $this->page->course;
        if (!$this->element_exists('sitepages')) {
            $this->init_default_config();
        }

        ob_start();
        include ("{$CFG->dirroot}/blocks/course_menu/config/elements.php");
        $cc = ob_get_contents();
        ob_end_clean();
        return $cc;
    }

    function config_links()
    {
        global $CFG, $USER, $OUTPUT;

        $icons = $this->get_link_icons();

        ob_start();
        include ("{$CFG->dirroot}/blocks/course_menu/config/links.php");
        $cc = ob_get_contents();
        ob_end_clean();
        return $cc;
    }

    function instance_config_save($data, $nolongerused = false)
    {
        //append stuff to data - this is BAD
        //chapters
        $chapters = array();
        $lastIndex = 0;
        $total = 0;
        if ($data->chapEnable == 0) {
            $data->subChapEnable = 0;
        }
        if ($this->page->course->id != SITEID) { //save chapters
            foreach ($_POST['chapterNames'] as $k => $name) {
                $chapter = array();
                $chapter['name'] = $name;
                $chapter['childElements'] = array();

                for ($i = $lastIndex; $i < $lastIndex + $_POST['chapterChildElementsNumber'][$k]; $i++) {
                    $child = array();
                    if ($data->chapEnable == 0) { //only one subchapter
                        $child['type'] = "subchapter";
                        $child['count'] = count($this->get_sections());
                        $child['name'] = get_string("subchapter", "block_course_menu") . " 1-1";
                    } elseif ($data->subChapEnable == 0) {
                        $child['type'] = "subchapter";
                        $xx = $k + 1;
                        $child['name'] = get_string("subchapter", "block_course_menu") . " {$xx}-1";
                        $child['count'] = $_POST['chapterCounts'][$k];
                    } else {
                        $child['type'] = $_POST['childElementTypes'][$i];
                        if ($child['type'] == "subchapter") {
                            $child['count'] = $_POST['childElementCounts'][$i];
                            $total += $child['count'];
                            $child['name'] = $_POST['childElementNames'][$i];
                        }
                    }
                    $chapter['childElements'][] = $child;
                }
                $lastIndex = $i;
                $chapters[] = $chapter;
            }
        } else {
            $data->chapEnable = 0;
            $data->subChapEnable = 0;
            $data->subChaptersCount = 1;
            $data->chapters = array();

            $chapter = array();
            $chapter['name'] = get_string("chapter", "{$this->blockname}") . " 1";

            $child = array();
            $child['type'] = "subchapter";
            $child['name'] = get_string("subchapter", "{$this->blockname}") . " 1";
            $child['count'] = 0;
            $chapter['childElements'] = array($child);

            $chapters[] = $chapter;
        }
        $data->chapters = $chapters;

        // elements
        $data->elements = array();
        foreach ($_POST['ids'] as $k => $id) {
            $url = $_POST['urls'][$k];
            $icon = $_POST['icons'][$k];
            $canHide = $_POST['canHides'][$k];
            $visible = $_POST['visibles'][$k];
            $name = $this->get_name($id);
            $data->elements[] = $this->create_element($id, $name, $url, $icon, $canHide, $visible);
        }

        //links
        $data->links = array();
        if (isset($_POST['linkNames'])) { // means: if instance config. we don't have links in global config
            foreach ($_POST['linkNames'] as $k => $name) {
                $link = array();
                $link['name'] = $name;
                $link['target'] = $_POST['linkTargets'][$k];
                $link['icon'] = $_POST['linkIcons'][$k];

                // url
                $link['url'] = $_POST['linkUrls'][$k];
                if (strpos($_POST['linkUrls'][$k], "://") === false) {
                    // if no protocol then add "http://" - [CM-TD2]
                    $link['url'] = "http://" . $link['url'];
                }

                // checkbox configs
                $idx = "keeppagenavigation$k";
                $link['keeppagenavigation'] = (isset($_POST[$idx])) && ($_POST[$idx] == "on") ? 1 : 0;

                $idx = "allowresize$k";
                $link['allowresize'] = (isset($_POST[$idx])) && ($_POST[$idx] == "on") ? 1 : 0;

                $idx = "allowresize$k";
                $link['allowresize'] = (isset($_POST[$idx])) && ($_POST[$idx] == "on") ? 1 : 0;

                $idx = "allowresize$k";
                $link['allowresize'] = (isset($_POST[$idx])) && ($_POST[$idx] == "on") ? 1 : 0;

                $idx = "allowscroll$k";
                $link['allowscroll'] = (isset($_POST[$idx])) && ($_POST[$idx] == "on") ? 1 : 0;

                $idx = "showdirectorylinks$k";
                $link['showdirectorylinks'] = (isset($_POST[$idx])) && ($_POST[$idx] == "on") ? 1 : 0;

                $idx = "showlocationbar$k";
                $link['showlocationbar'] = (isset($_POST[$idx])) && ($_POST[$idx] == "on") ? 1 : 0;

                $idx = "showmenubar$k";
                $link['showmenubar'] = (isset($_POST[$idx])) && ($_POST[$idx] == "on") ? 1 : 0;

                $idx = "showtoolbar$k";
                $link['showtoolbar'] = (isset($_POST[$idx])) && ($_POST[$idx] == "on") ? 1 : 0;

                $idx = "showstatusbar$k";
                $link['showstatusbar'] = (isset($_POST[$idx])) && ($_POST[$idx] == "on") ? 1 : 0;

                // defaultwidth + defaultheight
                $link['defaultwidth'] = !empty($_POST['defaultwidth'][$k]) ? $_POST['defaultwidth'][$k] : 0;
                $link['defaultheight'] = !empty($_POST['defaultheight'][$k]) ? $_POST['defaultheight'][$k] : 0;

                $data->links[] = $link;
            }
        }
        return parent::instance_config_save($data, $nolongerused);
    }

    public function has_config()
    {
        return true;
    }

    function output_global_config()
    {
        global $CFG, $THEME, $OUTPUT;

        $icons = $this->get_link_icons();
        // if any config is missing then set eveything to default
        if (empty($CFG->block_course_menu_global_config)) {
            $this->init_default_config(false);
        } else {
            $this->config = @unserialize($CFG->block_course_menu_global_config);

            if (!$this->element_exists('sitepages')) {
                $this->init_default_config(false);
            } else {
                if ($this->remove_deprecated()) {
                    $CFG->block_course_menu_global_config = serialize($this->config);
                    set_config('block_course_menu_global_config', $CFG->block_course_menu_global_config);
                }
            }
        }

        // elements: set names
        foreach ($this->config->elements as $k => $element) {
            $this->config->elements[$k]['name'] = $this->get_name($element['id']);
        }

        ob_start();
        include ("{$CFG->dirroot}/blocks/course_menu/config/global.php");
        $cc = ob_get_contents();
        ob_end_clean();
        return $cc;
    }

    function element_exists($id)
    {
        if (!is_object($this->config) || !isset($this->config->elements) || !is_array($this->config->elements)) {
            return false;
        }
        foreach ($this->config->elements as $element) {
            if ($element['id'] == $id) {
                return true;
            }
        }
        return false;
    }

    function is_navigation_element($id)
    {
        switch ($id) {
            case 'sitepages':
            case 'myprofile':
            case 'mycourses':
                return true;
            default:
                return false;
        }
    }

    function is_settings_element($id)
    {
        switch ($id) {
            case 'courseadministration':
            case 'myprofilesettings':
                return true;
            default:
                return false;
        }
    }

    function remove_deprecated()
    {
        //showallsections has been removed
        foreach ($this->config->elements as $k => $element) {
            if ($element['id'] == 'showallsections' || $element['id'] == 'coursemainpage') {
                array_slice($this->config->elements, $k, 1);
                if (!empty($this->instance) && !empty($this->instance->id)) {
                    $this->save_config_to_db();
                }
                return true;
            }
        }

        return false;
    }

}

?>