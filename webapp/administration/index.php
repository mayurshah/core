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

require '../loader.php';

require_once 'osc/Controller/Secure.php';
require_once 'osc/Controller/Administration.php';

$page = Params::getParam('page');
if( empty( $page ) ) $page = 'index';

$action = Params::getParam( 'action' );
if( empty( $action ) ) $action = 'index';

$classLoader->getClassInstance( 'Ui_MainTheme' );

require 'library/osc/urls.php';
require 'library/osc/themes.php';

$ctrlPath = osc_admin_base_path() . '/controllers/' . $page . '/' . $action . '.php';
require $ctrlPath;

$className = 'CAdmin' . ucfirst( $page );
$ctrl = new $className;
$ctrl->init();
$ctrl->processRequest( new HttpRequest, new HttpResponse );


