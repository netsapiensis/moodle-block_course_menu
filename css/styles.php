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
?>
<style type="text/css">
td#chaptersTableContainer table tr td {
    padding: 2px 6px;
    border: 1px solid #000;
}
a.enableDisable {
    text-decoration: none !important;
}
a.showHide {
    display: block;
    padding-left: 20px;
}
a.showHide.minus {
    background: url("<?php echo $OUTPUT->pix_url('t/expanded') ?>") right center no-repeat;
    padding-left: 0;
    padding-right: 20px;
}

a.showHide.plus {
    background: url("<?php echo $OUTPUT->pix_url('t/collapsed') ?>") left center no-repeat;
}

.showHideCont {
    float: right;
    margin-right: 50px;
}
</style>