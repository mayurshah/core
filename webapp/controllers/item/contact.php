<?php if ( ! defined('ABS_PATH')) exit('ABS_PATH is not loaded. Direct access is not allowed.');

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

    class CWebItem extends BaseModel
    {
        private $itemManager;
        private $user;
        private $userId;

        function __construct() {
            parent::__construct() ;
            $this->itemManager = Item::newInstance();

            // here allways userId == ''
            if( osc_is_web_user_logged_in() ){
                $this->userId = osc_logged_user_id();
                $this->user = User::newInstance()->findByPrimaryKey($this->userId);
            }else{
                $this->userId = null;
                $this->user = null;
            }
        }

        function doModel() {
            $locales = OSCLocale::newInstance()->listAllEnabled() ;
            $this->_exportVariableToView('locales', $locales) ;

            switch( $this->action ){
                case 'contact':
                    $item = $this->itemManager->findByPrimaryKey( Params::getParam('id') ) ;
                    if( empty($item) ){
                        osc_add_flash_error_message( _m('This item doesn\'t exist') );
                        $this->redirectTo( osc_base_url(true) );
                    } else {
                        $this->_exportVariableToView('item', $item) ;
                        
                        if( osc_item_is_expired () ) {
                            osc_add_flash_error_message( _m('We\'re sorry, but the item has expired. You can\'t contact the seller')) ;
                            $this->redirectTo( osc_item_url() );
                        }

                        if( osc_reg_user_can_contact() && osc_is_web_user_logged_in() || !osc_reg_user_can_contact() ){
                            $this->doView('item-contact.php');
                        } else {
                            osc_add_flash_error_message( _m('You can\'t contact the seller, only registered users can')) ;
                            $this->redirectTo( osc_item_url() );
                        }
                    }
                break;
                case 'contact_post':
                    $item = $this->itemManager->findByPrimaryKey( Params::getParam('id') ) ;
                    $this->_exportVariableToView('item', $item) ;
                    if ((osc_recaptcha_private_key() != '') && Params::existParam("recaptcha_challenge_field")) {
                        if(!osc_check_recaptcha()) {
                            osc_add_flash_error_message( _m('The Recaptcha code is wrong')) ;                    
                            Session::newInstance()->_setForm("yourEmail",   Params::getParam('yourEmail'));
                            Session::newInstance()->_setForm("yourName",    Params::getParam('yourName'));
                            Session::newInstance()->_setForm("phoneNumber", Params::getParam('phoneNumber'));
                            Session::newInstance()->_setForm("message_body",Params::getParam('message'));
                            $this->redirectTo( osc_item_url( ) );
                            return false; // BREAK THE PROCESS, THE RECAPTCHA IS WRONG
                        }
                    }

                    $category = Category::newInstance()->findByPrimaryKey($item['fk_i_category_id']);

                    if($category['i_expiration_days'] > 0) {
                        $item_date = strtotime($item['dt_pub_date'])+($category['i_expiration_days']*(24*3600)) ;
                        $date = time();
                        if($item_date < $date && $item['b_premium']!=1) {
                            // The item is expired, we can not contact the seller
                            osc_add_flash_error_message( _m('We\'re sorry, but the item has expired. You can\'t contact the seller')) ;
                            $this->redirectTo(osc_item_url( ));
                        }
                    }

                    $mItem = new ItemActions(false);

                    $result = $mItem->contact();
                    
                    if(is_string($result)){
                        osc_add_flash_error_message( $result ) ;
                    } else {
                        osc_add_flash_ok_message( _m('We\'ve just sent an e-mail to the seller')) ;
                    }
                    
                    $this->redirectTo( osc_item_url( ) );

                    break;
            }
        }

        function doView($file) {
            osc_run_hook("before_html");
            osc_current_web_theme_path($file) ;
            Session::newInstance()->_clearVariables();
            osc_run_hook("after_html");
        }
    }

