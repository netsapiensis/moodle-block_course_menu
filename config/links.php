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

if (!isset ($this->config->links)) {
    error('Unauthorized');
}

$linksShow = $this->config->linksEnable ? 'i/hide' : 'i/show';
?>
<div class="showHideCont">
    <a class="showHide minus" rel="div_links" href="javascript:void(0)">
        <?php echo get_string('hide', $this->blockname) ?>
    </a>
    <a class="showHide plus" rel="div_links" href="javascript:void(0)" style="display: none">
        <?php echo get_string('show', $this->blockname) ?>
    </a>
</div>
<div class="clear"></div>
<div id="t_div_links">
    <div id="linksEnableContainer">
        <div class="">
            <a href="javascript:void(0)" rel="linksEnable">
                <img src="<?php echo $OUTPUT->pix_url($linksShow) ?>" border="0" class="show-hide" alt="" />
                <?php echo get_string('activatecustomlinks', $this->blockname) ?>
                <input type="hidden" name="linksEnable" class="linksEnable" value="<?php echo $this->config->linksEnable ?>" id="id_linksEnable" />
            </a>
        </div>
    </div>
    <div id="linksContainer" <?php if (!$this->config->linksEnable) echo 'style="display: none"' ?> class="fitem clearfix" style="margin-bottom: 5px;">
        <div class="fitemtitle">
            <label><?php echo get_string('numberoflinks', 'block_course_menu') ?></label>
        </div>
        <div class="felement ftext">
            <input type="text" name="linksCount" id="linksCount" value="<?php echo !empty($this->config->links) ? count($this->config->links) : '0' ?>" />
            <br />
            <button type="button" id="change-links-no"><?php echo get_string('change', 'block_course_menu') ?></button>
        </div>
        <div id="linksTableContainer"></div>
    </div>
    <div style="display: none" id="link-template" class="link-template">
        <div class="fitem link-template" style="margin-bottom:0; margin-top: 3px;">
            <input type="hidden" name="_change_" class="skip" value="1" />
            <hr />
            <div class="felement"><strong>__title__</strong></div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><label><?php echo get_string('name', 'block_course_menu') ?></label></div>
            <div class="felement"><input name="cm_link_name" type="text" class="link-text name" value="__name__" size="30" /></div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><label><?php echo get_string('url', 'block_course_menu') ?></label></div>
            <div class="felement"><input name="cm_link_url" type="text" class="link-text url" value="__url__" size="30" /></div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><label><?php echo get_string('window', 'block_course_menu') ?></label></div>
            <div class="felement">
                <select name="cm_link_target" class="link-select target" id="target">
                    <option style="padding: 2px;" value=""><?php echo get_string('samewindow', 'block_course_menu') ?></option>
                    <option style="padding: 2px;" value="_blank"><?php echo get_string('newwindow', 'block_course_menu') ?></option>
                </select>
            </div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><label class="iconLabel"><?php echo get_string('icon', 'block_course_menu') ?></label></div>
            <div class="felement">
                <select name="cm_link_icon" class="link-select icon">
                    <?php foreach ($icons as $icon) : ?>
                    <option style="padding: 5px 2px 5px 28px; background: url('<?php echo $icon['img'] ?>') no-repeat scroll 2px 2px transparent;" 
                            value="<?php echo $icon['val'] ?>"><?php echo $icon['name'] ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><input name="cm_link_keeppagenavigation" type="checkbox" class="disabled-if-not" /></div>
            <div class="felement"><label class="cm_link_keeppagenavigation"><?php echo get_string('keeppagenavigation', 'block_course_menu') ?></label></div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><input name="cm_link_allowresize" type="checkbox" class="disabled-if" /></div>
            <div class="felement"><label class="cm_link_allowresize"><?php echo get_string('allowresize', 'block_course_menu') ?></label></div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><input name="cm_link_allowscroll" type="checkbox" class="disabled-if" /></div>
            <div class="felement"><label class="cm_link_allowscroll"><?php echo get_string('allowscroll', 'block_course_menu') ?></label></div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><input name="cm_link_showdirectorylinks" type="checkbox" class="disabled-if" /></div>
            <div class="felement"><label class="cm_link_showdirectorylinks"><?php echo get_string('showdirectorylinks', 'block_course_menu') ?></label></div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><input name="cm_link_showlocationbar" type="checkbox" class="disabled-if" /></div>
            <div class="felement"><label class="cm_link_showlocationbar"><?php echo get_string('showlocationbar', 'block_course_menu') ?></label></div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><input name="cm_link_showmenubar" type="checkbox" class="disabled-if" /></div>
            <div class="felement"><label class="cm_link_showmenubar"><?php echo get_string('showmenubar', 'block_course_menu') ?></label></div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><input name="cm_link_showtoolbar" type="checkbox" class="disabled-if" /></div>
            <div class="felement"><label class="cm_link_showtoolbar"><?php echo get_string('showtoolbar', 'block_course_menu') ?></label></div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><input name="cm_link_showstatusbar" type="checkbox" class="disabled-if" /></div>
            <div class="felement"><label class="cm_link_showstatusbar"><?php echo get_string('showstatusbar', 'block_course_menu') ?></label></div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><label class="cm_link_defaultwidth"><?php echo get_string('defaultwidth', 'block_course_menu') ?></label></div>
            <div class="felement"><input name="cm_link_defaultwidth" type="text" class="disabled-if input-text" size="30" value="__dw__" /></div>
            <div style="clear:both;height:3px;"></div>
            <div class="fitemtitle"><label class="cm_link_defaultheight"><?php echo get_string('defaultheight', 'block_course_menu') ?></label></div>
            <div class="felement"><input name="cm_link_defaultheight" type="text" class="disabled-if input-text" size="30" value="__dh__" /></div>
            <div style="clear:both;height:3px;"></div>
        </div>
    </div>
</div>