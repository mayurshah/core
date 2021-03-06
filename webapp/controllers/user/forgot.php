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
class CWebUser extends Controller
{
	public function __construct() 
	{
		parent::__construct();
		if (!osc_users_enabled()) 
		{
			$this->getSession()->addFlashMessage( _m('Users not enabled'), 'ERROR' );
			$this->redirectTo(osc_base_url(true));
		}
	}

	public function doGet( HttpRequest $req, HttpResponse $res )
	{
		$user = ClassLoader::getInstance()->getClassInstance( 'Model_User' )->findByIdPasswordSecret(Params::getParam('userId'), Params::getParam('code'));
		if ($user) 
		{
			echo $this->getView()->render( 'user/forgot-password' );

		}
		else
		{
			$this->getSession()->addFlashMessage( _m('Sorry, the link is not valid'), 'ERROR' );
			$this->redirectToBaseUrl();
		}
	}
	
	public function doPost( HttpRequest $req, HttpResponse $res )
	{
		if ((Params::getParam('new_password') == '') || (Params::getParam('new_password2') == '')) 
		{
			$this->getSession()->addFlashMessage( _m('Password cannot be blank'), 'WARNING' );
			$this->redirectTo(osc_forgot_user_password_confirm_url(Params::getParam('userId'), Params::getParam('code')));
		}
		$user = ClassLoader::getInstance()->getClassInstance( 'Model_User' )->findByIdPasswordSecret(Params::getParam('userId'), Params::getParam('code'));
		if ($user['b_enabled'] == 1) 
		{
			if (Params::getParam('new_password') == Params::getParam('new_password2')) 
			{
				ClassLoader::getInstance()->getClassInstance( 'Model_User' )->update(array('s_pass_code' => osc_genRandomPassword(50), 's_pass_date' => date('Y-m-d H:i:s', 0), 's_pass_ip' => $_SERVER['REMOTE_ADDR'], 's_password' => sha1(Params::getParam('new_password'))), array('pk_i_id' => $user['pk_i_id']));
				$this->getSession()->addFlashMessage( _m('The password has been changed') );
				$this->redirectTo(osc_user_login_url());
			}
			else
			{
				$this->getSession()->addFlashMessage( _m('Error, the password don\'t match'), 'ERROR' );
				$this->redirectTo(osc_forgot_user_password_confirm_url(Params::getParam('userId'), Params::getParam('code')));
			}
		}
		else
		{
			$this->getSession()->addFlashMessage( _m('Sorry, the link is not valid'), 'ERROR' );
		}
		$this->redirectToBaseUrl();
	}
}

