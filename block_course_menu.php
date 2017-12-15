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
    protected $section_names = array();

    public $blockname;

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

    /**
     * Gets Javascript that may be required for navigation
     */
    function load_all_js($expandable)
    {
        global $CFG;
        user_preference_allow_ajax_update('docked_block_instance_' . $this->instance->id, PARAM_INT);
        $limit = 20;
        if (!empty($CFG->navcourselimit)) {
            $limit = $CFG->navcourselimit;
        }
        $expansionlimit = 0;
        if (!empty($this->config->expansionlimit)) {
            $expansionlimit = $this->config->expansionlimit;
        }
        if (!empty($CFG->block_course_menu_docked_background)) {
            $bg_color = $CFG->block_course_menu_docked_background;
        } else {
            $bg_color = self::DEFAULT_DOCKED_BG;
        }
        $arguments = array(
            'id' => $this->instance->id,
            'instance' => $this->instance->id,
            'candock' => $this->instance_can_be_docked(),
            'courselimit' => $limit,
            'expansionlimit' => $expansionlimit,
            'bg_color' => $bg_color,
            'expansions' => $expandable
        );

        $this->page->requires->string_for_js('viewallcourses', 'moodle');
        $this->page->requires->yui_module(array('moodle-core-dock', 'moodle-block_course_menu-navigation'), 'M.block_course_menu.init_add_tree', array($arguments));
    }

    function get_content()
    {
        if ($this->contentgenerated) {
            return $this->content;
        }

        global $CFG, $USER, $DB, $OUTPUT;

        if ($this->page->course->id == SITEID) {
            if (!empty($CFG->block_course_menu_sitetitle)) {
                $this->title = $CFG->block_course_menu_sitetitle;
            } else {
                $this->title = self::DEFAULT_SITE_LEVEL_TITLE;
            }
            $this->_site_level = 1;
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

        $this->load_all_js($expandable);

        //render output
        /** @var block_course_menu_renderer $renderer */
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
                        case 'showallsections':
                            $courseFormat = $this->course->format == 'topics' ? 'topic' : 'week';
                            $element['url'] = "$CFG->wwwroot/course/view.php?id={$this->course->id}&{$courseFormat}=all";
                            $lis .= $renderer->render_leaf($element['name'], $icon, array(), $element['url'], false, '', !$first);
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
                                if (!(empty($currentcontext) ||
                                        ($this->page->course->id == SITEID && !has_capability('moodle/site:viewparticipants', context_system::instance())) ||
                                        !has_capability('moodle/course:viewparticipants', $currentcontext))) {

                                    $element['url'] = $CFG->wwwroot . '/user/index.php?contextid=' . $currentcontext->id;
                                    $child_node = new navigation_node(array(
                                        'text' => get_string('participantlist', $this->blockname),
                                        'shorttext' => get_string('participantlist', $this->blockname),
                                        'icon' => new pix_icon('i/users', get_string('participantlist', $this->blockname)),
                                        'action' => $element['url']
                                    ));
                                    $_node->add_node($child_node);
                                }
                                $lis .= $renderer->render_navigation_node($_node, $expansionlimit, !$first);
                            }
                            break;
                        case 'reports':
                            if ($node_collection instanceof navigation_node_collection) {
                                $_course = $node_collection->find($this->page->course->id, global_navigation::TYPE_COURSE)->children;
                                $_node = $_course->get(1, global_navigation::TYPE_CONTAINER); // Moodle 2.3 - 2.6
                                if (empty($_node)) { // Moodle >= 2.6
                                    $settings = $this->page->settingsnav->children;
                                    $admin_node = $settings->get('courseadmin');
                                    if ($admin_node && $admin_node->has_children()) {
                                        foreach ($admin_node->find_all_of_type(navigation_node::TYPE_CONTAINER) as $settings_node) {
                                            if ($settings_node->text == get_string('reports')) {
                                                $_node = $settings_node;
                                                break;
                                            }
                                        }
                                    }
                                }
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
                                } elseif (isset($this->config->links[$linkIndex])) { //weird bug #0000166 
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
                                    if ($good instanceof navigation_node) {
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
                                            if ($item->text == get_string($key, $this->blockname)) {
                                                $s = $item;
                                                break;
                                            }
                                        }
                                    }
                                    if ($s instanceof navigation_node) {
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
        $this->content->text = '<style>.block_navigation .block_tree .tree_item.hasicon{white-space:normal;}</style>'.$output;

        return $this->content;
    }

    public function find_expandable(& $navigation, array &$expandable)
    {
        foreach ($navigation->children as &$child) {
            if ($child->display && $child->has_children() && $child->children->count() == 0) {
                $child->id = 'cm_expandable_branch_' . (count($expandable) + 1);
                $child->skip_record_events = true;
                $navigation->add_class('canexpand');
                $expandable[] = array('id' => $child->id, 'key' => $child->key, 'type' => $child->type);
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

        $elements []= $this->create_element('showallsections', $this->get_name('showallsections'), '', $CFG->wwwroot . '/blocks/course_menu/icons/viewall.gif', 1, 0);

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
            if (!empty($CFG->block_course_menu_global_config)) {
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
        /* check instance config values that are missing and add them from global config */
        if (empty($this->config->trimlength) && !empty($CFG->block_course_menu_trimlength)) {
            $this->config->trimlength = $CFG->block_course_menu_trimlength;
        }
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
                if (in_array($elementId, array('sitepages', 'mycourses'/*, 'myprofile'*/))) {
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

            $context = context_course::instance($this->course->id);

            $genericName = get_string("sectionname", 'format_' . $this->course->format);

            $modinfo = get_fast_modinfo($this->page->course);
            $mods = $modinfo->get_cms();
            //keep backwards compatibillity with moodle 2.3
            if (!isset($this->course->numsections) && function_exists('course_get_format')) {
                $this->course = course_get_format($this->course)->get_course();
            }
            $allSections = $modinfo->get_section_info_all();
            $sections = array();
            if ($this->course->format != 'social' && $this->course->format != 'scorm') {
                foreach ($allSections as $k => $section) {

                    if (!isset($this->course->numsections) || $k <= $this->course->numsections) {
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

                            if ($section->uservisible) {
                                // resources
                                $newSec['resources'] = array();
                                $sectionmods = explode(",", $section->sequence);
                                foreach ($sectionmods as $modnumber) {
                                    if (empty($mods[$modnumber])) {
                                        continue;
                                    }
                                    $mod = $mods[$modnumber];
                                    if ($mod->uservisible) {
                                        $instancename = urldecode($mod->name);

                                        if (!empty($CFG->filterall)) {
                                            $instancename = filter_manager::instance()->filter_text($instancename, $context);
                                        }

                                        // don't do anything for labels
                                        if ($mod->has_view()) {
                                            // Normal activity

                                            if (!strlen(trim($instancename))) {
                                                $instancename = $mod->modfullname;
                                            }

                                            $url = isset($mod->url) ? /* >= Moodle 2.6 */ $mod->url : /* Moodle 2.3 - Moodle 2.6  */ $mod->get_url();

                                            $iconurl = $mod->get_icon_url();

                                            $resource = array(
                                                'name' => $instancename,
                                                'url' => $url ? $url->out() : '',
                                                'icon' => $iconurl ? $iconurl->out() : '',
                                            );

                                            $newSec['resources'][] = $resource;
                                        }
                                    }
                                }
                            }

                            $showsection = $section->uservisible ||
                                    ($section->visible && !$section->available && !empty($section->availableinfo));
                            //hide hidden sections from students if the course settings say that - bug #212
                            $coursecontext = context_course::instance($this->course->id);
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
        $icons[0]['val'] = '';
        $icons[1]['name'] = get_string('linkfileorsite', $this->blockname);
        $icons[1]['img'] = "{$CFG->wwwroot}/blocks/course_menu/icons/link.gif";
        $icons[1]['val'] = $icons[1]['img'];
        $icons[2]['name'] = get_string('displaydirectory', $this->blockname);
        $icons[2]['img'] = "{$CFG->wwwroot}/blocks/course_menu/icons/directory.gif";
        $icons[2]['val'] = $icons[2]['img'];

        $allmods = $DB->get_records("modules");
        foreach ($allmods as $mod) {
            $icon = array();
            $icon['name'] = get_string("modulename", $mod->name);
            $icon['img'] = (string) $OUTPUT->pix_url('icon', $mod->name);
            $icon['val'] = 'pix_' . $mod->name;

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

        $str_length = class_exists('core_text') ?
            /* >= Moodle 2.6 */ core_text::strlen($str) :
            /* Moodle 2.3 - Moodle 2.6 */ textlib::strlen($str);

        switch ($mode) {
            case self::TRIM_RIGHT :
                if ($str_length > ($length + 3)) {
                    return (class_exists('core_text') ?
                        /* >= Moodle 2.6 */ substr($str, 0, $length) :
                        /* Moodle 2.3 - Moodle 2.6 */ textlib::substr($str, 0, $length)) . '...';
                }
                break;
            case self::TRIM_LEFT :
                if ($str_length > ($length + 3)) {
                    return '...' . (class_exists('core_text') ?
                        /* >= Moodle 2.6 */ core_text::substr($str, $str_length - $length) :
                        /* Moodle 2.3 - Moodle 2.6 */ textlib::substr($str, $str_length - $length));
                }
                break;
            case self::TRIM_CENTER :
                if ($str_length > ($length + 3)) {
                    $trimlength = ceil($length / 2);
                    $start = class_exists('core_text') ?
                        /* >= Moodle 2.6 */ core_text::substr($str, 0, $trimlength) :
                        /* Moodle 2.3 - Moodle 2.6 */ textlib::substr($str, 0, $trimlength);
                    $end = class_exists('core_text') ?
                        /* >= Moodle 2.6 */ core_text::substr($str, $str_length - $trimlength) :
                        /* Moodle 2.3 - Moodle 2.6 */ textlib::substr($str, $str_length - $trimlength);
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
        $this->section_names = array();
        foreach ($sections as $section) {
            $this->section_names[] = $section['name'];
        }
        $this->check_redo_chaptering(count($sections));
        $sectionNames = array();
        ob_start();
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

        if (!$this->element_exists('showallsections')) {
            array_splice($this->config->elements, 1, 0, array($this->create_element('showallsections', $this->get_name('showallsections'), '', $CFG->wwwroot . '/blocks/course_menu/icons/viewall.gif', 1, 0)));
        }

        if ($this->page->course->id == SITEID) {
            $elements = array();
            $allowed = array('calendar', 'sitepages', 'myprofile', 'mycourses', 'myprofilesettings');
            foreach ($this->config->elements as $element) {
                if (in_array($element['id'], $allowed) || substr($element['id'], 0, 4) == 'link') {
                    $elements [] = $element;
                }
            }
            $this->config->elements = $elements;
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

        $chapterNames = optional_param_array('chapterNames', array(), PARAM_RAW_TRIMMED);
        $childrenElementsNo = optional_param_array('chapterChildElementsNumber', array(), PARAM_INT);
        $chapterCounts = optional_param_array('chapterCounts', array(), PARAM_INT);
        $childElementTypes = optional_param_array('childElementTypes', array(), PARAM_ALPHANUMEXT);
        $childElementCounts = optional_param_array('childElementCounts', array(), PARAM_INT);
        $childElementNames = optional_param_array('childElementNames', array(), PARAM_RAW_TRIMMED);

        if ($this->page->course->id != SITEID) { //save chapters
            foreach ($chapterNames as $k => $name) {
                $chapter = array();
                $chapter['name'] = $name;
                $chapter['childElements'] = array();

                for ($i = $lastIndex; $i < $lastIndex + $childrenElementsNo[$k]; $i++) {
                    $child = array();
                    if ($data->chapEnable == 0) { //only one subchapter
                        $child['type'] = "subchapter";
                        $child['count'] = count($this->get_sections());
                        $child['name'] = get_string("subchapter", "block_course_menu") . " 1-1";
                    } elseif ($data->subChapEnable == 0) {
                        $child['type'] = "subchapter";
                        $xx = $k + 1;
                        $child['name'] = get_string("subchapter", "block_course_menu") . " {$xx}-1";
                        $child['count'] = $chapterCounts[$k];
                    } else {
                        $child['type'] = $childElementTypes[$i];
                        if ($child['type'] == "subchapter") {
                            $child['count'] = $childElementCounts[$i];
                            $total += $child['count'];
                            $child['name'] = $childElementNames[$i];
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
        $ids = optional_param_array('ids', array(), PARAM_RAW_TRIMMED);
        $urls = optional_param_array('urls', array(), PARAM_RAW_TRIMMED);
        $icons = optional_param_array('icons', array(), PARAM_RAW_TRIMMED);
        $canHides = optional_param_array('canHides', array(), PARAM_INT);
        $visibles = optional_param_array('visibles', array(), PARAM_INT);

        foreach ($ids as $k => $id) {
            if (empty($id)) {
                continue;
            }
            if (strpos($id, 'link') !== false) {
                $index = str_replace('link', '', $id);
                $name = optional_param('cm_link_name' . $index, '', PARAM_RAW_TRIMMED);
                if (!$name) {
                    $name = get_string('link', 'block_course_menu');
                }
            } else {
                $name = $this->get_name($id);
            }
            $data->elements[] = $this->create_element($id, $name, $urls[$k], $icons[$k], $canHides[$k], $visibles[$k]);
        }

        //links
        $linkCounter = optional_param_array('linkCounter', array(), PARAM_INT);
        $data->links = array();
        foreach ($linkCounter as $k => $notimportant) {
            $url = optional_param('cm_link_url' . $k, '', PARAM_RAW_TRIMMED);
            if (empty($url)) { //no empty urls
                continue;
            }
            $link = array();
            $link['name'] = optional_param('cm_link_name' . $k, '', PARAM_RAW_TRIMMED);
            $link['target'] = optional_param('cm_link_target' . $k, '', PARAM_RAW_TRIMMED);
            $link['icon'] = optional_param('cm_link_icon' . $k, '', PARAM_RAW_TRIMMED);
            // url
            $link['url'] = $url;
            if (!preg_match('/http(s)?:\/\//i', $link['url'])) {
                $link['url'] = 'http://' . $link['url'];
            }

            // checkbox configs
            foreach ($this->get_link_checkboxes() as $field) {
                $idx = "cm_link_{$field}{$k}";
                $link[$field] = optional_param($idx, '', PARAM_RAW_TRIMMED) ? 1 : 0;
            }
            // defaultwidth + defaultheight
            $link['defaultwidth'] = optional_param('cm_link_defaultwidth' . $k, 0, PARAM_INT);
            $link['defaultheight'] = optional_param('cm_link_defaultheight' . $k, 0, PARAM_INT);

            $data->links[] = $link;
        }

        parent::instance_config_save($data, $nolongerused);
    }

    public function has_config()
    {
        return true;
    }

    function output_global_config()
    {
        global $CFG, $THEME, $OUTPUT, $PAGE;
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

        if (!$this->element_exists('showallsections')) {
            array_splice($this->config->elements, 1, 0, array(
                $this->create_element('showallsections', $this->get_name('showallsections'), '', $CFG->wwwroot . '/blocks/course_menu/icons/viewall.gif', 1, 0)
            ));
        }

        // elements: set names
        foreach ($this->config->elements as $k => $element) {
            if (empty($element['name'])) {
                $this->config->elements[$k]['name'] = $this->get_name($element['id']);
            }
        }

        $PAGE->requires->yui_module(
                array('moodle-block_course_menu-settings'), 'M.block_course_menu_settings.global', array($this->get_settings_util_js(), $this->config), null, true);

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
        $removed = 0;
        foreach ($this->config->elements as $k => $element) {
            if ($element['id'] == 'coursemainpage') {
                array_splice($this->config->elements, $k - $removed, 1);
                $removed++;
            }
        }

        if ($removed > 0) {
            if (!empty($this->instance) && !empty($this->instance->id)) {
                $this->save_config_to_db();
            }
            return true;
        }

        return false;
    }

    public function get_settings_util_js()
    {
        global $OUTPUT;
        $util = array();
        foreach (array('chaptering', 'subchaptering', 'numberofchapter', 'numberofsubchapter', 'change', 'defaultgrouping', 'chapters',
    'chapter', 'subchapter', 'subchapters', 'wrongnumber', 'wrongsubchapnumber', 'warningchapnochange', 'warningsubchapnochange',
    'activatecustomlinks', 'numberoflinks', 'change', 'customlink', 'name', 'url', 'window', 'samewindow', 'newwindow',
    'icon', 'linkswrongnumber', 'customlink', 'correcturlmsg', 'cannotmoveright', 'emptychapname', 'emptysubchapname',
    'warningsubchapenable', 'keeppagenavigation', 'allowresize', 'allowscroll', 'showdirectorylinks', 'showlocationbar', 'showmenubar',
    'showtoolbar', 'showstatusbar', 'defaultwidth', 'defaultheight', 'linknoname', 'linknourl', 'cannotmovetopicup',
    'cannotmovetopicdown', 'sections') as $key) {
            $util['str'][$key] = get_string($key, 'block_course_menu');
        }

        $util['img']['hide'] = (string) $OUTPUT->pix_url('i/hide');
        $util['img']['show'] = (string) $OUTPUT->pix_url('i/show');
        $util['img']['up'] = (string) $OUTPUT->pix_url('t/up');
        $util['img']['right'] = (string) $OUTPUT->pix_url('t/right');
        $util['img']['left'] = (string) $OUTPUT->pix_url('t/left');
        $util['img']['down'] = (string) $OUTPUT->pix_url('t/down');
        $util['img']['edit'] = (string) $OUTPUT->pix_url('i/edit');

        return $util;
    }

    public function get_config()
    {
        return $this->config;
    }

    public function get_section_names()
    {
        return $this->section_names;
    }

    public function get_link_checkboxes()
    {
        return array('keeppagenavigation', 'allowresize', 'allowscroll', 'showdirectorylinks',
            'showlocationbar', 'showmenubar', 'showtoolbar', 'showstatusbar');
    }

    public function is_site_level()
    {
        return ($this->page->course->id == SITEID);
    }

}
