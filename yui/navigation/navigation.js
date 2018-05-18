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

YUI.add('moodle-block_course_menu-navigation', function (Y) {

    /**
     * A 'actionkey' Event to help with Y.delegate().
     * The event consists of the left arrow, right arrow, enter and space keys.
     * More keys can be mapped to action meanings.
     * actions: collapse , expand, toggle, enter.
     *
     * This event is delegated to branches in the navigation tree.
     * The on() method to subscribe allows specifying the desired trigger actions as JSON.
     *
     * Todo: This could be centralised, a similar Event is defined in blocks/dock.js
     */
    Y.Event.define("actionkey", {
        // Webkit and IE repeat keydown when you hold down arrow keys.
        // Opera links keypress to page scroll; others keydown.
        // Firefox prevents page scroll via preventDefault() on either
        // keydown or keypress.
        _event: (Y.UA.webkit || Y.UA.ie) ? 'keydown' : 'keypress',

        _keys: {
            //arrows
            '37': 'collapse',
            '39': 'expand',
            //(@todo: lrt/rtl/M.core_dock.cfg.orientation decision to assign arrow to meanings)
            '32': 'toggle',
            '13': 'enter'
        },

        _keyHandler: function (e, notifier, args) {
            if (!args.actions) {
                var actObj = {collapse: true, expand: true, toggle: true, enter: true};
            } else {
                var actObj = args.actions;
            }
            if (this._keys[e.keyCode] && actObj[this._keys[e.keyCode]]) {
                e.action = this._keys[e.keyCode];
                notifier.fire(e);
            }
        },

        on: function (node, sub, notifier) {
            // subscribe to _event and ask keyHandler to handle with given args[0] (the desired actions).
            if (sub.args == null) {
                //no actions given
                sub._detacher = node.on(this._event, this._keyHandler, this, notifier, {actions: false});
            } else {
                sub._detacher = node.on(this._event, this._keyHandler, this, notifier, sub.args[0]);
            }
        },

        detach: function (node, sub, notifier) {
            //detach our _detacher handle of the subscription made in on()
            sub._detacher.detach();
        },

        delegate: function (node, sub, notifier, filter) {
            // subscribe to _event and ask keyHandler to handle with given args[0] (the desired actions).
            if (sub.args == null) {
                //no actions given
                sub._delegateDetacher = node.delegate(this._event, this._keyHandler, filter, this, notifier, {actions: false});
            } else {
                sub._delegateDetacher = node.delegate(this._event, this._keyHandler, filter, this, notifier, sub.args[0]);
            }
        },

        detachDelegate: function (node, sub, notifier) {
            sub._delegateDetacher.detach();
        }
    });

    var EXPANSIONLIMIT_EVERYTHING = 0,
        EXPANSIONLIMIT_COURSE = 20,
        EXPANSIONLIMIT_SECTION = 30,
        EXPANSIONLIMIT_ACTIVITY = 40;


    /**
     * Navigation tree class.
     *
     * This class establishes the tree initially, creating expandable branches as
     * required, and delegating the expand/collapse event.
     */
    var TREE = function (config) {
        TREE.superclass.constructor.apply(this, arguments);
    }
    TREE.prototype = {
        /**
         * The tree's ID, normally its block instance id.
         */
        id: null,
        /**
         * Initialise the tree object when its first created.
         */
        initializer: function (config) {
            this.id = config.id;

            var node = Y.one('#inst' + config.id);

            // Can't find the block instance within the page
            if (node === null) {
                return;
            }

            // Delegate event to toggle expansion
            var self = this;
            Y.delegate('click', function (e) {
                self.toggleExpansion(e);
            }, node.one('.block_tree'), '.tree_item.branch');
            Y.delegate('actionkey', function (e) {
                self.toggleExpansion(e);
            }, node.one('.block_tree'), '.tree_item.branch');
            Y.delegate('click', function (e) {
                self.toggleSectionExpansion(e);
            }, node.one('.block_tree'), 'a.section_link');
            // node.all('a.section_link').on('click', this.toggleSectionExpansion , this);
            // Gather the expandable branches ready for initialisation.
            var expansions = [];
            if (config.expansions) {
                expansions = config.expansions;
            } else if (window['navtreeexpansions' + config.id]) {
                expansions = window['navtreeexpansions' + config.id];
            }
            // Establish each expandable branch as a tree branch.
            for (var i in expansions) {
                new BRANCH({
                    tree: this,
                    branchobj: expansions[i],
                    overrides: {
                        expandable: true,
                        children: [],
                        haschildren: true
                    }
                }).wire();
                M.block_course_menu.expandablebranchcount++;
            }
        },
        /**
         * This is a callback function responsible for expanding and collapsing the
         * branches of the tree. It is delegated to rather than multiple event handles.
         */
        toggleExpansion: function (e) {
            // First check if they managed to click on the li iteslf, then find the closest
            // LI ancestor and use that

            if (e.target.test('a') && (e.keyCode == 0 || e.keyCode == 13)) {
                // A link has been clicked (or keypress is 'enter') don't fire any more events just do the default.
                e.stopPropagation();
                return;
            }

            // Makes sure we can get to the LI containing the branch.
            var target = e.target;
            if (!target.test('li')) {
                target = target.ancestor('li');
            }
            if (!target) {
                return;
            }
            // Toggle expand/collapse providing its not a root level branch.
            if (!target.hasClass('depth_1')) {
                if (e.type == 'actionkey') {
                    switch (e.action) {
                        case 'expand' :
                            target.removeClass('collapsed');
                            break;
                        case 'collapse' :
                            target.addClass('collapsed');
                            break;
                        default :
                            target.toggleClass('collapsed');
                    }
                    e.halt();
                } else {
                    target.toggleClass('collapsed');
                }
            }
            var isExpanded = !target.hasClass('collapsed');
            // console.log(target.get('children'));
            // console.log(target.all('ul'));
            // if(!isExpanded){
            //     target.all('ul').setStyle('display', 'none');
            // }else{
            //     target.all('ul').setStyle('display', 'block');
            // }
            target.set('aria-expanded', isExpanded);

            this.recordEvent(target, isExpanded);

            // If the accordian feature has been enabled collapse all siblings.
            if (this.get('accordian')) {
                target.siblings('li').each(function () {
                    if (this.get('id') !== target.get('id') && !this.hasClass('collapsed')) {
                        this.addClass('collapsed');
                        this.set('aria-expanded', false);
                    }
                });
            }

            // If this block can dock tell the dock to resize if required and check
            // the width on the dock panel in case it is presently in use.
            if (this.get('candock')) {
                var _o = M.core_dock ? M.core_dock : M.core.dock.get();
                _o.resize();
                var panel = _o.getPanel();
                if (panel.visible) {
                    panel.correctWidth();
                }
            }
        },
        toggleSectionExpansion: function($section){
            var $id = $section.target.get('id');
            var $sectionId = $id.split("-");
            $sectionId = $sectionId[$sectionId.length-1];
            $toggleSectionId = '#toggle-'+$sectionId;
            if(Y.one("#toggles-all-closed")) {
                YUI().use('node-event-simulate', function (Y) {
                    Y.one("#toggles-all-closed").simulate("click");
                });
            }
            if(Y.one($toggleSectionId)) {
                Y.one($toggleSectionId).all('.the_toggle').replaceClass("toggle_closed", "toggle_opened").set('aria-pressed', 'true');
                $toggledSectionId = '#toggledsection-' + $sectionId;
                Y.one($toggledSectionId).addClass('sectionopen');
            }
            setTimeout(function(){ window.scrollBy(0, -70); }, 50);

        },
        recordEvent: function ($li, isExpanded) {
            var target = $li.one('.item_name');
            if (target) {
                var act = isExpanded ? "add" : "remove";
                var elName = target.get('innerHTML');
                var instId = this.id;
                Y.io(M.cfg.wwwroot + '/blocks/course_menu/ajax.php', {
                    method: 'POST',
                    data: 'element_name=' + elName + '&action=' + act + '&instance=' + instId,
                    on: {
                        success: function () {
                        }
                    },
                    context: this
                });
            }
        }
    }
// The tree extends the YUI base foundation.
    Y.extend(TREE, Y.Base, TREE.prototype, {
        NAME: 'block_course_menu_navigation-tree',
        ATTRS: {
            instance: {
                value: null
            },
            candock: {
                validator: Y.Lang.isBool,
                value: false
            },
            accordian: {
                validator: Y.Lang.isBool,
                value: false
            },
            expansionlimit: {
                value: 0,
                setter: function (val) {
                    return parseInt(val);
                }
            }
        }
    });
    if (M.core_dock && M.core_dock.genericblock) {
        Y.augment(TREE, M.core_dock.genericblock);
    }

    /**
     * The tree branch class.
     * This class is used to manage a tree branch, in particular its ability to load
     * its contents by AJAX.
     */
    var BRANCH = function (config) {
        BRANCH.superclass.constructor.apply(this, arguments);
    }
    BRANCH.prototype = {
        /**
         * The node for this branch (p)
         */
        node: null,
        /**
         * A reference to the ajax load event handlers when created.
         */
        event_ajaxload: null,
        event_ajaxload_actionkey: null,
        /**
         * Initialises the branch when it is first created.
         */
        initializer: function (config) {
            if (config.branchobj !== null) {
                // Construct from the provided xml
                for (var i in config.branchobj) {
                    this.set(i, config.branchobj[i]);
                }
                var children = this.get('children');
                this.set('haschildren', (children.length > 0));
            }
            if (config.overrides !== null) {
                // Construct from the provided xml
                for (var i in config.overrides) {
                    this.set(i, config.overrides[i]);
                }
            }
            // Get the node for this branch
            this.node = Y.one('#', this.get('id'));
            // Now check whether the branch is not expandable because of the expansionlimit
            var expansionlimit = this.get('tree').get('expansionlimit');
            var type = this.get('type');
            if (expansionlimit != EXPANSIONLIMIT_EVERYTHING && type >= expansionlimit && type <= EXPANSIONLIMIT_ACTIVITY) {
                this.set('expandable', false);
                this.set('haschildren', false);
            }
        },
        /**
         * Draws the branch within the tree.
         *
         * This function creates a DOM structure for the branch and then injects
         * it into the navigation tree at the correct point.
         */
        draw: function (element) {

            var isbranch = (this.get('expandable') || this.get('haschildren'));
            var branchli = Y.Node.create('<li></li>');
            var link = this.get('link');
            var branchp = Y.Node.create('<p class="tree_item"></p>').setAttribute('id', this.get('id'));
            if (!link) {
                //add tab focus if not link (so still one focus per menu node).
                // it was suggested to have 2 foci. one for the node and one for the link in MDL-27428.
                branchp.setAttribute('tabindex', '0');
            }
            if (isbranch) {
                branchli.addClass('collapsed').addClass('contains_branch');
                branchli.set('aria-expanded', false);
                branchp.addClass('branch');
            }

            // Prepare the icon, should be an object representing a pix_icon
            var branchicon = false;
            var icon = this.get('icon');
            if (icon && (!isbranch || this.get('type') == 40)) {
                branchicon = Y.Node.create('<img alt="" />');
                branchicon.setAttribute('src', M.util.image_url(icon.pix, icon.component));
                branchli.addClass('item_with_icon');
                if (icon.alt) {
                    branchicon.setAttribute('alt', icon.alt);
                }
                if (icon.title) {
                    branchicon.setAttribute('title', icon.title);
                }
                if (icon.classes) {
                    for (var i in icon.classes) {
                        branchicon.addClass(icon.classes[i]);
                    }
                }
            }

            if (!link) {
                if (branchicon) {
                    branchp.appendChild(branchicon);
                }
                branchp.append(this.get('name'));
            } else {
                var branchlink = Y.Node.create('<a title="' + this.get('title') + '" href="' + link + '"></a>');
                if (branchicon) {
                    branchlink.appendChild(branchicon);
                }
                branchlink.append(this.get('name'));
                if (this.get('hidden')) {
                    branchlink.addClass('dimmed');
                }
                branchp.appendChild(branchlink);
            }

            branchli.appendChild(branchp);
            element.appendChild(branchli);
            this.node = branchp;
            return this;
        },
        /**
         * Attaches required events to the branch structure.
         */
        wire: function () {
            this.node = this.node || Y.one('#' + this.get('id'));
            if (!this.node) {
                return false;
            }
            if (this.get('expandable')) {
                this.event_ajaxload = this.node.on('ajaxload|click', this.ajaxLoad, this);
                this.event_ajaxload_actionkey = this.node.on('actionkey', this.ajaxLoad, this);
            }
            return this;
        },
        /**
         * Gets the UL element that children for this branch should be inserted into.
         */
        getChildrenUL: function () {
            var ul = this.node.next('ul');
            if (!ul) {
                ul = Y.Node.create('<ul></ul>');
                this.node.ancestor().append(ul);
            }
            return ul;
        },
        /**
         * Load the content of the branch via AJAX.
         *
         * This function calls ajaxProcessResponse with the result of the AJAX
         * request made here.
         */
        ajaxLoad: function (e) {
            if (e.type == 'actionkey' && e.action != 'enter') {
                e.halt();
            } else {
                e.stopPropagation();
            }
            if (e.type = 'actionkey' && e.action == 'enter' && e.target.test('A')) {
                this.event_ajaxload_actionkey.detach();
                this.event_ajaxload.detach();
                return true; // no ajaxLoad for enter
            }

            if (this.node.hasClass('loadingbranch')) {
                return true;
            }

            this.node.addClass('loadingbranch');

            var params = {
                elementid: this.get('id'),
                id: this.get('key'),
                type: this.get('type'),
                sesskey: M.cfg.sesskey,
                instance: this.get('tree').get('instance')
            };

            Y.io(M.cfg.wwwroot + '/lib/ajax/getnavbranch.php', {
                method: 'POST',
                data: build_querystring(params),
                on: {
                    complete: this.ajaxProcessResponse
                },
                context: this
            });
            return true;
        },
        /**
         * Processes an AJAX request to load the content of this branch through
         * AJAX.
         */
        ajaxProcessResponse: function (tid, outcome) {
            this.node.removeClass('loadingbranch');
            this.event_ajaxload.detach();
            this.event_ajaxload_actionkey.detach();
            try {
                var object = Y.JSON.parse(outcome.responseText);
                if (object.children && object.children.length > 0) {
                    var coursecount = 0;
                    for (var i in object.children) {
                        if (typeof(object.children[i]) == 'object') {
                            if (object.children[i].type == 20) {
                                coursecount++;
                            }
                            this.addChild(object.children[i]);
                        }
                    }
                    if (this.get('type') == 10 && coursecount >= M.block_course_menu.courselimit) {
                        this.addViewAllCoursesChild(this);
                    }
                    this.get('tree').toggleExpansion({target: this.node});
                    return true;
                }
            } catch (ex) {
                // If we got here then there was an error parsing the result
            }
            // The branch is empty so class it accordingly
            this.node.replaceClass('branch', 'emptybranch');
            return true;
        },
        /**
         * Turns the branch object passed to the method into a proper branch object
         * and then adds it as a child of this branch.
         */
        addChild: function (branchobj) {
            // Make the new branch into an object
            var branch = new BRANCH({tree: this.get('tree'), branchobj: branchobj});
            if (branch.draw(this.getChildrenUL())) {
                branch.wire();
                var count = 0, i, children = branch.get('children');
                for (i in children) {
                    // Add each branch to the tree
                    if (children[i].type == 20) {
                        count++;
                    }
                    if (typeof(children[i]) == 'object') {
                        branch.addChild(children[i]);
                    }
                }
                if (branch.get('type') == 10 && count >= M.block_course_menu.courselimit) {
                    this.addViewAllCoursesChild(branch);
                }
            }
            return true;
        },

        /**
         * Add a link to view all courses in a category
         */
        addViewAllCoursesChild: function (branch) {
            branch.addChild({
                name: M.str.moodle.viewallcourses,
                title: M.str.moodle.viewallcourses,
                link: M.cfg.wwwroot + '/course/category.php?id=' + branch.get('key'),
                haschildren: false,
                icon: {'pix': "i/navigationitem", 'component': 'moodle'}
            });
        }
    }
    Y.extend(BRANCH, Y.Base, BRANCH.prototype, {
        NAME: 'block_course_menu_navigation-branch',
        ATTRS: {
            tree: {
                validator: Y.Lang.isObject
            },
            name: {
                value: '',
                validator: Y.Lang.isString,
                setter: function (val) {
                    return val.replace(/\n/g, '<br />');
                }
            },
            title: {
                value: '',
                validator: Y.Lang.isString
            },
            id: {
                value: '',
                validator: Y.Lang.isString,
                getter: function (val) {
                    if (val == '') {
                        val = 'cm_expandable_branch_' + M.block_course_menu.expandablebranchcount;
                        M.block_course_menu.expandablebranchcount++;
                    }
                    return val;
                }
            },
            key: {
                value: null
            },
            type: {
                value: null
            },
            link: {
                value: false
            },
            icon: {
                value: false,
                validator: Y.Lang.isObject
            },
            expandable: {
                value: false,
                validator: Y.Lang.isBool
            },
            hidden: {
                value: false,
                validator: Y.Lang.isBool
            },
            haschildren: {
                value: false,
                validator: Y.Lang.isBool
            },
            children: {
                value: [],
                validator: Y.Lang.isArray
            }
        }
    });

    M.block_course_menu = M.block_course_menu || {
        /** The number of expandable branches in existence */
        expandablebranchcount: 1,
        courselimit: 20,
        instance: null,
        /**
         * Add new instance of navigation tree to tree collection
         */
        init_add_tree: function (properties) {

            if (properties.courselimit) {
                this.courselimit = properties.courselimit;
            }
            var _LEGACY = false;

            if (M.core_dock) {
                try {
                    M.core_dock.init(Y);
                    _LEGACY = true;
                } catch (e) {
                    _LEGACY = false;
                }
            }

            var _dock = M.core && M.core.dock ? M.core.dock.get() : M.core_dock;

            new TREE(properties);
            if (typeof properties.bg_color !== 'undefined') {
                var _bg = properties.bg_color,
                    _setBackground = function (items) {
                        if (!items) {
                            return;
                        }
                        for (var i in items) {
                            if (_LEGACY) {
                                if (items[i] && items[i].blockclass == 'block_course_menu') {
                                    items[i].nodes.docktitle.setStyle("background", "none").setStyle("backgroundColor", _bg);
                                }
                            } else {
                                var current = items[i].get('dockTitleNode');
                                if (items[i].get('blockinstanceid') == properties.instance) {
                                    current.setStyle("background", "none").setStyle("backgroundColor", _bg);
                                }
                            }
                        }
                    }
                _dock.on('dock:itemadded', function () {
                    var _items = this.items ? this.items : (this.dockeditems ? this.dockeditems : []);
                    _setBackground(_items);
                });
                var _items = _dock.items ? _dock.items : (_dock.dockeditems ? _dock.dockeditems : []);
                if (_items) {
                    _setBackground(_items);
                }
            }
        }
    };

}, '@VERSION@', {requires: ['base', 'core_dock', 'io-base', 'node', 'dom', 'event-custom', 'event-delegate', 'json-parse']});
