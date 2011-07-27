var chapterBg 		= "#ccc";
var subChapterBg 	= "#FF88FF";
var topicBg			= "yellow";

function $(id)
{
	return document.getElementById(id);
}

// --- elementsTable ------------------------------------------------------------------------------- //

drawElements($('elementsContainer'));

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
				img.src = otherInfo.imgHide;
			} else {
				img.src = otherInfo.imgShow;
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
			img.src = otherInfo.imgUp;
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
			img.src = otherInfo.imgDown;
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
	
	drawElements($('elementsContainer'));
}

// function ----------------------------------------
function changeVisibility(tr) 
{
	for (i = 0; i < elementsBody.childNodes.length; i++) {
		if (elementsBody.childNodes[i] == tr) {
			elements[i].visible = (parseInt(elements[i].visible) + 1) % 2;
		}
	}
		
	drawElements($('elementsContainer'));
}
// --- (end) elementsTable ------------------------------------------------------------------------- //

// --- expandableTree ------------------------------------------------------------------------------ //

drawExpandableTree($('expandableTreeContainer'));

function drawExpandableTree(parent)
{
	// clear parent 
	while (parent.hasChildNodes()) {
		parent.removeChild(parent.firstChild);
	}

	expandableTreeTable = document.createElement('table');
	expandableTreeTable.cellpadding = 9;
	expandableTreeTable.cellspacing = 0; 
	expandableTreeTable.border = 0; 
	expandableTreeTable.align = "center";
	parent.appendChild(expandableTreeTable);
	
	expandableBody = document.createElement("tbody");
	expandableTreeTable.appendChild(expandableBody);
	
	tr = document.createElement('tr');
	expandableBody.appendChild(tr);
	
	td = document.createElement('td');
	td.className = "expandableTreeTd";
	tr.appendChild(td); 
	
	a         = document.createElement('a');
	a.href    = "";
	a.onclick = function() { 
		var tr = this.parentNode.parentNode;
		changeEnableExpand(tr); 
		return false; 
	};
	td.appendChild(a);
	
	img = document.createElement('img');
	if (expandableTree.enable == 1) {
		img.src = otherInfo.imgHide;
	} else {
		img.src = otherInfo.imgShow;
	}
	a.appendChild(img);
	
	txt = document.createTextNode(" " + expandableTree.text);
	td.appendChild(txt);
	
	input = document.createElement('input');
	input.type  = "hidden";
	input.name  = "expandableTree";
	input.value = expandableTree.enable;
	td.appendChild(input);
}

// function ----------------------------------------
function changeEnableExpand(tr)
{
	td  = tr.childNodes[0];
	a   = td.childNodes[0]; 
	img = a.childNodes[0]; 
	
	enabInput = td.childNodes[2];
	if (enabInput.value == 1) {
		enabInput.value = 0;
		img.src = otherInfo.imgShow;
	} else {
		enabInput.value = 1;
		img.src = otherInfo.imgHide;
	}
}
// --- (end) expandableTree ------------------------------------------------------------------------ //

// --- chapEnable ---------------------------------------------------------------------------------- //
chapEnableTable = document.createElement('table');
chapEnableTable.cellpadding = 9;
chapEnableTable.cellspacing = 0; 
chapEnableTable.border = 0; 
chapEnableTable.align = "center";
$('chapEnableContainer').appendChild(chapEnableTable);

tbody = document.createElement("tbody");
chapEnableTable.appendChild(tbody);

tr = document.createElement('tr');
tbody.appendChild(tr);

td = document.createElement('td');
td.className = "expandableTreeTd";
tr.appendChild(td); 

a         = document.createElement('a');
a.href    = "";
a.onclick = function() { 
	var tr = this.parentNode.parentNode;
	changeEnableChap(tr); 
	return false; 
};
td.appendChild(a);

