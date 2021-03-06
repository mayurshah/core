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
class CAdminUser extends Controller_Administration
{
	public function doGet( HttpRequest $req, HttpResponse $res )
	{
		$aCountries = array();
		$aRegions = array();
		$aCities = array();
		$aCountries = ClassLoader::getInstance()->getClassInstance( 'Model_Country' )->listAll();
		$region = ClassLoader::getInstance()->getClassInstance( 'Model_Region' );
		if (isset($aCountries[0]['pk_c_code'])) 
		{
			$aRegions = $region->findByCountry($aCountries[0]['pk_c_code']);
		}
		if (isset($aRegions[0]['pk_i_id'])) 
		{
			$aCities = City::newInstance()->findByRegion($aRegions[0]['pk_i_id']);
		}
		$this->getView()->assign('user', null);
		$this->getView()->assign('countries', $aCountries);
		$this->getView()->assign('regions', $aRegions);
		$this->getView()->assign('cities', $aCities);
		$this->getView()->assign('locales', ClassLoader::getInstance()->getClassInstance( 'Model_Locale' )->listAllEnabled());
		$this->doView("users/frm.php");
	}

	public function doPost( HttpRequest $req, HttpResponse $res )
	{
		$userActions = $this->getClassLoader()->getClassInstance( 'Manager_User', false, array( true ) );
		$success = $userActions->add();
		$session = $this->getSession();
		switch ($success) 
		{
		case 1:
			$session->addFlashMessage(_m('The user has been created. We\'ve sent an activation e-mail') );
			break;

		case 2:
			$session->addFlashMessage(_m('The user has been created successfully') );
			break;

		case 3:
			$session->addFlashMessage(_m('Sorry, but that e-mail is already in use'), 'WARNING' );
			break;

		case 5:
			$session->addFlashMessage(_m('The specified e-mail is not valid'), 'WARNING' );
			break;

		case 6:
			$session->addFlashMessage(_m('Sorry, the password cannot be empty'), 'WARNING' );
			break;

		case 7:
			$session->addFlashMessage(_m("Sorry, passwords don't match"), 'WARNING' );
			break;
		}
		$this->redirectTo(osc_admin_base_url(true) . '?page=user');
	}
}

