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

class block_cm_admin_setting_confightml extends admin_setting
{

    public $html;
    private $_block;

    /**
     * Config text constructor
     *
     * @param string $name unique ascii name, either 'mysetting' for settings that in config, or 'myplugin/mysetting' for ones in config_plugins.
     * @param string $visiblename localised
     * @param string $description long localised info
     * @param string $defaultsetting
     * @param block_course_menu $block object
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $block)
    {
        $this->_block = $block;
        $name = 'block_course_menu_' . $name;
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    /**
     * Return the setting
     *
     * @return mixed returns config if successful else null
     */
    public function get_setting()
    {
        $_data = $this->config_read($this->name);
        if (is_null($_data)) {
            return null;
        }
        return unserialize($this->config_read($this->name));
    }

    public function write_setting($data)
    {
        $valid = optional_param('s__' . $this->name, '', PARAM_RAW_TRIMMED);
        if (!empty($valid)) {
            $data = new stdClass();
            $data->expandableTree = optional_param('expandableTree', 0, PARAM_INT);
            $data->linksEnable = optional_param('linksEnable', 0, PARAM_INT);
            $trimLength = optional_param('s__block_course_menu_trimlength', null, PARAM_INT);
            if ($trimLength !== null) {
                $data->trimlength = $trimLength;
            }

            // elements
            $data->elements = array();
            $ids = optional_param_array('ids', array(), PARAM_RAW_TRIMMED);
            $urls = optional_param_array('urls', array(), PARAM_RAW_TRIMMED);
            $icons = optional_param_array('icons', array(), PARAM_RAW_TRIMMED);
            $canHides = optional_param_array('canHides', array(), PARAM_INT);
            $visibles = optional_param_array('visibles', array(), PARAM_INT);
            
            foreach ($ids as $k => $id) {
                if (!$id) {
                    continue; //last $id will be empty
                }
                if (strpos($id, 'link') !== false) {
                    $index = str_replace('link', '', $id);
                    $name = optional_param('cm_link_name' . $index, '', PARAM_RAW_TRIMMED);
                    if (! $name) {
                        $name = get_string('link', 'block_course_menu');
                    }
                } else {
                    $name = $this->_block->get_name($id);
                }
                $data->elements[] = $this->_block->create_element($id, $name, $urls[$k], $icons[$k], $canHides[$k], $visibles[$k]);
            }

            //links
            $data->links = array();
            $linkCounter = optional_param_array('linkCounter', array(), PARAM_INT);
            foreach ($linkCounter as $k => $notimportant) {
                $url = optional_param('cm_link_url' . $k, '', PARAM_RAW_TRIMMED);
//                if (empty($url)) { //no empty urls
//                    continue;
//                }
                $link = array();
                $link['name']   = optional_param('cm_link_name' . $k, '', PARAM_RAW_TRIMMED);
                $link['target'] = optional_param('cm_link_target' . $k, '', PARAM_RAW_TRIMMED);
                $link['icon']   = optional_param('cm_link_icon' . $k, '', PARAM_RAW_TRIMMED);
                // url
                $link['url'] = $url;
                if (!preg_match('/http(s)?:\/\//i', $link['url'])) {
                    $link['url'] = 'http://' . $link['url'];
                }

                // checkbox configs
                foreach ($this->_block->get_link_checkboxes() as $field) {
                    $idx = "cm_link_{$field}{$k}";
                    $link[$field] = optional_param($idx, '', PARAM_RAW_TRIMMED) ? 1 : 0;
                }
                // defaultwidth + defaultheight
                $link['defaultwidth'] = optional_param('cm_link_defaultwidth' . $k, 0, PARAM_INT);
                $link['defaultheight'] = optional_param('cm_link_defaultheight' . $k, 0, PARAM_INT);

                $data->links[] = $link;
            }
            $data = serialize($data);
        }

        return ($this->config_write($this->name, $data) ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Return an XHTML string for the setting
     * @return string Returns an XHTML string
     */
    public function output_html($data, $query = '')
    {
        $default = $this->get_defaultsetting();
        $current = $this->get_setting();
        $this->html = $this->_block->output_global_config();
        return $this->html;
    }

}