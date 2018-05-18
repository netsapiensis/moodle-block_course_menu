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

M.block_course_menu = M.block_course_menu || {
    /** The number of expandable branches in existence */
    expandablebranchcount:0,
    /** An array of initialised trees */
    treecollection:[],
    initDone: false,
    /**
     * Will contain all of the classes for the navigation blocks
     * @namespace
     */
    classes:{},
    courselimit : 20,
    /**
     * This function gets called when the module is first loaded as required by
     * the YUI.add statement at the bottom of the page.
     *
     * NOTE: This will only be executed ONCE
     * @function
     */
    init:function(Y) {

    	M.core_dock.init(Y);
	    if (M.core_dock.genericblock) {
            // Give the tree class the dock block properties
	        Y.augment(M.block_course_menu.classes.tree, M.core_dock.genericblock);
			//adjust the title to fit the content ?

	    }
    },
    /**
     * Add new instance of navigation tree to tree collection
     */
    init_add_tree:function(Y, id, properties) {
        this.bg_color = properties.bg_color;
    	if (properties.courselimit) {
            this.courselimit = properties.courselimit;
        }
    	M.block_course_menu.treecollection[id] = new M.block_course_menu.classes.tree(Y, id, properties);
    }
};

/**
 * @class tree
 * @constructor
 * @base M.core_dock.genericblock
 * @param {YUI} Y A yui instance to use with the navigation
 * @param {string} id The name of the tree
 * @param {object} properties Object containing tree properties
 */
M.block_course_menu.classes.tree = function(Y, id, properties) {
	this.Y = Y;
    this.id = id;
    this.key = id;
    this.errorlog = [];
    this.ajaxbranches = 0;
    this.expansions = [];
    this.instance = id;
    this.cachedcontentnode = null;
    this.cachedfooter = null;
    this.position = 'block';
    this.skipsetposition = false;
    this.candock = false;
    this.docked = properties.docked

    var _bg = properties.bg_color;
    M.core_dock.on('dock:itemadded', function () {
        var _items = this.items;
        for (var i = 0; i < _items.length; i++) {
            if (_items[i] && typeof _items[i] == "object" && _items[i].blockclass == 'block_course_menu') {
                var _current = _items[i];
                var ttl = _current.nodes.docktitle;
                ttl.setStyle("background", "none").setStyle("backgroundColor", _bg);
            }
        }
    });

    if (properties.expansions) {
        this.expansions = properties.expansions;
    }
    if (properties.instance) {
        this.instance = properties.instance;
    }
    if (properties.candock) {
        this.candock = true;
    }

    var node = this.Y.one('#inst'+this.id);

    // Can't find the block instance within the page
    if (node === null) {
        return;
    }


    // Attach event to toggle expansion
    node.all('.tree_item.branch').on('click', this.toggleexpansion , this);
    var uri = location.href;
    var section = '';
    var sectionLinks = Y.all("a.section_link");

    section = uri.split("#")[1];
    sectionLinks.each (function (v) {
        if(v.getAttribute("href") == "#" + section) {
            v.get('parentNode').addClass("active_tree_node");
            v.get('parentNode').get('parentNode').addClass("current_branch");
            Y.one("#showonlysection_nr").set('text', section.split("-")[1]);
            var newHref = "";
            var pp = Y.one("#showallsections").getAttribute('href').split("=");
            pp.pop();
            for (var x in pp) {
                newHref += pp[x] + "=";
            }
            newHref += section.split("-")[1];
            Y.one("#showallsections").setAttribute('href', newHref);
        }
    });
    sectionLinks.on('click', function (e) {
        var v = e.currentTarget;
        if (v.getAttribute("href").indexOf("#") != -1) {
            section = v.getAttribute("href");
            Y.all(".active_tree_node").each (function (v) {
               v.removeClass("active_tree_node");
               v.get('parentNode').removeClass("current_branch");
            });
            v.get('parentNode').addClass("active_tree_node");
            v.get('parentNode').get('parentNode').addClass("current_branch");
            Y.one("#showonlysection_nr").set('text', section.split("-")[1]);
            var newHref = "";
            var pp = Y.one("#showallsections").getAttribute('href').split("=");
            pp.pop();
            for (var x in pp) {
                newHref += pp[x] + "=";
            }
            newHref += section.split("-")[1];
            Y.one("#showallsections").setAttribute('href', newHref);
        }
    });
    // Attach events to expand by AJAX
    //var expandablenode;
    for (var i in this.expansions) {
    	var expandablenode = Y.one('#'+this.expansions[i].id);
        if (expandablenode) {
            expandablenode.on('ajaxload|click', this.init_load_ajax, this, this.expansions[i]);
            M.block_course_menu.expandablebranchcount++;
        } else if (M.cfg.debug) {
            Y.one(document.body).append(Y.Node.create('<div class="notification" style="font-size:6pt;">Expandable node within navigation was missing [#'+this.expansions[i].id+']</div>'));
        } else {
            // Failing over silently
        }
    }

    if (node.hasClass('block_js_expansion')) {
        node.on('mouseover', function(e){this.toggleClass('mouseover');}, node);
        node.on('mouseout', function(e){this.toggleClass('mouseover');}, node);
    }
};

