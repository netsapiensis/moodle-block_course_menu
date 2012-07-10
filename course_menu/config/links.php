<?php
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

if (!isset ($this->config->links)) {
    error('Unauthorized');
}
include($CFG->dirroot . "/blocks/course_menu/js/course_menu.js.php");
$linksShow = $this->config->linksEnable ? 'i/hide' : 'i/show';
?>

<div class="showHideCont">
    <a class="showHide minus" id="hide-3" href="#" onclick="section_hide(this, 'div_links'); return false;">
        <?php echo get_string('hide', $this->blockname) ?>
    </a>
    <a class="showHide plus" id="show-3" href="#" onclick="section_show(this, 'div_links'); return false;" style="display: none">
        <?php echo get_string('show', $this->blockname) ?>
    </a>
</div>
<div class="clear"></div>
<div id="div_links">
    <div id="linksEnableContainer">
        <div class="expandableTreeTd">
            <a href="" onclick="changeEnableLinks(); return false" class="enableDisable">
                <img src="<?php echo $OUTPUT->pix_url($linksShow) ?>" border="0" id="img_config_linksEnable" alt="" />
                <?php echo get_string('activatecustomlinks', $this->blockname) ?>
            </a>
        </div>
    </div>
    <div id="linksContainer" <?php if (!$this->config->linksEnable) echo 'style="display: none";' ?>></div>
</div>

<script type="text/javascript">

var links                = <?php echo json_encode($this->config->links) ?>;
var elements             = elements || <?php echo json_encode($this->config->elements) ?>;
var icons                = <?php echo json_encode($icons) ?>;
var oldLinksNoForBlur    = <?php echo count($this->config->links); ?>;
var restoreLinksNoOnBlur = true;
var linkTempStr          = '';

addLoadEvent(function () {
	drawLinks($('linksContainer'));
});

function changeEnableLinks()
{
    var enabInput = $("id_config_linksEnable");
    var img = $("img_config_linksEnable");
	if (enabInput.value == 1) {
		$('linksContainer').style.display = "none";
		enabInput.value = 0;
		img.src = otherInfo.imgShow;

		var i;
		for (i = elements.length - 1; i > 0; i--) {
			if (elements[i].id.substring(0, 4) == "link") {
				elements = removeArrayIdx(elements, i);
			}
		}
	} else {
		$('linksContainer').style.display = "block";
		enabInput.value = 1;
		img.src = otherInfo.imgHide;

		var i;
		for (i = 0; i < links.length; i++) {
			var newId = elements.length;
			elements[newId]         = new Object();
			elements[newId].id      = "link" + i;
			elements[newId].name    = '';
			elements[newId].url     = '';
			elements[newId].icon    = '';
			elements[newId].canHide = 0;
			elements[newId].visible = 1;
		}
	}

	drawElements($('elementsContainer'));
}

function drawLinks(parent) 
{
	// clear parent 
	while (parent.hasChildNodes()) {
		parent.removeChild(parent.firstChild);
	}
	
	table = document.createElement('table');
	table.cellpadding = 9;
	table.cellspacing = 0; 
	table.border = 0; 
	table.align = "center";
	parent.appendChild(table);
	
	tbody = document.createElement("tbody");
	table.appendChild(tbody);
	
	// first tr - configs
	tr = document.createElement('tr');
	tbody.appendChild(tr);
	
	td = document.createElement('td');
	td.align = "center";
	tr.appendChild(td); 
	
	br = document.createElement('br');
	td.appendChild(br);
	
	txt = document.createTextNode(" " + otherInfo.txt.numberoflinks);
	td.appendChild(txt);
	
	input = document.createElement('input');
	input.type  = "text";
	input.style.width = "50px";
	input.name  = "linksCount";
	input.id    = "linksCount";
	input.value = links.length;
	input.onkeypress = function (e) {
		return doneEditingLinksNo(e);
	}
	input.onblur = function () {
		setLinksNo();
	}
	td.appendChild(input);
	
	input = document.createElement('input');
	input.type  = "submit";
	input.value = otherInfo.txt.change;
	input.onclick = function () {
		changeLinksNo(true); 
		return false;
	}
	td.appendChild(input);
	
	// second tr - the table
	tr = document.createElement('tr');
	tbody.appendChild(tr);
	
	td = document.createElement('td');
	td.align = "center";
	td.id = "linksTableContainer";
	drawLinksTable(td);
	tr.appendChild(td); 
}

