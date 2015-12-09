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

class block_course_menu_renderer extends plugin_renderer_base
{

    private $topic_depth = 1;
    private $chapter_depth = 2;
    private $subchater_depth = 3;
    public $session;
    private $displaysection = 1000;

    public function render_chapter_tree($instance, $config, $chapters, $sections, $displaysection, $hr = false)
    {
        $this->displaysection = $displaysection;
        $this->session = $_SESSION['cm_tree'][$instance]['expanded_elements'];
        if ($config->chapEnable) {
            $this->topic_depth++;
            if ($config->subChapEnable) {
                $this->topic_depth++;
            }
        }
        if ($config->expandableTree) {
            $this->topic_depth++;
        }
        $sectionIndex = 0;
        $contents = '';
        foreach ($chapters as $chapter) {
            $subchapter = '';
            foreach ($chapter['childElements'] as $child) {
                $topic = '';
                $cl = "";
                if ($child['type'] == 'subchapter') {
                    for ($i = 0; $i < $child['count']; $i++) {
                        $topic .= $this->render_topic($config, $sections[$sectionIndex], 0, $displaysection == $sections[$sectionIndex]['id']);
                        $sectionIndex++;
                    }
                    if ($config->subChapEnable) {
                        $title = html_writer::tag('span', $child['name'], array('class' => 'item_name'));
                        $p = html_writer::tag('p', $title, array('class' => 'cm_tree_item tree_item branch'));
                        $topic = html_writer::tag('ul', $topic);
                        $collapsed = "collapsed";
                        if ($child['expanded']) {
                            $collapsed = "";
                        }
                        $topic = html_writer::tag('li', $p . $topic, array('class' => "type_structure depth_{$this->subchater_depth} {$collapsed} contains_branch"));
                    }
                } else { //topic
                    $d = $this->topic_depth;
                    if ($config->subChapEnable) {
                        $d--;
                    }
                    $topic = $this->render_topic($config, $sections[$sectionIndex], $d, $displaysection == $sections[$sectionIndex]['id']);
                    $sectionIndex++;
                }
                $subchapter .= $topic;
            }
            //$subchapter - a collection of <li> elements
            if ($config->chapEnable) {
                $subchapter = html_writer::tag('ul', $subchapter);
                $title = html_writer::tag('span', $chapter['name'], array('class' => 'item_name'));
                $p = html_writer::tag('p', $title, array('class' => 'cm_tree_item tree_item branch'));
                $collapsed = "collapsed";
                if ($chapter['expanded']) {
                    $collapsed = "";
                }
                $contents .= html_writer::tag('li', $p . $subchapter, array('class' => "type_structure depth_{$this->chapter_depth} {$collapsed} contains_branch"));
            } else {
                $contents .= $subchapter;
            }
        }
        if ($hr) {
            $contents = html_writer::tag('li', html_writer::empty_tag('hr')) . $contents;
        }
        return $contents;
    }

    public function render_topic($config, $section, $depth = 0, $current = false)
    {
        if ($depth == 0) {
            $depth = $this->topic_depth;
        }

        global $OUTPUT;
        $html = '';
        if ($config->expandableTree) {
            foreach ($section['resources'] as $resource) {
                $visible_title = $resource['trimmed_name'];
                $attributes = array('title' => $resource['name'], 'class' => '');
                if (!$section['visible'] || (!$section['uservisible'] || $section['availableinfo'])) {
                    $attributes['class'] .= 'dimmed_text';
                }
                $icon = $this->icon($resource['icon'], $resource['trimmed_name'], array('class' => 'smallicon navicon'));
                $html .= $this->render_leaf($visible_title, $icon, $attributes, $resource['url']);
            }
            $html = html_writer::tag('ul', $html);

            $attributes = array('class' => 'item_name section_link', 'id' => 'block-course-menu-section-' . $section['index']);
            if (!$section['visible'] || (!$section['uservisible'] || $section['availableinfo'])) {
                $attributes['class'] .= ' dimmed_text';
            }
            if ($current) {
                $attributes['class'] .= ' active_tree_node';
            }

            $title = html_writer::link($section['url'], $section['trimmed_name'], $attributes);
            $cl = '';
            if ($current) {
                $cl = "active_tree_node";
            }
            $p = html_writer::tag('p', $title, array('class' => 'cm_tree_item tree_item branch ' . $cl));
            $collapsed = "collapsed";
            if ($section['expanded']) {
                $collapsed = "";
            }
            $append = "";
            if ($current) {
                $append = "current_branch";
            }
            $html = html_writer::tag('li', $p . $html, array('class' => "type_structure contains_branch depth_{$depth} {$collapsed} {$append}"));
        } else {
            $attributes = array('class' => 'section_link', 'title' => $section['name'], 'id' => 'block-course-menu-section-' . $section['index']);
            if (!$section['visible'] || (!$section['uservisible'] || $section['availableinfo'])) {
                $attributes['class'] .= ' dimmed_text';
            }
            if ($current) {
                $attributes['class'] .= ' active_tree_node';
            }
            $leafIcon = $this->icon($OUTPUT->pix_url('i/navigationitem'), $section['trimmed_name'], array('class' => 'smallicon'));

            $html = $this->render_leaf($section['trimmed_name'], $leafIcon, $attributes, $section['url'], $current);
        }

        return $html;
    }

