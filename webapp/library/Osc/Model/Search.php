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

class Search extends SearchAbstract
{
	private $conditions;
	private $tables;
	private $sql;
	private $order_column;
	private $order_direction;
	private $limit_init;
	private $results_per_page;
	private $cities;
	private $city_areas;
	private $regions;
	private $countries;
	private $categories;
	private $search_fields;
	private $total_results;

	private $queryString;

	public function __construct($expired = false) 
	{
		$this->city_areas = array();
		$this->cities = array();
		$this->regions = array();
		$this->countries = array();
		$this->categories = array();
		$this->conditions = array();
		$this->search_fields = array();
		$this->tables[] = sprintf('%st_item_description as d USE INDEX (fk_i_item_id), %st_category_description as cd ', DB_TABLE_PREFIX, DB_TABLE_PREFIX);
		$this->order();
		$this->limit();
		$this->results_per_page = 10;
		if (!$expired) 
		{
			$this->addTable(sprintf('%st_category', DB_TABLE_PREFIX));
			$this->addConditions(sprintf("%sitem.b_active = 1 ", DB_TABLE_PREFIX));
			$this->addConditions(sprintf("%sitem.b_enabled = 1 ", DB_TABLE_PREFIX));
			$this->addConditions(sprintf("%sitem.b_spam = 0", DB_TABLE_PREFIX));
			$this->addConditions(sprintf(" (%sitem.b_premium = 1 || %st_category.i_expiration_days = 0 || DATEDIFF('%s', %sitem.pub_date) < %st_category.i_expiration_days) ", DB_TABLE_PREFIX, DB_TABLE_PREFIX, date('Y-m-d H:i:s'), DB_TABLE_PREFIX, DB_TABLE_PREFIX));
			$this->addConditions(sprintf("%st_category.b_enabled = 1", DB_TABLE_PREFIX));
			$this->addConditions(sprintf("%st_category.pk_i_id = %sitem.fk_i_category_id", DB_TABLE_PREFIX, DB_TABLE_PREFIX));
		}
		$this->total_results = null;
		parent::__construct();
		$this->setTableName('item');
		$this->setFields(array('pk_i_id'));
	}

	public function setQueryString( $queryString )
	{
		$this->queryString = $queryString;
	}