/**
 * Loads a branch via AJAX
 *
 * @param {event} e The event object
 * @param {object} branch A branch to load via ajax
 */
M.block_course_menu.classes.tree.prototype.init_load_ajax = function(e, branch) {
    e.stopPropagation();
    var target = e.target;
    if (target.test('span')) {
        target = target.ancestor('p');
    }
    if (!target || !target.test('p')) {
        return true;
    }
    var cfginstance = '', Y = this.Y;
    if (this.instance != null) {
        cfginstance = '&instance='+this.instance
    }
    Y.io(M.cfg.wwwroot+'/lib/ajax/getnavbranch.php', {
        method:'POST',
        data:'elementid='+branch.id+'&id='+branch.branchid+'&type='+branch.type+'&sesskey='+M.cfg.sesskey+cfginstance,
        on: {
            complete:this.load_ajax,
            success:function() {Y.detach('click', this.init_load_ajax, target);}
        },
        context:this,
        arguments:{
            target:target
        }
    });
    return true;
};

/**
 * Takes an branch provided through ajax and loads it into the tree
 * @param {int} tid The transaction id
 * @param {object} outcome
 * @param {mixed} args
 * @return bool
 */
M.block_course_menu.classes.tree.prototype.load_ajax = function(tid, outcome, args) {
    try {
        var object = this.Y.JSON.parse(outcome.responseText);
        if (this.add_branch(object, args.target.ancestor('li') ,1)) {
            if (this.candock) {
                M.core_dock.resize();
            }
            return true;
        }
    } catch (e) {
        // If we got here then there was an error parsing the result
    }
    // The branch is empty so class it accordingly
    args.target.replaceClass('branch', 'emptybranch');
    return true;
};

/**
 * Adds a branch into the tree provided with some XML
 * @param {object} branchobj
 * @param {Y.Node} target
 * @param {int} depth
 * @return bool
 */
M.block_course_menu.classes.tree.prototype.add_branch = function(branchobj, target, depth) {

    // Make the new branch into an object
    var branch = new M.block_course_menu.classes.branch(this, branchobj);
    var childrenul = false, Y = this.Y;
    if (depth === 1) {
        if (!branch.children) {
            return false;
        }
        childrenul = Y.Node.create('<ul></ul>');
        target.appendChild(childrenul);
    } else {
        childrenul = branch.inject_into_dom(target);
    }
    if (childrenul) {
        var count = 0;
        for (var i in branch.children) {
            // Add each branch to the tree
            if (branch.children[i].type == 20) {
                count++;
            }
            if (typeof(branch.children[i])=='object') {
                this.add_branch(branch.children[i], childrenul, depth+1);
            }
        }
        if (branch.type == 10 && count >= M.block_course_menu.courselimit) {
            var properties = Array();
            properties['name'] = M.str.moodle.viewallcourses;
            properties['title'] = M.str.moodle.viewallcourses;
            properties['link'] = M.cfg.wwwroot+'/course/category.php?id='+branch.key;
            properties['haschildren'] = false;
            properties['icon'] = {'pix':"i/navigationitem",'component':'moodle'};
            this.add_branch(properties, childrenul, depth+1);
        }
    }
    return true;
};
/**
 * Toggle a branch as expanded or collapsed
 * @param {Event} e
 */
M.block_course_menu.classes.tree.prototype.toggleexpansion = function(e) {
    // First check if they managed to click on the li iteslf, then find the closest
    // LI ancestor and use that

    if (e.target.get('nodeName').toUpperCase() == 'A') {
        // A link has been clicked don't fire any more events just do the default.
        e.stopPropagation();
        return;
    }

    var target = e.target;
    if (!target.test('li')) {
        target = target.ancestor('li')
    }

    if (target && !target.hasClass('depth_1')) {
        target.toggleClass('collapsed');
    }

    if (this.candock) {
        M.core_dock.resize();
    }

    var act = "add";
    if (target.hasClass('collapsed')) {
    	act = "remove";
    }
    var elName = target.one('.item_name').get('innerHTML');
    var Y = this.Y;
    var instId = this.id;
    Y.io(M.cfg.wwwroot + '/blocks/course_menu/ajax.php', {
        method:'POST',
        data:'element_name='+elName+'&action='+act+'&instance='+instId,
        on: {
            success:function() {  }
        },
        context:this
    });

};

