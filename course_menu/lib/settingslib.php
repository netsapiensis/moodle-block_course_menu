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

class admin_setting_confightml extends admin_setting {

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
    public function __construct($name, $visiblename, $description, $defaultsetting, $block) {
        $this->_block = $block;
        $this->html = $this->_block->output_global_config();
        $name = 'block_course_menu_' . $name;
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    /**
     * Return the setting
     *
     * @return mixed returns config if successful else null
     */
    public function get_setting() {
        return unserialize($this->config_read($this->name));
    }

    public function write_setting($data) {
        
        if (!empty($_POST['s__' . $this->name])) {
            $data = new stdClass();
            $data->expandableTree = $_POST['expandableTree'];
            $data->linksEnable = $_POST['linksEnable'];
            $data->trimlength = $_POST['s__block_course_menu_trimlength'];
            // elements
            $data->elements = array();
            foreach ($_POST['ids'] as $k => $id) {
                $url     = $_POST['urls'][$k];
                $icon    = $_POST['icons'][$k];
                $canHide = $_POST['canHides'][$k];
                $visible = $_POST['visibles'][$k];
                $name    = $this->_block->get_name($id);
                $data->elements[] = $this->_block->create_element($id, $name, $url, $icon, $canHide, $visible);
            }

            //links
            $data->links = array();
            if (isset($_POST['linkNames'])) { // means: if instance config. we don't have links in global config
                foreach ($_POST['linkNames'] as $k => $name) {
                    $link = array();
                    $link['name']   = $name;
                    $link['target'] = $_POST['linkTargets'][$k];
                    $link['icon']   = $_POST['linkIcons'][$k];

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

            $data = serialize($data);
        }
        
        return ($this->config_write($this->name, $data) ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Return an XHTML string for the setting
     * @return string Returns an XHTML string
     */
    public function output_html($data, $query = '') {

        $default = $this->get_defaultsetting();
        $current = $this->get_setting();
        return $this->html;
    }
}
?>