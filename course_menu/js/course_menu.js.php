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

Config_Block_Course_Menu.otherInfo.imgHide = "<?php echo $OUTPUT->pix_url('i/hide'); ?>";
Config_Block_Course_Menu.otherInfo.imgShow = "<?php echo $OUTPUT->pix_url('i/show'); ?>";
Config_Block_Course_Menu.otherInfo.imgUp   = "<?php echo $OUTPUT->pix_url('t/up'); ?>";
Config_Block_Course_Menu.otherInfo.imgRight = "<?php echo $OUTPUT->pix_url('t/right'); ?>";
Config_Block_Course_Menu.otherInfo.imgLeft = "<?php echo $OUTPUT->pix_url('t/left'); ?>";
Config_Block_Course_Menu.otherInfo.imgDown = "<?php echo $OUTPUT->pix_url('t/down'); ?>";
Config_Block_Course_Menu.otherInfo.imgEdit = "<?php echo $OUTPUT->pix_url('i/edit'); ?>";

<?php if (!empty($this->course)) : ?>
    Config_Block_Course_Menu.otherInfo.courseFormat = "<?php echo $this->course->format == 'topics' ? get_string('topics', 'block_course_menu') : get_string('weeks', 'block_course_menu'); ?>"
<?php else : ?>
    Config_Block_Course_Menu.otherInfo.courseFormat = "";
<?php endif ?>
    
//lang strings
<?php 
$txt = new stdClass();
foreach (array('chaptering', 'subchaptering', 'numberofchapter', 'numberofsubchapter', 'change', 'defaultgrouping', 'chapters',
 'chapter', 'subchapter', 'subchapters', 'wrongnumber', 'wrongsubchapnumber', 'warningchapnochange', 'warningsubchapnochange',
 'activatecustomlinks', 'numberoflinks', 'change', 'customlink', 'name', 'url', 'window', 'samewindow', 'newwindow',
 'icon', 'linkswrongnumber', 'customlink', 'correcturlmsg', 'cannotmoveright', 'emptychapname', 'emptysubchapname',
 'warningsubchapenable', 'keeppagenavigation', 'allowresize', 'allowscroll', 'showdirectorylinks', 'showlocationbar', 'showmenubar',
 'showtoolbar', 'showstatusbar', 'defaultwidth', 'defaultheight', 'linknoname', 'linknourl', 'cannotmovetopicup',
 'cannotmovetopicdown') as $key) {
    $txt->{$key} = get_string($key, 'block_course_menu');
}
?>
Config_Block_Course_Menu.otherInfo.txt = <?php echo json_encode($txt) ?>;
</script>