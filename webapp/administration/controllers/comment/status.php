<?php
/*
 *      OpenSourceClassifieds – software for creating and publishing online classified
 *                           advertising platforms
 *
 *                        Copyright (C) 2011 OpenSourceClassifieds
 *
 *       This program is free software: you can redistribute it and/or
 *     modify it under the terms of the GNU Affero General Public License
 *     as published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful, but
 *         WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Affero General Public License for more details.
 *
 *      You should have received a copy of the GNU Affero General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
class CAdminComment extends Controller_Administration
{
	private $itemCommentManager;
	function __construct() 
	{
		parent::__construct();
		$this->itemCommentManager = ClassLoader::getInstance()->getClassInstance( 'Model_ItemComment' );
	}

	function doModel() 
	{
		parent::doModel();
		$id = Params::getParam('id');
		$value = Params::getParam('value');
		if (!$id) return false;
		$id = (int)$id;
		if (!is_numeric($id)) return false;
		if (!in_array($value, array('ACTIVE', 'INACTIVE', 'ENABLE', 'DISABLE'))) return false;
		if ($value == 'ACTIVE') 
		{
			$iUpdated = $this->itemCommentManager->update(array('b_active' => 1), array('pk_i_id' => $id));
			if ($iUpdated) 
			{
				$this->sendCommentActivated($id);
			}
			osc_add_hook("activate_comment", $id);
			osc_add_flash_ok_message(_m('The comment has been approved'), 'admin');
		}
		else if ($value == 'INACTIVE') 
		{
			$iUpdated = $this->itemCommentManager->update(array('b_active' => 1), array('pk_i_id' => $id));
			osc_add_hook("deactivate_comment", $id);
			osc_add_flash_ok_message(_m('The comment has been disapproved'), 'admin');
		}
		else if ($value == 'ENABLE') 
		{
			$iUpdated = $this->itemCommentManager->update(array('b_enabled' => 1), array('pk_i_id' => $id));
			osc_add_hook("enable_comment", $id);
			osc_add_flash_ok_message(_m('The comment has been enabled'), 'admin');
		}
		else if ($value == 'DISABLE') 
		{
			$iUpdated = $this->itemCommentManager->update(array('b_enabled' => 0), array('pk_i_id' => $id));
			osc_add_hook("disable_comment", $id);
			osc_add_flash_ok_message(_m('The comment has been disabled'), 'admin');
		}
		$this->redirectTo(osc_admin_base_url(true) . "?page=comment");
	}

	function sendCommentActivated($commentId) 
	{
		$aComment = $this->itemCommentManager->findByPrimaryKey($commentId);
		$aItem = ClassLoader::getInstance()->getClassInstance( 'Model_Item' )->findByPrimaryKey($aComment['fk_i_item_id']);
		$this->getView()->assign('item', $aItem);
		osc_run_hook('hook_email_comment_validated', $aComment);
	}
}
