
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

/** handle all js needed for the settings pages (and global block config) **/
YUI.add('moodle-block_course_menu-settings', function(Y) {
    
    /**
     * Utils
     */
    var U = {
        img: {},
        str: {}
    };
    var config = {};
    
    var Util = {
        show_hide: function( alink, callback ) {
            var $input = alink.one('input.' + alink.get('rel'));
            var becomes_visible;
            alink.on('click', function() {
                becomes_visible = $input.get('value') === '0';
                this.one('img.show-hide').set('src', becomes_visible ? U.img.hide : U.img.show);
                $input.set('value', becomes_visible ? '1' : '0');
                
                if (typeof callback === 'function') {
                    callback.call( alink, becomes_visible );
                }
                return false;
            });
        }
    };

    var Links = {
        tpl: '',
        linksCount: null,
        container: null,
        linksEnable: false,
        init: function() {
            this.linksEnable = config.linksEnable ? true : false;
            this.tpl = Y.one('#link-template').get('innerHTML');
            this.linksCount = Y.one('#linksCount');
            this.container = Y.one('#linksTableContainer');
            var self = this;
            Util.show_hide(Y.one('#linksEnableContainer a'), function( is_visible ) {
                Y.one('#linksContainer').setStyle('display', is_visible ? 'block' : 'none');
                self.linksEnable = is_visible;
                if (Y.one('#id_config_linksEnable')) { //instance config
                    Y.one('#id_config_linksEnable').set('value', is_visible ? '1' : '0');
                }
                Elements.refreshLinks(is_visible);
            });
            this.draw();
            Y.one('#change-links-no').on('click', function() {
                self.changeLinksNo();
                return false;
            });
            this.linksCount.on('keypress', function( e ) {
                if (e.keyCode == 13) { //enter
                    self.changeLinksNo();
                    e.preventDefault();
                    return false;
                }
            });
        },
        changeLinksNo: function() {
            var val = parseInt(this.linksCount.get('value'));
            if (isNaN(val) || val < 1) {
                alert(U.str.linkswrongnumber);
                return false;
            }
            if (val === config.links.length) {
                return true;
            }
            if (val < config.links.length) {
                config.links = config.links.slice(0, val);
                Elements.slice(val);
            } else {
                for (var i = config.links.length; i < val; i++) {
                    config.links.push(this.newLink(i + 1));
                    Elements.createLink(i);
                }
            }
            this.draw();
        },
        newLink: function(linkNumber) {
            return {
                name: U.str.customlink + ' ' + linkNumber,
                url: '',
                target: '',
                icon: '',
                defaultwidth: '',
                defaultheight: ''
            };
        },
        draw: function() {
            if (typeof config.links === 'undefined') {
                return false;
            }
            this.container.empty();
            for (var i = 0; i < config.links.length; i++) {
                var html = this.getRow(config.links[i], i);
                this.container.append(html);
            }
        },
        getRow: function( link, linkIndex ) {
            var item = this.tpl,
                self = this;
            item = item.replace('__title__', U.str.customlink + ' ' + (linkIndex + 1));
            item = item.replace('__name__', link.name);
            item = item.replace('__url__', link.url);
            item = item.replace('__dw__', link.defaultwidth);
            item = item.replace('__dh__', link.defaultheight);
            item = Y.Node.create(item);
            item.all('input[type=checkbox]').each(function( node ) {
                var l_field = node.get('name').replace('cm_link_', '');
                if (typeof link[l_field] !== 'undefined' && link[l_field]) {
                    node.set('checked', 'checked');
                } else {
                    node.removeAttribute('checked');
                }
            });
            item.all('select').each( function( node ) {
                var _field = node.get('name').replace('cm_link_', '');
                var _toSel = node.one('option[value="' + link[_field] + '"]');
                if (_toSel) {
                    _toSel.set('selected', 'selected');
                }
            });
            item.one('select#target').on('change', function() {
                if (this.get('selectedIndex') == 0) {
                    item.all('.disabled-if').set('disabled', 'disabled');
                    item.all('.disabled-if-not').removeAttribute('disabled');
                } else {
                    item.all('.disabled-if-not').set('disabled', 'disabled');
                    item.all('.disabled-if').removeAttribute('disabled');
                }
            });
            item.all('input,select').each(function( node ) {
                if (node.hasClass('skip')) {
                    node.set('name', 'linkCounter[]');
                    return;
                }
                var _name = node.get('name');
                node.set('id', _name + linkIndex);
                node.set('name', _name + linkIndex);
                var label = item.one('label.' + _name);
                if (label) {
                    label.set('for', _name + linkIndex);
                }
                if (node.test('select,input[type=checkbox]')) {
                    node.on('change', function() {
                        self.inputChanged(this, linkIndex);
                    });
                } else { //text input
                    node.on('keypress', function(e) {
                        if (e.keyCode == 13) {
                            e.preventDefault();
                            return false;
                        }
                    });
                    node.on('keyup', function() {
                        self.inputChanged(this, linkIndex);
                    });
                }
            });
            if (typeof link.target !== 'undefined' && link.target !== '_blank') {
                item.all('.disabled-if').set('disabled', 'disabled');
                item.all('.disabled-if-not').removeAttribute('disabled');
            } else {
                item.all('.disabled-if-not').set('disabled', 'disabled');
                item.all('.disabled-if').removeAttribute('disabled');
            }
            
            return item;
        },
        inputChanged: function(element, linkIndex) {
            var field = element.get('name').replace(/\d+/, '').replace('cm_link_', ''),
                    value = '';
            if (element.test('select')) {
                var index = element.get('selectedIndex');
                value = element.get('options').item(index).get('value');    
            } else if (element.test('input[type=checkbox]')) {
                value = element.get('checked') ? 1 : 0;
            } else { //input type text
                value = element.get('value');
            }
            this.setLinkData(linkIndex, field, value);
        },
        setLinkData: function(index, field, value) {
            config.links[index][field] = value;
            if (field === 'name') {
                Elements.linkNameChanged(index, value);
            }
        },
        validate: function() {
            if (config.links.length && this.linksEnable) {
                for (var i = 0; i < config.links.length; i++) {
                    var input = Y.one('#cm_link_url' + i);
                    if (! config.links[i].url) {
                        alert(U.str.linknourl);
                        input.focus();
                        input.addClass('cm-error');
                        return false;
                    } else if (! config.links[i].name) {
                        input = Y.one('#cm_link_name' + i);
                        alert(U.str.linknoname);
                        input.focus();
                        input.addClass('cm-error');
                        return false;
                    }
                    input.removeClass('cm-error');
                }
            }
            return true;
        }
    };

    var Elements = {
        table: null,
        tpl: null,
        init: function() {
            this.table = Y.one('#elementsContainer table tbody');
            this.tpl = Y.one('#element-template').get('innerHTML');
            this.table.all('.e-hide-element').each(function(node) {
                Util.show_hide(node);
            });
            var self = this;
            this.table.delegate('click', function() {
                self.move(this.ancestor('tr'), 'up');
            }, '.element-move-up a');
            this.table.delegate('click', function() {
                self.move(this.ancestor('tr'), 'down');
            }, '.element-move-down a');
        },
        createLink: function(i) {
            var element = this.createLinkElement(i);
            config.elements.push(element);
            var html = this.tpl.replace('__name__', element.name);
            var node = Y.Node.create(html);
            node.set('id', 'element-link' + i);
            for (var field in element) {
                var _i = node.one('input.e-' + field);
                if (_i) {
                    _i.set('value', element[field]);
                }
            }
            node.addClass('link-element');
            this.table.append(node);
            this.refresh();
        },
        refresh: function() {
            this.table.all('.element-move-up a, .element-move-down a').setStyle('display', 'inline');
            var firstTr = this.table.all('tr').item(0);
            firstTr.one('td.element-move-up a').setStyle('display', 'none');
            firstTr.one('td.element-move-down a').setStyle('display', 'inline');
            var lastTr = this.table.all('tr').pop();
            lastTr.one('td.element-move-down a').setStyle('display', 'none');
            lastTr.one('td.element-move-up a').setStyle('display', 'inline');
        },
        createLinkElement: function(index) {
            var linkElement = {
                id: 'link' + index,
                name: '',
                url: '',
                icon: '',
                canHide: 0,
                visible: 1
            };
            if( typeof config.links[index] !== 'undefined' ) {
                linkElement.name = config.links[index].name;
            } else {
                linkElement.name = U.str.customlink + ' ' + (index + 1);
            }
            return linkElement;
        },
        linkNameChanged: function(index, name) {
            var tr = this.table.one('tr#element-link' + index);
            if (tr) {
                tr.one('.element-name').set('innerHTML', name);
            }
        },
        move: function(tr, dir) {
            if (dir === 'up') {
                var target = tr.previous('tr');
                tr.remove();
                this.table.insert(tr, target);
            } else {
                var target = tr.next('tr');
                target.remove();
                this.table.insert(target, tr);
            }
            this.refresh();
        },
        slice: function(linkCount) {
            var index = 0;
            while (typeof config.elements[index] !== 'undefined') {
                if (config.elements[index].id.indexOf('link') === 0) {
                    var _link = parseInt(config.elements[index].id.replace('link', ''));
                    if (_link >= linkCount) {
                        config.elements.splice(index, 1);
                        var tr = this.table.one('#element-link' + _link);
                        if ( tr ) {
                            tr.remove();
                        }
                    } else {
                        index++;
                    }
                } else {
                    index++;
                }
            }
            this.refresh();
        },
        refreshLinks: function( is_visible ) {
            if (! is_visible) {
                this.slice(0);
            } else {
                for(var i = 0; i < config.links.length; i++) {
                    this.createLink(i);
                }
            }
        }
    };

    var Chapters = {
        table: null,
        chap_enable: null,
        chap_count: null,
        subchap_count: null,
        subchap_enable: null,
        chap_container: null,
        chapterBg: "#DFDFDF",
        subChapterBg: "#FFA7FF",
        topicBg: "#FFFF91",
        sectionCounter: 0,
        sectionNames: [],
        isEditingName: false,
        init: function( sectionNames ) {
            this.sectionNames = sectionNames;
            this.table = Y.one('#chaptersTableContainer table tbody');
            this.chap_enable = Y.one('input#id_config_chapEnable');
            this.subchap_enable = Y.one('input#id_config_subChapEnable');
            this.chap_container = Y.one('#chaptersContainer');
            this.chap_count = Y.one('#chaptersCount');
            this.subchap_count = Y.one('#subChaptersCount');
            var self = this;
            Y.one('#chap-enable').on('click', function() {
                var newValue = '';
                if (self.chap_enable.get('value') === '1') {
                    this.one('img').set('src', U.img.show);
                    self.chap_container.hide();
                    Y.all('.cm-chapter-enable').hide();
                    newValue = 0;
                } else {
                    this.one('img').set('src', U.img.hide);
                    self.chap_container.show();
                    Y.all('.cm-chapter-enable').show();
                    newValue = 1;
                }
                self.chap_enable.set('value', newValue);
                config.chapEnable = newValue;
                self.draw();
            });
        
            Y.one('#subchap-enable').on('click', function() {
                var newValue = '', valueChanged = false;
                if (self.subchap_enable.get('value') === '1') {
                    this.one('img').set('src', U.img.show);
                    newValue = 0;
                    Y.all('.cm-subchapter-enable').hide();
                    valueChanged = true;
                } else {
                    if (confirm(U.str.warningsubchapenable)) {
                        this.one('img').set('src', U.img.hide);
                        Y.one('.cm-subchapter-enable').show();
                        newValue = 1;
                        valueChanged = true;
                    }
                }
                if (valueChanged) {
                    self.subchap_enable.set('value', newValue);
                    config.subChapEnable = newValue;
                    config.subChaptersCount = config.chapters.length;
                    self.subchap_count.set('value', config.subChaptersCount);
                    self.resetSubchapterGroupings().draw();
                }
            });
            this.listenInputs();
            this.enableEdits(); //listeners for editing chapter and subchapter names 
            this.draw();
        },
        listenInputs: function() {
            var self = this;
            this.chap_count.on('keypress', function( e ) {
                if ( e.keyCode === 13 ) {
                    e.preventDefault();
                    self.changeChapterNo();
                    return false;
                }
            });
            this.subchap_count.on('keypress', function( e ) {
                if ( e.keyCode === 13 ) {
                    e.preventDefault();
                    self.changeSubChapterNo();
                    return false;
                }
            });
            Y.one('#btn-change-chap-no').on('click', function( e ) {
                self.changeChapterNo();
            });
            Y.one('#btn-change-subchap-no').on('click', function( e ) {
                self.changeSubChapterNo();
            });
            Y.one('#btn-default-grouping').on('click', function( e ) {
                self.defaultGrouping(false).draw();
            });
        },
        changeChapterNo: function() {
            var val = parseInt(this.chap_count.get('value'));
            if (isNaN(val) || val < 1 || val > this.sectionNames.length || (config.subChapEnable && val > config.subChaptersCount)) {
                alert(U.str.wrongnumber);
                this.chap_count.set('value', config.chapters.length);
                return ;
            }
            if (!confirm(U.str.warningchapnochange)) {
                this.chap_count.set('value', config.chapters.length);
                return false;
            }
            this.defaultGrouping(true).draw();
        },
        changeSubChapterNo: function() {
            var val = parseInt(this.subchap_count.get('value'));
            if (isNaN(val) || val < config.chapters.length || val > this.sectionNames.length) {
                alert(U.str.wrongsubchapnumber);
                this.subchap_count.set('value', config.subChaptersCount);
                return ;
            }
            if (!confirm(U.str.warningsubchapnochange)) {
                this.subchap_count.set('value', config.subChaptersCount);
                return false;
            }
            config.subChaptersCount = this.subchap_count.get('value');
            this.defaultGrouping(true).draw();
        },
        draw: function() {
            this.table.empty();
            this.table.append(this.getHeader());
            
            this.sectionCounter = 0;
            
            for (var i = 0; i < config.chapters.length; i++) {
                var chapter = config.chapters[i];
                this.table.append(this.getChapterRow(chapter, i));
                
                this.drawChapter(chapter, i);
            }
        },
        getHeader: function() {
            var html = '<tr><td align="center" colspan="2" width="200">' + U.str.chapters + '</td>';
            if (config.subChapEnable) {
                html += '<td align="center" colspan="3" width="200">' + U.str.subchapters + '</td>';
            }
            html += '<td align="center" colspan="2" width="200">' + U.str.sections + '</td></tr>'
            return html;
        },
        getChapterRow: function(chapter, index) {
            var chapterCounts = '';
            if (! config.chapEnable || ! config.subChapEnable) {
                chapterCounts = chapter.childElements[0].count;
            }
            var html = '<tr id="cm-chapter-' + index + '"><td width="20" align="left" style="background-color:' + this.chapterBg + '">' + 
                    '<a href="javascript:void(0)" class="cm-edit-chapter"><img alt="" src="' + U.img.edit + '" /></a></td>';
            //chapter cell
            html += '<td align="left" style="background-color:' + this.chapterBg + '"><span class="cm-chapter-name">' + chapter.name + '</span>' + 
                    '<input type="text" style="display: none" class="edit-chapter-name" name="chapterNames[]" value="' + chapter.name + '" />' + 
                    '<input type="hidden" name="chapterCounts[]" value="' + chapterCounts + '" />' + 
                    '<input type="hidden" name="chapterChildElementsNumber[]" value="' + chapter.childElements.length + '" /></td>';
            if (config.subChapEnable) {
                //3 empty <td>s
                html += '<td style="background-color:' + this.chapterBg + '">&nbsp;</td><td style="background-color:' + this.chapterBg + '">&nbsp;</td>';
                html += '<td style="background-color:' + this.chapterBg + '">&nbsp;</td>';
            }
            //2 empty <td>s
            html += '<td style="background-color:' + this.chapterBg + '">&nbsp;</td><td style="background-color:' + this.chapterBg + '">&nbsp;</td>';
            return html;
        },
        drawChapter: function(chapter, i) {
            var clr, html = '', next = {}, previous = {}, 
                    count = chapter.childElements.length, 
                    chapterCount = config.chapters.length;
            
            for (var k = 0; k < count; k++) {
                var element = chapter.childElements[k];
                if (config.subChapEnable) {
                    if (element.type === 'topic') {
                        clr = this.topicBg;
                    } else {
                        clr = this.subChapterBg;
                    }
                    //2 empty <td>s
                    html += '<tr id="cm-subchapter-' + i + '-' + k + '"><td>&nbsp;</td><td>&nbsp;</td>';
                    var w = '20', temp = '';
                    // add move image
                    if (element.type === 'topic' && (next.type === 'subchapter' || previous.type === 'subchapter')) {
                        w = '40';
                    }
                    
                    if (i > 0 && k === 0 && count > 1) {
                        w = '40';
                        //move up subchapter
                        temp = '<a href="javascript:void(0)" class="cm-move-subchapter" rel="up"><img alt="" src="' + U.img.up + '" /></a>';
                    } else if (i !== chapterCount - 1 && k === count - 1 && count > 1) {
                        w = '40'
                        //move down subchapter
                        temp = '<a href="javascript:void(0)" class="cm-move-subchapter" rel="down"><img alt="" src="' + U.img.down + '" /></a>'
                    }
                    html += '<td align="center" style="background-color:' + clr + '" width="' + w + '">' + temp;
                    //gets the next (row) that could be: 1) next child element of current chapter; 2) new chapter - first child element; 3) empty for the last chapter, last row
                    next = (k === count - 1) ? (i === chapterCount - 1 ? {type:'___'} : config.chapters[i + 1].childElements[0]) : chapter.childElements[k + 1];
                    //get the previous (row): 1) last child of the previous chapter; 2) previous child element in current chapter; 3) empty for first chapter, first row
                    previous = (k === 0) ? (i === 0 ? {type:'___'} : config.chapters[i - 1].childElements.slice(-1)[0]) : chapter.childElements[k - 1];
                    
                    if (element.type === 'topic' && (next.type === 'subchapter' || previous.type === 'subchapter')) {
                        //move topic right (topic in column of subchapter)
                        var a = '<a href="javascript:void(0)" class="cm-move-topic"';
                        
                        if (previous.type === 'subchapter') {
                            a += ' rel="right-above"';
                        } else {
                            a += ' rel="right-below"';
                        }
                        a += '><img alt="" src="' + U.img.right + '" /></a>';
                        html += a;
                    }
                    html += '</td>';
                    // add edit subchapter name
                    html += '<td width="20" align="center" style="background-color:' + clr + '">';
                    if (element.type === 'subchapter') {
                        //edit link
                        html += '<a href="javascript:void(0)" class="cm-edit-subchapter"><img alt="" src="' + U.img.edit + '" /></a>';
                    } else {
                        //do nothing, this will be empty for now
                        html += '&nbsp;'
                    }
                    html += '</td>';

                    //add subchapter name column or topic name if type == "topic"
                    html += '<td align="left" style="background-color:' + clr + '">';
                    if (element.type === 'subchapter') {
                        html += '<span class="cm-subchapter-name">' + element.name + '</span>';
                    } else if (element.type === 'topic') {
                        html += '<span class="cm-section-name">' + this.sectionNames[this.sectionCounter++] + '</span>';
                    }
                    //create inputs
                    html += '<input type="text" style="display: none" class="edit-subchapter-name" name="childElementNames[]" value="';
                    html += (element.type === 'subchapter' ? element.name : '') + '" />';
                    html += '<input type="hidden" name="childElementCounts[]" value="';
                    html += (element.type === 'subchapter' ? element.count : '') + '" />';
                    html += '<input type="hidden" name="childElementTypes[]" value="' + element.type + '" />';

                    // add 2 empty <td>s
                    html += '</td><td style="background-color:' + clr + '">&nbsp;</td><td style="background-color:' + clr + '">&nbsp;</td>';
                    //end row
                    html += '</tr>';
                }
                if (! config.subChapEnable || element.type === 'subchapter') { //create topics
                    for (var j = 0; j < element.count; j++) {
                        //2 empty <td>s
                        html += '<tr id="index-' + i + '-' + k + '-' + j + '"><td>&nbsp;</td><td>&nbsp;</td>';
                        
                        //add another 3 empty tds if subchaptersEnable
                        if (config.subChapEnable) {
                            html += '<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
                        }
                        // add move image
                        html += '<td align="center" style="background-color:' + this.topicBg + '"';
                        var tw = '20';
                        
                        if (element.count > 1 && j === 0 && (i > 0 || (k > 0 && config.subChapEnable))) {
                            var links = '';
                            //move the topic left and above the subchapter
                            if (config.subChapEnable) {
                                tw = '40';
                                links += '<a href="javascript:void(0)" class="cm-move-topic" rel="left-above"><img alt="" src="' + U.img.left + '" /></a>'
                            }
                            //move up link
                            links += '<a href="javascript:void(0)" class="cm-move-topic" rel="up"><img alt="" src="' + U.img.up + '" /></a>';
                            html += ' width="' + tw + '">' + links;
                            
                        } else if (element.count > 1 && j === element.count - 1 && (i < chapterCount - 1 || (k < count - 1 && config.subChapEnable))) {
                            var links = '';
                            //move topic left and below current subchapter
                            if (config.subChapEnable) {
                                tw = '40';
                                links += '<a href="javascript:void(0)" class="cm-move-topic" rel="left-below"><img alt="" src="' + U.img.left + '" /></a>'
                            }
                            //move down link
                            links += '<a href="javascript:void(0)" class="cm-move-topic" rel="down"><img alt="" src="' + U.img.down + '" /></a>';
                            html += ' width="' + tw + '">' + links;
                        }
                        html += '</td>';
                        // add section name
                        html += '<td align="left" style="background-color:' + this.topicBg + '">' + this.sectionNames[this.sectionCounter++] + '</td></tr>';
                    }
                }
            }
            this.table.append(html);
        },
        enableEdits: function() {
            var self = this;
            //"live" event listeners for chapter editing names
            this.table.delegate('click', function() {
                if (self.isEditingName) {
                    return false;
                }
                var tr = this.ancestor('tr'),
                    isChapter = (this.hasClass('cm-edit-chapter')),
                    span = tr.one(isChapter ? '.cm-chapter-name' : '.cm-subchapter-name'),
                    input = tr.one(isChapter ? '.edit-chapter-name' : '.edit-subchapter-name');
                span.hide();
                input.show();
                input.focus();
                self.isEditingName = true;
            }, 'a.cm-edit-chapter, a.cm-edit-subchapter');
            this.table.delegate('keypress', function( e ) {
                var tr = this.ancestor('tr'),
                    isChapter = (this.hasClass('edit-chapter-name')),
                    span = tr.one(isChapter ? '.cm-chapter-name' : '.cm-subchapter-name');
                if (e.keyCode === 13) {
                    self.doneEditingName( span, this, isChapter );
                    e.preventDefault();
                    return false;
                }
            }, '.edit-chapter-name, .edit-subchapter-name');
            this.table.delegate('blur', function() {
                var tr = this.ancestor('tr'),
                    isChapter = (this.hasClass('edit-chapter-name')),
                    span = tr.one(isChapter ? '.cm-chapter-name' : '.cm-subchapter-name');
                    self.doneEditingName( span, this, isChapter );
            }, '.edit-chapter-name, .edit-subchapter-name');
            this.table.delegate('click', function() {
                var tr = this.ancestor('tr'),
                    dir = this.get('rel').split('-');
                self.moveTopic(tr, dir[0], typeof dir[1] !== 'undefined' ? dir[1] : null);
            }, '.cm-move-topic');
            this.table.delegate('click', function() {
                var tr = this.ancestor('tr'),
                    dir = this.get('rel');
                self.moveSubChapter(tr, dir);
            }, '.cm-move-subchapter');
        },
        doneEditingName: function(span, input, isChapter) {
            var tr = span.ancestor('tr'),
                name = input.get('value');
            if (! name) {
                alert( isChapter ? U.str.emptychapname : U.str.emptysubchapname );
                return true;
            }
            input.hide();
            span.show().set('innerHTML', name);
            this.isEditingName = false;
            if (isChapter) {
                var index = parseInt(tr.get('id').replace('cm-chapter-', ''));
                config.chapters[index].name = name;
            } else {
                var parts = tr.get('id').replace('cm-subchapter-', '').split('-');
                config.chapters[parseInt(parts[0])].childElements[parseInt(parts[1])].name = name;
            }
        },
        //hardReset will reset all chapters
        defaultGrouping: function(hardReset) {
            
            if (hardReset) {
                config.chapters = [];
            } else {
                this.chap_count.set('value', config.chapters.length);
            }
            var chapNo = parseInt(this.chap_count.get('value'));
            var c = Math.floor(this.sectionNames.length / chapNo);
            var r = this.sectionNames.length - c * chapNo;
            for (var i = 0; i < chapNo; i++) {
                if (! config.subChapEnable || hardReset) {
                    if (hardReset) {
                        config.chapters[i] = {
                            name: U.str.chapter + ' ' + (i+1)
                        };
                    }
                    config.chapters[i].childElements = [];
                    config.chapters[i].childElements[0] = {
                        type: 'subchapter',
                        count: i < r ? c + 1 : c
                    };
                }
            }
            this.resetSubchapterGroupings(hardReset);
            return this;
        },
        resetSubchapterGroupings: function(hardReset) {
            this.subchap_count.set('value', config.subChaptersCount);
            var count = config.chapters.length;
            if (! config.subChapEnable) {
                var sect = 0;
                for (var i = 0; i < count; i++) {
                    var oldVal = config.chapters[i].childElements;
                    config.chapters[i].childElements = [];
                    config.chapters[i].childElements[0] = {
                        type: 'subchapter'
                    };
                    sect = 0;
                    for (var j = 0; j < oldVal.length; j++) {
                        if (oldVal[j].type === 'topic') {
                            sect++;
                        } else if (oldVal[j].type === 'subchapter') {
                            sect += parseInt(oldVal[j].count);
                        }
                    }
                    config.chapters[i].childElements[0].count = sect;
                }
            } else {
                var childElementNames = [];
                var index = 0;
                if (! hardReset) {
                    for (var i = 0; i < count; i++) {
                        for (var j = 0; j < config.chapters[i].childElements.length; j++) {
                            if (config.chapters[i].childElements[j].type === 'subchapter') {
                                childElementNames[index] = config.chapters[i].childElements[j].name;
                                index++;
                            }
                        }
                    }
                }
                var subChaptersPerChapter = Math.floor(config.subChaptersCount / config.chapters.length),
                        dif = config.subChaptersCount - (config.chapters.length * subChaptersPerChapter),
                        topicsPerSubchapter = Math.floor(this.sectionNames.length / config.subChaptersCount),
                        topicDif = this.sectionNames.length - (config.subChaptersCount * topicsPerSubchapter),
                        tracker = 0, topicTracker = 0, i = 0;
                index = 0;
                while (i < count) {
                    config.chapters[i].childElements = new Array();
                    for (j = 0; j < subChaptersPerChapter; j++) {
                        config.chapters[i].childElements[j] = {
                            type: 'subchapter',
                            name: hardReset || typeof childElementNames[index] === 'undefined' ? U.str.subchapter + ' ' + (i + 1) + '-' + (j + 1) : childElementNames[index],
                            count: topicsPerSubchapter
                        };
                        if (topicDif > topicTracker) {
                            config.chapters[i].childElements[j].count++;
                            topicTracker++;
                        }
                        index++;
                    }
                    if (dif > tracker) {
                        config.chapters[i].childElements[j] = {
                            type: 'subchapter',
                            name: hardReset || typeof childElementNames[index] === 'undefined' ? U.str.subchapter + ' ' + (i + 1) + '-' + (j + 1) : childElementNames[index],
                            count: topicsPerSubchapter
                        };
                        if (topicDif > topicTracker) {
                            config.chapters[i].childElements[j].count++;
                            topicTracker++;
                        }
                        index++;
                        tracker++;
                    }
                    i++;
                }
            }
            return this;
        },
        moveTopic: function(tr, direction, whereToInsert) {
            var target = tr.get('id').replace('cm-subchapter-', '').replace('index-', '').replace('cm-chapter-', '').split('-'),
                upperTR = tr.previous(), //this should be TR with subchapter
                mostUpperTR	= upperTR.previous(), //chapter or normal topic from another subchapter
                evenMoreUpperTR = mostUpperTR.previous(),
                lowerTR = tr.next(),
                mostLowerTR = lowerTR.next(),
                chapterIndex = parseInt(target[0]),
                subChapterIndex = parseInt(target[1]);
                
            if (config.subChapEnable) {
                if (direction === 'up') {
                    if (mostUpperTR.all('td').item(4).one('.cm-section-name') || evenMoreUpperTR.all('td').item(4).one('.cm-section-name')) {
                        alert(U.str.cannotmovetopicup);
                        return;
                    }
                    if (subChapterIndex === 0) {
                        var prev = config.chapters[chapterIndex - 1].childElements;
                        config.chapters[chapterIndex - 1].childElements[prev.length - 1].count++;
                        config.chapters[chapterIndex].childElements[subChapterIndex].count--;
                    } else {
                        config.chapters[chapterIndex].childElements[subChapterIndex - 1].count++;
                        config.chapters[chapterIndex].childElements[subChapterIndex].count--;
                    }
                } else if (direction === 'down') {
                    if (lowerTR.all('td').item(4).one('.cm-section-name') || mostLowerTR.all('td').item(4).one('.cm-section-name')) {
                        alert(U.str.cannotmovetopicdown);
                        return;    
                    }
                    if (subChapterIndex === config.chapters[chapterIndex].childElements.length - 1) {
                        config.chapters[chapterIndex + 1].childElements[0].count++;
                        config.chapters[chapterIndex].childElements[subChapterIndex].count--;
                    } else {
                        config.chapters[chapterIndex].childElements[subChapterIndex + 1].count++;
                        config.chapters[chapterIndex].childElements[subChapterIndex].count--;
                    }
                } else if (direction === 'right') {
                    config.chapters[chapterIndex].childElements.splice(subChapterIndex, 1);
                    if (whereToInsert === 'above') {
                        if (subChapterIndex === 0) {
                            chapterIndex --;
                            subChapterIndex = config.chapters[chapterIndex].childElements.length;
                        }
                        config.chapters[chapterIndex].childElements[subChapterIndex - 1].count++;
                    } else {
                        if (subChapterIndex === config.chapters[chapterIndex].childElements.length) {
                            chapterIndex++;
                            subChapterIndex = 0;
                        }
                        config.chapters[chapterIndex].childElements[subChapterIndex].count++;
                    }
                } else if (direction === 'left') {
                    var child = {
                        type: 'topic'
                    };
                    if (whereToInsert === 'above') {
                        config.chapters[chapterIndex].childElements.splice(subChapterIndex, 0, child);
                        config.chapters[chapterIndex].childElements[subChapterIndex + 1].count--;
                    } else {
                        config.chapters[chapterIndex].childElements.splice(subChapterIndex + 1, 0, child);
                        config.chapters[chapterIndex].childElements[subChapterIndex].count --;
                    }
                }
            } else {
                var targetChapterIndex = chapterIndex + 1;
                if (direction === 'up') {
                    targetChapterIndex = chapterIndex - 1;
                }
                config.chapters[chapterIndex].childElements[0].count--;
                config.chapters[targetChapterIndex].childElements[0].count++;
            }
            this.draw();
        },
        moveSubChapter: function(tr, dir) {
            var target = tr.get('id').replace('cm-subchapter-', '').replace('index-', '').replace('cm-chapter-', '').split('-'),
                chapterIndex = parseInt(target[0]);

            if (dir === 'up') {
                var elem = config.chapters[chapterIndex].childElements.shift();
                config.chapters[chapterIndex - 1].childElements.push(elem);
            } else {
                var elem = config.chapters[chapterIndex].childElements.pop();
                config.chapters[chapterIndex + 1].childElements.unshift(elem);
            }
            this.draw();
        }
    };
    
    M.block_course_menu_settings = M.block_course_menu_setings || {
        //instance settings
        instance: function() {
            this.init( arguments );
            Links.init();
            Elements.init();
            if (! arguments[3]) {
                Chapters.init(arguments[2]);
            }
            Y.all('.showHideCont a').each( function(node) {
                var _parent = node.ancestor('.showHideCont');
                node.on('click', function() {
                    var target = Y.one('#t_' + this.get('rel'));
                    this.setStyle('display', 'none');
                    if (this.hasClass('plus')) {
                        target.setStyle('display', 'block');
                        _parent.one('.minus').setStyle('display', 'inline');
                        _parent.setStyle('cssFloat', 'right');
                    } else {
                        target.setStyle('display', 'none');
                        _parent.one('.plus').setStyle('display', 'inline');
                        _parent.setStyle('cssFloat', 'left');
                    }
                });
            });
        },
        //global settings
        global: function() {
            this.init( arguments );
            Util.show_hide(Y.one('#expandableTreeContainer a'));
            Links.init();
            Elements.init();
        },
        init: function( args ) {
            U.img = args[0].img;
            U.str = args[0].str;
            config = args[1];
            var _form = Y.one('form#adminsettings'); //general (global) settings
            if (! _form ) {
                _form = Y.one('form.mform'); // instance config
            }
            if (_form) {
                _form.on('submit', function( e ) {
                    if (! Links.validate()) {
                        e.preventDefault();
                    }
                    return false;
                });
            }
        }
    };

}, '@VERSION@', {requires:['base', 'io-base', 'node', 'dom', 'event-custom', 'event-delegate', 'json-parse']});