<?php
/**
 * OpenSourceClassifieds – software for creating and publishing online classified advertising platforms
 *
 * Copyright (C) 2012 OpenSourceClassifieds
 *
 * This program is free software: you can redistribute it and/or modify it under the terms
 * of the GNU Affero General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
$pageForm = $classLoader->getClassInstance( 'Form_Page' );
if (isset($email['pk_i_id'])) 
{
	$edit = true;
	$title = __("Edit email/alert");
	$action_frm = "edit";
	$btn_text = __("Update");
}
else
{
	$edit = false;
	$title = __("Add an email/alert");
	$action_frm = "add";
	$btn_text = __('Add');
}
?>

        <script type="text/javascript">
            tinyMCE.init({
                mode : "textareas",
                theme : "advanced",
                skin: "o2k7",
                width: "70%",
                height: "140px",
                skin_variant : "silver",
                theme_advanced_buttons1 : "bold,italic,underline,separator,undo,redo,separator,justifycenter,justifyright,justifyfull,separator,bullist,numlist,separator,link,unlink,separator,image,code",
                theme_advanced_buttons2 : "",
                theme_advanced_buttons3 : "",
                theme_advanced_toolbar_align : "left",
                theme_advanced_toolbar_location : "top",
                plugins : "media",
                theme_advanced_buttons1_add : "media"
            });
        </script>
                <div id="content_header" class="content_header">
                    <div style="float: left;">
                        <img src="<?php echo osc_current_admin_theme_url('images/pages-icon.png'); ?>" title="" alt=""/>
                    </div>
                    <div id="content_header_arrow">&raquo; <?php _e($title); ?></div>
                    <div style="clear: both;"></div>
                </div>
                <div id="content_separator"></div>
                <div id="settings_form">
                    <form name="emails_form" id="emails_form" action="<?php echo osc_admin_base_url(true); ?>" method="post" onSubmit="return checkForm()">
                        <input type="hidden" name="action" value="<?php echo $action_frm; ?>" />
                        <input type="hidden" name="page" value="email" />
                        <?php $pageForm->primary_input_hidden($email); ?>
                        <div class="FormElement">
                            <div class="FormElementName">
                                <?php _e('Internal name (name to easily identify this email/alert)'); ?>
                            </div>
			    <div class="FormElementInput">
				<input id="s_internal_name" type="text" name="s_internal_name" value="email_item_inquiry" disabled="disabled" readonly="readonly" />
                            </div>
                        </div>
                        <div class="clear50"></div>
                        <?php
$locales = ClassLoader::getInstance()->getClassInstance( 'Model_Locale' )->listAllEnabled();
$pageForm->multilanguage_name_description($locales, $email);
?>
                        <div class="FormElement">
                            <div class="FormElementName"></div>
                            <div class="FormElementInput">
                                <button class="formButton" type="button" onclick="window.location='<?php echo osc_admin_base_url(true); ?>?page=email';" ><?php _e('Cancel'); ?></button>
                                <button class="formButton" type="submit"><?php echo $btn_text; ?></button>
                            </div>
                        </div>
                    </form>
                </div>

