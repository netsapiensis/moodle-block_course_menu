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

$expandableShow = $this->config->expandableTree ? 'i/hide' : 'i/show';
$linksShow = $this->config->linksEnable ? 'i/hide' : 'i/show';
$prefix = "s__block_course_menu_"; //use only as a flag, get the full data from _POST
?>
<div id="expandableTreeContainer">
    <div class="expandableTreeTd">
        <a href="javascript:void(0)" rel="expandableTree">
            <img src="<?php echo $OUTPUT->pix_url($expandableShow) ?>" alt="" border="0" class="show-hide" />
            <?php echo get_string('expandable_tree', $this->blockname) ?>
            <input type="hidden" class="expandableTree" name="expandableTree" id="id_expandableTree" value="<?php echo $this->config->expandableTree ?>" />
            <!-- use only as a flag, get the full data from _POST -->
            <input type="hidden" name="<?php echo $prefix ?>global_config" value="1" />
        </a>
    </div>
</div>
<div id="linksEnableContainer">
    <div class="expandableTreeTd">
        <a href="javascript:void(0)" rel="linksEnable">
            <img src="<?php echo $OUTPUT->pix_url($linksShow) ?>" border="0" class="show-hide" alt="" />
            <?php echo get_string('activatecustomlinks', $this->blockname) ?>
            <input type="hidden" name="linksEnable" class="linksEnable" value="<?php echo $this->config->linksEnable ?>" id="id_linksEnable" />
        </a>
    </div>
</div>
<div id="linksContainer" <?php if (!$this->config->linksEnable) echo 'style="display: none"' ?> class="form-item clearfix" style="margin-bottom: 5px;">
    <div class="form-label">
        <label><?php echo get_string('numberoflinks', 'block_course_menu') ?></label>
        <span class="form-shortname">block_course_menu_links</span>
    </div>
    <div class="form-setting">
        <input type="text" name="linksCount" id="linksCount" value="<?php echo !empty($this->config->links) ? count($this->config->links) : '0' ?>" />
        <br />
        <button type="button" id="change-links-no"><?php echo get_string('change', 'block_course_menu') ?></button>
    </div>
    <div id="linksTableContainer"></div>
</div>
<div style="display: none" id="link-template" class="link-template">
    <div class="form-item link-template" style="margin-bottom:0; margin-top: 3px;">
        <input type="hidden" name="_change_" class="skip" value="1" />
        <hr />
        <div class="form-setting"><strong>__title__</strong></div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><label><?php echo get_string('name', 'block_course_menu') ?></label></div>
        <div class="form-setting"><input name="cm_link_name" type="text" class="link-text name" value="__name__" size="30" /></div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><label><?php echo get_string('url', 'block_course_menu') ?></label></div>
        <div class="form-setting"><input name="cm_link_url" type="text" class="link-text url" value="__url__" size="30" /></div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><label><?php echo get_string('window', 'block_course_menu') ?></label></div>
        <div class="form-setting">
            <select name="cm_link_target" class="link-select target" id="target">
                <option style="padding: 2px;" value=""><?php echo get_string('samewindow', 'block_course_menu') ?></option>
                <option style="padding: 2px;" value="_blank"><?php echo get_string('newwindow', 'block_course_menu') ?></option>
            </select>
        </div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><label class="iconLabel"><?php echo get_string('icon', 'block_course_menu') ?></label></div>
        <div class="form-setting">
            <select name="cm_link_icon" class="link-select icon">
                <?php foreach ($icons as $icon) : ?>
                <option style="padding: 5px 2px 5px 28px; background: url('<?php echo $icon['img'] ?>') no-repeat scroll 2px 2px transparent;" 
                        value="<?php echo $icon['val'] ?>"><?php echo $icon['name'] ?></option>
                <?php endforeach ?>
            </select>
        </div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><input name="cm_link_keeppagenavigation" type="checkbox" class="disabled-if-not" /></div>
        <div class="form-setting"><label class="cm_link_keeppagenavigation checkbox-label"><?php echo get_string('keeppagenavigation', 'block_course_menu') ?></label></div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><input name="cm_link_allowresize" type="checkbox" class="disabled-if" /></div>
        <div class="form-setting"><label class="cm_link_allowresize"><?php echo get_string('allowresize', 'block_course_menu') ?></label></div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><input name="cm_link_allowscroll" type="checkbox" class="disabled-if" /></div>
        <div class="form-setting"><label class="cm_link_allowscroll"><?php echo get_string('allowscroll', 'block_course_menu') ?></label></div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><input name="cm_link_showdirectorylinks" type="checkbox" class="disabled-if" /></div>
        <div class="form-setting"><label class="cm_link_showdirectorylinks"><?php echo get_string('showdirectorylinks', 'block_course_menu') ?></label></div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><input name="cm_link_showlocationbar" type="checkbox" class="disabled-if" /></div>
        <div class="form-setting"><label class="cm_link_showlocationbar"><?php echo get_string('showlocationbar', 'block_course_menu') ?></label></div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><input name="cm_link_showmenubar" type="checkbox" class="disabled-if" /></div>
        <div class="form-setting"><label class="cm_link_showmenubar"><?php echo get_string('showmenubar', 'block_course_menu') ?></label></div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><input name="cm_link_showtoolbar" type="checkbox" class="disabled-if" /></div>
        <div class="form-setting"><label class="cm_link_showtoolbar"><?php echo get_string('showtoolbar', 'block_course_menu') ?></label></div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><input name="cm_link_showstatusbar" type="checkbox" class="disabled-if" /></div>
        <div class="form-setting"><label class="cm_link_showstatusbar"><?php echo get_string('showstatusbar', 'block_course_menu') ?></label></div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><label class="cm_link_defaultwidth"><?php echo get_string('defaultwidth', 'block_course_menu') ?></label></div>
        <div class="form-setting"><input name="cm_link_defaultwidth" type="text" class="disabled-if input-text" size="30" value="__dw__" /></div>
        <div style="clear:both;height:3px;"></div>
        <div class="form-label"><label class="cm_link_defaultheight"><?php echo get_string('defaultheight', 'block_course_menu') ?></label></div>
        <div class="form-setting"><input name="cm_link_defaultheight" type="text" class="disabled-if input-text" size="30" value="__dh__" /></div>
        <div style="clear:both;height:3px;"></div>
    </div>
