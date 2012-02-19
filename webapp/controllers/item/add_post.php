<?php
/**
 * OpenSourceClassifieds – software for creating and publishing online classified advertising platforms
 *
 * Copyright (C) 2011 OpenSourceClassifieds
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

class CWebItem extends Controller_Default
{
	private $itemManager;
	private $user;
	private $userId;
	function __construct() 
	{
		parent::__construct();
		$this->itemManager = ClassLoader::getInstance()->getClassInstance( 'Model_Item' );
		// here allways userId == ''
		if (osc_is_web_user_logged_in()) 
		{
			$this->userId = osc_logged_user_id();
			$this->user = ClassLoader::getInstance()->getClassInstance( 'Model_User' )->findByPrimaryKey($this->userId);
		}
		else
		{
			$this->userId = null;
			$this->user = null;
		}
	}

	public function doPost( HttpRequest $req, HttpResponse $res )
	{
		$classLoader = ClassLoader::getInstance();
		$classLoader->loadFile( 'helpers/security' );
		$classLoader->loadFile( 'helpers/sanitize' );
		$locales = $classLoader->getClassInstance( 'Model_Locale' )->listAllEnabled();
		$this->getView()->assign('locales', $locales);
		if (osc_reg_user_post() && $this->user == null) 
		{
			osc_add_flash_warning_message(_m('Only registered users are allowed to post items'));
			$this->redirectTo(osc_base_url(true));
		}
		$mItems = $classLoader->getClassInstance( 'Manager_Item', false, array( false ) );
		$mItems->prepareData(true);
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
		if ((osc_recaptcha_private_key() != '') && Params::existParam("recaptcha_challenge_field")) 
		{
			if (!osc_check_recaptcha()) 
			{
				osc_add_flash_error_message(_m('The Recaptcha code is wrong'));
				$this->redirectTo(osc_item_post_url());
				return false; // BREAK THE PROCESS, THE RECAPTCHA IS WRONG
				
			}
		}
		$success = $mItems->add();
		if ($success != 1 && $success != 2) 
		{
			osc_add_flash_error_message($success);
			$this->redirectTo(osc_item_post_url());
		}
		else
		{
			$this->getSession()->_dropkeepForm('meta_' . $key);
			if ($success == 1) 
			{
				osc_add_flash_ok_message(_m('Check your inbox to verify your email address'));
			}
			else
			{
				osc_add_flash_ok_message(_m('Your item has been published'));
			}
			$itemId = Params::getParam('itemId');
			$item = $this->itemManager->findByPrimaryKey($itemId);
			osc_run_hook('posted_item', $item);

			$classLoader = ClassLoader::getInstance();
			$category = $classLoader->getClassInstance( 'Model_Category' )->findByPrimaryKey(Params::getParam('catId'));
			$searchUrls = $classLoader->getClassInstance( 'Url_Search' );

			$this->getView()->assign('category', $category);
			$url = $searchUrls->osc_search_category_url( $category );
			$this->redirectTo( $url );
		}
	}
}
