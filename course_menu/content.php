<!-- css -->
<style>
<?php foreach ($elements as $element) { ?>
	<?php if (!empty($element['iconClass'])) { ?>
		.<?php echo $element['iconClass'] ?> {
			display:block; padding-left: 25px; 
			background: transparent url(<?php echo $element['icon'] ?>) 0 0 no-repeat; 
		}
	<?php } ?>
<?php } ?>

<?php foreach ($links as $link) { ?>
	<?php if (!empty($link['iconClass'])) { ?>
		.<?php echo $link['iconClass'] ?> {
			display:block; padding-left: 25px; 
			background: transparent url(<?php echo $link['icon'] ?>) 0 0 no-repeat; 
		}
	<?php } ?>
<?php } ?>

<?php foreach ($sections as $section) { ?>
	<?php foreach ($section['resources'] as $l => $resource) { ?>
		<?php if (!empty($resource['iconClass'])) { ?>
			.<?php echo $resource['iconClass'] ?> {
				display:block; padding-left: 25px; 
				background: transparent url(<?php echo $resource['icon'] ?>) 0 0 no-repeat; 
			}
		<?php } ?>
	<?php } ?>
<?php } ?>
.ygtvspacer, .ygtvlabel {
    outline: none;
}

</style>
<!-- (end) css -->

<!-- html -->
<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/lib/yui/yahoo/yahoo-min.js"></script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/lib/yui/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/lib/yui/event/event-min.js"></script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/lib/yui/container/container-min.js"></script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/lib/yui/treeview/treeview-min.js"></script>

<div id="treeDiv"></div>
<!-- (end) html -->

<script type="text/javascript"><!--
//<![CDATA[
// --- read data from PHP -------------------------------------------------------------------------- //
var elements       = new Array();
var expandableTree = new Object();

var sections       = new Array();
var displaySection;

var addClassToLinks = <?php echo ($isSectionRequest) ? "false" : "true" ?>;
var highlight = <?php echo ($highlight) ? "true" : "false" ?>;

var chapEnable;
var subChapEnable;
var oldChapNoForBlur;
var restoreChapNoOnBlur;
var currentChap;
var currentSubChap;
var chapters       = new Array();

var links          = new Array();

var wwwroot = "<?php echo $CFG->wwwroot; ?>";
var courseid = <?php echo $this->course->id; ?>;

// read elements
<?php foreach ($elements as $k => $element) { ?>
	elements[<?php echo $k; ?>]           = new Object();
	elements[<?php echo $k; ?>].id        = "<?php echo $element['id']; ?>";
	elements[<?php echo $k; ?>].name      = "<?php echo $element['name']; ?>";
	elements[<?php echo $k; ?>].url       = "<?php echo $element['url']; ?>";
	elements[<?php echo $k; ?>].icon      = "<?php echo $element['icon']; ?>";
	elements[<?php echo $k; ?>].iconClass = "<?php echo $element['iconClass']; ?>";
	elements[<?php echo $k; ?>].canHide   = "<?php echo $element['canHide']; ?>";
	elements[<?php echo $k; ?>].visible   = "<?php echo $element['visible']; ?>";
<?php } ?>

// read expandableTree
expandableTree.enable = <?php echo isset($this->config['expandableTree']['enable']) ? $this->config['expandableTree']['enable'] : '0'; ?>; 
expandableTree.text   = "<?php echo isset($expandableTreeText) ? $expandableTreeText : '0'; ?>";

// sections
<?php foreach ($sections as $k => $section) { ?>
	sections[<?php echo $k; ?>] = new Object();
	sections[<?php echo $k; ?>].name      = "<?php echo str_replace('"', "''", $section['name']); ?>";
	sections[<?php echo $k; ?>].url       = "<?php echo $section['url']; ?>";
	sections[<?php echo $k; ?>].visible   = "<?php echo $section['visible']; ?>";
    sections[<?php echo $k; ?>].resources = new Array();
	<?php foreach ($section['resources'] as $l => $resource) { ?>
		sections[<?php echo $k; ?>].resources[<?php echo $l; ?>] = new Object();
		sections[<?php echo $k; ?>].resources[<?php echo $l; ?>].name      = "<?php echo str_replace('"', "''", $resource['name']); ?>";
		sections[<?php echo $k; ?>].resources[<?php echo $l; ?>].url       = "<?php echo $resource['url']; ?>";
		sections[<?php echo $k; ?>].resources[<?php echo $l; ?>].icon      = "<?php echo $resource['icon']; ?>";
		sections[<?php echo $k; ?>].resources[<?php echo $l; ?>].iconClass = "<?php echo $resource['iconClass']; ?>";		
	<?php } ?>
<?php } ?>
    

