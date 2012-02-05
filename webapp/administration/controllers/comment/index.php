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
		//specific things for this class
		$this->itemCommentManager = ClassLoader::getInstance()->getClassInstance( 'Model_ItemComment' );
	}

	function doModel() 
	{
		parent::doModel();
		//specific things for this class
		switch ($this->action) 
		{
		case 'bulk_actions':
			$id = Params::getParam('id');
			if ($id) 
			{
				switch (Params::getParam('bulk_actions')) 
				{
				case 'delete_all':
					$this->itemCommentManager->delete(array(DB_CUSTOM_COND => 'pk_i_id IN (' . implode(', ', $id) . ')'));
					foreach ($id as $_id) 
					{
						$iUpdated = $this->itemCommentManager->delete(array('pk_i_id' => $_id));
						osc_add_hook("delete_comment", $_id);
					}
					osc_add_flash_ok_message(_m('The comments have been deleted'), 'admin');
					break;

				case 'activate_all':
					foreach ($id as $_id) 
					{
						$iUpdated = $this->itemCommentManager->update(array('b_active' => 1), array('pk_i_id' => $_id));
						if ($iUpdated) 
						{
							$this->sendCommentActivated($_id);
						}
						osc_add_hook("activate_comment", $_id);
					}
					osc_add_flash_ok_message(_m('The comments have been approved'), 'admin');
					break;

				case 'deactivate_all':
					foreach ($id as $_id) 
					{
						$this->itemCommentManager->update(array('b_active' => 0), array('pk_i_id' => $_id));
						osc_add_hook("deactivate_comment", $_id);
					}
					osc_add_flash_ok_message(_m('The comments have been disapproved'), 'admin');
					break;

				case 'enable_all':
					foreach ($id as $_id) 
					{
						$iUpdated = $this->itemCommentManager->update(array('b_enabled' => 1), array('pk_i_id' => $_id));
						if ($iUpdated) 
						{
							$this->sendCommentActivated($_id);
						}
						osc_add_hook("enable_comment", $_id);
					}
					osc_add_flash_ok_message(_m('The comments have been approved'), 'admin');
					break;

				case 'disable_all':
					foreach ($id as $_id) 
					{
						$this->itemCommentManager->update(array('b_enabled' => 0), array('pk_i_id' => $_id));
						osc_add_hook("disable_comment", $_id);
					}
					osc_add_flash_ok_message(_m('The comments have been disapproved'), 'admin');
					break;
				}
			}
			$this->redirectTo(osc_admin_base_url(true) . "?page=comment");
			break;

		case 'comment_edit':
			$id = Params::getParam('id');
			$comment = ClassLoader::getInstance()->getClassInstance( 'Model_ItemComment' )->findByPrimaryKey($id);
			$this->getView()->assign('comment', $comment);
			$this->doView('comments/frm.php');
			break;

		case 'comment_edit_post':
			$this->itemCommentManager->update(array('s_title' => Params::getParam('title'), 's_body' => Params::getParam('body'), 's_author_name' => Params::getParam('authorName'), 's_author_email' => Params::getParam('authorEmail')), array('pk_i_id' => Params::getParam('id')));
			osc_run_hook('edit_comment', Params::getParam('id'));
			osc_add_flash_ok_message(_m('Great! We just updated your comment'), 'admin');
			$this->redirectTo(osc_admin_base_url(true) . "?page=comment");
			break;

		case 'delete':
			$this->itemCommentManager->deleteByPrimaryKey(Params::getParam('id'));
			osc_add_flash_ok_message(_m('The comment have been deleted'), 'admin');
			osc_run_hook('delete_comment', Params::getParam('id'));
			$this->redirectTo(osc_admin_base_url(true) . "?page=comment");
			break;

		default:
			if (Params::getParam('id') != '') 
			{
				$comments = $this->itemCommentManager->getAllComments(Params::getParam('id'));
			}
			else
			{
				$comments = $this->itemCommentManager->getAllComments();
			}
			$this->getView()->assign('comments', $comments);
			//calling the view...
			$this->doView('comments/index.php');
		}
	}

	function sendCommentActivated($commentId) 
	{
		$aComment = $this->itemCommentManager->findByPrimaryKey($commentId);
		$aItem = ClassLoader::getInstance()->getClassInstance( 'Model_Item' )->findByPrimaryKey($aComment['fk_i_item_id']);
		View::newInstance()->assign('item', $aItem);
		osc_run_hook('hook_email_comment_validated', $aComment);
	}
}