	public static function getAllowedColumnsForSorting() 
	{
		return (array('i_price', 'pub_date'));
	}
	public static function getAllowedTypesForSorting() 
	{
		return (array(0 => 'asc', 1 => 'desc'));
	}
	/**
	 * Add conditions to the search
	 *
	 * @access public
	 * @since unknown
	 * @param mixed $conditions
	 */
	public function addConditions($conditions) 
	{
		if (is_array($conditions)) 
		{
			foreach ($conditions as $condition) 
			{
				$condition = trim($condition);
				if ($condition != '') 
				{
					if (!in_array($condition, $this->conditions)) 
					{
						$this->conditions[] = $condition;
					}
				}
			}
		}
		else
		{
			$conditions = trim($conditions);
			if ($conditions != '') 
			{
				if (!in_array($conditions, $this->conditions)) 
				{
					$this->conditions[] = $conditions;
				}
			}
		}
	}
	/**
	 * Add new fields to the search
	 *
	 * @access public
	 * @since unknown
	 * @param mixed $fields
	 */
	public function addField($fields) 
	{
		if (is_array($fields)) 
		{
			foreach ($fields as $field) 
			{
				$field = trim($field);
				if ($field != '') 
				{
					if (!in_array($field, $this->fields)) 
					{
						$this->search_fields[] = $field;
					}
				}
			}
		}
		else
		{
			$fields = trim($fields);
			if ($fields != '') 
			{
				if (!in_array($fields, $this->fields)) 
				{
					$this->search_fields[] = $fields;
				}
			}
		}
	}
	/**
	 * Add extra table to the search
	 *
	 * @access public
	 * @since unknown
	 * @param mixed $tables
	 */
	public function addTable($tables) 
	{
		if (is_array($tables)) 
		{
			foreach ($tables as $table) 
			{
				$table = trim($table);
				if ($table != '') 
				{
					if (!in_array($table, $this->tables)) 
					{
						$this->tables[] = $table;
					}
				}
			}
		}
		else
		{
			$tables = trim($tables);
			if ($tables != '') 
			{
				if (!in_array($tables, $this->tables)) 
				{
					$this->tables[] = $tables;
				}
			}
		}
	}
	/**
	 * Establish the order of the search
	 *
	 * @access public
	 * @since unknown
	 * @param string $o_c column
	 * @param string $o_d direction
	 * @param string $table
	 */
	public function order($o_c = 'pub_date', $o_d = 'DESC', $table = NULL) 
	{
		if ($table == '') 
		{
			$this->order_column = $o_c;
		}
		else if ($table != '') 
		{
			if ($table == '%suser') 
			{
				$this->order_column = sprintf("ISNULL($table.$o_c), $table.$o_c", DB_TABLE_PREFIX, DB_TABLE_PREFIX);
			}
			else
			{
				$this->order_column = sprintf("$table.$o_c", DB_TABLE_PREFIX);
			}
		}
		else
		{
			$this->order_column = sprintf("$o_c", DB_TABLE_PREFIX);
		}
		$this->order_direction = $o_d;
	}
	/**
	 * Limit the results of the search
	 *
	 * @access public
	 * @since unknown
	 * @param int $l_i
	 * @param int $t_p_p results per page
	 */
	public function limit($l_i = 0, $r_p_p = 10) 
	{
		$this->limit_init = $l_i;
		$this->results_per_page = $r_p_p;
	}
	/**
	 * Select the page of the search
	 *
	 * @access public
	 * @since unknown
	 * @param int $p page
	 * @param int $t_p_p results per page
	 */
	public function page($p = 0, $r_p_p = null) 
	{
		if ($r_p_p != null) 
		{
			$this->results_per_page = $r_p_p;
		};
		$this->limit_init = $this->results_per_page * $p;
		$this->results_per_page = $this->results_per_page;
	}
	/**
	 * Add city areas to the search
	 *
	 * @access public
	 * @since unknown
	 * @param mixed $city_area
	 */
	public function addCityArea($city_area = array()) 
	{
		if (is_array($city_area)) 
		{
			foreach ($city_area as $c) 
			{
				$c = trim($c);
				if ($c != '') 
				{
					if (is_numeric($c)) 
					{
						$this->city_areas[] = sprintf("%st_item_location.fk_i_city_area_id = %d ", DB_TABLE_PREFIX, $c);
					}
					else
					{
						$this->city_areas[] = sprintf("%st_item_location.s_city_area LIKE '%%%s%%' ", DB_TABLE_PREFIX, $c);
					}
				}
			}
		}
		else
		{
			$city_area = trim($city_area);
			if ($city_area != "") 
			{
				if (is_numeric($city_area)) 
				{
					$this->city_areas[] = sprintf("%st_item_location.fk_i_city_area_id = %d ", DB_TABLE_PREFIX, $city_area);
				}
				else
				{
					$this->city_areas[] = sprintf("%st_item_location.s_city_area LIKE '%%%s%%' ", DB_TABLE_PREFIX, $city_area);
				}
			}
		}
	}
	/**
	 * Add cities to the search
	 *
	 * @access public
	 * @since unknown
	 * @param mixed $city
	 */
	public function addCity($city = array()) 
	{
		if (is_array($city)) 
		{
			foreach ($city as $c) 
			{
				$c = trim($c);
				if ($c != '') 
				{
					if (is_numeric($c)) 
					{
						$this->cities[] = sprintf("%st_item_location.fk_i_city_id = %d ", DB_TABLE_PREFIX, $c);
					}
					else
					{
						$this->cities[] = sprintf("%st_item_location.s_city LIKE '%%%s%%' ", DB_TABLE_PREFIX, $c);
					}
				}
			}
		}
		else
		{
			$city = trim($city);
			if ($city != "") 
			{
				if (is_numeric($city)) 
				{
					$this->cities[] = sprintf("%st_item_location.fk_i_city_id = %d ", DB_TABLE_PREFIX, $city);
				}
				else
				{
					$this->cities[] = sprintf("%st_item_location.s_city LIKE '%%%s%%' ", DB_TABLE_PREFIX, $city);
				}
			}
		}
	}
	/**
	 * Add regions to the search
	 *
	 * @access public
	 * @since unknown
	 * @param mixed $region
	 */
	public function addRegion($region = array()) 
	{
		if (is_array($region)) 
		{
			foreach ($region as $r) 
			{
				$r = trim($r);
				if ($r != '') 
				{
					if (is_numeric($r)) 
					{
						$this->regions[] = sprintf("%st_item_location.fk_i_region_id = %d ", DB_TABLE_PREFIX, $r);
					}
					else
					{
						$this->regions[] = sprintf("%st_item_location.s_region LIKE '%%%s%%' ", DB_TABLE_PREFIX, $r);
					}
				}
			}
		}
		else
		{
			$region = trim($region);
			if ($region != "") 
			{
				if (is_numeric($region)) 
				{
					$this->regions[] = sprintf("%st_item_location.fk_i_region_id = %d ", DB_TABLE_PREFIX, $region);
				}
				else
				{
					$this->regions[] = sprintf("%st_item_location.s_region LIKE '%%%s%%' ", DB_TABLE_PREFIX, $region);
				}
			}
		}
	}
	/**
	 * Add countries to the search
	 *
	 * @access public
	 * @since unknown
	 * @param mixed $country
	 */
	public function addCountry($country = array()) 
	{
		if (is_array($country)) 
		{
			foreach ($country as $c) 
			{
				$c = trim($c);
				if ($c != '') 
				{
					if (strlen($c) == 2) 
					{
						$this->countries[] = sprintf("%st_item_location.fk_c_country_code = '%s' ", DB_TABLE_PREFIX, strtolower($c));
					}
					else
					{
						$this->countries[] = sprintf("%st_item_location.s_country LIKE '%%%s%%' ", DB_TABLE_PREFIX, $c);
					}
				}
			}
		}
		else
		{
			$country = trim($country);
			if ($country != "") 
			{
				if (strlen($country) == 2) 
				{
					$this->countries[] = sprintf("%st_item_location.fk_c_country_code = '%s' ", DB_TABLE_PREFIX, strtolower($country));
				}
				else
				{
					$this->countries[] = sprintf("%st_item_location.s_country LIKE '%%%s%%' ", DB_TABLE_PREFIX, $country);
				}
			}
		}
	}
	/**
	 * Establish price range
	 *
	 * @access public
	 * @since unknown
	 * @param int $price_min
	 * @param int $price_max
	 */
	public function priceRange($price_min = 0, $price_max = 0) 
	{
		$price_min = 1000000 * $price_min;
		$price_max = 1000000 * $price_max;
		if (is_numeric($price_min) && $price_min != 0) 
		{
			$this->addConditions(sprintf("i_price >= %0.0f", $price_min));
		}
		if (is_numeric($price_max) && $price_max > 0) 
		{
			$this->addConditions(sprintf("i_price <= %0.0f", $price_max));
		}
	}
	/**
	 * Establish max price
	 *
	 * @access public
	 * @since unknown
	 * @param int $price
	 */
	public function priceMax($price) 
	{
		$this->priceRange(null, $price);
	}
	/**
	 * Establish min price
	 *
	 * @access public
	 * @since unknown
	 * @param int $price
	 */
	public function priceMin($price) 
	{
		$this->priceRange($price, null);
	}
	/**
	 * Filter by ad with picture or not
	 *
	 * @access public
	 * @since unknown
	 * @param bool $pic
	 */
	public function withPicture($pic = false) 
	{
		if ($pic) 
		{
			$this->addTable(sprintf('%st_item_resource', DB_TABLE_PREFIX));
			$this->addConditions(sprintf("%st_item_resource.s_content_type LIKE '%%image%%' AND %sitem.pk_i_id = %st_item_resource.fk_i_item_id", DB_TABLE_PREFIX, DB_TABLE_PREFIX, DB_TABLE_PREFIX));
		}
	}
	/**
	 * Return ads from specified users
	 *
	 * @access public
	 * @since unknown
	 * @param mixed $id
	 */
	public function fromUser($id = NULL) 
	{
		if (is_array($id)) 
		{
			$ids = array();
			foreach ($id as $_id) 
			{
				$ids[] = sprintf("%sitem.fk_i_user_id = %d ", DB_TABLE_PREFIX, $_id);
			}
			$this->addConditions(" ( " . implode(" || ", $ids) . " ) ");
		}
		else
		{
			$this->addConditions(sprintf("%sitem.fk_i_user_id = %d ", DB_TABLE_PREFIX, $id));
		}
	}
	/**
	 * Clear the categories
	 *
	 * @access private
	 * @since unknown
	 * @param array $branches
	 */
	private function pruneBranches($branches = null) 
	{
		if ($branches != null) 
		{
			foreach ($branches as $branch) 
			{
				if (!in_array($branch['pk_i_id'], $this->categories)) 
				{
					$this->categories[] = sprintf("%sitem.fk_i_category_id = %d ", DB_TABLE_PREFIX, $branch['pk_i_id']);
					if (isset($branch['categories'])) 
					{
						$list = $this->pruneBranches($branch['categories']);
					}
				}
			}
		}
	}
	/**
	 * Add categories to the search
	 *
	 * @access public
	 * @since unknown
	 * @param mixed $category
	 */
	public function addCategory( $category = null )
	{
		if( empty( $category ) )
			return;

		$categoryModel = new \Osc\Model\Category;
		if (!is_numeric($category)) 
		{
			$category = preg_replace('|/$|', '', $category);
			$aCategory = explode('/', $category);
			$category = $categoryModel->findBySlug($aCategory[count($aCategory) - 1]);
			$category = $category['pk_i_id'];
		}
		$tree = $categoryModel->toSubTree($category);
		if (!in_array($category, $this->categories)) 
		{
			$this->categories[] = sprintf("%sitem.fk_i_category_id = %d ", DB_TABLE_PREFIX, $category);
		}
		$this->pruneBranches($tree);
	}
	public function getCategories()
	{
		return $this->categories;
	}
	private function _conditions() 
	{
		if (count($this->city_areas) > 0) 
		{
			$this->addConditions("( " . implode(' || ', $this->city_areas) . " )");
			$this->addConditions(sprintf(" %sitem.pk_i_id  = %st_item_location.fk_i_item_id ", DB_TABLE_PREFIX, DB_TABLE_PREFIX));
		}
		if (count($this->cities) > 0) 
		{
			$this->addConditions("( " . implode(' || ', $this->cities) . " )");
			$this->addConditions(sprintf(" %sitem.pk_i_id  = %st_item_location.fk_i_item_id ", DB_TABLE_PREFIX, DB_TABLE_PREFIX));
		}
		if (count($this->regions) > 0) 
		{
			$this->addConditions("( " . implode(' || ', $this->regions) . " )");
			$this->addConditions(sprintf(" %sitem.pk_i_id  = %st_item_location.fk_i_item_id ", DB_TABLE_PREFIX, DB_TABLE_PREFIX));
		}
		if (count($this->countries) > 0) 
		{
			$this->addConditions("( " . implode(' || ', $this->countries) . " )");
			$this->addConditions(sprintf(" %sitem.pk_i_id  = %st_item_location.fk_i_item_id ", DB_TABLE_PREFIX, DB_TABLE_PREFIX));
		}
		if (count($this->categories) > 0) 
		{
			$this->addConditions("( " . implode(' || ', $this->categories) . " )");
		}
		$conditionsSQL = implode(' AND ', $this->conditions);
		if ($conditionsSQL != '') 
		{
			$conditionsSQL = " AND " . $conditionsSQL;
		}
		$extraFields = "";
		if (count($this->search_fields) > 0) 
		{
			$extraFields = ",";
			$extraFields.= implode(' ,', $this->search_fields);
		}
		return array('extraFields' => $extraFields, 'conditionsSQL' => $conditionsSQL);
	}
	/**
	 * Make the SQL for the search with all the conditions and filters specified
	 *
	 * @access public
	 * @since unknown
	 * @param bool $count
	 */
	public function makeSQL($count = false) 
	{
		$arrayConditions = $this->_conditions();
		$extraFields = $arrayConditions['extraFields'];
		$conditionsSQL = $arrayConditions['conditionsSQL'];
		$tableList = implode(', ', $this->tables);
		if ($count) 
		{
			$this->sql = sprintf("SELECT  COUNT(DISTINCT %sitem.pk_i_id) as totalItems FROM %sitem, %st_item_location, %s WHERE %st_item_location.fk_i_item_id = %sitem.pk_i_id %s AND %sitem.pk_i_id = d.fk_i_item_id AND %sitem.fk_i_category_id = cd.fk_i_category_id", DB_TABLE_PREFIX, DB_TABLE_PREFIX, DB_TABLE_PREFIX, implode(', ', $this->tables), DB_TABLE_PREFIX, DB_TABLE_PREFIX, $conditionsSQL, DB_TABLE_PREFIX, DB_TABLE_PREFIX, DB_TABLE_PREFIX);
		}
		else
		{
			$this->sql = <<<SQL
SELECT SQL_CALC_FOUND_ROWS DISTINCT
	/*TABLE_PREFIX*/item.pk_i_id, /*TABLE_PREFIX*/item.s_contact_name AS s_user_name, /*TABLE_PREFIX*/item.s_contact_email AS s_user_email, /*TABLE_PREFIX*/item.*, /*TABLE_PREFIX*/t_item_location.*, /*TABLE_PREFIX*/t_item_description.s_title, /*TABLE_PREFIX*/t_category_description.s_name AS s_category_name $extraFields
FROM
	/*TABLE_PREFIX*/item
INNER JOIN
	/*TABLE_PREFIX*/t_item_description ON ( /*TABLE_PREFIX*/t_item_description.fk_i_item_id = /*TABLE_PREFIX*/item.pk_i_id )
INNER JOIN
	/*TABLE_PREFIX*/t_category ON ( /*TABLE_PREFIX*/t_category.pk_i_id = /*TABLE_PREFIX*/item.fk_i_category_id )
INNER JOIN
	/*TABLE_PREFIX*/t_category_description ON ( /*TABLE_PREFIX*/t_category_description.fk_i_category_id = /*TABLE_PREFIX*/t_category.pk_i_id )
LEFT JOIN
	/*TABLE_PREFIX*/t_item_stats ON ( /*TABLE_PREFIX*/t_item_stats.fk_i_item_id = /*TABLE_PREFIX*/item.pk_i_id )
LEFT JOIN
	/*TABLE_PREFIX*/t_item_location ON ( /*TABLE_PREFIX*/t_item_location.fk_i_item_id = /*TABLE_PREFIX*/item.pk_i_id )
WHERE
	TRUE $conditionsSQL
GROUP BY
	/*TABLE_PREFIX*/item.pk_i_id
ORDER BY
	$this->order_column $this->order_direction
LIMIT
	$this->limit_init, $this->results_per_page
SQL;
		}
		$this->sql = str_replace( '/*TABLE_PREFIX*/', DB_TABLE_PREFIX, $this->sql );
		return $this->sql;
	}
	/**
	 * Make the SQL for the location search (returns number of ads from each location)
	 *
	 * @access public
	 * @since unknown
	 * @deprecated it's not used anymore by OpenSourceClassifieds' core
	 * @param string $location
	 */
	public function makeSQLLocation($location = 's_city') 
	{
		$this->addTable(sprintf("%st_item_location", DB_TABLE_PREFIX));
		$condition_sql = implode(' AND ', $this->conditions);
		if ($condition_sql != '') 
		{
			$where_sql = " AND " . $condition_sql;
		}
		else
		{
			$where_sql = "";
		}
		$this->sql = sprintf("SELECT %st_item_location.s_country as country_name, %st_item_location.fk_i_city_id as city_id, %st_item_location.fk_c_country_code, %st_item_location.s_region as region_name, %st_item_location.fk_i_region_id as region_id, %st_item_location.s_city as city_name,COUNT( DISTINCT %st_item_location.fk_i_item_id) as items FROM %sitem, %s WHERE %s GROUP BY %st_item_location.%s", DB_TABLE_PREFIX, DB_TABLE_PREFIX, DB_TABLE_PREFIX, DB_TABLE_PREFIX, DB_TABLE_PREFIX, DB_TABLE_PREFIX, DB_TABLE_PREFIX, DB_TABLE_PREFIX, implode(', ', $this->tables), $where_sql, DB_TABLE_PREFIX, $location);
		return $this->sql;
	}
	/**
	 * Return number of ads selected
	 *
	 * @access public
	 * @since unknown
	 */
	public function count() 
	{
		if( is_null( $this->total_results ) )
			$this->doSearch();

		return intval( $this->total_results );
	}
	/**
	 * Perform the search
	 *
	 * @access public
	 * @since unknown
	 * @param bool $extended if you want to extend ad's data
	 */
	public function doSearch($extended = true) 
	{
		$sql = $this->makeSQL( false );
		$result = $this->dbCommand->query( $sql );
		// get total items
		$datatmp = $this->dbCommand->query('SELECT FOUND_ROWS() as totalItems');
		$data = $datatmp->row();
		if (isset($data['totalItems'])) 
		{
			$this->total_results = $data['totalItems'];
		}
		else
		{
			$this->total_results = 0;
		}
		if ($result == false) 
		{
			return array();
		}
		if ($result) 
		{
			$items = $result->result();
		}
		else
		{
			$items = array();
		}
		if ($extended) 
		{
			$itemModel = new \Osc\Model\Item;
			return $itemModel->extendData($items);
		}
		else
		{
			return $items;
		}
	}
	/**
	 * Return premium ads related to the search
	 *
	 * @access public
	 * @since unknown
	 * @param int $max
	 */
	public function getPremiums($max = 2) 
	{
		$this->sql = <<<SQL
SELECT
	/*TABLE_PREFIX*/item.*, /*TABLE_PREFIX*/t_item_location.*, /*TABLE_PREFIX*/t_category_description.s_name AS s_category_name,
	SUM( /*TABLE_PREFIX*/t_item_stats.i_num_premium_views ) AS total_premium_views,
	( SUM( /*TABLE_PREFIX*/t_item_stats.i_num_premium_views ) + SUM( /*TABLE_PREFIX*/t_item_stats.i_num_premium_views ) * RAND() * 0.7 + DATEDIFF( ?, /*TABLE_PREFIX*/item.pub_date ) * 0.3 ) AS order_premium_views
FROM
	/*TABLE_PREFIX*/item
INNER JOIN
	/*TABLE_PREFIX*/t_category ON ( /*TABLE_PREFIX*/item.fk_i_category_id = /*TABLE_PREFIX*/t_category.pk_i_id )
INNER JOIN
	/*TABLE_PREFIX*/t_category_description ON ( /*TABLE_PREFIX*/t_category_description.fk_i_category_id = /*TABLE_PREFIX*/t_category.pk_i_id )
LEFT JOIN
	/*TABLE_PREFIX*/t_item_stats ON ( /*TABLE_PREFIX*/t_item_stats.fk_i_item_id = /*TABLE_PREFIX*/item.pk_i_id )
LEFT JOIN
	/*TABLE_PREFIX*/t_item_location ON ( /*TABLE_PREFIX*/t_item_location.fk_i_item_id = /*TABLE_PREFIX*/item.pk_i_id )
WHERE
	b_premium IS TRUE
GROUP BY
	/*TABLE_PREFIX*/item.pk_i_id
ORDER BY
	order_premium_views ASC
LIMIT
	0, ?
SQL;
		$currentDate = date( 'Y-m-d H:i:s' );

		$stmt = $this->prepareStatement( $this->sql );
		$stmt->bind_param( 'sd', $currentDate, $max );
		$items = $this->fetchAll( $stmt );
		$stmt->close();

		$mStat = ClassLoader::getInstance()->getClassInstance( 'Model_ItemStats' );
		foreach ($items as $item) 
		{
			$mStat->increase('i_num_premium_views', $item['pk_i_id']);
		}

		return $this->classLoader->getClassInstance( 'Model_Item' )->extendData( $items );
	}
	public function getLatestItems() 
	{
		$arrayConditions = $this->_conditions();
		$extraFields = $arrayConditions['extraFields'];
		$conditionsSQL = $arrayConditions['conditionsSQL'];
		$this->sql = <<<SQL
SELECT
	/*TABLE_PREFIX*/item.*, /*TABLE_PREFIX*/t_item_location.*, /*TABLE_PREFIX*/t_category_description.s_name AS s_category_name $extraFields
FROM
	/*TABLE_PREFIX*/item
INNER JOIN
	/*TABLE_PREFIX*/t_category ON ( /*TABLE_PREFIX*/item.fk_i_category_id = /*TABLE_PREFIX*/t_category.pk_i_id )
INNER JOIN
	/*TABLE_PREFIX*/t_category_description ON ( /*TABLE_PREFIX*/t_category_description.fk_i_category_id = /*TABLE_PREFIX*/t_category.pk_i_id )
LEFT JOIN
	/*TABLE_PREFIX*/t_item_location ON ( /*TABLE_PREFIX*/t_item_location.fk_i_item_id = /*TABLE_PREFIX*/item.pk_i_id )
WHERE
	TRUE $conditionsSQL
GROUP BY
	/*TABLE_PREFIX*/item.pk_i_id
ORDER BY
	?, ?
LIMIT
	?, ?
SQL;
		$stmt = $this->prepareStatement( $this->sql );
		$stmt->bind_param( 'ssdd', $this->order_column, $this->order_direction, $this->limit_init, $this->results_per_page );
		$items = $this->fetchAll( $stmt );
		$stmt->close();

		$itemModel = new \Osc\Model\Item;
		return $itemModel->extendData( $items );
	}
	/**
	 * Returns number of ads from each country
	 *
	 * @access public
	 * @since unknown
	 * @param string $zero if you want to include locations with zero results
	 * @param string $order
	 */
	public function listCountries($zero = ">", $order = "items DESC") 
	{
		$sql = '';
		$sql.= 'SELECT ' . DB_TABLE_PREFIX . 't_country.pk_c_code, ' . DB_TABLE_PREFIX . 't_country.fk_c_locale_code, ' . DB_TABLE_PREFIX . 't_country.s_name as country_name, IFNULL(b.items,0) as items ';
		$sql.= 'FROM (SELECT  ' . DB_TABLE_PREFIX . 't_country.pk_c_code, count(*) as items ';
		$sql.= 'FROM (' . DB_TABLE_PREFIX . 't_item_location, ' . DB_TABLE_PREFIX . 't_category) ';
		$sql.= 'RIGHT JOIN ' . DB_TABLE_PREFIX . 't_item ON ' . DB_TABLE_PREFIX . 'item.pk_i_id = ' . DB_TABLE_PREFIX . 't_item_location.fk_i_item_id ';
		$sql.= 'RIGHT JOIN ' . DB_TABLE_PREFIX . 't_country ON ' . DB_TABLE_PREFIX . 't_country.pk_c_code = ' . DB_TABLE_PREFIX . 't_item_location.fk_c_country_code ';
		$sql.= 'WHERE ' . DB_TABLE_PREFIX . 'item.b_enabled = 1 ';
		$sql.= 'AND ' . DB_TABLE_PREFIX . 'item.b_active = 1 ';
		$sql.= 'AND ' . DB_TABLE_PREFIX . 'item.b_spam = 0 ';
		$sql.= 'AND ' . DB_TABLE_PREFIX . 't_category.b_enabled = 1 ';
		$sql.= 'AND ' . DB_TABLE_PREFIX . 't_category.pk_i_id = ' . DB_TABLE_PREFIX . 'item.fk_i_category_id ';
		$sql.= 'AND (' . DB_TABLE_PREFIX . 'item.b_premium = 1 || ' . DB_TABLE_PREFIX . 't_category.i_expiration_days = 0 || DATEDIFF(\'' . date('Y-m-d H:i:s') . '\',' . DB_TABLE_PREFIX . 'item.pub_date) < ' . DB_TABLE_PREFIX . 't_category.i_expiration_days) ';
		$sql.= 'GROUP BY ' . DB_TABLE_PREFIX . 't_country.pk_c_code ) b ';
		$sql.= 'RIGHT JOIN ' . DB_TABLE_PREFIX . 't_country ON ' . DB_TABLE_PREFIX . 't_country.pk_c_code = b.pk_c_code ';
		$sql.= 'HAVING items ' . $zero . ' 0 ';
		$sql.= 'ORDER BY ' . $order;
		$result = $this->dbCommand->query($sql);
		if ($result == false) 
		{
			return array();
		}
		return $result->result();
	}
	/**
	 * Returns number of ads from each region
	 * <code>
	 *  Search::newInstance()->listRegions($country, ">=", "country_name ASC" )
	 * </code>
	 * @access public
	 * @since unknown
	 * @param string $country
	 * @param string $zero if you want to include locations with zero results
	 * @param string $order
	 */
	public function listRegions($country = '%%%%', $zero = ">", $order = "items DESC") 
	{
		$sql = '';
		$sql.= 'SELECT ' . DB_TABLE_PREFIX . 't_region.pk_i_id as region_id, ' . DB_TABLE_PREFIX . 't_region.s_name as region_name, IFNULL(b.items,0) as items FROM ( ';
		$sql.= 'SELECT fk_i_region_id as region_id, s_region as region_name, count(*) as items ';
		$sql.= 'FROM ( ' . DB_TABLE_PREFIX . 'item, ' . DB_TABLE_PREFIX . 't_item_location, ' . DB_TABLE_PREFIX . 't_category ) ';
		$sql.= 'WHERE ' . DB_TABLE_PREFIX . 'item.pk_i_id = ' . DB_TABLE_PREFIX . 't_item_location.fk_i_item_id ';
		$sql.= 'AND ' . DB_TABLE_PREFIX . 'item.b_enabled = 1 ';
		$sql.= 'AND ' . DB_TABLE_PREFIX . 'item.b_active = 1 ';
		$sql.= 'AND ' . DB_TABLE_PREFIX . 'item.b_spam = 0 ';
		$sql.= 'AND ' . DB_TABLE_PREFIX . 't_category.b_enabled = 1 ';
		$sql.= 'AND ' . DB_TABLE_PREFIX . 't_category.pk_i_id = ' . DB_TABLE_PREFIX . 'item.fk_i_category_id ';
		$sql.= 'AND (' . DB_TABLE_PREFIX . 'item.b_premium = 1 || ' . DB_TABLE_PREFIX . 't_category.i_expiration_days = 0 || DATEDIFF(\'' . date('Y-m-d H:i:s') . '\',' . DB_TABLE_PREFIX . 'item.pub_date) < ' . DB_TABLE_PREFIX . 't_category.i_expiration_days) ';
		$sql.= 'GROUP BY ' . DB_TABLE_PREFIX . 't_item_location.fk_i_region_id ';
		$sql.= 'HAVING items ';
		$sql.= 'ORDER BY ' . $order . ' ) as b ';
		$sql.= 'RIGHT JOIN ' . DB_TABLE_PREFIX . 't_region ON ' . DB_TABLE_PREFIX . 't_region.pk_i_id = b.region_id ';
		if ($country != '%%%%') 
		{
			$sql.= 'WHERE ' . DB_TABLE_PREFIX . 't_region.fk_c_country_code = \'' . $this->dbCommand->connId->real_escape_string($country) . '\' ';
		}
		$sql.= 'HAVING items ' . $zero . ' 0 ';
		$sql.= 'ORDER BY ' . $order;
		$result = $this->dbCommand->query($sql);
		if ($result == false) 
		{
			return array();
		}
		return $result->result();
	}
	/**
	 * Returns number of ads from each city
	 *
	 * <code>
	 *  Search::newInstance()->listCities($region, ">=", "city_name ASC" )
	 * </code>
	 *
	 * @access public
	 * @since unknown
	 * @param string $region
	 * @param string $zero if you want to include locations with zero results
	 * @param string $order
	 */
	public function listCities($region = null, $zero = ">", $order = "city_name ASC") 
	{
		$city_table = $this->getTablePrefix() . 't_city';
		$location_table = $this->getTablePrefix() . 't_item_location';
		$item_table = $this->getTablePrefix() . 'item';
		$category_table = $this->getTablePrefix() . 't_category';
		$this->dbCommand->select(array($location_table . '.fk_i_city_id as city_id', $location_table . '.s_city as city_name', 'count(*) as items',));
		$this->dbCommand->from(array($item_table, $location_table, $category_table,));
		$this->dbCommand->where($item_table . '.pk_i_id = ' . $location_table . '.fk_i_item_id');
		$this->dbCommand->where($category_table . '.pk_i_id = ' . $item_table . '.fk_i_category_id');
		$this->dbCommand->where($item_table . '.b_enabled', '1');
		$this->dbCommand->where($item_table . '.b_active', '1');
		$this->dbCommand->where($item_table . '.b_spam', '0');
		$this->dbCommand->where($category_table . '.b_enabled', '1');
		$this->dbCommand->where($location_table . '.fk_i_city_id IS NOT NULL');
		$this->dbCommand->where('(' . $item_table . '.b_premium = 1 || ' . $category_table . '.i_expiration_days = 0 || DATEDIFF(\'' . date('Y-m-d H:i:s') . '\', ' . $item_table . '.pub_date) < ' . $category_table . '.i_expiration_days)');
		if (is_numeric($region)) 
		{
			$this->dbCommand->where($location_table . '.fk_i_region_id', $region);
		}
		$this->dbCommand->groupBy($location_table . '.fk_i_city_id');
		$this->dbCommand->having('items > 0');
		$this->dbCommand->orderBy($order);
		$rs = $this->dbCommand->get();
		if ($rs == false) 
		{
			return array();
		}
		$result = $rs->result();
		if ($zero == '>=') 
		{
			$aCities = City::newInstance()->listAll();
			$totalCities = array();
			foreach ($aCities as $city) 
			{
				$totalCities[$city['pk_i_id']] = array('city_id' => $city['pk_i_id'], 'city_name' => $city['s_name'], 'items' => 0);
			}
			unset($aCities);
			foreach ($result as $c) 
			{
				$totalCities[$c['city_id']]['items'] = $c['items'];
			}
			$result = $totalCities;
			unset($totalCities);
		}
		return $result;
	}
	/**
	 * Returns number of ads from each city area
	 *
	 * @access public
	 * @since unknown
	 * @param string $city
	 * @param string $zero if you want to include locations with zero results
	 * @param string $order
	 */
	public function listCityAreas($city = null, $zero = ">", $order = "items DESC") 
	{
		$aOrder = split(' ', $order);
		$nOrder = count($aOrder);
		if ($nOrder == 2) $this->dbCommand->orderBy($aOrder[0], $aOrder[1]);
		else if ($nOrder == 1) $this->dbCommand->orderBy($aOrder[0], 'DESC');
		else $this->dbCommand->orderBy('item', 'DESC');
		$this->dbCommand->select('fk_i_city_area_id as city_area_id, s_city_area as city_area_name, fk_i_city_id , s_city as city_name, fk_i_region_id as region_id, s_region as region_name, fk_c_country_code as pk_c_code, s_country  as country_name, count(*) as items, ' . DB_TABLE_PREFIX . 't_country.fk_c_locale_code');
		$this->dbCommand->from(DB_TABLE_PREFIX . 'item, ' . DB_TABLE_PREFIX . 't_item_location, ' . DB_TABLE_PREFIX . 't_category, ' . DB_TABLE_PREFIX . 't_country');
		$this->dbCommand->where(DB_TABLE_PREFIX . 'item.pk_i_id = ' . DB_TABLE_PREFIX . 't_item_location.fk_i_item_id');
		$this->dbCommand->where(DB_TABLE_PREFIX . 'item.b_enabled = 1');
		$this->dbCommand->where(DB_TABLE_PREFIX . 'item.b_active = 1');
		$this->dbCommand->where(DB_TABLE_PREFIX . 'item.b_spam = 0');
		$this->dbCommand->where(DB_TABLE_PREFIX . 't_category.b_enabled = 1');
		$this->dbCommand->where(DB_TABLE_PREFIX . 't_category.pk_i_id = ' . DB_TABLE_PREFIX . 'item.fk_i_category_id');
		$this->dbCommand->where('(' . DB_TABLE_PREFIX . 'item.b_premium = 1 || ' . DB_TABLE_PREFIX . 't_category.i_expiration_days = 0 || DATEDIFF(\'' . date('Y-m-d H:i:s') . '\',' . DB_TABLE_PREFIX . 'item.pub_date) < ' . DB_TABLE_PREFIX . 't_category.i_expiration_days)');
		$this->dbCommand->where('fk_i_city_area_id IS NOT NULL');
		$this->dbCommand->where(DB_TABLE_PREFIX . 't_country.pk_c_code = fk_c_country_code');
		$this->dbCommand->groupBy('fk_i_city_area_id');
		$this->dbCommand->having("items $zero 0");
		$city_int = (int)$city;
		if (is_numeric($city_int) && $city_int != 0) 
		{
			$this->dbCommand->where("fk_i_city_id = $city_int");
		}
		$result = $this->dbCommand->get();
		return $result->result();
	}
}
