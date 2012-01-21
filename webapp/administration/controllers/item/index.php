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
class CAdminItem extends AdminSecBaseModel
{
	private $itemManager;
	function __construct() 
	{
		parent::__construct();
		//specific things for this class
		$this->itemManager = $this->getClassLoader()->getClassInstance( 'Model_Item' );
	}
	function doModel() 
	{
		parent::doModel();
		//specific things for this class
		switch ($this->action) 
		{
		case 'delete': //delete
			$id = Params::getParam('id');
			$success = false;
			foreach ($id as $i) 
			{
				if ($i) 
				{
					$aItem = $this->itemManager->findByPrimaryKey($i);
					$mItems = new ItemActions(true);
					$success = $mItems->delete($aItem['s_secret'], $aItem['pk_i_id']);
				}
			}
			if ($success) 
			{
				osc_add_flash_ok_message(_m('The item has been deleted'), 'admin');
			}
			else
			{
				osc_add_flash_error_message(_m('The item couldn\'t be deleted'), 'admin');
			}
			$this->redirectTo(osc_admin_base_url(true) . "?page=item");
			break;

		case 'status': //status
			$id = Params::getParam('id');
			$value = Params::getParam('value');
			if (!$id) return false;
			$id = (int)$id;
			if (!is_numeric($id)) return false;
			if (!in_array($value, array('ACTIVE', 'INACTIVE', 'ENABLE', 'DISABLE'))) return false;
			try
			{
				$item = $this->itemManager->findByPrimaryKey($id);
				switch ($value) 
				{
				case 'ACTIVE':
					$this->itemManager->update(array('b_active' => 1), array('pk_i_id' => $id));
					osc_add_flash_ok_message(_m('The item has been activated'), 'admin');
					if ($item['b_enabled'] == 1 && $item['b_active'] == 0) 
					{
						CategoryStats::newInstance()->increaseNumItems($item['fk_i_category_id']);
						if ($item['fk_i_user_id'] != null) 
						{
							$user = $this->getClassLoader()->getClassInstance( 'Model_User' )->findByPrimaryKey($item['fk_i_user_id']);
							if ($user) 
							{
								$this->getClassLoader()->getClassInstance( 'Model_User' )->update(array('i_items' => $user['i_items'] + 1), array('pk_i_id' => $user['pk_i_id']));
							}
						}
					}
					break;

				case 'INACTIVE':
					$this->itemManager->update(array('b_active' => 0), array('pk_i_id' => $id));
					osc_add_flash_ok_message(_m('The item has been deactivated'), 'admin');
					if ($item['b_enabled'] == 1 && $item['b_active'] == 1) 
					{
						CategoryStats::newInstance()->decreaseNumItems($item['fk_i_category_id']);
						if ($item['fk_i_user_id'] != null) 
						{
							$user = $this->getClassLoader()->getClassInstance( 'Model_User' )->findByPrimaryKey($item['fk_i_user_id']);
							if ($user) 
							{
								$this->getClassLoader()->getClassInstance( 'Model_User' )->update(array('i_items' => $user['i_items'] - 1), array('pk_i_id' => $user['pk_i_id']));
							}
						}
					}
					break;

				case 'ENABLE':
					$this->itemManager->update(array('b_enabled' => 1), array('pk_i_id' => $id));
					osc_add_flash_ok_message(_m('The item has been enabled'), 'admin');
					if ($item['b_enabled'] == 0 && $item['b_active'] == 1) 
					{
						CategoryStats::newInstance()->increaseNumItems($item['fk_i_category_id']);
						if ($item['fk_i_user_id'] != null) 
						{
							$user = $this->getClassLoader()->getClassInstance( 'Model_User' )->findByPrimaryKey($item['fk_i_user_id']);
							if ($user) 
							{
								$this->getClassLoader()->getClassInstance( 'Model_User' )->update(array('i_items' => $user['i_items'] + 1), array('pk_i_id' => $user['pk_i_id']));
							}
						}
					}
					break;

				case 'DISABLE':
					$this->itemManager->update(array('b_enabled' => 0), array('pk_i_id' => $id));
					osc_add_flash_ok_message(_m('The item has been disabled'), 'admin');
					if ($item['b_enabled'] == 1 && $item['b_active'] == 1) 
					{
						CategoryStats::newInstance()->decreaseNumItems($item['fk_i_category_id']);
						if ($item['fk_i_user_id'] != null) 
						{
							$user = $this->getClassLoader()->getClassInstance( 'Model_User' )->findByPrimaryKey($item['fk_i_user_id']);
							if ($user) 
							{
								$this->getClassLoader()->getClassInstance( 'Model_User' )->update(array('i_items' => $user['i_items'] - 1), array('pk_i_id' => $user['pk_i_id']));
							}
						}
					}
					break;
				}
			}
			catch(Exception $e) 
			{
				osc_add_flash_error_message(sprintf(_m('Error: %s'), $e->getMessage()), 'admin');
			}
			$this->redirectTo(osc_admin_base_url(true) . "?page=item");
			break;

		case 'status_premium': //status premium
			$id = Params::getParam('id');
			$value = Params::getParam('value');
			if (!$id) return false;
			$id = (int)$id;
			if (!is_numeric($id)) return false;
			if (!in_array($value, array(0, 1))) return false;
			try
			{
				$mItems = new ItemActions(true);
				$mItems->premium($id, $value == 1 ? true : false);
				/*$this->itemManager->update(
				                                                array('b_premium' => $value),
				                                                array('pk_i_id' => $id)
				                                        );*/
				osc_add_flash_ok_message(_m('Changes have been applied'), 'admin');
			}
			catch(Exception $e) 
			{
				osc_add_flash_error_message(sprintf(_m('Error: %s'), $e->getMessage()), 'admin');
			}
			$this->redirectTo(osc_admin_base_url(true) . "?page=item");
			break;

		case 'status_spam': //status spam
			$id = Params::getParam('id');
			$value = Params::getParam('value');
			if (!$id) return false;
			$id = (int)$id;
			if (!is_numeric($id)) return false;
			if (!in_array($value, array(0, 1))) return false;
			try
			{
				$this->itemManager->update(array('b_spam' => $value), array('pk_i_id' => $id));
				osc_add_flash_ok_message(_m('Changes have been applied'), 'admin');
			}
			catch(Exception $e) 
			{
				osc_add_flash_error_message(sprintf(_m('Error: %s'), $e->getMessage()), 'admin');
			}
			$this->redirectTo(osc_admin_base_url(true) . "?page=item");
			break;

		case 'clear_stat':
			$id = Params::getParam('id');
			$stat = Params::getParam('stat');
			if (!$id) return false;
			if (!$stat) return false;
			$id = (int)$id;
			if (!is_numeric($id)) return false;
			$success = $this->itemManager->clearStat($id, $stat);
			if ($success) 
			{
				osc_add_flash_ok_message(_m('The item has been unmarked as') . " $stat", 'admin');
			}
			else
			{
				osc_add_flash_error_message(_m('The item hasn\'t been unmarked as') . " $stat", 'admin');
			}
			$this->redirectTo(osc_admin_base_url(true) . "?page=item&stat=" . $stat);
			break;

		case 'deleteResource': //delete resource
			$id = Params::getParam('id');
			$name = Params::getParam('name');
			$fkid = Params::getParam('fkid');
			// delete files
			osc_deleteResource($id);
			ItemResource::newInstance()->delete(array('pk_i_id' => $id, 'fk_i_item_id' => $fkid, 's_name' => $name));
			osc_add_flash_ok_message(_m('Resource deleted'), 'admin');
			$this->redirectTo(osc_admin_base_url(true) . "?page=item");
			break;

		case 'post': // add item
			$form = count($this->getSession()->_getForm());
			$keepForm = count($this->getSession()->_getKeepForm());
			if ($form == 0 || $form == $keepForm) 
			{
				$this->getSession()->_dropKeepForm();
			}
			$this->getView()->_exportVariableToView("new_item", TRUE);
			$this->doView('items/frm.php');
			break;

		case 'post_item': //post item
			$mItem = new ItemActions(true);
			$mItem->prepareData(true);
			// set all parameters into session
			foreach ($mItem->data as $key => $value) 
			{
				$this->getSession()->_setForm($key, $value);
			}
			$meta = Params::getParam('meta');
			if (is_array($meta)) 
			{
				foreach ($meta as $key => $value) 
				{
					$this->getSession()->_setForm('meta_' . $key, $value);
					$this->getSession()->_keepForm('meta_' . $key);
				}
			}
			$success = $mItem->add();
			if ($success == 1 || $success == 2) 
			{
				osc_add_flash_ok_message(_m('A new item has been added'), 'admin');
				$this->redirectTo(osc_admin_base_url(true) . "?page=item");
			}
			else
			{
				osc_add_flash_error_message($success, 'admin');
				$this->redirectTo(osc_admin_base_url(true) . "?page=item&action=post");
			}
			break;

		default: //default
			$catId = Params::getParam('catId');
			$countries = $this->getClassLoader()->getClassInstance( 'Model_Country' )->listAll();
			$regions = array();
			if (count($countries) > 0) 
			{
				$regions = Region::newInstance()->findByCountry($countries[0]['pk_c_code']);
			}
			$cities = array();
			if (count($regions) > 0) 
			{
				$cities = City::newInstance()->findByRegion($regions[0]['pk_i_id']);
			}
			//preparing variables for the view
			$this->getView()->_exportVariableToView("users", $this->getClassLoader()->getClassInstance( 'Model_User' )->listAll());
			$this->getView()->_exportVariableToView("catId", $catId);
			$this->getView()->_exportVariableToView("stat", Params::getParam('stat'));
			$this->getView()->_exportVariableToView("countries", $countries);
			$this->getView()->_exportVariableToView("regions", $regions);
			$this->getView()->_exportVariableToView("cities", $cities);
			//calling the view...
			$this->doView('items/index.php');
		}
	}
	function doView($file) 
	{
		osc_current_admin_theme_path($file);
		$this->getSession()->_clearVariables();
	}
}
