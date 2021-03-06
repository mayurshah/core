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

	public function doGet( HttpRequest $req, HttpResponse $res )
	{
		$locales = ClassLoader::getInstance()->getClassInstance( 'Model_Locale' )->listAllEnabled();
		$this->getView()->assign('locales', $locales);
		$item = $this->itemManager->findByPrimaryKey(Params::getParam('id'));
		if (empty($item)) 
		{
			$this->getSession()->addFlashMessage( _m('This item doesn\'t exist'), 'ERROR' );
			$this->redirectTo(osc_base_url(true));
		}
		else
		{
			$view = $this->getView();
			$view->assign( 'item', $item );
			$view->addJavaScript( osc_current_web_theme_js_url('jquery.validate.min.js') );
			$view->addJavaScript( '/static/scripts/contact.js' );
			if (osc_item_is_expired()) 
			{
				$this->getSession()->addFlashMessage( _m('We\'re sorry, but the item has expired. You can\'t contact the seller'), 'ERROR' );
				$this->redirectTo(osc_item_url());
			}
			if (osc_reg_user_can_contact() && osc_is_web_user_logged_in() || !osc_reg_user_can_contact()) 
			{
				$view->setTitle( __('Contact seller', 'modern') . ' - ' . osc_item_title() . ' - ' . osc_page_title() );
				echo $view->render( 'item/contact' );
			}
			else
			{
				$this->getSession()->addFlashMessage( _m('You can\'t contact the seller, only registered users can'), 'ERROR' );
				$this->redirectTo(osc_item_url());
			}
		}
	}

	public function doPost( HttpRequest $req, HttpResponse $res )
	{
		$classLoader = $this->getClassLoader();
		$classLoader->loadFile( 'helpers/sanitize' );

		$item = $this->itemManager->findByPrimaryKey(Params::getParam('id'));
		$this->getView()->assign('item', $item);
		if ((osc_recaptcha_private_key() != '') && Params::existParam("recaptcha_challenge_field")) 
		{
			if (!osc_check_recaptcha()) 
			{
				$this->getSession()->addFlashMessage( _m('The Recaptcha code is wrong'), 'ERROR' );
				$this->getSession()->_setForm("yourEmail", Params::getParam('yourEmail'));
				$this->getSession()->_setForm("yourName", Params::getParam('yourName'));
				$this->getSession()->_setForm("phoneNumber", Params::getParam('phoneNumber'));
				$this->getSession()->_setForm("message_body", Params::getParam('message'));
				$this->redirectTo(osc_item_url());
				return false; // BREAK THE PROCESS, THE RECAPTCHA IS WRONG
				
			}
		}
		$category = $classLoader->getClassInstance( 'Model_Category' )->findByPrimaryKey($item['fk_i_category_id']);
		if ($category['i_expiration_days'] > 0) 
		{
			$item_date = strtotime($item['pub_date']) + ($category['i_expiration_days'] * (24 * 3600));
			$date = time();
			if ($item_date < $date && $item['b_premium'] != 1) 
			{
				// The item is expired, we can not contact the seller
				$this->getSession()->addFlashMessage( _m('We\'re sorry, but the item has expired. You can\'t contact the seller'), 'ERROR' );
				$this->redirectTo(osc_item_url());
			}
		}

		$flash_error = '';
		$item = $this->itemManager->findByPrimaryKey(Params::getParam('id'));
		$aItem['item'] = $item;
		ClassLoader::getInstance()->getClassInstance( 'View_Html' )->assign('item', $aItem['item']);
		$aItem['id'] = Params::getParam('id');
		$aItem['yourEmail'] = Params::getParam('yourEmail');
		$aItem['yourName'] = Params::getParam('yourName');
		$aItem['message'] = Params::getParam('message');
		$aItem['phoneNumber'] = Params::getParam('phoneNumber');

		$flash_error = false;
		// check parameters
		if (!osc_validate_email($aItem['yourEmail'], true)) 
		{
			$flash_error.= __("Invalid email address") . PHP_EOL;
		}
		else if (!osc_validate_text($aItem['message'])) 
		{
			$flash_error.= __("Message: this field is required") . PHP_EOL;
		}
		else if (!osc_validate_text($aItem['yourName'])) 
		{
			$flash_error.= __("Your name: this field is required") . PHP_EOL;
		}
		if ($flash_error ==false ) 
		{
			osc_run_hook('hook_email_item_inquiry', $aItem);
			$this->getSession()->addFlashMessage( _m('We\'ve just sent an e-mail to the seller') );
		}
		else
		{
			$this->getSession()->addFlashMessage( $result, 'ERROR' );
		}
		$itemUrls = $classLoader->getClassInstance( 'Url_Item' );
		$this->redirectTo( $itemUrls->getDetailsUrl( $item ) );
	}
}
