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
 * Model database for City table
 *
 * @package OpenSourceClassifieds
 * @subpackage Model
 * @since unknown
 */
class City extends \DAO
{
	function __construct() 
	{
		parent::__construct();
		$this->setTableName('t_city');
		$this->setPrimaryKey('pk_i_id');
		$this->setFields(array('pk_i_id', 'fk_i_region_id', 's_name', 'fk_c_country_code', 'b_active'));
	}
	/**
	 * Get the cities having part of the city name and region (it can be null)
	 *
	 * @access public
	 * @since unknown
	 * @param string $query The beginning of the city name to look for
	 * @param int|null $regionId Region id
	 * @return array If there's an error or 0 results, it returns an empty array
	 */
	function ajax($query, $regionId = null) 
	{
		$this->dbCommand->select('pk_i_id as id, s_name as label, s_name as value');
		$this->dbCommand->from($this->getTableName());
		$this->dbCommand->like('s_name', $query, 'after');
		if ($regionId != null) 
		{
			$this->dbCommand->where('fk_i_region_id', $regionId);
		}
		$result = $this->dbCommand->get();
		if ($result == false) 
		{
			return array();
		}
		return $result->result();
	}
	/**
	 * Get the cities from an specific region id. It's deprecated, use findByRegion
	 *
	 * @access public
	 * @since unknown
	 * @deprecated deprecated since 2.3
	 * @see City::findByRegion
	 * @param int $regionId Region id
	 * @return array If there's an error or 0 results, it returns an empty array
	 */
	function getByRegion($regionId) 
	{
		return $this->findByRegion($regionId);
	}
	/**
	 * Get the cities from an specific region id
	 *
	 * @access public
	 * @since 2.3
	 * @param int $regionId Region id
	 * @return array If there's an error or 0 results, it returns an empty array
	 */
	function findByRegion($regionId) 
	{
		$this->dbCommand->select($this->getFields());
		$this->dbCommand->from($this->getTableName());
		$this->dbCommand->where('fk_i_region_id', $regionId);
		$this->dbCommand->orderBy('s_name', 'ASC');
		$result = $this->dbCommand->get();
		if ($result == false) 
		{
			return array();
		}
		return $result->result();
	}
	/**
	 * Get the citiy by its name and region
	 *
	 * @access public
	 * @since unknown
	 * @param string $query
	 * @param int $regionId
	 * @return array
	 */
	function findByName($cityName, $regionId = null) 
	{
		$this->dbCommand->select($this->getFields());
		$this->dbCommand->from($this->getTableName());
		$this->dbCommand->where('s_name', $cityName);
		$this->dbCommand->limit(1);
		if ($regionId != null) 
		{
			$this->dbCommand->where('fk_i_region_id', $regionId);
		}
		$result = $this->dbCommand->get();
		if ($result == false) 
		{
			return array();
		}
		return $result->row();
	}
	/**
	 * Get all the rows from the table t_city
	 *
	 * @access public
	 * @since unknown
	 * @return array
	 */
	function listAll() 
	{
		$sql = <<<SQL
SELECT
	pk_i_id, fk_i_region_id, s_name, fk_c_country_code, b_active
FROM
	/*TABLE_PREFIX*/t_city
ORDER BY
	s_name ASC
SQL;

		$stmt = $this->prepareStatement( $sql );
		$cities = $this->fetchAll( $stmt );
		$stmt->close();

		return $cities;
	}
}
