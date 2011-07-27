/* CSS Document */

a.selectedTopicWeek:link, a.selectedTopicWeek:active, a.selectedTopicWeek:visited, a.selectedTopicWeek:hover {
	background: #BBB;
	color: black;
}

a.hiddenTopicWeek:link, a.hiddenTopicWeek:active, a.hiddenTopicWeek:visited, a.hiddenTopicWeek:hover {
    color: #AAAAAA;
}

#treeDiv a {
	display: block;
	height: auto; /* imnportant! do not change! use padding-bottom instead */
	padding-bottom: 3px;
}

#treeDiv a:hover {
    text-decoration: none;
}

.webfx-tree-container {
	margin: 0px;
	padding: 0px;
	white-space: nowrap;
}

.webfx-tree-item {
	padding: 2px;
	margin: 4px;
	white-space: nowrap;
	height: 14px;
}

.webfx-tree-item a, .webfx-tree-item a:active, .webfx-tree-item a:hover, .webfx-tree-item a:visited {
	margin-left: 3px;
	padding: 0px 2px 1px 2px;
}

.webfx-tree-item img {
	vertical-align: middle;
	border: 0px;
}

.webfx-tree-icon {

}



#linksEnableContainer, #elementsContainer, #chapEnableContainer {
}

#elementsContainer tr td {
	padding: 15px 0 0 2px;
}

#elementsContainer tr td img, 
#expandableTreeContainer tr td img,
#chapEnableContainer tr td img,
#chaptersContainer tr td img {
	margin-right: 5px;
}

#chaptersContainer tr td input {
	margin-right: 10px; 
}

.elementsFirstTd {
	width: 200px;
}

.expandableTreeTd {
	width: 260px;
}

.linkMsg {
	font-size: 0.8em;
	width: 200px;
}

/* YAHOO tree css */

/* first or middle sibling, no children */
.ygtvtn { background: url(<?php echo $CFG->wwwroot.'/blocks/course_menu/icons/tree'; ?>/tn.gif) 0 0 no-repeat; width:17px; height:22px; }

/* first or middle sibling, collapsable */
.ygtvtm { background: url(<?php echo $CFG->wwwroot.'/blocks/course_menu/icons/tree'; ?>/tm.gif) 0 0 no-repeat; width:34px; height:22px; cursor:pointer }

/* first or middle sibling, collapsable, hover */
.ygtvtmh { background: url(<?php echo $CFG->wwwroot.'/blocks/course_menu/icons/tree'; ?>/tmh.gif) 0 0 no-repeat; width:34px; height:22px; cursor:pointer }

/* first or middle sibling, expandable */
.ygtvtp { background: url(<?php echo $CFG->wwwroot.'/blocks/course_menu/icons/tree'; ?>/tp.gif) 0 0 no-repeat; width:34px; height:22px; cursor:pointer }

/* first or middle sibling, expandable, hover */
.ygtvtph { background: url(<?php echo $CFG->wwwroot.'/blocks/course_menu/icons/tree'; ?>/tph.gif) 0 0 no-repeat; width:34px; height:22px; cursor:pointer }

/* last sibling, no children */
.ygtvln { background: url(<?php echo $CFG->wwwroot.'/blocks/course_menu/icons/tree'; ?>/ln.gif) 0 0 no-repeat; width:17px; height:22px; }

/* Last sibling, collapsable */
.ygtvlm { background: url(<?php echo $CFG->wwwroot.'/blocks/course_menu/icons/tree'; ?>/lm.gif) 0 0 no-repeat; width:34px; height:22px; cursor:pointer }

/* Last sibling, collapsable, hover */
.ygtvlmh { background: url(<?php echo $CFG->wwwroot.'/blocks/course_menu/icons/tree'; ?>/lmh.gif) 0 0 no-repeat; width:34px; height:22px; cursor:pointer }

/* Last sibling, expandable */
.ygtvlp { background: url(<?php echo $CFG->wwwroot.'/blocks/course_menu/icons/tree'; ?>/lp.gif) 0 0 no-repeat; width:34px; height:22px; cursor:pointer }

/* Last sibling, expandable, hover */
.ygtvlph { background: url(<?php echo $CFG->wwwroot.'/blocks/course_menu/icons/tree'; ?>/lph.gif) 0 0 no-repeat; width:34px; height:22px; cursor:pointer }

/* Loading icon */
.ygtvloading { background: url(<?php echo $CFG->wwwroot.'/blocks/course_menu/icons/tree'; ?>/loading.gif) 0 0 no-repeat; width:16px; height:22px; }

/* the style for the empty cells that are used for rendering the depth 
 * of the node */
.ygtvdepthcell { background: url(<?php echo $CFG->wwwroot.'/blocks/course_menu/icons/tree'; ?>/vline.gif) 0 0 no-repeat; width:17px; height:22px; }

.ygtvblankdepthcell { width:17px; height:22px; }

/* the style of the div around each node */
.ygtvitem { }  

.ygtvitem  table{
    margin-bottom:0;
    width: 100%;
}
.ygtvitem  td {
    border:none;padding:0;
} 

/* the style of the div around each node's collection of children */
.ygtvchildren { }  
* html .ygtvchildren { height:1%; }  

/* the style of the text label in ygTextNode */
.ygtvlabel, .ygtvlabel:link, .ygtvlabel:visited, .ygtvlabel:hover { 
	margin-left:2px;
	text-decoration: none;
}

.links td {
	padding: 5px;
}

.links label {
	display: block;
	float: left;
	width: 70px;
}

.divRight {
	display: block;
	width: 460px;
	text-align: right;
}

.iconLabel {
	display: block;
	float: left;
	width: 50px;
	padding-left: 30px;
}

.links input {
	float: left;
	width: 380px;
}

.links select {
	float: left;
}

#linksContainer input {
	margin-right: 10px;
}

#chaptersTableContainer table tbody tr td {
	border: 1px solid #000;
}


