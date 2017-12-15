<script type="text/javascript">
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

var Config_Block_Course_Menu = ( typeof Config_Block_Course_Menu != 'undefined' ) ? Config_Block_Course_Menu : {
    $: function( elementid ) {
        return document.getElementById( elementid );
    },
    addLoadEvent: function( func ) {
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
    },
    section_hide: function( aElem, sectionId ) {
        this.$( sectionId ).style.display = 'none';
        var showId = "show-" + aElem.id.split("-")[1];
        aElem.style.display = 'none';
        this.$(showId).style.display = 'block';
        aElem.parentNode.style.cssFloat = "left";
        return false;
    },
    section_show: function( aElem, sectionId ) {
        this.$( sectionId ).style.display = 'block';
        var hideId = "hide-" + aElem.id.split("-")[1];
        aElem.style.display = 'none';
        this.$( hideId ).style.display = 'block';
        aElem.parentNode.style.cssFloat = "right";
        return false;
    },
    IsNumeric: function( strString ) {
        var strValidChars = "0123456789";
        var strChar;
        var blnResult = true;

        for (var i = 0; i < strString.length && blnResult == true; i++) {
            strChar = strString.charAt(i);
            if (strValidChars.indexOf(strChar) == -1) {
                blnResult = false;
            }
        }
        return blnResult;
    },
    otherInfo: {}
}

Config_Block_Course_Menu.otherInfo.imgHide = "<?php echo $CFG->wwwroot.'/pix/i/hide.gif'; ?>";
Config_Block_Course_Menu.otherInfo.imgShow = "<?php echo $CFG->wwwroot.'/pix/i/show.gif'; ?>";

Config_Block_Course_Menu.otherInfo.imgUp   = "<?php echo $CFG->wwwroot.'/pix/t/up.gif'; ?>";
Config_Block_Course_Menu.otherInfo.imgRight = "<?php echo $CFG->wwwroot.'/pix/t/right.gif'; ?>";
Config_Block_Course_Menu.otherInfo.imgLeft = "<?php echo $CFG->wwwroot.'/pix/t/left.gif'; ?>";
Config_Block_Course_Menu.otherInfo.imgDown = "<?php echo $CFG->wwwroot.'/pix/t/down.gif'; ?>";

Config_Block_Course_Menu.otherInfo.imgEdit = "<?php echo $CFG->wwwroot.'/pix/i/edit.gif'; ?>";
<?php if (!empty($this->course)) : ?>
    Config_Block_Course_Menu.otherInfo.courseFormat = "<?php echo $this->course->format == 'topics' ? get_string('topics', 'block_course_menu') : get_string('weeks', 'block_course_menu'); ?>"
<?php else : ?>
    Config_Block_Course_Menu.otherInfo.courseFormat = "";
<?php endif ?>