displaySection = <?php echo isset($displaysection) ? $displaysection : 0; ?>;

// chapters
chapEnable = <?php echo isset($this->config['chapEnable']) ? $this->config['chapEnable'] : 0; ?>;
subChapEnable = <?php echo isset($this->config['subChapEnable']) ? $this->config['subChapEnable'] : 0; ?>;
<?php foreach ($this->config['chapters'] as $k => $chapter) { ?>
	chapters[<?php echo $k; ?>]       = new Object();
	chapters[<?php echo $k; ?>].name  = "<?php echo $chapter['name']; ?>";
	chapters[<?php echo $k; ?>].childElements = new Array();
    chapters[<?php echo $k; ?>].expanded = <?php echo isset($_SESSION['yui_tree'][$this->course->id]['expanded_elements']) && in_array($chapter['name'], $_SESSION['yui_tree'][$this->course->id]['expanded_elements']) ? "true" : "false" ?>;

	<?php foreach ($chapter['childElements'] as $kk => $childElement) { ?>
		chapters[<?php echo $k; ?>].childElements[<?php echo $kk; ?>] = new Object();
		chapters[<?php echo $k; ?>].childElements[<?php echo $kk; ?>].type = "<?php echo $childElement['type'] ?>";
		<?php if ($childElement['type'] == "topic") { ?>
			//do nothing for now
		<?php } elseif ($childElement['type'] == "subchapter") { ?>
			chapters[<?php echo $k; ?>].childElements[<?php echo $kk; ?>].name = "<?php echo $childElement['name'] ?>";
			chapters[<?php echo $k; ?>].childElements[<?php echo $kk; ?>].count = "<?php echo $childElement['count'] ?>";
            chapters[<?php echo $k; ?>].childElements[<?php echo $kk; ?>].expanded = <?php echo isset($_SESSION['yui_tree'][$this->course->id]['expanded_elements']) && in_array($childElement['name'], $_SESSION['yui_tree'][$this->course->id]['expanded_elements']) ? "true" : "false" ?>;
		<?php } ?>
	<?php } ?>
<?php } ?>

oldChapNoForBlur    = <?php echo count($this->config['chapters']); ?>;
restoreChapNoOnBlur = true;

var sum = 0;
currentChap = 0;
if (displaySection == 0) {
    currentChap = currentSubChap = 500;
} else {
    for (i = 0; i < chapters.length; i++) {
        for (j = 0; j < chapters[i].childElements.length; j++) {
            if (chapters[i].childElements[j].type == "subchapter") {
                sum += parseInt(chapters[i].childElements[j].count);
            } else if (chapters[i].childElements[j].type == "topic") {
                sum++;
            }
            
            if (sum >= displaySection) {
                currentChap = i;
                currentSubChap = j;
                break;
            }
        }
        if (sum >= displaySection) {
            break;
        }
    }
}

// links
<?php foreach ($links as $k => $link) { ?>
	links[<?php echo $k; ?>]           = new Object();
	links[<?php echo $k; ?>].name      = "<?php echo str_replace('"', "''", $link['name']); ?>";
	links[<?php echo $k; ?>].url       = "<?php echo $link['url']; ?>";
	links[<?php echo $k; ?>].target    = "<?php echo $link['target']; ?>";
	links[<?php echo $k; ?>].icon      = "<?php echo $link['icon']; ?>";
	links[<?php echo $k; ?>].iconClass = "<?php echo $link['iconClass']; ?>";

	links[<?php echo $k; ?>].keeppagenavigation = "<?php echo $link['keeppagenavigation']; ?>";
	links[<?php echo $k; ?>].allowresize        = "<?php echo $link['allowresize']; ?>";
	links[<?php echo $k; ?>].allowscroll        = "<?php echo $link['allowscroll']; ?>";
	links[<?php echo $k; ?>].showdirectorylinks = "<?php echo $link['showdirectorylinks']; ?>";
	links[<?php echo $k; ?>].showlocationbar    = "<?php echo $link['showlocationbar']; ?>";
	links[<?php echo $k; ?>].showmenubar        = "<?php echo $link['showmenubar']; ?>";
	links[<?php echo $k; ?>].showtoolbar        = "<?php echo $link['showtoolbar']; ?>";
	links[<?php echo $k; ?>].showstatusbar      = "<?php echo $link['showstatusbar']; ?>";
	links[<?php echo $k; ?>].defaultwidth       = "<?php echo $link['defaultwidth']; ?>";
	links[<?php echo $k; ?>].defaultheight      = "<?php echo $link['defaultheight']; ?>";
<?php } ?>