function drawLinksTable(parent)
{
	// clear parent
	while (parent.hasChildNodes()) {
		parent.removeChild(parent.firstChild);
	}

	var table, tr, td;

	br = document.createElement('br');
	parent.appendChild(br);

	linksTable = document.createElement('table');
	linksTable.cellpadding = 9;
	linksTable.cellspacing = 0;
	linksTable.align = "center";
	linksTable.className = "links";
	parent.appendChild(linksTable);

	linksBody = document.createElement("tbody");
	linksTable.appendChild(linksBody);

	for (i = 0; i < links.length; i++) {
		// the message "Custom link i:
		tr = document.createElement('tr');
		linksBody.appendChild(tr);
		td = document.createElement('td');
		tr.appendChild(td);

		strong = document.createElement('strong');
		td.appendChild(strong);

		txt = document.createTextNode(otherInfo.txt.customlink + (i+1));
		strong.appendChild(txt);

		// name
		tr = document.createElement('tr');
		linksBody.appendChild(tr);
		td = document.createElement('td');
		tr.appendChild(td);

		label = document.createElement('label');
		td.appendChild(label);
		txt = document.createTextNode(otherInfo.txt.name);
		label.appendChild(txt);

		input = document.createElement('input');
		input.type  = "text";
		input.name  = "linkNames[]";
		input.onkeyup = function () {
			updateLinksData();
		}
		input.onfocus = function () {
			linkTempStr = this.value;
		}
		input.onblur = function () {
			if (this.value == '') {
				alert(otherInfo.txt.linknoname);
				this.value = linkTempStr;
			}
		}
		input.value = links[i].name;
		td.appendChild(input);

		// url
		tr = document.createElement('tr');
		linksBody.appendChild(tr);
		td = document.createElement('td');
		tr.appendChild(td);

		label = document.createElement('label');
		td.appendChild(label);
		txt = document.createTextNode(otherInfo.txt.url);
		label.appendChild(txt);

		input = document.createElement('input');
		input.type  = "text";
		input.name  = "linkUrls[]";
		input.onkeyup = function () {
			updateLinksData();
		}
		input.onfocus = function () {
			linkTempStr = this.value;
		}
		input.onblur = function () {
			if (this.value == '') {
				alert(otherInfo.txt.linknourl);
				this.value = linkTempStr;
			}
		}
		input.value = links[i].url != '' ? links[i].url : 'http://';
		td.appendChild(input);

		// tr for window and icon
		tr = document.createElement('tr');
		linksBody.appendChild(tr);
		td = document.createElement('td');
		tr.appendChild(td);

		// window
		label = document.createElement('label');
		td.appendChild(label);
		txt = document.createTextNode(otherInfo.txt.window);
		label.appendChild(txt);

		select = document.createElement('select');
		select.name = "linkTargets[]";
		select.onchange = function () {
			updateLinksData();
		}
		td.appendChild(select);

		option = document.createElement('option');
		option.value = "";
		option.style.padding    = "2px";
		if (links[i].target == "") {
			option.selected = "selected";
		}
		select.appendChild(option);
		txt = document.createTextNode(otherInfo.txt.samewindow);
		option.appendChild(txt);

		option = document.createElement('option');
		option.value = "_blank";
		option.style.padding    = "2px";
		if (links[i].target == "_blank") {
			option.selected = "selected";
		}
		select.appendChild(option);
		txt = document.createTextNode(otherInfo.txt.newwindow);
		option.appendChild(txt);

		// icon
		label = document.createElement('label');
		label.className = "iconLabel";
		td.appendChild(label);
		txt = document.createTextNode(otherInfo.txt.icon);
		label.appendChild(txt);

		select = document.createElement('select');
		select.name = "linkIcons[]";
		select.onchange = function () {
			updateLinksData();
		}
		td.appendChild(select);

		var j;
		for (j = 0; j < icons.length; j++) {
			option                  = document.createElement('option');
			option.value            = icons[j].img;
			option.style.padding    = "2px 2px 2px 20px";
			option.style.background = "url('" + icons[j].img + "') no-repeat 2px 2px";
			if (links[i].icon == icons[j].img) {
				option.selected = "selected";
			}
			select.appendChild(option);

			txt = document.createTextNode(icons[j].name);
			option.appendChild(txt);
		}

		// checkbox configs
		tr = document.createElement('tr');
		linksBody.appendChild(tr);
		td = document.createElement('td');
		tr.appendChild(td);

			// keeppagenavigation
		div = document.createElement('div');
		div.className = "divRight";
		td.appendChild(div);
		txt = document.createTextNode(otherInfo.txt.keeppagenavigation);
		div.appendChild(txt);

		input = document.createElement('input');
		input.style.width = "30px";
		input.style.cssFloat = input.style.styleFloat = "none";
		input.type  = "checkbox";
		input.name  = "keeppagenavigation" + i;
		input.onclick = function () {
			updateLinksData();
		}
		if (links[i].keeppagenavigation == '1') {
			input.checked = "checked";
		}
		if (links[i].target == "_blank") {
			input.disabled = "disabled";
		}
		div.appendChild(input);

			// allowresize
		div = document.createElement('div');
		div.className = "divRight";
		td.appendChild(div);
		txt = document.createTextNode(otherInfo.txt.allowresize);
		div.appendChild(txt);

		input = document.createElement('input');
		input.style.width = "30px";
		input.style.cssFloat = input.style.styleFloat = "none";
		input.type  = "checkbox";
		input.name  = "allowresize" + i;
		input.onclick = function () {
			updateLinksData();
		}
		if (links[i].allowresize == '1') {
			input.checked = "checked";
		}
		if (links[i].target != "_blank") {
			input.disabled = "disabled";
		}
		div.appendChild(input);

			// allowscroll
		div = document.createElement('div');
		div.className = "divRight";
		td.appendChild(div);
		txt = document.createTextNode(otherInfo.txt.allowscroll);
		div.appendChild(txt);

		input = document.createElement('input');
		input.style.width = "30px";
		input.style.cssFloat = input.style.styleFloat = "none";
		input.type  = "checkbox";
		input.name  = "allowscroll" + i;
		input.onclick = function () {
			updateLinksData();
		}
		if (links[i].allowscroll == '1') {
			input.checked = "checked";
		}
		if (links[i].target != "_blank") {
			input.disabled = "disabled";
		}
		div.appendChild(input);

			// showdirectorylinks
		div = document.createElement('div');
		div.className = "divRight";
		td.appendChild(div);
		txt = document.createTextNode(otherInfo.txt.showdirectorylinks);
		div.appendChild(txt);

		input = document.createElement('input');
		input.style.width = "30px";
		input.style.cssFloat = input.style.styleFloat = "none";
		input.type  = "checkbox";
		input.name  = "showdirectorylinks" + i;
		input.onclick = function () {
			updateLinksData();
		}
		if (links[i].showdirectorylinks == '1') {
			input.checked = "checked";
		}
		if (links[i].target != "_blank") {
			input.disabled = "disabled";
		}
		div.appendChild(input);

			// showlocationbar
		div = document.createElement('div');
		div.className = "divRight";
		td.appendChild(div);
		txt = document.createTextNode(otherInfo.txt.showlocationbar);
		div.appendChild(txt);

		input = document.createElement('input');
		input.style.width = "30px";
		input.style.cssFloat = input.style.styleFloat = "none";
		input.type  = "checkbox";
		input.name  = "showlocationbar" + i;
		input.onclick = function () {
			updateLinksData();
		}
		if (links[i].showlocationbar == '1') {
			input.checked = "checked";
		}
		if (links[i].target != "_blank") {
			input.disabled = "disabled";
		}
		div.appendChild(input);

			// showmenubar
		div = document.createElement('div');
		div.className = "divRight";
		td.appendChild(div);
		txt = document.createTextNode(otherInfo.txt.showmenubar);
		div.appendChild(txt);

		input = document.createElement('input');
		input.style.width = "30px";
		input.style.cssFloat = input.style.styleFloat = "none";
		input.type  = "checkbox";
		input.name  = "showmenubar" + i;
		input.onclick = function () {
			updateLinksData();
		}
		if (links[i].showmenubar == '1') {
			input.checked = "checked";
		}
		if (links[i].target != "_blank") {
			input.disabled = "disabled";
		}
		div.appendChild(input);

			// showtoolbar
		div = document.createElement('div');
		div.className = "divRight";
		td.appendChild(div);
		txt = document.createTextNode(otherInfo.txt.showtoolbar);
		div.appendChild(txt);

		input = document.createElement('input');
		input.style.width = "30px";
		input.style.cssFloat = input.style.styleFloat = "none";
		input.type  = "checkbox";
		input.name  = "showtoolbar" + i;
		input.onclick = function () {
			updateLinksData();
		}
		if (links[i].showtoolbar == '1') {
			input.checked = "checked";
		}
		if (links[i].target != "_blank") {
			input.disabled = "disabled";
		}
		div.appendChild(input);

			// showstatusbar
		div = document.createElement('div');
		div.className = "divRight";
		td.appendChild(div);
		txt = document.createTextNode(otherInfo.txt.showstatusbar);
		div.appendChild(txt);

		input = document.createElement('input');
		input.style.width = "30px";
		input.style.cssFloat = input.style.styleFloat = "none";
		input.type  = "checkbox";
		input.name  = "showstatusbar" + i;
		input.onclick = function () {
			updateLinksData();
		}
		if (links[i].showstatusbar == '1') {
			input.checked = "checked";
		}
		if (links[i].target != "_blank") {
			input.disabled = "disabled";
		}
		div.appendChild(input);

			// defaultwidth
		div = document.createElement('div');
		div.className = "divRight";
		td.appendChild(div);
		txt = document.createTextNode(otherInfo.txt.defaultwidth);
		div.appendChild(txt);

		input = document.createElement('input');
		input.style.width = "30px";
		input.style.cssFloat = input.style.styleFloat = "none";
		input.type  = "text";
		input.name  = "defaultwidth[]";
		input.onkeyup = function () {
			updateLinksData();
		}
		input.value = links[i].defaultwidth != '' ? links[i].defaultwidth : "620";
		if (links[i].target != "_blank") {
			input.disabled = "disabled";
		}
		div.appendChild(input);

			// defaultheight
		div = document.createElement('div');
		div.className = "divRight";
		td.appendChild(div);
		txt = document.createTextNode(otherInfo.txt.defaultheight);
		div.appendChild(txt);

		input = document.createElement('input');
		input.style.width = "30px";
		input.style.cssFloat = input.style.styleFloat = "none";
		input.type  = "text";
		input.name  = "defaultheight[]";
		input.onkeyup = function () {
			updateLinksData();
		}
		input.value = links[i].defaultheight != '' ? links[i].defaultheight : "450";
		if (links[i].target != "_blank") {
			input.disabled = "disabled";
		}
		div.appendChild(input);

		// just a spacer
		tr = document.createElement('tr');
		linksBody.appendChild(tr);
		td = document.createElement('td');
		tr.appendChild(td);
		br = document.createElement('br');
		td.appendChild(br);
		hr = document.createElement('hr');
		td.appendChild(hr);
	}

	br = document.createElement('br');
	parent.appendChild(br);

	span = document.createElement('span');
	span.className = 'linkMsg';
	parent.appendChild(span);
	txt = document.createTextNode(otherInfo.txt.correcturlmsg);
	span.appendChild(txt);
}

