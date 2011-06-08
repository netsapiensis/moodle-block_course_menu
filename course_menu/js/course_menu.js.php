<script type="text/javascript">
/*
 * ---------------------------------------------------------------------------------------------------------------------
 *
 * This file is part of the Course Menu block for Moodle
 *
 * The Course Menu block for Moodle software package is Copyright © 2008 onwards NetSapiensis AB and is provided under
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

var otherInfo = {};
otherInfo.imgHide = "<?php echo $CFG->wwwroot.'/pix/i/hide.gif'; ?>";
otherInfo.imgShow = "<?php echo $CFG->wwwroot.'/pix/i/show.gif'; ?>";

otherInfo.imgUp   = "<?php echo $CFG->wwwroot.'/pix/t/up.gif'; ?>";
otherInfo.imgRight = "<?php echo $CFG->wwwroot.'/pix/t/right.gif'; ?>";
otherInfo.imgLeft = "<?php echo $CFG->wwwroot.'/pix/t/left.gif'; ?>";
otherInfo.imgDown = "<?php echo $CFG->wwwroot.'/pix/t/down.gif'; ?>";

otherInfo.imgEdit = "<?php echo $CFG->wwwroot.'/pix/i/edit.gif'; ?>";
<?php if (!empty($this->course)) : ?>
    otherInfo.courseFormat = "<?php echo $this->course->format == 'topics' ? get_string('topics', 'block_course_menu') : get_string('weeks', 'block_course_menu'); ?>"
<?php else : ?>
    otherInfo.courseFormat = "";
<?php endif ?>
    
otherInfo.txt = new Object();
otherInfo.txt.chaptering          = "<?php print_string('chaptering', 'block_course_menu'); ?>";
//subchapters
otherInfo.txt.subchaptering       = "<?php print_string('subchaptering', 'block_course_menu'); ?>";
//end subchapters
otherInfo.txt.numberofchapter     = "<?php print_string('numberofchapter', 'block_course_menu'); ?>: ";
otherInfo.txt.numberofsubchapter     = "<?php print_string('numberofsubchapter', 'block_course_menu'); ?>: ";
otherInfo.txt.change              = "<?php print_string('change', 'block_course_menu'); ?>";
otherInfo.txt.defaultgrouping     = "<?php print_string('defaultgrouping', 'block_course_menu'); ?>";
otherInfo.txt.chapters            = "<?php print_string('chapters', 'block_course_menu'); ?>";
otherInfo.txt.chapter             = "<?php print_string('chapter', 'block_course_menu') ?> ";
otherInfo.txt.subchapter          = "<?php print_string('subchapter', 'block_course_menu') ?> ";
otherInfo.txt.subchapters         = "<?php print_string('subchapters', 'block_course_menu') ?> ";
otherInfo.txt.wrongnumber         = "<?php print_string('wrongnumber', 'block_course_menu'); ?>";
otherInfo.txt.wrongsubchapnumber  = "<?php print_string('wrongsubchapnumber', 'block_course_menu'); ?>";
otherInfo.txt.warningchapnochange = "<?php print_string('warningchapnochange', 'block_course_menu'); ?>";
otherInfo.txt.warningsubchapnochange = "<?php print_string('warningsubchapnochange', 'block_course_menu'); ?>";
otherInfo.txt.activatecustomlinks = "<?php print_string('activatecustomlinks', 'block_course_menu'); ?>";
otherInfo.txt.numberoflinks       = "<?php print_string('numberoflinks', 'block_course_menu'); ?>: ";
otherInfo.txt.change              = "<?php print_string('change', 'block_course_menu'); ?>";
otherInfo.txt.customlink          = "<?php print_string('customlink', 'block_course_menu'); ?> ";
otherInfo.txt.name                = "<?php print_string('name', 'block_course_menu'); ?>:";
otherInfo.txt.url                 = "<?php print_string('url', 'block_course_menu'); ?>:";
otherInfo.txt.window              = "<?php print_string('window', 'block_course_menu'); ?>:"
otherInfo.txt.samewindow          = "<?php print_string('samewindow', 'block_course_menu'); ?>";
otherInfo.txt.newwindow           = "<?php print_string('newwindow', 'block_course_menu'); ?>";
otherInfo.txt.icon                = "<?php print_string('icon', 'block_course_menu'); ?>:";
otherInfo.txt.linkswrongnumber    = "<?php print_string('linkswrongnumber', 'block_course_menu'); ?>";
otherInfo.txt.customlink          = "<?php print_string('customlink', 'block_course_menu'); ?> ";
otherInfo.txt.correcturlmsg       = "<?php print_string('correcturlmsg', 'block_course_menu'); ?> ";
otherInfo.txt.cannotmoveright     = "<?php print_string('cannotmoveright', 'block_course_menu'); ?> ";
otherInfo.txt.emptychapname     = "<?php print_string('emptychapname', 'block_course_menu'); ?> ";
otherInfo.txt.emptysubchapname     = "<?php print_string('emptysubchapname', 'block_course_menu'); ?> ";
otherInfo.txt.warningsubchapenable     = "<?php print_string('warningsubchapenable', 'block_course_menu'); ?> ";

otherInfo.txt.keeppagenavigation  = "<?php print_string('keeppagenavigation', 'block_course_menu'); ?> ";
otherInfo.txt.allowresize         = "<?php print_string('allowresize', 'block_course_menu'); ?> ";
otherInfo.txt.allowscroll         = "<?php print_string('allowscroll', 'block_course_menu'); ?> ";
otherInfo.txt.showdirectorylinks  = "<?php print_string('showdirectorylinks', 'block_course_menu'); ?> ";
otherInfo.txt.showlocationbar     = "<?php print_string('showlocationbar', 'block_course_menu'); ?> ";
otherInfo.txt.showmenubar         = "<?php print_string('showmenubar', 'block_course_menu'); ?> ";
otherInfo.txt.showtoolbar         = "<?php print_string('showtoolbar', 'block_course_menu'); ?> ";
otherInfo.txt.showstatusbar       = "<?php print_string('showstatusbar', 'block_course_menu'); ?> ";
otherInfo.txt.defaultwidth        = "<?php print_string('defaultwidth', 'block_course_menu'); ?> ";
otherInfo.txt.defaultheight       = "<?php print_string('defaultheight', 'block_course_menu'); ?> ";

otherInfo.txt.linknoname          = "<?php print_string('linknoname', 'block_course_menu'); ?> ";
otherInfo.txt.linknourl           = "<?php print_string('linknourl', 'block_course_menu'); ?> ";
otherInfo.txt.cannotmovetopicup   = "<?php print_string('cannotmovetopicup', 'block_course_menu'); ?> ";
otherInfo.txt.cannotmovetopicdown = "<?php print_string('cannotmovetopicdown', 'block_course_menu'); ?> ";
otherInfo.txt.expandableTree      = "<?php print_string('expandable_tree', 'block_course_menu') ?>";

function $(id) {
	return document.getElementById(id);
}
function addLoadEvent(func) {
    var oldonload = window.onload;
    if (typeof window.onload != 'function') {
        window.onload = func;
    } else {
        window.onload = function() {
            if (oldonload) {
                oldonload();
            }
            func();
        }
    }
}
function section_hide(aElem, sectionId) {
    $(sectionId).style.display = 'none';
    var showId = "show-" + aElem.id.split("-")[1];
    aElem.style.display = 'none';
    $(showId).style.display = 'block';
    aElem.parentNode.style.cssFloat = "left";
    return false;
}
function section_show(aElem, sectionId) {
    $(sectionId).style.display = 'block';
    var hideId = "hide-" + aElem.id.split("-")[1];
    aElem.style.display = 'none';
    $(hideId).style.display = 'block';
    aElem.parentNode.style.cssFloat = "right";
    return false;
}
// function ----------------------------------------
function IsNumeric(strString)
{
	var strValidChars = "0123456789";
	var strChar;
	var blnResult = true;

	for (i = 0; i < strString.length && blnResult == true; i++) {
		strChar = strString.charAt(i);
		if (strValidChars.indexOf(strChar) == -1) {
			blnResult = false;
		}
	}
	return blnResult;
}


</script>