// already truncated values
var originalTxts = new Array();
var truncated = new Array();
var truncWidth;
truncated  = unserialize("<?php echo isset($_SESSION['truncated'][$this->course->id]) ? $_SESSION['truncated'][$this->course->id] : ""; ?>");
truncWidth = "<?php echo isset($_SESSION['truncWidth'][$this->course->id]) ? $_SESSION['truncWidth'][$this->course->id] : ""; ?>";

// --- (end) read data from PHP -------------------------------------------------------------------- //

// --- find method for Arrays ---------------------------------------------------------------------- //
Array.prototype.find = function(labelElId) {
    var i;
    for (i = 0; i < this.length; i++) {
        if (this[i].labelElId == labelElId) {
            return this[i];
        }
    }
    
    return false;
}
// --- (end) find method for Arrays ---------------------------------------------------------------- //

// --- AJAX stuff ---------------------------------------------------------------------------------- //
function GetXmlHttpObject()
{
    var xmlHttp = null;
    try { // Firefox, Opera 8.0+, Safari
        xmlHttp = new XMLHttpRequest();
    } catch (e) { // Internet Explorer
        try {
            xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    return xmlHttp;
}

function serialize(trucated)
{
    var i;
    var data = '';
    for (i = 0; i < trucated.length; i++) {
        data += data.length > 0 ? '||' : '';
        data += trucated[i].labelElId + '|' + trucated[i].length + '|' + trucated[i].append;
    }
    
    return data;
}

function unserialize(data)
{
    if (data.length == 0) {
        return new Array();
    }

    var i;
    var truncated  = new Array();
    var objs = data.split("||");
    for (i = 0; i < objs.length; i++) {
        var vals = objs[i].split('|');
        
        truncObj = new Object();
        truncObj.labelElId  = vals[0];
        truncObj.length     = vals[1];
        truncObj.append     = vals[2] == 'true' ? true : false;
        truncated.push(truncObj);
    }
    
    return truncated;
}

function saveTrunc(truncated)
{
    var xmlHttp = GetXmlHttpObject();
    var url = "<?php echo $CFG->wwwroot; ?>/blocks/course_menu/saveTrunc.php";
    url += "?truncated="  + serialize(truncated);
    url += "&truncWidth=" + truncWidth;
    url += "&courseId=" + <?php echo $this->course->id; ?>;
    xmlHttp.open("GET", url, false);
    xmlHttp.send(null);
}

function saveState(action, label)
{
    var xmlHttp = GetXmlHttpObject();
    var url = "<?php echo $CFG->wwwroot; ?>/blocks/course_menu/ajax.php";
    url += "?element_name="  + label;
    url += "&action=" + action;
    url += "&courseId=" + <?php echo $this->course->id; ?>;
    xmlHttp.open("GET", url, false);
    xmlHttp.send(null);
}
// --- (end) AJAX stuff ---------------------------------------------------------------------------- //

//setup the TreeView control:
var tree = new YAHOO.widget.TreeView("treeDiv");
tree.subscribe("expandComplete", function(node) {
    saveState("add", node.label);
    cropAll(tree.getRoot(), 0);
    truncateAll(tree.getRoot(), 0);
    saveTrunc(truncated, truncWidth);
    changeHrefs();
});

tree.subscribe("collapseComplete", function(node) {
    saveState("remove", node.label);
});

var messagesNode;

tree.subscribe("labelClick", function(node1) {
	var idx = -1;
	for (i = 0; i < links.length; i++) {
		if (links[i].node == node1) {
			idx = i;
		}
	}
	if (idx != -1) {
		// so it is a custom link node
		if (links[idx].target == "_blank") {
			window.open(links[idx].url, links[idx].name, 'resizable=' + links[idx].allowresize + ',scrollbars=' + links[idx].allowscroll + ',directories=' + links[idx].showdirectorylinks + ',location=' + links[idx].showlocationbar + ',menubar=' + links[idx].showmenubar + ',toolbar=' + links[idx].showtoolbar + ',status=' + links[idx].showstatusbar + ',width=' + links[idx].defaultwidth + ',height=' + links[idx].defaultheight);
			return false;
		} else {
			// same window
			if (links[idx].keeppagenavigation == "1") {
				window.location = wwwroot + "/blocks/course_menu/link_with_navigation.php?courseid=" + courseid + "&name=" + links[idx].name + "&url=" + links[idx].url;
				return false;
			}
		}
	}

	if (node1 == messagesNode) {
		openpopup(node1.data.href, 'message', 'menubar=0,location=0,scrollbars,status,resizable,width=400,height=500', 0);
		return false; 
	}
    
});

function draw() {
    //get a reference to the root node; all
    //top level nodes are children of the root node:
    var rootNode = tree.getRoot();
    
    var i;
    for (i = 0; i < elements.length; i++) {
    	if (elements[i].id == "tree") {
    		n = 0;
            j = 0;
    		for (j = 0; j < chapters.length; j++) {
                
                // add chapter node
    			if (chapEnable == 1) {
        			
    				var obj = { label: chapters[j].name, href: ""};
                    if (j == currentChap && !chapters[j].expanded) {
                        saveState("add", chapters[j].name);
                    }
    				var isOpen = (j == currentChap) || chapters[j].expanded;
                    var chapRoot = new YAHOO.widget.TextNode(obj, rootNode, isOpen);
                    txtObj = new Object();
                    txtObj.labelElId = chapRoot.labelElId;
                    txtObj.txt = chapters[j].name;
                    originalTxts.push(txtObj);
                    
                    // check if we already have truncated this node
                    truncObj = truncated.find(chapRoot.labelElId);
                    if (!(truncObj === false)) {
                        chapRoot.label  = chapRoot.label.substring(0, truncObj.length);
                        chapRoot.label += (truncObj.append ? '...' : '' ); 
                    }
    			}
                for (l = 0; l < chapters[j].childElements.length; l++) {
                    
                    if (subChapEnable == 1) {
                        if (chapters[j].childElements[l].type == "subchapter") {
                            var obj = { label: chapters[j].childElements[l].name, href: ""};
                            if ((l == currentSubChap && j == currentChap) && !chapters[j].childElements[l].expanded) {
                                saveState("add", chapters[j].childElements[l].name);
                            }
                            var isSubOpen = (l == currentSubChap && j == currentChap) || chapters[j].childElements[l].expanded;
                            var subChapRoot = new YAHOO.widget.TextNode(obj, chapRoot, isSubOpen);

                            txtObj = new Object();
                            txtObj.labelElId = subChapRoot.labelElId;
                            txtObj.txt = chapters[j].childElements[l].name;
                            originalTxts.push(txtObj);

                            // check if we already have truncated this node
                            truncObj = truncated.find(subChapRoot.labelElId);
                            if (!(truncObj === false)) {
                                subChapRoot.label  = subChapRoot.label.substring(0, truncObj.length);
                                subChapRoot.label += (truncObj.append ? '...' : '' );
                            }
                        } else {
                            
                            var obj = { label: sections[n].name, title: sections[n].name, href:sections[n].url.replace(/&/, '&amp;'), onclick:addClassName(this) };
                            var root = chapEnable == 1 ? chapRoot : rootNode;
                            var secRoot = new YAHOO.widget.TextNode(obj, root, false);

                            txtObj = new Object();
                            txtObj.labelElId = secRoot.labelElId;
                            txtObj.txt = sections[n].name;
                            txtObj.txt = txtObj.txt.replace(/_/, '"');

                            originalTxts.push(txtObj);

                            // check if we already have truncated this node
                            truncObj = truncated.find(secRoot.labelElId);
                            if (!(truncObj === false)) {
                                secRoot.label = secRoot.label.substring(0, truncObj.length);
                                secRoot.label += (truncObj.append ? '...' : '' );
                            }

                            if (n+1 == displaySection && highlight) {
                                secRoot.labelStyle += " selectedTopicWeek";
                            }

                            if (sections[n].visible == 0) {
                                secRoot.labelStyle += " hiddenTopicWeek";
                            }

                            if (expandableTree.enable == 1) {
                                for (xx = 0; xx < sections[n].resources.length; xx++) {
                                    var obj  = { label: sections[n].resources[xx].name, href: sections[n].resources[xx].url.replace(/&/, '&amp;') };

                                    var node = new YAHOO.widget.TextNode(obj, secRoot, false);
                                    node.labelStyle  = sections[n].resources[xx].iconClass;

                                    txtObj = new Object();
                                    txtObj.labelElId = node.labelElId;
                                    txtObj.txt = sections[n].resources[xx].name;

                                    originalTxts.push(txtObj);

                                    // check if we already have truncated this node
                                    truncObj = truncated.find(node.labelElId);
                                    if (!(truncObj === false)) {
                                        node.label = node.label.substring(0, truncObj.length);
                                        node.label += (truncObj.append ? '...' : '' );
                                    }

                                }
                            }
                            n++;
                        }
                    }
                    if (chapters[j].childElements[l].type == "subchapter") {
                        for (k = 0; k < chapters[j].childElements[l].count; k++) {
                            // add a section (Topic/Week)

                            var obj = { label: sections[n].name, title: sections[n].name, href:sections[n].url.replace(/&/, '&amp;') };
                            var root = chapEnable == 1 ? (subChapEnable == 1 ? subChapRoot : chapRoot) : rootNode;
                            var secRoot = new YAHOO.widget.TextNode(obj, root, false);

                            txtObj = new Object();
                            txtObj.labelElId = secRoot.labelElId;
                            txtObj.txt = sections[n].name;
                            txtObj.txt = txtObj.txt.replace(/_/, '"');

                            originalTxts.push(txtObj);

                            // check if we already have truncated this node
                            truncObj = truncated.find(secRoot.labelElId);
                            if (!(truncObj === false)) {
                                secRoot.label = secRoot.label.substring(0, truncObj.length);
                                secRoot.label += (truncObj.append ? '...' : '' );
                            }

                            if (n+1 == displaySection && highlight) {
                                secRoot.labelStyle += " selectedTopicWeek";
                            }

                            if (sections[n].visible == 0) {
                                secRoot.labelStyle += " hiddenTopicWeek";
                            }

                            if (expandableTree.enable == 1) {
                                for (ll = 0; ll < sections[n].resources.length; ll++) {
                                    var obj  = { label: sections[n].resources[ll].name, href: sections[n].resources[ll].url.replace(/&/, '&amp;') };

                                    var node = new YAHOO.widget.TextNode(obj, secRoot, false);
                                    node.labelStyle  = sections[n].resources[ll].iconClass;

                                    txtObj = new Object();
                                    txtObj.labelElId = node.labelElId;
                                    txtObj.txt = sections[n].resources[ll].name;

                                    originalTxts.push(txtObj);

                                    // check if we already have truncated this node
                                    truncObj = truncated.find(node.labelElId);
                                    if (!(truncObj === false)) {
                                        node.label = node.label.substring(0, truncObj.length);
                                        node.label += (truncObj.append ? '...' : '' );
                                    }

                                }
                            }

                            n++;
                        }
                    }
                }
    		}
    	} else {
        	
    		if (elements[i].visible == 1) {
    			if (elements[i].id.substring(0,4) == "link") {
    				linkIdx = parseInt(elements[i].id.substring(4));
    				var obj  = { label:links[linkIdx].name, href:links[linkIdx].url.replace(/&/, '&amp;'), target:links[linkIdx].target };
    				var node = new YAHOO.widget.TextNode(obj, rootNode, false);
    				node.labelStyle  = links[linkIdx].iconClass;
    
                    txtObj = new Object();
                    txtObj.labelElId = node.labelElId;
                    txtObj.txt = links[linkIdx].name;
                    originalTxts.push(txtObj);
                    
                    // check if we already have truncated this node 
                    truncObj = truncated.find(node.labelElId);
                    if (!(truncObj === false)) {
                        node.label = node.label.substring(0, truncObj.length);
                        node.label += (truncObj.append ? '...' : '' );
                    }
    			} else {
    				var obj  = { label:elements[i].name, title:elements[i].name, href:elements[i].url.replace(/&/, '&amp;') };
    				var node = new YAHOO.widget.TextNode(obj, rootNode, false);
    				node.labelStyle = elements[i].iconClass;
                    
                    txtObj = new Object();
                    txtObj.labelElId = node.labelElId;
                    txtObj.txt = elements[i].name;
                    originalTxts.push(txtObj);
                    
    				// save node for 'messages'
    				if (elements[i].id == 'messages') {
    					messagesNode = node;
    				}
    				 
                    // check if we already have truncated this node 
                    truncObj = truncated.find(node.labelElId);
                    if (!(truncObj === false)) {
                        node.label = node.label.substring(0, truncObj.length);
                        node.label += (truncObj.append ? '...' : '' );
                    }
    			}
    		}
    	}
    }

}

var goodLength = new Array();

function cropAll(el, level)
{
    if ((typeof(el.children) != "undefined") && (el != null)) {
        var ch = el.children;
        if ((ch.length > 0) && (typeof(ch.length) != "undefined")) {
            var i;
            for (i = 0; i < ch.length; i++) {
                var toTrunc = document.getElementById(ch[i].labelElId);
                if (toTrunc != null) {
                    if ((toTrunc.title == "") || (toTrunc.title == "undefined") || (toTrunc.title == null)) {
                        toTrunc.title = originalTxts.find(ch[i].labelElId).txt;
                    }
                    if (truncated.find(ch[i].labelElId) === false) {
                        toTrunc.childNodes[0].nodeValue = '.';
                    }
                }
                cropAll(ch[i], level+1);
            }
        }
    }
}

function truncateString(el, level)
{
    var txt;

    var okH = el.parentNode.offsetHeight;
    var okW = el.parentNode.offsetWidth;
    
    if ((okH > 0) && (okW > 0)) {

        el.childNodes[0].nodeValue = el.title;
        if ((el.offsetHeight > okH) || (el.offsetWidth > okW)) {
            txt = el.title;
            el.childNodes[0].nodeValue = txt;

            if ((goodLength[level] == "") || (goodLength[level] == "undefined") || (goodLength[level] == null)) {
                var i = 0;
                el.childNodes[0].nodeValue = txt.substring(0, i+1) + '...';
                while ((el.offsetHeight <= okH) && (el.offsetWidth <= okW) && (i < txt.length)) {
                    i++;
                    el.childNodes[0].nodeValue = txt.substring(0, i+1) + '...';
                }
                el.childNodes[0].nodeValue = txt.substring(0, i) + '...';
                goodLength[level] = i;

            } else {
                var i = goodLength[level];
                el.childNodes[0].nodeValue = txt.substring(0, i) + '...';
                while (((el.offsetHeight > okH) || (el.offsetWidth > okW)) && (i > 0)) {
                    i--;
                    el.childNodes[0].nodeValue = txt.substring(0, i) + '...';
                }
                goodLength[level] = i;
            }

            //if the font size is too small then the icons from the left of the tree nodes will be cut off
            //TODO: fix it - it makes IE onresize event to not work properly
            if (okH < 20) {
//                el.style.height = "20px";
//                el.style.paddingTop = ((20 - okH) / 2) + "px";
            }

            truncObj = new Object();
            truncObj.length = i;
            truncObj.append = true;
            return truncObj;
        }

        truncObj = new Object();
        truncObj.length = el.title.length;
        truncObj.append = false;
        return truncObj;
    }
    return false;
}

function truncateAll(el, level)
{
    if ((typeof(el.children) != "undefined") && (el != null)){
        var ch = el.children;
        if ((ch.length > 0) && (typeof(ch.length) != "undefined")) {
            var i;
            for (i = 0; i < ch.length; i++) {
                var toTrunc = document.getElementById(ch[i].labelElId);
                if (toTrunc != null) {
                    if (truncated.find(ch[i].labelElId) === false) {
                        var truncObj = truncateString(toTrunc, level);
                        if (!(truncObj === false)) {
                            truncObj.labelElId  = ch[i].labelElId;
                            truncated.push(truncObj);
                        }
                    }
                }
                truncateAll(ch[i], level+1);
            }
        }
    }
}

function addLoadEvent(func) 
{
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

function addResizeEvent(func) 
{
    var oldonresize = window.onresize;
    if (typeof window.onresize != 'function') {
        window.onresize = func;
    } else {
        window.onresize = function() {
            if (oldonresize) {
                oldonresize();
            }
            func();
        }
    }
}

var b = h = null;
var oldData = '';

var onLoadTruncateExecuted = false;
var loadTimer;
loadTimer = setTimeout("onLoadTruncate()", 30000); // in 30 seconds the truncate function will fire, in case the page never ends loading

// when all the elements were loaded truncate the names
function onLoadTruncate() 
{
    h = document.getElementsByTagName('html')[0];
    b = document.getElementsByTagName('body')[0];
    if (!onLoadTruncateExecuted) {
        draw();
        //the tree won't show up until you draw (render) it:
        tree.draw();
        onLoadTruncateExecuted = true;
        clearTimeout(loadTimer);

        var tempData = h.offsetWidth + ' ' + h.clientWidth + ' ' + b.offsetWidth + ' ' + b.clientWidth;

        oldData = tempData;

        for (i = 0; i < goodLength.length; i++) {
            goodLength[i] = "";
        }
        truncated  = new Array();
        truncWidth = document.getElementById('treeDiv').offsetWidth + '|' + document.getElementById('treeDiv').clientWidth;
        saveTrunc(truncated, truncWidth);

        cropAll(tree.getRoot(), 0);
        truncateAll(tree.getRoot(), 0);
        saveTrunc(truncated, truncWidth);
  
    }
}
addLoadEvent(onLoadTruncate);

//change href-s for the folders a in the course menu block
function changeHrefs()
{
    var hyperlinks = getElementsByClassName(document, "a", "ygtvspacer");
	for (i = 0; i < hyperlinks.length; i++) {
		hyperlinks[i].onclick = function () { return false; };
	}
    if (addClassToLinks) {
        hyperlinks = getElementsByClassName(document.getElementById('treeDiv'), "a", "ygtvlabel");
        for (i = 0; i < hyperlinks.length; i++) {
            hyperlinks[i].onclick = function () {
                removeClassNames();
                YAHOO.util.Dom.addClass(this, "selectedTopicWeek");
            };
        }
    }
}

function removeClassNames()
{
    var elems = getElementsByClassName(document.getElementById("treeDiv"), "a", "selectedTopicWeek");
    for (i = 0; i < elems.length; i++) {
        YAHOO.util.Dom.removeClass(elems[i], "selectedTopicWeek");
    }
}

addLoadEvent(changeHrefs);

function getInternetExplorerVersion() {
    var rv = -1; // Return value assumes failure.
    if (navigator.appName == 'Microsoft Internet Explorer') {
        var ua = navigator.userAgent;
        var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null)
            rv = parseFloat(RegExp.$1);
    }
    return rv;
}

function checkVersion() {
    var msg = "You're not using Windows Internet Explorer.";
    var ver = getInternetExplorerVersion();
    if (ver >= 8.0) {
        updateTableRowsIE8();
    }
}

function updateTableRowsIE8()
{
    var trs = getElementsByClassName(document, "tr", "section");
    var td;
    for (i = 0; i < trs.length; i++) {
        idName = trs[i].id;
        td = trs[i].firstChild;
        a = document.createElement("a");
        a.setAttribute("name", idName);
        td.appendChild(a);
    }
}

addLoadEvent(checkVersion);
// when the window resizes truncate the names
//setTimeout(
addResizeEvent(function() {
    h = document.getElementsByTagName('html')[0];
    b = document.getElementsByTagName('body')[0];

    var tempData = h.offsetWidth + ' ' + h.clientWidth + ' ' + b.offsetWidth + ' ' + b.clientWidth;
    if (oldData != tempData) {
        
        oldData = tempData;
        
        for (i = 0; i < goodLength.length; i++) {
            goodLength[i] = "";
        }
        truncated  = new Array();
        truncWidth = document.getElementById('treeDiv').offsetWidth + '|' + document.getElementById('treeDiv').clientWidth;
        saveTrunc(truncated, truncWidth);
        
        cropAll(tree.getRoot(), 0);
        truncateAll(tree.getRoot(), 0);
        saveTrunc(truncated, truncWidth);
    }
});

function addClassName(aElem)
{
    //Have no idea what this should do
}
//]]>
--></script>