/**
 * This class represents a branch for a tree
 * @class branch
 * @constructor
 * @param {M.block_course_menu.classes.tree} tree
 * @param {object|null} obj
 */
M.block_course_menu.classes.branch = function(tree, obj) {
    this.tree = tree;
    this.name = null;
    this.title = null;
    this.classname = null;
    this.id = null;
    this.key = null;
    this.type = null;
    this.link = null;
    this.icon = null;
    this.expandable = null;
    this.expansionceiling = null;
    this.hidden = false;
    this.haschildren = false;
    this.children = false;
    if (obj !== null) {
        // Construct from the provided xml
        this.construct_from_json(obj);
    }
};
/**
 * Populates this branch from a JSON object
 * @param {object} obj
 */
M.block_course_menu.classes.branch.prototype.construct_from_json = function(obj) {
    for (var i in obj) {
        this[i] = obj[i];
    }
    if (this.children && this.children.length > 0) {
        this.haschildren = true;
    } else {
        this.children = [];
    }
    if (this.id && this.id.match(/^expandable_branch_\d+$/)) {
        // Assign a new unique id for this new expandable branch
        M.block_course_menu.expandablebranchcount++;
        this.id = 'expandable_branch_'+M.block_course_menu.expandablebranchcount;
    }
};
/**
 * Injects a branch into the tree at the given location
 * @param {element} element
 */
M.block_course_menu.classes.branch.prototype.inject_into_dom = function(element) {

    var Y = this.tree.Y;

    var isbranch = ((this.expandable !== null || this.haschildren) && this.expansionceiling===null);
    var branchli = Y.Node.create('<li></li>');
    var branchp = Y.Node.create('<p class="tree_item"></p>');

    if (isbranch) {
        branchli.addClass('collapsed');
        branchli.addClass('contains_branch');
        branchp.addClass('branch');
        branchp.on('click', this.tree.toggleexpansion, this.tree);
        if (this.expandable) {
            branchp.on('ajaxload|click', this.tree.init_load_ajax, this.tree, {branchid:this.key,id:this.id,type:this.type});
        }
    }

    if (this.myclass !== null) {
        branchp.addClass(this.myclass);
    }
    if (this.id !== null) {
        branchp.setAttribute('id', this.id);
    }

    // Prepare the icon, should be an object representing a pix_icon
    var branchicon = false;
    if (this.icon != null && (!isbranch || this.type == 40)) {
        branchicon = Y.Node.create('<img alt="" />');
        branchicon.setAttribute('src', M.util.image_url(this.icon.pix, this.icon.component));
        branchli.addClass('item_with_icon');
        if (this.icon.alt) {
            branchicon.setAttribute('alt', this.icon.alt);
        }
        if (this.icon.title) {
            branchicon.setAttribute('alt', this.icon.title);
        }
        if (this.icon.classes) {
            for (var i in this.icon.classes) {
                branchicon.addClass(this.icon.classes[i]);
            }
        }
    }

    if (this.link === null) {
        if (branchicon) {
            branchp.appendChild(branchicon);
        }
        branchp.append(this.name.replace(/\n/g, '<br />'));
    } else {
        var branchlink = Y.Node.create('<a title="'+this.title+'" href="'+this.link+'"></a>');
        if (branchicon) {
            branchlink.appendChild(branchicon);
        }
        branchlink.append(this.name.replace(/\n/g, '<br />'));
        if (this.hidden) {
            branchlink.addClass('dimmed');
        }
        branchp.appendChild(branchlink);
    }

    branchli.appendChild(branchp);
    if (this.haschildren) {
        var childrenul = Y.Node.create('<ul></ul>');
        branchli.appendChild(childrenul);
        element.appendChild(branchli);
        return childrenul
    } else {
        element.appendChild(branchli);
        return false;
    }
};

/**
 * Causes the course menu block module to initalise the first time the module
 * is used!
 *
 * NOTE: Never convert the second argument to a function reference...
 * doing so causes scoping issues
 */
YUI.add('block_course_menu', function(Y){M.block_course_menu.init(Y);}, '0.0.0.1', M.yui.loader.modules.block_course_menu.requires);