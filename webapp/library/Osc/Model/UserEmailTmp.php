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
namespace Osc\Model;
/**
 *
 */
class UserEmailTmp extends \DAO
{
	function __construct() 
	{
		parent::__construct();
		$this->setTableName('t_user_email_tmp');
		$this->setPrimaryKey('fk_i_user_id');
		$this->setFields(array('fk_i_user_id', 's_new_email', 'dt_date'));
	}
	/**
	 *
	 * @access public
	 * @since unknown
	 * @param type $id
	 * @return array
	 */
	function findByPk($id) 
	{
		return $this->findByPrimaryKey($id);
	}
	/**
	 *
	 * @access public
	 * @since unknown
	 * @param type $userEmailTmp
	 * @return array
	 */
	public function insertOrUpdate($userEmailTmp) 
	{
		$status = $this->dbCommand->insert($this->getTableName(), array('fk_i_user_id' => $userEmailTmp['fk_i_user_id'], 's_new_email' => $userEmailTmp['s_new_email'], 'dt_date' => date('Y-m-d H:i:s')));
		if (!$status) 
		{
			$this->dbCommand->update($this->getTableName(), array('s_new_email' => $userEmailTmp['s_new_email'], 'dt_date' => date('Y-m-d H:i:s')), array('fk_i_user_id' => $userEmailTmp['fk_i_user_id']));
		}
	}
}
