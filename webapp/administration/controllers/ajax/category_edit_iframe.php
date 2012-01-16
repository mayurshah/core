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
define('IS_AJAX', true);
class CAdminAjax extends AdminSecBaseModel
{
	function __construct() 
	{
		parent::__construct();
		$this->ajax = true;
	}

	function doModel() 
	{
		$this->_exportVariableToView('category', Category::newInstance()->findByPrimaryKey(Params::getParam("id")));
		$this->_exportVariableToView('languages', Locale::newInstance()->listAllEnabled());
		osc_current_admin_theme_path("categories/iframe.php");
	}
}

