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

require_once '../../config.php';

require_login();

$courseid   = optional_param('courseid', 0, PARAM_INT);
$name       = optional_param('name', '', PARAM_RAW);
$url        = optional_param('url', '', PARAM_RAW);
$frameset   = optional_param('frameset', 0, PARAM_RAW);
$urls = new moodle_url('/blocks/course_menu/link_with_navigation.php', array('courseid' => $courseid, 'name' => $name, 'url' => $url, 'frameset' => $frameset));
$context = context_course::instance($courseid);
$PAGE->set_url($urls);

if (empty($frameset)) { ?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
	
	<html>
	<head>
	
	<meta content="text/html; charset=utf-8" http-equiv="content-type" />
	<title><?php echo $name; ?></title>
	</head>
	
	<frameset rows="130,*">
		<frame title="Resource" src="link_with_navigation.php?courseid=<?php echo $courseid; ?>&name=<?php echo $name; ?>&frameset=top" />
		<frame title="Resource" src="<?php echo $url; ?>" />
	</frameset>
	</html>

<?php } elseif ($frameset == "top") { 
    
    $module = array(
        'name' => 'block_course_menu',
        'fullpath' => '/blocks/course_menu/js/link_with_navigation.js', 
        'requires' => array('dom')
    );
    $PAGE->requires->js_init_call('M.block_course_menu_fix_links.init', array(), true, $module);

	$course = $DB->get_record('course', array('id' => $courseid));
    $PAGE->set_course($course);
    $PAGE->set_title($name);
    $PAGE->navbar->add($name);
    $navlinks = array();
    
    $navlinks[0] = array();
    $navlinks[0]['type'] = '';
	$navlinks[0]['link'] = $CFG->wwwroot.'/course/view.php?id='.$course->id;
	$navlinks[0]['name'] = $course->shortname;
    $navlinks[0]['target'] = '_top';
	
    $navlinks[1] = array();
    $navlinks[1]['type'] = '';
	$navlinks[1]['link'] = '';
	$navlinks[1]['name'] = $name;
    $navlinks[1]['target'] = '_top';

    echo $OUTPUT->heading($course->fullname);
	
	echo $OUTPUT->header($navlinks);
    ?>
<script type="text/javascript">
    Y.on('domready', function() {
        Y.all('.breadcrumb a').each( function() {
            this.set('target', '_top');
        } );
    });
</script>
<?php } ?>