img = document.createElement('img');
if (chapEnable == 1) {
	$('chaptersContainer').style.display = "block";
	img.src = otherInfo.imgHide;
} else {
	$('chaptersContainer').style.display = "none";
	img.src = otherInfo.imgShow;
}
a.appendChild(img);

txt = document.createTextNode(" " + otherInfo.txt.chaptering);
td.appendChild(txt);

input = document.createElement('input');
input.type  = "hidden";
input.name  = "chapEnable";
input.value = chapEnable;
td.appendChild(input);

// function ----------------------------------------
function changeEnableChap(tr)
{
	td  = tr.childNodes[0];
	a   = td.childNodes[0]; 
	img = a.childNodes[0]; 
	
	enabInput = td.childNodes[2];
	if (enabInput.value == 1) {
		$('chaptersContainer').style.display = "none";
        enabInput.value = 0;
		img.src = otherInfo.imgShow;
		$('subChapEnableContainer').style.display = "none";
	} else {
		$('chaptersContainer').style.display = "block";
		enabInput.value = 1;
		img.src = otherInfo.imgHide;
		$('subChapEnableContainer').style.display = "block";
	}
}
// --- (end) chapEnable ---------------------------------------------------------------------------- //

// --- subChapters enable -------------------------------------------------------------------------//

subChapEnableTable = document.createElement('table');
subChapEnableTable.cellpadding = 9;
subChapEnableTable.cellspacing = 0; 
subChapEnableTable.border = 0; 
subChapEnableTable.align = "center";
if (chapEnable == 0) {
    $('subChapEnableContainer').style.display = "none";
}
$('subChapEnableContainer').appendChild(subChapEnableTable);


tbody = document.createElement("tbody");
subChapEnableTable.appendChild(tbody);

tr = document.createElement('tr');
tbody.appendChild(tr);

td = document.createElement('td');
td.className = "expandableTreeTd";
tr.appendChild(td); 

a         = document.createElement('a');
a.href    = "";
a.onclick = function() {
    var tr = this.parentNode.parentNode;
    if (subChapEnable == 0) {
        if (confirm(otherInfo.txt.warningsubchapenable)) {
            subChaptersNo = chapters.length;
            $('subChaptersCount').value = subChaptersNo;
            changeEnableSubChap(tr);
        }
    } else {
        changeEnableSubChap(tr);
    }
	return false; 
};
td.appendChild(a);

img = document.createElement('img');
if (subChapEnable == 1) {
	$('subChaptersContainer').style.display = "block";
	img.src = otherInfo.imgHide;
} else {
	$('subChaptersContainer').style.display = "none";
	img.src = otherInfo.imgShow;
}
a.appendChild(img);

txt = document.createTextNode(" " + otherInfo.txt.subchaptering);
td.appendChild(txt);

input = document.createElement('input');
input.type  = "hidden";
input.name  = "subChapEnable";
input.value = subChapEnable;
td.appendChild(input);

// function ----------------------------------------
function changeEnableSubChap(tr)
{
	td  = tr.childNodes[0];
	a   = td.childNodes[0]; 
	img = a.childNodes[0]; 
	
	enabInput = td.childNodes[2];
	if (enabInput.value == 1) {
		enabInput.value = 0;
		$('subChaptersNumber').style.display = "none";
		img.src = otherInfo.imgShow;
		subChapEnable = 0;
	} else {
		$('subChaptersNumber').style.display = "";
		enabInput.value = 1;
		img.src = otherInfo.imgHide;
		subChapEnable = 1;
	}
	resetSubchapterGroupings();
	drawChapTable($("chaptersTableContainer"));
}
// --- (end) subChapEnable ---------------------------------------------------------------------------- //


// --- chapters ------------------------------------------------------------------------------------ //
table = document.createElement('table');
table.cellpadding = 9;
table.cellspacing = 0; 
table.border = 0; 
table.align = "center";
$('chaptersContainer').appendChild(table);

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

txt = document.createTextNode(" " + otherInfo.txt.numberofchapter);
td.appendChild(txt);

