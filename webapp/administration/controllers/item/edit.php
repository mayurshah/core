<?php
/*
 *      OpenSourceClassifieds – software for creating and publishing online classified
 *                           advertising platforms
 *
 *                        Copyright (C) 2012 OpenSourceClassifieds
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
class CAdminItem extends Controller_Administration
{
	public function doGet( HttpRequest $req, HttpResponse $res )
	{
		$id = Params::getParam('id');
		$item = ClassLoader::getInstance()->getClassInstance( 'Model_Item' )->findByPrimaryKey($id);
		if (count($item) <= 0) 
		{
			$this->redirectTo(osc_admin_base_url(true) . "?page=item");
		}
		$form = count(->getSession()->_getForm());
		$keepForm = count(->getSession()->_getKeepForm());
		if ($form == 0 || $form == $keepForm) 
		{
		$this->getSession()->_dropKeepForm();
		}
		$this->getView()->assign("item", $item);
		$this->getView()->assign("new_item", FALSE);
		echo $this->getView()->render( 'items/frm.php' );
		$this->getSession()->_clearVariables();
	}

	public function doPost( HttpRequest $req, HttpResponse $res )
	{
		$mItems = new ItemActions(true);
		$mItems->prepareData(false);
		// set all parameters into session
		foreach ($mItems->getData() as $key => $value) 
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
		$success = $mItems->edit();
		if ($success == 1) 
		{
			$id = Params::getParam('userId');
			if ($id != '') 
			{
				$user = ClassLoader::getInstance()->getClassInstance( 'Model_User' )->findByPrimaryKey($id);
				ClassLoader::getInstance()->getClassInstance( 'Model_Item' )->update(array('fk_i_user_id' => $id, 's_contact_name' => $user['s_name'], 's_contact_email' => $user['s_email']), array('pk_i_id' => Params::getParam('id'), 's_secret' => Params::getParam('secret')));
			}
			else
			{
				ClassLoader::getInstance()->getClassInstance( 'Model_Item' )->update(array('fk_i_user_id' => NULL, 's_contact_name' => Params::getParam('contactName'), 's_contact_email' => Params::getParam('contactEmail')), array('pk_i_id' => Params::getParam('id'), 's_secret' => Params::getParam('secret')));
			}
			$this->getSession()->addFlashMessage( _m('Changes saved correctly'), 'admin' );
			$this->redirectTo(osc_admin_base_url(true) . "?page=item");
		}
		else
		{
			$this->getSession()->addFlashMessage( $success, 'admin', 'ERROR' );
			$this->redirectTo(osc_admin_base_url(true) . "?page=item&action=edit&id=" . Params::getParam('id'));
		}
	}
}
