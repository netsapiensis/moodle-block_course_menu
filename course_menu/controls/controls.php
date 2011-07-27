<?php
// this file is intended to make it easier for teachers to figure out the admin settings
global $USER, $CFG;
require_once("../../../config.php");
require_once("$CFG->dirroot/course/lib.php");

$action = optional_param('action', PARAM_RAW);

$id = required_param('id', PARAM_INT);  // course id

require_login();

if (! $course = get_record("course", "id", $id)) {
    error("Course ID was incorrect");
}
$links = array(0 => array (
                    'name' => $course->shortname,
                    'type' => "linkitem",
                    'link' => "$CFG->wwwroot/course/view.php?id=$course->id"
        ),
        1 => array('name' => 'Control Panel'));
print_header("$course->fullname", "$course->fullname", build_navigation($links));

if (isteacher($course->id)) {
    $tabs = array();
    $tabrows = array();

    optional_variable($action, 'mcp');

    $tabrows[] = new tabobject('mcp', "?action=mcp&amp;id=$course->id", get_string('main_control_panel', 'block_course_menu'));
    $tabrows[] = new tabobject('um', "?action=um&amp;id=$course->id", get_string('user_management', 'block_course_menu'));
    $tabrows[] = new tabobject('br', "?action=br&amp;id=$course->id", get_string('backup_restore', 'block_course_menu'));
    $tabrows[] = new tabobject('oc', "?action=oc&amp;id=$course->id", get_string('other_controls', 'block_course_menu'));
    $tabs[] = $tabrows;

    print_tabs($tabs, $action);
    
    if ($action != 'sc') {
        print '<table border="1" cellpadding="4" cellspacing="3" align="center" class="controls" width="80%">';
    }
    switch($action) {
        case 'mcp':
            include('mcp.html');
            break;
        case 'um':
            include('um.html');
            break;
        case 'br':
            include('br.html');
            break;
        case 'oc':
            include('oc.html');
            break;
        case 'sc':
            include('studentcontrols.html');
            break;
    }

    if ($action != 'sc') {
        print '</table>';
    }
} else {
   // students & guests
   if (!isguest()){
       require("studentcontrols.html");
   }
   else { 
       print "Guests can't view this page";
   }
}

print_footer($course);

?>