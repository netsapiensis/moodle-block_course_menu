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

if (!isset ($this->config->elements)) {
    error('Unauthorized');
}
include($CFG->dirroot . "/blocks/course_menu/js/course_menu.js.php");
$chapShow = $this->config->chapEnable ? 'i/hide' : 'i/show';
$subChapShow = $this->config->subChapEnable ? 'i/hide' : 'i/show';
if ($this->page->course->id == SITEID) {
    $elements = array ();
    $allowed = array ('calendar', 'sitepages', 'myprofile', 'mycourses', 'myprofilesettings');
    foreach ($this->config->elements as $element) {
        if (in_array($element['id'], $allowed) || substr($element['id'], 0, 4) == 'link') {
            $elements []= $element;
        }
    }
    $this->config->elements = $elements;
}

?>
<div class="showHideCont">
    <a class="showHide chapters minus" id="hide-2" href="#" onclick="Config_Block_Course_Menu.section_hide(this, 'div_elements'); return false;">
        <?php echo get_string('hide', $this->blockname) ?>
    </a>
    <a class="showHide chapters plus" id="show-2" href="#" onclick="Config_Block_Course_Menu.section_show(this, 'div_elements'); return false;" style="display: none">
        <?php echo get_string('show', $this->blockname) ?>
    </a>
</div>
<div class="clear"></div>
<div id="div_elements">
    <div id="elementsContainer"></div>
</div>

<script type="text/javascript">
Config_Block_Course_Menu.E = new function() {
    var elements       = <?php echo json_encode($this->config->elements) ?>;
    var links          = <?php echo json_encode($this->config->links) ?>;
    var elementsTable, elementsBody;
    
    // function ----------------------------------------
    function drawElements(parent) {
        // clear parent
        while (parent.hasChildNodes()) {
            parent.removeChild(parent.firstChild);
        }

        elementsTable = document.createElement('table');
        elementsTable.cellpadding = 9;
        elementsTable.cellspacing = 0;
        elementsTable.border = 0;
        elementsTable.align = "center";
        parent.appendChild(elementsTable);

        elementsBody = document.createElement("tbody");
        elementsTable.appendChild(elementsBody);

        for (i = 0; i < elements.length; i++) {
            if (elements[i].id.substring(0, 4) == "link") {
                // it is a custom link; update the info for elements[i] for display
                linkIdx = parseInt(elements[i].id.substring(4));
                elements[i].name = links[linkIdx].name;
                elements[i].canHide = 0;
                elements[i].visible = 1;
            }

            tr = document.createElement('tr');
            elementsBody.appendChild(tr);

            // first td: show image + text + hidden input
            td = document.createElement('td');
            td.className = "elementsFirstTd";
            tr.appendChild(td);

            if (elements[i].canHide == 1) {
                a         = document.createElement('a');
                a.href    = "";
                a.onclick = function() {
                    var tr = this.parentNode.parentNode;
                    changeVisibility(tr);
                    return false;
                };
                td.appendChild(a);

                img = document.createElement('img');
                if (elements[i].visible == 1) {
                    img.src = Config_Block_Course_Menu.otherInfo.imgHide;
                } else {
                    img.src = Config_Block_Course_Menu.otherInfo.imgShow;
                }
                a.appendChild(img);
            }

            txt = document.createTextNode(" " + elements[i].name);
            td.appendChild(txt);

                // add the hidden inputs
            input = document.createElement('input');
            input.type  = "hidden";
            input.name  = "ids[]";
            input.value = elements[i].id;
            td.appendChild(input);

            input = document.createElement('input');
            input.type  = "hidden";
            input.name  = "canHides[]";
            input.value = elements[i].canHide;
            td.appendChild(input);

            input = document.createElement('input');
            input.type  = "hidden";
            input.name  = "visibles[]";
            input.value = elements[i].visible;
            td.appendChild(input);

            input = document.createElement('input');
            input.type  = "hidden";
            input.name  = "urls[]";
            input.value = elements[i].url;
            td.appendChild(input);

            input = document.createElement('input');
            input.type  = "hidden";
            input.name  = "icons[]";
            input.value = elements[i].icon;
            td.appendChild(input);

            // second td: up arrow
            td = document.createElement('td');
            tr.appendChild(td);
            if (i > 0) {
                a         = document.createElement('a');
                td.appendChild(a);
                a.href    = "";
                a.onclick = function() {
                    var tr = this.parentNode.parentNode;
                    moveTr(tr, "up");
                    return false;
                };

                img = document.createElement('img');
                img.src = Config_Block_Course_Menu.otherInfo.imgUp;
                a.appendChild(img);
            }

            // third td: down arrow
            td = document.createElement('td');
            tr.appendChild(td);
            if (i < elements.length - 1) {
                a         = document.createElement('a');
                a.href    = "";
                a.onclick = function() {
                    var tr = this.parentNode.parentNode;
                    moveTr(tr, "down");
                    return false;
                };
                td.appendChild(a);

                img = document.createElement('img');
                img.src = Config_Block_Course_Menu.otherInfo.imgDown;
                a.appendChild(img);
            }
        }
    }

    // function ----------------------------------------
    function moveTr(tr, direction)
    {
        for (i = 0; i < elementsBody.childNodes.length; i++) {
            if (elementsBody.childNodes[i] == tr) {
                if (direction == "up") {
                    temp          = elements[i];
                    elements[i]   = elements[i-1];
                    elements[i-1] = temp;
                } else {
                    temp          = elements[i];
                    elements[i]   = elements[i+1];
                    elements[i+1] = temp;
                }
            }
        }

        drawElements(Config_Block_Course_Menu.$('elementsContainer'));
    }

    // function ----------------------------------------
    function changeVisibility(tr)
    {
        for (i = 0; i < elementsBody.childNodes.length; i++) {
            if (elementsBody.childNodes[i] == tr) {
                elements[i].visible = (parseInt(elements[i].visible) + 1) % 2;
            }
        }

        drawElements(Config_Block_Course_Menu.$('elementsContainer'));
    }
    // --- (end) elementsTable ------------------------------------------------------------------------- //
    
    return {
        init: function() {
            drawElements(Config_Block_Course_Menu.$('elementsContainer'));
        }
    };
}

Config_Block_Course_Menu.addLoadEvent(function () {
    Config_Block_Course_Menu.E.init();
});

</script>