input = document.createElement('input');
input.type  = "text";
input.style.width = "50px";
input.name  = "chaptersCount";
input.id    = "chaptersCount";
input.value = chapters.length;
input.onkeypress = function (e) {
	return doneEditingChapNo(e);
}
input.onblur = function () {
	setChapNo();
}
td.appendChild(input);

input = document.createElement('input');
input.type  = "submit";
input.value = otherInfo.txt.change;
input.onclick = function () {
	changeChapNo(true); 
	return false;
}
td.appendChild(input);

br = document.createElement('br');
td.appendChild(br);
br = document.createElement('br');
td.appendChild(br);

input = document.createElement('input');
input.type  = "submit";
input.value = otherInfo.txt.defaultgrouping;
input.onclick = function () {
	chapNo = $("chaptersCount").value;
	defaultChaptering(chapNo, 0);
	drawChapTable($("chaptersTableContainer"));
	return false;
}
td.appendChild(input);


//subchapters number
tr = document.createElement('tr');
tbody.appendChild(tr);

td = document.createElement('td');
td.align = "center";
td.id = "subChaptersNumber";
td.style.display = subChapEnable == 0 ? "none" : "";
tr.appendChild(td); 

br = document.createElement('br');
td.appendChild(br);

txt = document.createTextNode(" " + otherInfo.txt.numberofsubchapter);
td.appendChild(txt);

input = document.createElement('input');
input.type  = "text";
input.style.width = "50px";
input.name  = "subChaptersCount";
input.id    = "subChaptersCount";
input.value = subChaptersNo;
input.onkeypress = function (e) {
	return doneEditingSubChapNo(e);
}
input.onblur = function () {
	setSubChapNo();
}
td.appendChild(input);

input = document.createElement('input');
input.type  = "submit";
input.value = otherInfo.txt.change;
input.onclick = function () {
	changeSubChapNo(true); 
	return false;
}
td.appendChild(input);
//end subchapters number





// second tr - the table
tr = document.createElement('tr');
tbody.appendChild(tr);

td = document.createElement('td');
td.align = "center";
td.id = "chaptersTableContainer";
drawChapTable(td);
tr.appendChild(td);