function updateLinksData()
{
	var i, n = 0;
	for (i = 1; i < linksBody.childNodes.length; i += 6) {
		// update name
		td    = linksBody.childNodes[i].childNodes[0];
		input = td.childNodes[1];
		links[n].name = input.value;

		// update url
		td    = linksBody.childNodes[i+1].childNodes[0];
		input = td.childNodes[1];
		links[n].url = input.value;

		// update window (target)
		td     = linksBody.childNodes[i+2].childNodes[0];
		select = td.childNodes[1];
		if (links[n].target != select.value) {
			links[n].target = select.value;
			drawLinks($('linksContainer'));
		}

		// update icon
		td     = linksBody.childNodes[i+2].childNodes[0];
		select = td.childNodes[3];
		links[n].icon = select.value;

		// update checkbox configs
		td    = linksBody.childNodes[i+3].childNodes[0];

			// keeppagenavigation
		input = td.childNodes[0].childNodes[1];
		links[n].keeppagenavigation = input.checked ? 1 : 0;

			// allowresize
		input = td.childNodes[1].childNodes[1];
		links[n].allowresize = input.checked ? 1 : 0;

			// allowscroll
		input = td.childNodes[2].childNodes[1];
		links[n].allowscroll = input.checked ? 1 : 0;

			// showdirectorylinks
		input = td.childNodes[3].childNodes[1];
		links[n].showdirectorylinks = input.checked ? 1 : 0;

			// showlocationbar
		input = td.childNodes[4].childNodes[1];
		links[n].showlocationbar = input.checked ? 1 : 0;

			// showmenubar
		input = td.childNodes[5].childNodes[1];
		links[n].showmenubar = input.checked ? 1 : 0;

			// showtoolbar
		input = td.childNodes[6].childNodes[1];
		links[n].showtoolbar = input.checked ? 1 : 0;

			// showstatusbar
		input = td.childNodes[7].childNodes[1];
		links[n].showstatusbar = input.checked ? 1 : 0;

			// defaultwidth
		input = td.childNodes[8].childNodes[1];
		links[n].defaultwidth = input.value;

			// defaultheight
		input = td.childNodes[9].childNodes[1];
		links[n].defaultheight = input.value;

		n++;
	}

	drawElements($('elementsContainer'));
}

