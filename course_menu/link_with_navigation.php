<?php if (empty($_REQUEST['frameset'])) {?>

	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
	
	<html>
	<head>
	
	<meta content="text/html; charset=utf-8" http-equiv="content-type" />
	<title><?php echo $_REQUEST['name']; ?></title>
	</head>
	
	<frameset rows="130,*">
		<frame title="Resource" src="link_with_navigation.php?courseid=<?php echo $_REQUEST['courseid']; ?>&name=<?php echo $_REQUEST['name']; ?>&frameset=top" />
		<frame title="Resource" src="<?php echo $_REQUEST['url']; ?>" />
	</frameset>
	</html>

<?php } elseif ($_REQUEST['frameset'] == "top") { ?>

	<?php
	// requiered stuff
	global $CFG, $THEME, $USER;
    require_once("../../config.php");
    
    $course = get_record('course', 'id', $_REQUEST['courseid']);
    
    $navlinks = array();
    
    $navlinks[0] = array();
    $navlinks[0]['type'] = '';
	$navlinks[0]['link'] = $CFG->wwwroot.'/course/view.php?id='.$course->id;
	$navlinks[0]['name'] = $course->shortname;
	
    $navlinks[1] = array();
    $navlinks[1]['type'] = '';
	$navlinks[1]['link'] = '';
	$navlinks[1]['name'] = $_REQUEST['name'];
	
	$navigation = build_navigation($navlinks);
	print_header($_REQUEST['name'], $course->fullname, $navigation);
	print_footer('empty');
	?>

<?php } ?>