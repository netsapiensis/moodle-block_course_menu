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

if (!isset ($chapters)) {
    error('Unauthorized');
}
include($CFG->dirroot . "/blocks/course_menu/js/course_menu.js.php");
$chapShow = $this->config->chapEnable ? 'i/hide' : 'i/show';
$subChapShow = $this->config->subChapEnable ? 'i/hide' : 'i/show';
?>
<?php if ($this->page->course->id == SITEID) : ?>
    <?php echo get_string('notapplicable', 'block_course_menu') ?>
<?php else : ?>
    <div class="showHideCont">
        <a class="showHide chapters minus" id="hide-1" href="#" onclick="Config_Block_Course_Menu.section_hide(this, 'div_chapters'); return false;">
            <?php echo get_string('hide', $this->blockname) ?>
        </a>
        <a class="showHide chapters plus" id="show-1" href="#" onclick="Config_Block_Course_Menu.section_show(this, 'div_chapters'); return false;" style="display: none">
            <?php echo get_string('show', $this->blockname) ?>
        </a>
    </div>
    <div class="clear"></div>
    <div id="div_chapters">
        <div id="chapEnableContainer">
            <div class="expandableTreeTd">
                <a href="" onclick="Config_Block_Course_Menu.C.changeEnableChap(); return false" class="enableDisable">
                    <img src="<?php echo $OUTPUT->pix_url($chapShow) ?>" border="0" id="img_config_chapEnable" alt="" />
                    <?php echo get_string('chaptering', $this->blockname) ?>
                </a>
            </div>
        </div>
        <div id="subChapEnableContainer" <?php if (!$this->config->chapEnable) echo 'style="display: none"' ?>>
            <div class="expandableTreeTd">
                <a href="" onclick="Config_Block_Course_Menu.C.callEnableSubChap(); return false" class="enableDisable">
                    <img src="<?php echo $OUTPUT->pix_url($subChapShow) ?>" border="0" id="img_config_subChapEnable" alt="" />
                    <?php echo get_string('subchaptering', $this->blockname) ?>
                </a>
            </div>
        </div>
        <div id="subChaptersContainer"></div>
        <div id="chaptersContainer" <?php if (!$this->config->chapEnable) echo 'style="display: none;"' ?>>
            <table border="0">
                <tbody>
                    <tr>
                        <td>
                            <br />
                            <?php echo get_string('numberofchapter', $this->blockname) ?>
                            <input style="width: 50px;" type="text" id="chaptersCount" value="<?php echo count($this->config->chapters) ?>" />
                            <input type="hidden" name="config_chaptersCount" id="id_config_chaptersCount" value="<?php echo count($this->config->chapters) ?>" />
                            <button type="submit" onclick="Config_Block_Course_Menu.C.changeChapNo(); return false;">
                                <?php echo get_string('change', $this->blockname) ?>
                            </button>
                            <br /><br />
                            <button type="submit" onclick="Config_Block_Course_Menu.C.defaultGroupingClicked(); return false;">
                                <?php echo get_string('defaultgrouping', $this->blockname) ?>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td id="subChaptersNumber" <?php if (!$this->config->subChapEnable) echo 'style="display: none"' ?>>
                            <br />
                            <?php echo get_string('numberofsubchapter', $this->blockname) ?>
                            <input type="text" name="" id="subChaptersCount"
                                value="<?php echo $this->config->subChaptersCount ?>"
                                onkeypress="return Config_Block_Course_Menu.C.doneEditingSubChapNo(event);" />
                        </td>
                    </tr>
                    <tr>
                        <td id="chaptersTableContainer" align="center"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<script type="text/javascript">
    Config_Block_Course_Menu.C = new function() {
        var config          	= <?php echo json_encode($this->config) ?>;
        var chapters        	= <?php echo json_encode($this->config->chapters) ?>;
        var sectionNames    	= <?php echo json_encode($sectionNames) ?>;

        var chapterBg 			= "#ccc";
        var subChapterBg 		= "#FF88FF";
        var topicBg				= "yellow";

        var oldChapNoForBlur        = <?php echo count($this->config->chapters) ?>;
        var restoreSubChapNoOnBlur  = true;
        var oldSubChapNoForBlur     = <?php echo $this->config->subChaptersCount ?>;
        
        var chapNo;

        return {
            init: function () {
                drawChapTable(Config_Block_Course_Menu.$("chaptersTableContainer"));
            },
            changeEnableChap: function() {
                var enabInput = Config_Block_Course_Menu.$("id_config_chapEnable");
                var img = Config_Block_Course_Menu.$("img_config_chapEnable");
                if (enabInput.value == 1) {
                    Config_Block_Course_Menu.$('chaptersContainer').style.display = "none";
                    enabInput.value = 0;
                    img.src = Config_Block_Course_Menu.otherInfo.imgShow;
                    Config_Block_Course_Menu.$('subChapEnableContainer').style.display = "none";
                    config.chapEnable = 0;
                } else {
                    Config_Block_Course_Menu.$('chaptersContainer').style.display = "block";
                    enabInput.value = 1;
                    config.chapEnable = 1;
                    img.src = Config_Block_Course_Menu.otherInfo.imgHide;
                    Config_Block_Course_Menu.$('subChapEnableContainer').style.display = "block";
                }
            },
            // --- subChapters enable -------------------------------------------------------------------------//
            callEnableSubChap: function() {
                if (config.subChapEnable == 0) {
                    if (confirm(Config_Block_Course_Menu.otherInfo.txt.warningsubchapenable)) {
                        config.subChaptersCount = chapters.length;
                        Config_Block_Course_Menu.$('subChaptersCount').value = config.subChaptersCount;
                        changeEnableSubChap();
                    }
                } else {
                    changeEnableSubChap();
                }
            },
            changeChapNo: function() {
                var newValue = parseInt(Config_Block_Course_Menu.$("chaptersCount").value);
                if 	((!Config_Block_Course_Menu.IsNumeric(newValue)) || (newValue < 1) || (newValue > sectionNames.length) || (config.subChapEnable == 1 && newValue > config.subChaptersCount)) {
                    alert(Config_Block_Course_Menu.otherInfo.txt.wrongnumber);
                    Config_Block_Course_Menu.$("chaptersCount").value = oldChapNoForBlur;
                } else {
                    if (confirm(Config_Block_Course_Menu.otherInfo.txt.warningchapnochange)) {
                        defaultChaptering(newValue, 1);
                        drawChapTable(Config_Block_Course_Menu.$("chaptersTableContainer"));
                        oldChapNoForBlur = newValue;
                        Config_Block_Course_Menu.$("id_config_chaptersCount").value = newValue;
                    }
                }
            },
            defaultGroupingClicked: function() {
                chapNo = Config_Block_Course_Menu.$("chaptersCount").value;
                defaultChaptering(chapNo, 0);
                drawChapTable(Config_Block_Course_Menu.$("chaptersTableContainer"));
                return false;
            },
            doneEditingSubChapNo: function( event ) {
                var keynum = event.keyCode;

                if (keynum == 13) {
                    changeSubChapNo(false);
                    return false;
                }

                return true;
            }
        }

        function changeEnableSubChap()
        {
            var enabInput = Config_Block_Course_Menu.$("id_config_subChapEnable");
            var img = Config_Block_Course_Menu.$("img_config_subChapEnable");
            if (enabInput.value == 1) {
                enabInput.value = 0;
                Config_Block_Course_Menu.$('subChaptersNumber').style.display = "none";
                img.src = Config_Block_Course_Menu.otherInfo.imgShow;
                config.subChapEnable = 0;
            } else {
                Config_Block_Course_Menu.$('subChaptersNumber').style.display = "";
                enabInput.value = 1;
                img.src = Config_Block_Course_Menu.otherInfo.imgHide;
                config.subChapEnable = 1;
            }
            resetSubchapterGroupings();
            drawChapTable(Config_Block_Course_Menu.$("chaptersTableContainer"));
        }
        
        // --- chapters ------------------------------------------------------------------------------------ //

        // function ----------------------------------------
        function drawChapTable(parent)
        {
            // clear parent
            while (parent.hasChildNodes()) {
                parent.removeChild(parent.firstChild);
            }

            var tr, td;

            br = document.createElement('br');
            parent.appendChild(br);

            chapTable = document.createElement('table');
            chapTable.cellpadding = 9;
            chapTable.cellspacing = 0;
            chapTable.align = "center";
            parent.appendChild(chapTable);

            chapBody = document.createElement("tbody");
            chapTable.appendChild(chapBody);

            tr = document.createElement('tr');
            chapBody.appendChild(tr);

            td = document.createElement('td');
            td.align   = "center";
            td.colSpan = 2;
            td.width   = 200;
            tr.appendChild(td);

            txt = document.createTextNode(Config_Block_Course_Menu.otherInfo.txt.chapters);
            td.appendChild(txt);

            if (config.subChapEnable == 1) {
                td = document.createElement('td');
                td.align	= "center";
                td.colSpan 	= 3;
                td.width	= 200;
                tr.appendChild(td);

                txt = document.createTextNode(Config_Block_Course_Menu.otherInfo.txt.subchapters);
                td.appendChild(txt);
            }

            td = document.createElement('td');
            td.align   = "center";
            td.colSpan = 2;
            td.width   = 200;
            tr.appendChild(td);

            txt = document.createTextNode(Config_Block_Course_Menu.otherInfo.courseFormat);
            td.appendChild(txt);

            sections = 0;
            for (i = 0; i < chapters.length; i++) {
                tr = document.createElement('tr');
                chapBody.appendChild(tr);

                // add edit image
                td = document.createElement('td');
                td.width = 20;
                td.align = "left";
                td.style.backgroundColor = chapterBg;
                tr.appendChild(td);

                a         = document.createElement('a');
                a.href    = "";
                a.onclick = function() {
                    var tr = this.parentNode.parentNode;
                    editChapName(tr);
                    return false;
                };
                td.appendChild(a);

                img = document.createElement('img');
                img.src = Config_Block_Course_Menu.otherInfo.imgEdit;
                a.appendChild(img);

                // add chapter name
                td = document.createElement('td');
                td.align = "left";
                td.style.backgroundColor = chapterBg;
                tr.appendChild(td);

                txt = document.createTextNode(chapters[i].name);
                td.appendChild(txt);

                input = document.createElement('input');
                input.type  = "hidden";
                input.name  = "chapterNames[]";
                input.value = chapters[i].name;
                td.appendChild(input);

                //de asta nu o sa mai fie nevoie
                input = document.createElement('input');
                input.type  = "hidden";
                input.name  = "chapterCounts[]";
                //only needed when we don't have subchapters
                if (config.subChapEnable == 0 || config.chapEnable == 0) {
                    input.value = chapters[i].childElements[0].count;
                }
                td.appendChild(input);


                input = document.createElement('input');
                input.type  = "hidden";
                input.name  = "chapterChildElementsNumber[]";
                input.value = chapters[i].childElements.length;
                td.appendChild(input);


                // se va verifica din php - daca subchaptersEnable == 0 => ce mama dracu' rezulta ?
                if (config.subChapEnable == 1) {
                    //create 3 empty tds
                    td = document.createElement('td');
                    td.style.backgroundColor = chapterBg;
                    tr.appendChild(td);
                    td = document.createElement('td');
                    td.style.backgroundColor = chapterBg;
                    tr.appendChild(td);
                    td = document.createElement('td');
                    td.style.backgroundColor = chapterBg;
                    tr.appendChild(td);
                }


                // add 2 empty td-s
                td = document.createElement('td');
                td.style.backgroundColor = chapterBg;
                tr.appendChild(td);
                td = document.createElement('td');
                td.style.backgroundColor = chapterBg;
                tr.appendChild(td);

                // mai vedem
                for (k = 0; k < chapters[i].childElements.length; k++) {

                    if (config.subChapEnable == 1) {

                        if (chapters[i].childElements[k].type == "topic") {
                            clr = topicBg;
                        } else {
                            clr = subChapterBg;
                        }

                        tr = document.createElement('tr');
                        chapBody.appendChild(tr);

                        // add 2 empty td-s
                        td = document.createElement('td');
                        tr.appendChild(td);
                        td = document.createElement('td');
                        tr.appendChild(td);

                        // add move image
                        td = document.createElement('td');
                        td.style.backgroundColor = clr;
                        td.width = 16;
                        if (chapters[i].childElements[k].type == "topic" &&
                                ((nextElement.type == "subchapter") || (previousElement.type == "subchapter")))
                            td.width = 32;
                        td.align = "center";
                        tr.appendChild(td);
                        if (i > 0 && k == 0 && chapters[i].childElements.length > 1) {

                            a         = document.createElement('a');
                            a.href    = "";
                            a.onclick = function() {
                                var tr = this.parentNode.parentNode;
                                moveSubChapter(tr, "up");
                                return false;
                            };
                            td.appendChild(a);
                            img = document.createElement('img');
                            img.src = Config_Block_Course_Menu.otherInfo.imgUp;
                            a.appendChild(img);

                        } else if (i != chapters.length - 1 && k == chapters[i].childElements.length - 1 && chapters[i].childElements.length > 1) {
                            a         = document.createElement('a');
                            a.href    = "";
                            a.onclick = function() {
                                var tr = this.parentNode.parentNode;
                                moveSubChapter(tr, "down");
                                return false;
                            };
                            td.appendChild(a);

                            img = document.createElement('img');
                            img.src = Config_Block_Course_Menu.otherInfo.imgDown;
                            a.appendChild(img);

                        }
                        nextElement = getNextChild(i, k);
                        previousElement = new Array();
                        previousElement = getPreviousChild(i, k);
                        if 	(
                                chapters[i].childElements[k].type == "topic" &&
                                ((nextElement.type == "subchapter") || (previousElement.type == "subchapter"))
                            )
                        {
                            a         = document.createElement('a');
                            a.href    = "";
                            if (previousElement.type == "subchapter") {
                                a.onclick = function() {
                                    var tr = this.parentNode.parentNode;
                                    moveTopic(tr, "right", "above");
                                    return false;
                                };
                            } else {
                                a.onclick = function() {
                                    var tr = this.parentNode.parentNode;
                                    moveTopic(tr, "right", "below");
                                    return false;
                                };
                            }
                            td.appendChild(a);
                            img = document.createElement('img');
                            img.src = Config_Block_Course_Menu.otherInfo.imgRight;
                            a.appendChild(img);
                        }

                        // add edit subchapter name
                        td = document.createElement('td');
                        td.style.backgroundColor = clr;
                        td.width = 16;
                        td.align = "left";
                        tr.appendChild(td);
                        if (chapters[i].childElements[k].type == "subchapter") {
                            a         = document.createElement('a');
                            a.href    = "";
                            a.onclick = function() {
                                var tr = this.parentNode.parentNode;
                                editSubChapName(tr);
                                return false;
                            };
                            td.appendChild(a);

                            img = document.createElement('img');
                            img.src = Config_Block_Course_Menu.otherInfo.imgEdit;
                            a.appendChild(img);
                        } else if (chapters[i].childElements[k].type == "topic") {
                            //do nothing, this will be empty for now
                        }

                        //add subchapter name column or topic name if type == "topic"
                        td = document.createElement('td');
                        td.style.backgroundColor = clr;
                        td.align = "left";
                        tr.appendChild(td);

                        if (chapters[i].childElements[k].type == "subchapter") {
                            txt = document.createTextNode(chapters[i].childElements[k].name);
                        } else if (chapters[i].childElements[k].type == "topic") {
                            //chapters[i].childElements[k].topicNumber ar trebui sa fie egal cu sections altfel am facut un mare kkt
                            txt = document.createTextNode(sectionNames[sections]);
                            sections++;
                        }
                        td.appendChild(txt);

                        //create inputs
                        input = document.createElement('input');
                        input.type  = "hidden";
                        input.name  = "childElementNames[]";
                        input.value = (chapters[i].childElements[k].type == "subchapter") ? chapters[i].childElements[k].name : "";
                        td.appendChild(input);

                        input = document.createElement('input');
                        input.type  = "hidden";
                        input.name  = "childElementCounts[]";
                        input.value = (chapters[i].childElements[k].type == "subchapter") ? chapters[i].childElements[k].count : "";
                        td.appendChild(input);

                        input = document.createElement('input');
                        input.type  = "hidden";
                        input.name  = "childElementTypes[]";
                        input.value = chapters[i].childElements[k].type;
                        td.appendChild(input);


                        // add 2 empty td-s
                        td = document.createElement('td');
                        td.style.backgroundColor = clr;
                        tr.appendChild(td);
                        td = document.createElement('td');
                        td.style.backgroundColor = clr;
                        tr.appendChild(td);
                    }

                    if (config.subChapEnable == 0 || chapters[i].childElements[k].type == "subchapter") {
                        for (j = 0; j < chapters[i].childElements[k].count; j++) {
                            tr = document.createElement('tr');
                            chapBody.appendChild(tr);

                            // add 2 empty td-s
                            td = document.createElement('td');
                            tr.appendChild(td);
                            td = document.createElement('td');
                            tr.appendChild(td);

                            //add another 3 empty tds if subchaptersEnable
                            if (config.subChapEnable == 1) {
                                td = document.createElement('td');
                                tr.appendChild(td);
                                td = document.createElement('td');
                                tr.appendChild(td);
                                td = document.createElement('td');
                                tr.appendChild(td);
                            }

                            // add move image
                            td = document.createElement('td');
                            td.style.backgroundColor = topicBg;
                            td.width = 16;
                            td.align = "left";
                            tr.appendChild(td);

                            if ((chapters[i].childElements[k].count > 1) && (j == 0) && ((i > 0) || (k > 0 && config.subChapEnable == 1))) {
                                if (config.subChapEnable == 1) {
                                    td.width = 32;
                                    a         = document.createElement('a');
                                    a.href    = "";
                                    a.onclick = function() {
                                        var tr = this.parentNode.parentNode;
                                        moveTopic(tr, "left", "above");
                                        return false;
                                    };
                                    td.appendChild(a);
                                    img = document.createElement('img');
                                    img.src = Config_Block_Course_Menu.otherInfo.imgLeft;
                                    a.appendChild(img);
                                }


                                a         = document.createElement('a');
                                a.href    = "";
                                a.onclick = function() {
                                    var tr = this.parentNode.parentNode;
                                    moveTopic(tr, "up");
                                    return false;
                                };
                                td.appendChild(a);

                                img = document.createElement('img');
                                img.src = Config_Block_Course_Menu.otherInfo.imgUp;
                                a.appendChild(img);
                            } else if ((chapters[i].childElements[k].count > 1) &&
                                (j == chapters[i].childElements[k].count - 1) && ((i < chapters.length - 1) || (k < chapters[i].childElements.length - 1 && config.subChapEnable == 1)))
                            {
                                if (config.subChapEnable == 1) {
                                    td.width = 32;

                                    a         = document.createElement('a');
                                    a.href    = "";
                                    a.onclick = function() {
                                        var tr = this.parentNode.parentNode;
                                        moveTopic(tr, "left", "below");
                                        return false;
                                    };
                                    td.appendChild(a);
                                    img = document.createElement('img');
                                    img.src = Config_Block_Course_Menu.otherInfo.imgLeft;
                                    a.appendChild(img);
                                }

                                a         = document.createElement('a');
                                a.href    = "";
                                a.onclick = function() {
                                    var tr = this.parentNode.parentNode;
                                    moveTopic(tr, "down");
                                    return false;
                                };
                                td.appendChild(a);

                                img = document.createElement('img');
                                img.src = Config_Block_Course_Menu.otherInfo.imgDown;
                                a.appendChild(img);
                            }

                            // add section name
                            td = document.createElement('td');
                            td.style.backgroundColor = topicBg;
                            td.align = "left";
                            tr.appendChild(td);

                            txt = document.createTextNode(sectionNames[sections]);
                            td.appendChild(txt);

                            sections++;
                        }
                    }
                }
            }
        }

        // function ----------------------------------------
        function editChapName(tr)
        {
            td  = tr.childNodes[1];
            txt = td.childNodes[0];

            input = document.createElement('input');
            input.type  = "text";
            input.value = txt.nodeValue;
            oldChapName = input.value;
            input.onblur = function () {
                var tr = this.parentNode.parentNode;
                saveChapName(tr);
            };
            input.onkeypress = function(e) {
                var keynum;

                if(window.event) { // IE
                    keynum = e.keyCode
                } else if(e.which) { // Netscape/Firefox/Opera
                    keynum = e.which
                }

                if (keynum == 13) {
                    var tr = this.parentNode.parentNode;
                    saveChapName(tr);
                }
            };

            td.insertBefore(input, txt);
            td.removeChild(txt);

            input.focus();
        }

        // function ----------------------------------------
        function saveChapName(tr)
        {
            td = tr.childNodes[1];
            input = td.childNodes[0];

            nameInput = td.childNodes[1];
            if (input.value == "") {
                alert(Config_Block_Course_Menu.otherInfo.txt.emptychapname);
                input.value = oldChapName;
            }
            nameInput.value = input.value;

            txt = document.createTextNode(input.value);

            td.insertBefore(txt, input);
            td.removeChild(input);

            n = -2;
            for (i = 0; i < chapBody.childNodes.length; i++) {
                if (chapBody.childNodes[i].childNodes[1].childNodes.length > 0) {
                    n++;
                }

                if (chapBody.childNodes[i] == tr) {
                    chapters[n].name = input.value;
                }
            }
        }


        //function ----------------------------------------
        function editSubChapName(tr)
        {
            td  = tr.childNodes[4];
            txt = td.childNodes[0];

            input = document.createElement('input');
            input.type  = "text";
            input.value = txt.nodeValue;
            oldSubChapName = input.value;
            input.onblur = function () {
                var tr = this.parentNode.parentNode;
                saveSubChapName(tr);
            };
            input.onkeypress = function(e) {
                var keynum;

                if(window.event) { // IE
                    keynum = e.keyCode
                } else if(e.which) { // Netscape/Firefox/Opera
                    keynum = e.which
                }

                if (keynum == 13) {
                    var tr = this.parentNode.parentNode;
                    saveSubChapName(tr);
                }
            };

            td.insertBefore(input, txt);
            td.removeChild(txt);

            input.focus();
        }

        //function ----------------------------------------
        function saveSubChapName(tr)
        {
            td = tr.childNodes[4];
            input = td.childNodes[0];

            nameInput = td.childNodes[1];
            if (input.value == "") {
                alert(Config_Block_Course_Menu.otherInfo.txt.emptysubchapname);
                input.value = oldSubChapName;
            }
            nameInput.value = input.value;

            txt = document.createTextNode(input.value);

            td.insertBefore(txt, input);
            td.removeChild(input);

            chapterIndex = -1;
            subChapterIndex = -1;
            for (i = 1; i < chapBody.childNodes.length; i++) {
                if (chapBody.childNodes[i].childNodes[1].childNodes.length > 0) {
                    chapterIndex++;
                    subChapterIndex = -1;
                }
                if (chapBody.childNodes[i].childNodes[4].childNodes.length > 0) {
                    subChapterIndex++;
                }

                if (chapBody.childNodes[i] == tr) {
                    chapters[chapterIndex].childElements[subChapterIndex].name = input.value;
                }
            }
        }


        // function ----------------------------------------
        function setSubChapNo()
        {
            if (restoreSubChapNoOnBlur) {
                oldSubChapNoForBlur = Config_Block_Course_Menu.$("subChaptersCount").value;
                Config_Block_Course_Menu.$("subChaptersCount").value = config.subChaptersCount;
            }
        }

        //function ----------------------------------------
        function changeSubChapNo(changeInput)
        {
            if (changeInput) {
                Config_Block_Course_Menu.$("subChaptersCount").value = oldSubChapNoForBlur;
            }

            var newValue = parseInt(Config_Block_Course_Menu.$("subChaptersCount").value);
            if ((!Config_Block_Course_Menu.IsNumeric(newValue)) || (newValue < chapters.length) || (newValue > sectionNames.length)) {
                alert(Config_Block_Course_Menu.otherInfo.txt.wrongsubchapnumber);
            } else {
                restoreSubChapNoOnBlur = false;
                if (confirm(Config_Block_Course_Menu.otherInfo.txt.warningsubchapnochange)) {
                    config.subChaptersCount = newValue;
                    Config_Block_Course_Menu.$("id_config_subChaptersCount").value = newValue;
                    defaultChaptering(chapters.length, 1);
                    drawChapTable(Config_Block_Course_Menu.$("chaptersTableContainer"));
                }
                restoreSubChapNoOnBlur = true;
            }

            setSubChapNo();
        }
        // function ----------------------------------------
        function moveSubChapter(tr, direction)
        {
            var upperTR 	= tr.previousSibling; //this should be TR with subchapter
            var mostUpperTR	= upperTR.previousSibling; //chapter or normal topic from another subchapter
            var evenMoreUpperTR = mostUpperTR.previousSibling;

            //TODO: nr de topicuri se schimba pt subcap mutat? sau ramane originalul?

            var chapterIndex = -1;
            var subChapterIndex = -1;
            for (i = 1; i < chapBody.childNodes.length; i++) {

                if (chapBody.childNodes[i].childNodes[1].childNodes.length > 0) { //edit icon for chapters
                    chapterIndex++;
                    subChapterIndex = -1;
                }

                if (chapBody.childNodes[i].childNodes[4].childNodes.length > 0) { //ori subchapter, ori topic pe ac nivel cu subchapter
                    subChapterIndex++;
                }

                if (chapBody.childNodes[i] == tr) {
                    if (direction == "up") {
                        chapters[chapterIndex - 1].childElements.splice(chapters[chapterIndex - 1].childElements.length, 0, chapters[chapterIndex].childElements[0]);
                        chapters[chapterIndex].childElements.shift();
                    } else {
                        chapters[chapterIndex + 1].childElements.splice(0, 0, chapters[chapterIndex].childElements[chapters[chapterIndex].childElements.length - 1]);
                        chapters[chapterIndex].childElements.splice(chapters[chapterIndex].childElements.length - 1, 1);
                    }
                }
            }
            drawChapTable(Config_Block_Course_Menu.$("chaptersTableContainer"));
        }

        // function ----------------------------------------
        function moveTopic(tr, direction, whereToInsert)
        {
            n = -2;

            var upperTR 	= tr.previousSibling; //this should be TR with subchapter
            var mostUpperTR	= upperTR.previousSibling; //chapter or normal topic from another subchapter
            var evenMoreUpperTR = mostUpperTR.previousSibling;

            var lowerTR		= tr.nextSibling;
            var mostLowerTR = lowerTR.nextSibling;

            var chapterIndex = -1;
            var subChapterIndex = -1;

            if (config.subChapEnable == 1) {

                if 	(
                        (direction == "up") &&
                        (
                            (mostUpperTR.childNodes[4].childNodes.length > 0 && mostUpperTR.childNodes[3].childNodes.length == 0) ||
                            (evenMoreUpperTR.childNodes[4].childNodes.length > 0 && evenMoreUpperTR.childNodes[3].childNodes.length == 0)
                        )
                    )
                {
                    alert(Config_Block_Course_Menu.otherInfo.txt.cannotmovetopicup);
                    return;
                }

                if 	(
                        (direction == "down") &&
                        (
                            (lowerTR.childNodes[4].childNodes.length > 0 && lowerTR.childNodes[3].childNodes.length == 0) ||
                            (mostLowerTR.childNodes[4].childNodes.length > 0 && mostLowerTR.childNodes[3].childNodes.length == 0)
                        )
                    )
                {
                    alert(Config_Block_Course_Menu.otherInfo.txt.cannotmovetopicdown);
                    return;
                }


                for (i = 1; i < chapBody.childNodes.length; i++) {

                    if (chapBody.childNodes[i].childNodes[1].childNodes.length > 0) { //edit icon for chapters
                        chapterIndex++;
                        subChapterIndex = -1;
                    }

                    if (chapBody.childNodes[i].childNodes[4].childNodes.length > 0) { //ori subchapter, ori topic
                        subChapterIndex++;
                    }

                    if (chapBody.childNodes[i] == tr) {
                        if (direction == "up") {
                            if (subChapterIndex == 0) {
                                chapters[chapterIndex - 1].childElements[chapters[chapterIndex - 1].childElements.length - 1].count++;
                                chapters[chapterIndex].childElements[subChapterIndex].count--;
                            } else {
                                chapters[chapterIndex].childElements[subChapterIndex - 1].count++;
                                chapters[chapterIndex].childElements[subChapterIndex].count--;
                            }
                        } else if (direction == "down") {
                            if (subChapterIndex == chapters[chapterIndex].childElements.length - 1) {
                                chapters[chapterIndex + 1].childElements[0].count++;
                                chapters[chapterIndex].childElements[subChapterIndex].count--;
                            } else {
                                chapters[chapterIndex].childElements[subChapterIndex + 1].count++;
                                chapters[chapterIndex].childElements[subChapterIndex].count--;
                            }
                        } else if (direction == "right") {
                            if (whereToInsert == "above") {
                                chapters[chapterIndex].childElements.splice(subChapterIndex, 1);
                                if (subChapterIndex == 0) {
                                    chapterIndex --;
                                    subChapterIndex = chapters[chapterIndex].childElements.length;
                                }
                                chapters[chapterIndex].childElements[subChapterIndex - 1].count++;
                            } else {
                                chapters[chapterIndex].childElements.splice(subChapterIndex, 1);
                                if (subChapterIndex == chapters[chapterIndex].childElements.length) {
                                    chapterIndex++;
                                    subChapterIndex = 0;
                                }
                                chapters[chapterIndex].childElements[subChapterIndex].count++;
                            }
                        } else if (direction == "left") {
                            child = new Object();
                            child.type = "topic";
                            if (whereToInsert == "above") {
                                chapters[chapterIndex].childElements.splice(subChapterIndex, 0, child);
                                chapters[chapterIndex].childElements[subChapterIndex + 1].count --;
                            } else {
                                chapters[chapterIndex].childElements.splice(subChapterIndex + 1, 0, child);
                                chapters[chapterIndex].childElements[subChapterIndex].count --;
                            }
                        }
                        break;
                    }
                }

            } else {

                for (i = 0; i < chapBody.childNodes.length; i++) {
                    if (chapBody.childNodes[i].childNodes[1].childNodes.length > 0) { // daca am ajuns la un <tr> in care e chapter
                        n++; // nr de capitole
                    }

                    if (chapBody.childNodes[i] == tr) {
                        if (direction == "up") {
                            chapters[n-1].childElements[0].count++;
                            chapters[n].childElements[0].count--;
                        } else {
                            chapters[n].childElements[0].count--;
                            chapters[n+1].childElements[0].count++;
                        }
                    }
                }
            }

            drawChapTable(Config_Block_Course_Menu.$("chaptersTableContainer"));
        }

        // function ----------------------------------------
        function defaultChaptering(chapNo, setNames)
        {
            if (setNames == 1) {
                chapters = new Array();
            }

            var c = Math.floor(sectionNames.length / chapNo);
            var r = sectionNames.length - c * chapNo;
            for (i = 0; i < chapNo; i++) {
                if (config.subChapEnable == 0 || setNames == 1) {
                    if (setNames == 1) {
                        chapters[i] = new Object();
                        chapters[i].name = Config_Block_Course_Menu.otherInfo.txt.chapter + (i+1);
                    }
                    chapters[i].childElements = new Array();
                    chapters[i].childElements[0] = new Object();
                    chapters[i].childElements[0].type = "subchapter";
                    if (i < r) {
                        chapters[i].childElements[0].count = c + 1;
                    } else {
                        chapters[i].childElements[0].count = c;
                    }
                }
            }
            resetSubchapterGroupings(setNames);
        }

        // function ----------------------------------------
        function removeArrayIdx(array, idx)
        {
            var newArray = new Array();
            var n = 0;
            for (i = 0; i < array.length; i++) {
                if (i != idx) {
                    newArray[n] = array[i];
                    n++;
                }
            }

            return newArray;
        }

        //functie pt regruparea topicurilor in subcapitole cand se face enable/disable la Subchaptergroupings
        function resetSubchapterGroupings(setNames)
        {
            if (config.subChapEnable == 0) {
                var sect = 0;
                for (i = 0; i < chapters.length; i++) {
                    oldVal = chapters[i].childElements;
                    chapters[i].childElements = new Array();
                    chapters[i].childElements[0] = new Object();
                    chapters[i].childElements[0].type = "subchapter";
                    sect = 0;
                    for (j = 0; j < oldVal.length; j++) {
                        if (oldVal[j].type == "topic") {
                            sect++;
                        } else if (oldVal[j].type == "subchapter") {
                            sect += parseInt(oldVal[j].count);
                        }
                    }
                    chapters[i].childElements[0].count = sect;
                }
            } else {
                var childElementNames = new Array();
                var index = 0;
                if (setNames == 0) {
                    for (i = 0; i < chapters.length; i++) {
                        for (j = 0; j < chapters[i].childElements.length; j++) {
                            if (chapters[i].childElements[j].type == "subchapter") {
                                childElementNames[index] = chapters[i].childElements[j].name;
                                index++;
                            }
                        }
                    }
                }
                var subChaptersPerChapter = Math.floor(config.subChaptersCount / chapters.length);
                var dif = config.subChaptersCount - (chapters.length * subChaptersPerChapter);
                var topicsPerSubchapter = Math.floor(sectionNames.length / config.subChaptersCount);
                var topicDif = sectionNames.length - (config.subChaptersCount * topicsPerSubchapter);
                var tracker = 0;
                var subChapterIndex = 0;
                var topicTracker = 0;
                var i = 0;
                index = 0;
                while (i < chapters.length) {
                    chapters[i].childElements = new Array();
                    for (j = 0; j < subChaptersPerChapter; j++) {
                        chapters[i].childElements[j] = new Object();
                        chapters[i].childElements[j].type = "subchapter";
                        chapters[i].childElements[j].name = (setNames == 0) ? childElementNames[index] : "Subchapter " + (i+1) + "-" + (j+1);
                        chapters[i].childElements[j].count = topicsPerSubchapter;
                        if (topicDif > topicTracker) {
                            chapters[i].childElements[j].count++;
                            topicTracker++;
                        }
                        index++;
                    }
                    if (dif > tracker) {
                        chapters[i].childElements[j] = new Object();
                        chapters[i].childElements[j].type = "subchapter";
                        chapters[i].childElements[j].name = (setNames == 0) ? childElementNames[index] : "Subchapter " + (i+1) + "-" + (j+1);
                        chapters[i].childElements[j].count = topicsPerSubchapter;
                        if (topicDif > topicTracker) {
                            chapters[i].childElements[j].count++;
                            topicTracker++;
                        }
                        index++;
                        tracker++;
                    }
                    i++;
                }
            }

        }

        function getNextChild(chapterIndex, subChapterIndex)
        {
            if (subChapterIndex == chapters[chapterIndex].childElements.length - 1) {
                if (chapterIndex == chapters.length - 1) {
                    obj = new Object();
                    obj.type = "___";
                    return obj;
                }
                return chapters[chapterIndex + 1].childElements[0];
            } else {
                return chapters[chapterIndex].childElements[subChapterIndex + 1];
            }
        }

        function getPreviousChild(chapterIndex, subChapterIndex)
        {
            if (subChapterIndex == 0) {
                if (chapterIndex == 0) {
                    obj = new Object();
                    obj.type = "___";
                    return obj;
                }
                return chapters[chapterIndex - 1].childElements[chapters[chapterIndex - 1].childElements.length - 1];
            } else {
                return chapters[chapterIndex].childElements[subChapterIndex - 1];
            }
        }
    }
    Config_Block_Course_Menu.addLoadEvent( function () {
        Config_Block_Course_Menu.C.init();
    } );
    </script>
<?php endif ?>