function changeLinksNo(changeInput)
{
	if (changeInput) {
		$("linksCount").value = oldLinksNoForBlur;
	}

	var newValue = $("linksCount").value;
	if ((!IsNumeric(newValue))||(newValue<1)) {
		alert(otherInfo.txt.linkswrongnumber);
	} else {
		//restoreLinksNoOnBlur = false;
		linksNo = newValue;
		defaultLinks(linksNo);
		drawLinksTable($("linksTableContainer"));
		//restoreLinksNoOnBlur = true;
	}

	setLinksNo();
}

function doneEditingLinksNo(e)
{
	var keynum

	if(window.event) { // IE
		keynum = window.event.keyCode;
	} else if(e.which) { // Netscape/Firefox/Opera
		keynum = e.which;
	}
	if (keynum == 13) {
		changeLinksNo(false);
		return false;
	}

	return true;
}

function setLinksNo()
{
	if (restoreLinksNoOnBlur) {
		oldLinksNoForBlur = $("linksCount").value;
		$("linksCount").value = links.length;
	}
}

function defaultLinks(linksNo)
{
	if (linksNo < links.length) {
		var i;
		for (i = links.length - 1; i >= linksNo; i--) {
			links = removeArrayIdx(links, i);

			elIdx = 0;
			while (elements[elIdx].id != "link" + i) {
				elIdx++;
			}

			elements = removeArrayIdx(elements, elIdx);
		}

	} else {
		var i;
		for (i = links.length; i < linksNo; i++) {
			links[i] = new Object();
			links[i].name   = otherInfo.txt.customlink + (i+1);
			links[i].url    = '';
			links[i].target = '';
			links[i].icon   = '';

			links[i].defaultwidth   = '';
			links[i].defaultheight  = '';

			var newId = elements.length + i+1 - links.length;
			elements[newId]         = new Object();
			elements[newId].id      = "link" + i;
			elements[newId].name    = '';
			elements[newId].url     = '';
			elements[newId].icon    = '';
			elements[newId].canHide = 0;
			elements[newId].visible = 1;
		}
	}

	drawElements($('elementsContainer'));
}

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

</script>