    public function render_leaf($visible_title, $icon, $attributes, $link, $current = false, $extraNode = '', $hr = false)
    {
        $html = html_writer::link($link, $icon . $visible_title . $extraNode, $attributes);
        $html = html_writer::tag('p', $html, array('class' => 'tree_item leaf hasicon'));
        $append = "";
        if ($current) {
            $append = "current_branch";
        }
        if ($hr) {
            $html = html_writer::empty_tag('hr') . $html;
        }
        $html = html_writer::tag('li', $html, array('class' => "type_custom item_with_icon {$append}"));
        return $html;
    }

    public function icon($src, $title, $props = array())
    {
        global $OUTPUT;
        if (strpos($src, 'pix_') === 0) {
            $modname = str_replace('pix_', '', $src);
            $src = $OUTPUT->pix_url('icon', $modname);
        }
        $p = "";
        foreach ($props as $p => $v) {
            $p .= '"' . $p . '=' . $v . '" ';
        }
        return '<img src="' . $src . '" 
				class="smallicon" title="' . $title . '"
				alt="' . $title . '" ' . $p . ' />';
    }

    public function render_link($link, $course, $hr = false)
    {
        global $CFG;

        $url = $link['url'];
        if ($link['keeppagenavigation']) {
            $url = $CFG->wwwroot . "/blocks/course_menu/link_with_navigation.php?courseid={$course}&url={$link['url']}&name={$link['name']}";
        }
        $icon = '';
        if ($link['icon']) {
            $icon = $this->icon($link['icon'], $link['name'], array('class' => 'smallicon navicon'));
        }
        $attr = array();
        if ($link['target']) { // open in new window
            $attr['onclick'] = <<<HTML
window.open('{$url}', '{$link['name']}', 'resizable={$link['allowresize']},scrollbars={$link['allowscroll']},directories={$link['showdirectorylinks']},location={$link['showlocationbar']},menubar={$link['showmenubar']},toolbar={$link['showtoolbar']},status={$link['showstatusbar']},width={$link['defaultwidth']},height={$link['defaultheight']}'); return false;
HTML;
        }
        return $this->render_leaf($link['name'], $icon, $attr, $url, false, '', $hr);
    }

    public function render_navigation_node(navigation_node $node, $expansionlimit, $hr = false)
    {
        $content = '';
        if ($node->has_children()) {
            $node->preceedwithhr = $hr;
            $content = $this->navigation_node(array($node), array('class' => ''), $expansionlimit);
            return $content;
        }
        return $content;
    }