Config_Block_Course_Menu.otherInfo.txt = new Object();
Config_Block_Course_Menu.otherInfo.txt.chaptering          = "<?php print_string('chaptering', 'block_course_menu'); ?>";
//subchapters
Config_Block_Course_Menu.otherInfo.txt.subchaptering       = "<?php print_string('subchaptering', 'block_course_menu'); ?>";
//end subchapters
Config_Block_Course_Menu.otherInfo.txt.numberofchapter     = "<?php print_string('numberofchapter', 'block_course_menu'); ?>: ";
Config_Block_Course_Menu.otherInfo.txt.numberofsubchapter  = "<?php print_string('numberofsubchapter', 'block_course_menu'); ?>: ";
Config_Block_Course_Menu.otherInfo.txt.change              = "<?php print_string('change', 'block_course_menu'); ?>";
Config_Block_Course_Menu.otherInfo.txt.defaultgrouping     = "<?php print_string('defaultgrouping', 'block_course_menu'); ?>";
Config_Block_Course_Menu.otherInfo.txt.chapters            = "<?php print_string('chapters', 'block_course_menu'); ?>";
Config_Block_Course_Menu.otherInfo.txt.chapter             = "<?php print_string('chapter', 'block_course_menu') ?> ";
Config_Block_Course_Menu.otherInfo.txt.subchapter          = "<?php print_string('subchapter', 'block_course_menu') ?> ";
Config_Block_Course_Menu.otherInfo.txt.subchapters         = "<?php print_string('subchapters', 'block_course_menu') ?> ";
Config_Block_Course_Menu.otherInfo.txt.wrongnumber         = "<?php print_string('wrongnumber', 'block_course_menu'); ?>";
Config_Block_Course_Menu.otherInfo.txt.wrongsubchapnumber  = "<?php print_string('wrongsubchapnumber', 'block_course_menu'); ?>";
Config_Block_Course_Menu.otherInfo.txt.warningchapnochange = "<?php print_string('warningchapnochange', 'block_course_menu'); ?>";
Config_Block_Course_Menu.otherInfo.txt.warningsubchapnochange = "<?php print_string('warningsubchapnochange', 'block_course_menu'); ?>";
Config_Block_Course_Menu.otherInfo.txt.activatecustomlinks = "<?php print_string('activatecustomlinks', 'block_course_menu'); ?>";
Config_Block_Course_Menu.otherInfo.txt.numberoflinks       = "<?php print_string('numberoflinks', 'block_course_menu'); ?>: ";
Config_Block_Course_Menu.otherInfo.txt.change              = "<?php print_string('change', 'block_course_menu'); ?>";
Config_Block_Course_Menu.otherInfo.txt.customlink          = "<?php print_string('customlink', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.name                = "<?php print_string('name', 'block_course_menu'); ?>:";
Config_Block_Course_Menu.otherInfo.txt.url                 = "<?php print_string('url', 'block_course_menu'); ?>:";
Config_Block_Course_Menu.otherInfo.txt.window              = "<?php print_string('window', 'block_course_menu'); ?>:"
Config_Block_Course_Menu.otherInfo.txt.samewindow          = "<?php print_string('samewindow', 'block_course_menu'); ?>";
Config_Block_Course_Menu.otherInfo.txt.newwindow           = "<?php print_string('newwindow', 'block_course_menu'); ?>";
Config_Block_Course_Menu.otherInfo.txt.icon                = "<?php print_string('icon', 'block_course_menu'); ?>:";
Config_Block_Course_Menu.otherInfo.txt.linkswrongnumber    = "<?php print_string('linkswrongnumber', 'block_course_menu'); ?>";
Config_Block_Course_Menu.otherInfo.txt.customlink          = "<?php print_string('customlink', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.correcturlmsg       = "<?php print_string('correcturlmsg', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.cannotmoveright     = "<?php print_string('cannotmoveright', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.emptychapname       = "<?php print_string('emptychapname', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.emptysubchapname    = "<?php print_string('emptysubchapname', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.warningsubchapenable= "<?php print_string('warningsubchapenable', 'block_course_menu'); ?> ";

Config_Block_Course_Menu.otherInfo.txt.keeppagenavigation  = "<?php print_string('keeppagenavigation', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.allowresize         = "<?php print_string('allowresize', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.allowscroll         = "<?php print_string('allowscroll', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.showdirectorylinks  = "<?php print_string('showdirectorylinks', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.showlocationbar     = "<?php print_string('showlocationbar', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.showmenubar         = "<?php print_string('showmenubar', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.showtoolbar         = "<?php print_string('showtoolbar', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.showstatusbar       = "<?php print_string('showstatusbar', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.defaultwidth        = "<?php print_string('defaultwidth', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.defaultheight       = "<?php print_string('defaultheight', 'block_course_menu'); ?> ";

Config_Block_Course_Menu.otherInfo.txt.linknoname          = "<?php print_string('linknoname', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.linknourl           = "<?php print_string('linknourl', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.cannotmovetopicup   = "<?php print_string('cannotmovetopicup', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.cannotmovetopicdown = "<?php print_string('cannotmovetopicdown', 'block_course_menu'); ?> ";
Config_Block_Course_Menu.otherInfo.txt.expandableTree      = "<?php print_string('expandable_tree', 'block_course_menu') ?>";

</script>