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
class CAdminIndex extends Controller_Default
{
	public function doPost( HttpRequest $req, HttpResponse $res )
	{
		$classLoader = $this->getClassLoader();
		$admin = $classLoader->getClassInstance( 'Model_Admin' )->findByUsername(Params::getParam('user'));
		if ($admin) 
		{
			if ($admin["s_password"] == sha1(Params::getParam('password'))) 
			{
				if (Params::getParam('remember')) 
				{
					$classLoader->loadFile( 'helpers/security' );
					$secret = osc_genRandomPassword();
					$this->getClassLoader()->getClassInstance( 'Model_Admin' )->update(array('s_secret' => $secret), array('pk_i_id' => $admin['pk_i_id']));
					$this->getCookie()->set_expires(osc_time_cookie());
					$this->getCookie()->push('oc_adminId', $admin['pk_i_id']);
					$this->getCookie()->push('oc_adminSecret', $secret);
					$this->getCookie()->push('oc_adminLocale', Params::getParam('locale'));
					$this->getCookie()->set();
				}
				//we are logged in... let's go!
				$this->getSession()->_set('adminId', $admin['pk_i_id']);
				$this->getSession()->_set('adminUserName', $admin['s_username']);
				$this->getSession()->_set('adminName', $admin['s_name']);
				$this->getSession()->_set('adminEmail', $admin['s_email']);
				$this->getSession()->_set('adminLocale', Params::getParam('locale'));
			}
			else
			{
				osc_add_flash_error_message(_m('The password is incorrect'), 'admin');
			}
		}
		else
		{
			osc_add_flash_error_message(_m('That username does not exist'), 'admin');
		}
		$this->redirectTo(osc_admin_base_url());
	}
}