// function ----------------------------------------
function drawChapTable(parent)
{
	// clear parent 
	while (parent.hasChildNodes()) {
		parent.removeChild(parent.firstChild);
	}

	var table, tr, td;

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
	
	txt = document.createTextNode(otherInfo.txt.chapters);
	td.appendChild(txt);
	
	if (subChapEnable == 1) {
		td = document.createElement('td');
		td.align	= "center";
		td.colSpan 	= 3;
		td.width	= 200;
		tr.appendChild(td);
		
		txt = document.createTextNode(otherInfo.txt.subchapters);
		td.appendChild(txt);
	}
	
	td = document.createElement('td');
	td.align   = "center";
	td.colSpan = 2;
	td.width   = 200;
	tr.appendChild(td);
	
	txt = document.createTextNode(otherInfo.courseFormat);
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
		img.src = otherInfo.imgEdit;
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
		if (subChapEnable == 0 || chapEnable == 0) {
			input.value = chapters[i].childElements[0].count;
		}
		td.appendChild(input);
		
		
		input = document.createElement('input');
		input.type  = "hidden";
		input.name  = "chapterChildElementsNumber[]";
		input.value = chapters[i].childElements.length;
		td.appendChild(input);

		
		// se va verifica din php - daca subchaptersEnable == 0 => ce mama dracu' rezulta ?
		if (subChapEnable == 1) {
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
			
			if (subChapEnable == 1) {
				
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
					img.src = otherInfo.imgUp;
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
					img.src = otherInfo.imgDown;
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
					img.src = otherInfo.imgRight;
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
					img.src = otherInfo.imgEdit;
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
			
			if (subChapEnable == 0 || chapters[i].childElements[k].type == "subchapter") {
				for (j = 0; j < chapters[i].childElements[k].count; j++) {
					tr = document.createElement('tr');
					chapBody.appendChild(tr);
					
					// add 2 empty td-s		
					td = document.createElement('td');
					tr.appendChild(td);
					td = document.createElement('td');
					tr.appendChild(td);
					
					//add another 3 empty tds if subchaptersEnable
					if (subChapEnable == 1) {
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
					
					if ((chapters[i].childElements[k].count > 1) && (j == 0) && ((i > 0) || (k > 0 && subChapEnable == 1))) {
						if (subChapEnable == 1) {
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
							img.src = otherInfo.imgLeft;
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
						img.src = otherInfo.imgUp;
						a.appendChild(img);
					} else if ((chapters[i].childElements[k].count > 1) && 
						(j == chapters[i].childElements[k].count - 1) && ((i < chapters.length - 1) || (k < chapters[i].childElements.length - 1 && subChapEnable == 1))) 
					{
						if (subChapEnable == 1) {
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
							img.src = otherInfo.imgLeft;
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
						img.src = otherInfo.imgDown;
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
		alert(otherInfo.txt.emptychapname);
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
		alert(otherInfo.txt.emptysubchapname);
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

//function ----------------------------------------
function doneEditingSubChapNo(e)
{
	var keynum

	if(window.event) { // IE
		keynum = window.event.keyCode;
	} else if(e.which) { // Netscape/Firefox/Opera
		keynum = e.which
	}
	if (keynum == 13) {
		changeSubChapNo(false);
		return false;
	}
	
	return true;
}

// function ----------------------------------------
function setSubChapNo()
{
	if (restoreSubChapNoOnBlur) {
		oldSubChapNoForBlur = $("subChaptersCount").value;
		$("subChaptersCount").value = subChaptersNo;
	}
}

//function ----------------------------------------
function changeSubChapNo(changeInput)
{
	if (changeInput) {
		$("subChaptersCount").value = oldSubChapNoForBlur; 
	}
	
	var newValue = $("subChaptersCount").value;
	if 	(
			(!IsNumeric(newValue)) || (newValue < chapters.length) || (newValue > sectionNames.length)
		) 
	{
		alert(otherInfo.txt.wrongsubchapnumber);
	} else {
		restoreSubChapNoOnBlur = false;
		if (confirm(otherInfo.txt.warningsubchapnochange)) {
			subChaptersNo = newValue;
			defaultChaptering(chapters.length, 1);
			drawChapTable($("chaptersTableContainer"));
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
	drawChapTable($("chaptersTableContainer"));
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
	
	if (subChapEnable == 1) {
		
		if 	(
				(direction == "up") &&
				(
					(mostUpperTR.childNodes[4].childNodes.length > 0 && mostUpperTR.childNodes[3].childNodes.length == 0) ||
					(evenMoreUpperTR.childNodes[4].childNodes.length > 0 && evenMoreUpperTR.childNodes[3].childNodes.length == 0)
				)
			)
		{
			alert(otherInfo.txt.cannotmovetopicup);
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
			alert(otherInfo.txt.cannotmovetopicdown);
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
	
	drawChapTable($("chaptersTableContainer"));
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
		if (subChapEnable == 0 || setNames == 1) {
			if (setNames == 1) {
				chapters[i] = new Object();
				chapters[i].name = otherInfo.txt.chapter + (i+1);
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
function changeChapNo(changeInput)
{
	if (changeInput) {
		$("chaptersCount").value = oldChapNoForBlur; 
	}

	var newValue = $("chaptersCount").value;
	if 	(
			(!IsNumeric(newValue)) || (newValue < 1) || (newValue > sectionNames.length) || (subChapEnable == 1 && newValue > subChaptersNo)
		) 
	{
		alert(otherInfo.txt.wrongnumber);
	} else {
		restoreChapNoOnBlur = false;
		if (confirm(otherInfo.txt.warningchapnochange)) {
			chapNo = newValue;
			defaultChaptering(chapNo, 1);
			drawChapTable($("chaptersTableContainer"));
		}
		restoreChapNoOnBlur = true;
	}
	
	setChapNo();
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

// function ----------------------------------------
function doneEditingChapNo(e)
{
	var keynum

	if(window.event) { // IE
		keynum = window.event.keyCode;
	} else if(e.which) { // Netscape/Firefox/Opera
		keynum = e.which
	}
	if (keynum == 13) {
		changeChapNo(false);
		return false;
	}
	
	return true;
}

// function ----------------------------------------
function setChapNo()
{
	if (restoreChapNoOnBlur) {
		oldChapNoForBlur = $("chaptersCount").value;
		$("chaptersCount").value = chapters.length;
	}
}
// --- (end) chapters ------------------------------------------------------------------------------ //

// --- linksEnable --------------------------------------------------------------------------------- //

drawLinksEnable($('linksEnableContainer'));

function drawLinksEnable(parent) {
	// clear parent 
	while (parent.hasChildNodes()) {
		parent.removeChild(parent.firstChild);
	}

	linksEnableTable = document.createElement('table');
	linksEnableTable.cellpadding = 9;
	linksEnableTable.cellspacing = 0; 
	linksEnableTable.border = 0; 
	linksEnableTable.align = "center";
	parent.appendChild(linksEnableTable);
	
	tbody = document.createElement("tbody");
	linksEnableTable.appendChild(tbody);
	
	tr = document.createElement('tr');
	tbody.appendChild(tr);
	
	td = document.createElement('td');
	td.className = "expandableTreeTd";
	tr.appendChild(td); 
	
	a         = document.createElement('a');
	a.href    = "";
	a.onclick = function() { 
		var tr = this.parentNode.parentNode;
		changeEnableLinks(tr); 
		return false; 
	};
	td.appendChild(a);
	
	img = document.createElement('img');
	if (linksEnable == 1) {
		$('linksContainer').style.display = "block";
		img.src = otherInfo.imgHide;
	} else {
		$('linksContainer').style.display = "none";
		img.src = otherInfo.imgShow;
	}
	a.appendChild(img);
	
	txt = document.createTextNode(" " + otherInfo.txt.activatecustomlinks);
	td.appendChild(txt);
	
	input = document.createElement('input');
	input.type  = "hidden";
	input.name  = "linksEnable";
	input.value = linksEnable;
	td.appendChild(input);
}

// function ----------------------------------------
function changeEnableLinks(tr)
{
	td  = tr.childNodes[0];
	a   = td.childNodes[0]; 
	img = a.childNodes[0]; 
	
	enabInput = td.childNodes[2];
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
// --- (end) linksEnable --------------------------------------------------------------------------- //

// --- links --------------------------------------------------------------------------------------- //

drawLinks($('linksContainer'));

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

// function ----------------------------------------
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

// function ----------------------------------------
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

// function ----------------------------------------
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

// function ----------------------------------------
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

// function ----------------------------------------
function setLinksNo()
{
	if (restoreLinksNoOnBlur) {
		oldLinksNoForBlur = $("linksCount").value;
		$("linksCount").value = links.length;
	}
}

// function ----------------------------------------
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
// --- (end) links --------------------------------------------------------------------------------- //


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


//------------------ functions added by radu----------------

//functie pt regruparea topicurilor in subcapitole cand se face enable/disable la Subchaptergroupings
function resetSubchapterGroupings(setNames)
{
	if (subChapEnable == 0) {
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
		var subChaptersPerChapter = Math.floor(subChaptersNo / chapters.length);
		var dif = subChaptersNo - (chapters.length * subChaptersPerChapter);
		var topicsPerSubchapter = Math.floor(sectionNames.length / subChaptersNo);
		var topicDif = sectionNames.length - (subChaptersNo * topicsPerSubchapter);
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