    protected function navigation_node($items, $attrs = array(), $expansionlimit = null, $options = array(), $depth = 2)
    {
        // exit if empty, we don't want an empty ul element
        if (count($items) == 0) {
            return '';
        }

        // array of nested li elements
        $lis = array();
        foreach ($items as $item) {
            if (!$item->display && !$item->contains_active_node()) {
                continue;
            }
            
            $content = $item->get_content();
            $title = $item->get_title();
            
            $isexpandable = (empty($expansionlimit) || ($item->type > navigation_node::TYPE_ACTIVITY || $item->type < $expansionlimit) || ($item->contains_active_node() && $item->children->count() > 0));
            $isbranch = $isexpandable && ($item->children->count() > 0 || ($item->has_children() && (isloggedin() || $item->type <= navigation_node::TYPE_CATEGORY)));
            
            // Skip elements which have no content and no action - no point in showing them
            if (!$isexpandable && empty($item->action)) {
                continue;
            }
            
            $hasicon = ((!$isbranch || $item->type == navigation_node::TYPE_ACTIVITY || $item->type == navigation_node::TYPE_RESOURCE) && $item->icon instanceof renderable);
            
            $item->prev_opened = in_array(md5($content), $this->session);
            
            if ($hasicon) {
                $icon = $this->output->render($item->icon);
            } else {
                $icon = '';
            }
            $content = $icon.$content; // use CSS for spacing of icons
            if ($item->helpbutton !== null) {
                $content = trim($item->helpbutton) . html_writer::tag('span', $content, array('class' => 'clearhelpbutton'));
            }

            if ($content === '') {
                continue;
            }
            
            $attributes = array('class' => array());
            if ($title !== '') {
                $attributes['title'] = $title;
            }
            if ($item->hidden) {
                $attributes['class'] []= 'dimmed_text';
            }
            if (empty($item->skip_record_events)) {
                $attributes['class'] []= 'item_name';
            }
            $attributes['class'] = join(' ', $attributes['class']);
            if (is_string($item->action) || empty($item->action) || ($item->type === navigation_node::TYPE_CATEGORY && empty($options['linkcategories']))) {
                $attributes['tabindex'] = '0'; //add tab support to span but still maintain character stream sequence.
                $content = html_writer::tag('span', $content, $attributes);
            } else if ($item->action instanceof action_link) {
                //TODO: to be replaced with something else
                $link = $item->action;
                $link->text = $icon . ($link->text ? $link->text : $item->get_content());
                $link->attributes = array_merge($link->attributes, $attributes);
                $content = $this->output->render($link);
                $linkrendered = true;
            } else if ($item->action instanceof moodle_url) {
                $content = html_writer::link($item->action, $content, $attributes);
            }
            
            // this applies to the li item which contains all child lists too
            $liclasses = array($item->get_css_type(), 'depth_' . $depth);
            $liexpandable = array();
            if (!$item->prev_opened) {// && ($item->has_children() && (!$item->forceopen || $item->collapse))) {
                $liclasses[] = 'collapsed';
            }
            if ($isbranch) {
                $liclasses[] = 'contains_branch';
                $liexpandable = array('aria-expanded' => in_array('collapsed', $liclasses) ? "false" : "true");
            } else if ($hasicon) {
                $liclasses[] = 'item_with_icon';
            }
            if ($item->isactive === true) {
                $liclasses[] = 'current_branch';
            }
            $liattr = array('class' => join(' ', $liclasses)) + $liexpandable;
            // class attribute on the div item which only contains the item content
            $divclasses = array('cm_tree_item', 'tree_item');
            if ($isbranch) {
                $divclasses[] = 'branch';
            } else {
                $divclasses[] = 'leaf';
            }
            if ($hasicon) {
                $divclasses[] = 'hasicon';
            }
            if (!empty($item->classes) && count($item->classes) > 0) {
                $divclasses[] = join(' ', $item->classes);
            }
            $divattr = array('class' => join(' ', $divclasses));
            if (!empty($item->id)) {
                $divattr['id'] = $item->id;
            }
            $content = html_writer::tag('p', $content, $divattr);
            if ($isexpandable) {
                $content .= html_writer::tag('ul', $this->navigation_node($item->children, array(), $expansionlimit, $options, $depth + 1));
            }
            if (!empty($item->preceedwithhr) && $item->preceedwithhr === true) {
                $content = html_writer::empty_tag('hr') . $content;
            }
            $content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }

        if (count($lis)) {
            return implode("\n", $lis);
            //return html_writer::tag('ul', implode("\n", $lis));
        } else {
            return '';
        }
    }

}