</div>
<hr/>
<div class="form-item clearfix">
    <div class="form-label"><label><strong><?php echo get_string('defaultelements', 'block_course_menu') ?></strong></label></div>
    <div class="form-setting" id="elementsContainer">
        <table border="0">
            <tbody>
            <?php foreach ($this->config->elements as $index => $element) : ?>
                <tr id="element-<?php echo $element['id'] ?>"<?php if (strpos($element['id'], 'link') === 0) echo ' class="link-element"' ?>>
                    <td width="24" style="width: 24px">
                        <?php if (!empty($element['canHide'])) : ?>
                        <a href="javascript:void(0)" class="e-hide-element" rel="e-visible">
                            <img alt="" src="<?php echo $OUTPUT->pix_url($element['visible'] ? 'i/hide' : 'i/show') ?>" class="show-hide" />
                            <input type="hidden" name="visibles[]" value="<?php echo $element['visible'] ?>" class="e-visible" />
                        </a>
                        <?php else : ?>
                            <input type="hidden" name="visibles[]" value="1" class="e-visible" />
                            &nbsp;
                        <?php endif ?>
                    </td>
                    <td>
                        <span class="element-name"><?php echo $element['name'] ?></span>
                        <input type="hidden" name="ids[]" value="<?php echo $element['id'] ?>">
                        <input type="hidden" name="canHides[]" value="<?php echo $element['canHide'] ?>">
                        <input type="hidden" name="urls[]" value="<?php echo $element['url'] ?>">
                        <input type="hidden" name="icons[]" value="<?php echo $element['icon'] ?>">
                    </td>
                    <td class="element-move-up" width="24" style="width:24px">
                        <a href="javascript:void(0)" <?php if ($index == 0) echo 'style="display: none"' ?>>
                            <img src="<?php echo $OUTPUT->pix_url('t/up') ?>" alt="" />
                        </a>
                    </td>
                    <td class="element-move-down" width="24" style="width: 24px">
                        <a href="javascript:void(0)" <?php if ($index > count($this->config->elements) - 2) echo 'style="display: none"' ?>>
                            <img src="<?php echo $OUTPUT->pix_url('t/down') ?>" alt="" />
                        </a>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<table style="display: none">
    <tbody id="element-template">
        <tr>
            <td width="24" style="width: 24px">
                &nbsp;
                <input type="hidden" name="visibles[]" value="1" class="e-visible" />
            </td>
            <td>
                <span class="element-name">__name__</span>
                <input type="hidden" name="ids[]" value="" class="e-id" />
                <input type="hidden" name="canHides[]" value="" class="e-canHide" />
                <input type="hidden" name="urls[]" value="" class="e-url" />
                <input type="hidden" name="icons[]" value="" class="e-icon" />
            </td>
            <td class="element-move-up" width="24" style="width:24px">
                <a href="javascript:void(0)">
                    <img src="<?php echo $OUTPUT->pix_url('t/up') ?>" alt="" />
                </a>
            </td>
            <td class="element-move-down" width="24" style="width: 24px">
                <a href="javascript:void(0)">
                    <img src="<?php echo $OUTPUT->pix_url('t/down') ?>" alt="" />
                </a>
            </td>
        </tr>
    </tbody>
</table>