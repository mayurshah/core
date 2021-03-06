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
class Field extends \DAO
{
	/**
	 * Set data related to t_meta_fields table
	 */
	function __construct() 
	{
		parent::__construct();
		$this->setTableName('t_meta_fields');
		$this->setPrimaryKey('pk_i_id');
		$this->setFields(array('pk_i_id', 's_name', 'e_type', 'b_required', 's_slug', 's_options'));
	}
	/**
	 * Find a field by its id.
	 *
	 * @access public
	 * @since unknown
	 * @param int $id
	 * @return array Field information. If there's no information, return an empty array.
	 */
	public function findByPrimaryKey($id) 
	{
		$this->dbCommand->select();
		$this->dbCommand->from($this->getTableName());
		$this->dbCommand->where('pk_i_id', $id);
		$result = $this->dbCommand->get();
		if ($result == false) 
		{
			return array();
		}
		return $result->row();
	}
	/**
	 * Find a field by its name
	 *
	 * @access public
	 * @since unknown
	 * @param string $id
	 * @return array Field information. If there's no information, return an empty array.
	 */
	public function findByCategory($id) 
	{
		$this->dbCommand->select('mf.*');
		$this->dbCommand->from(sprintf('%st_meta_fields mf, %st_meta_categories mc', DB_TABLE_PREFIX, DB_TABLE_PREFIX));
		$this->dbCommand->where('mc.fk_i_category_id', $id);
		$this->dbCommand->where('mf.pk_i_id = mc.fk_i_field_id');
		$result = $this->dbCommand->get();
		if ($result == false) 
		{
			return array();
		}
		return $result->result();
	}
	/**
	 * Find fields from a category and an item
	 *
	 * @access public
	 * @since unknown
	 * @param string $id
	 * @return array Field information. If there's no information, return an empty array.
	 */
	public function findByCategoryItem($catId, $itemId) 
	{
		$result = $this->dbCommand->query(sprintf("SELECT query.*, im.s_value as s_value FROM (SELECT mf.* FROM %st_meta_fields mf, %st_meta_categories mc WHERE mc.fk_i_category_id = %d AND mf.pk_i_id = mc.fk_i_field_id) as query LEFT JOIN %st_item_meta im ON im.fk_i_field_id = query.pk_i_id AND im.fk_i_item_id = %d", DB_TABLE_PREFIX, DB_TABLE_PREFIX, $catId, DB_TABLE_PREFIX, $itemId));
		if ($result == false) 
		{
			return array();
		}
		return $result->result();
	}
	/**
	 * Find a field by its name
	 *
	 * @access public
	 * @since unknown
	 * @param string $name
	 * @return array Field information. If there's no information, return an empty array.
	 */
	public function findByName($name) 
	{
		$this->dbCommand->select();
		$this->dbCommand->from($this->getTableName());
		$this->dbCommand->where('s_name', $name);
		$result = $this->dbCommand->get();
		if ($result == false) 
		{
			return array();
		}
		return $result->row();
	}
	/**
	 * Find a field by its name
	 *
	 * @access public
	 * @since unknown
	 * @param string $slug
	 * @return array Field information. If there's no information, return an empty array.
	 */
	public function findBySlug($slug) 
	{
		$this->dbCommand->select();
		$this->dbCommand->from($this->getTableName());
		$this->dbCommand->where('s_slug', $slug);
		$result = $this->dbCommand->get();
		if ($result == false) 
		{
			return array();
		}
		return $result->row();
	}
	/**
	 * Gets which categories are associated with that field
	 *
	 * @access public
	 * @since unknown
	 * @param string $id
	 * @return array
	 */
	public function categories($id) 
	{
		$this->dbCommand->select('fk_i_category_id');
		$this->dbCommand->from(sprintf('%st_meta_categories', DB_TABLE_PREFIX));
		$this->dbCommand->where('fk_i_field_id', $id);
		$result = $this->dbCommand->get();
		if ($result == false) 
		{
			return array();
		}
		$categories = $result->result();
		$cats = array();
		foreach ($categories as $k => $v) 
		{
			$cats[] = $v['fk_i_category_id'];
		}
		return $cats;
	}
	/**
	 * Insert a new field
	 *
	 * @access public
	 * @since unknown
	 * @param string $name
	 * @param string $type
	 * @param string $slug
	 * @param bool $required
	 * @param array $options
	 * @param array $categories
	 */
	public function insertField($name, $type, $slug, $required, $options, $categories = null) 
	{
		$this->dbCommand->insert($this->getTableName(), array("s_name" => $name, "e_type" => $type, "b_required" => $required, "s_slug" => $slug, 's_options' => $options));
		$id = $this->dbCommand->insertedId();
		if ($slug == '') 
		{
			$this->dbCommand->update($this->getTableName(), array('s_slug' => $id), array('pk_i_id' => $id));
		}
		foreach ($categories as $c) 
		{
			$this->dbCommand->insert(sprintf('%st_meta_categories', DB_TABLE_PREFIX), array('fk_i_category_id' => $c, 'fk_i_field_id' => $id));
		}
	}
	/**
	 * Save the categories linked to a field
	 *
	 * @access public
	 * @since unknown
	 * @param int $id
	 * @param array $categories
	 */
	public function insertCategories($id, $categories = null) 
	{
		if ($categories != null) 
		{
			foreach ($categories as $c) 
			{
				$this->dbCommand->insert(sprintf('%st_meta_categories', DB_TABLE_PREFIX), array('fk_i_category_id' => $c, 'fk_i_field_id' => $id));
				$subcategories = ClassLoader::getInstance()->getClassInstance( 'Model_Category' )->findSubcategories($c);
				if (count($subcategories) > 0) 
				{
					foreach ($subcategories as $k => $v) 
					{
						$this->insertCategories($id, array($v['pk_i_id']));
					}
				}
			}
		}
	}
	/**
	 * Removes categories from a field
	 *
	 * @access public
	 * @since unknown
	 * @param int $id
	 * @return bool on success
	 */
	public function cleanCategoriesFromField($id) 
	{
		return $this->dbCommand->delete(sprintf('%st_meta_categories', DB_TABLE_PREFIX), array('fk_i_field_id' => $id));
	}
	/**
	 * Update a field value
	 *
	 * @access public
	 * @since unknown
	 * @param int $itemId
	 * @param int $field
	 * @param string $value
	 * @return mixed false on fail, int of num. of affected rows
	 */
	public function replace($itemId, $field, $value) 
	{
		return $this->dbCommand->replace(sprintf('%st_item_meta', DB_TABLE_PREFIX), array('fk_i_item_id' => $itemId, 'fk_i_field_id' => $field, 's_value' => $value));
	}
	/**
	 * Delete a field and all information associated with it
	 *
	 * @access public
	 * @since unknown
	 * @param int $id
	 * @return bool on success
	 */
	public function deleteByPrimaryKey($id) 
	{
		$this->dbCommand->delete(sprintf('%st_item_meta', DB_TABLE_PREFIX), array('fk_i_field_id' => $id));
		$this->dbCommand->delete(sprintf('%st_meta_categories', DB_TABLE_PREFIX), array('fk_i_field_id' => $id));
		return $this->dbCommand->delete($this->getTableName(), array('pk_i_id' => $id));
	}
}
