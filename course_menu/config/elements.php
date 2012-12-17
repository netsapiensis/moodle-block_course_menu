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
?>
<div class="showHideCont">
    <a class="showHide minus" rel="div_elements" href="javascript:void(0)">
        <?php echo get_string('hide', $this->blockname) ?>
    </a>
    <a class="showHide plus" rel="div_elements" href="javascript:void(0)" style="display: none">
        <?php echo get_string('show', $this->blockname) ?>
    </a>
</div>
<div class="clear"></div>
<div id="t_div_elements">
    <div class="fitem clearfix">
        <div class="felement" id="elementsContainer">
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