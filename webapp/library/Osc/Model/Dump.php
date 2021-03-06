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
 * Model database for Dump database tables
 *
 * @package OpenSourceClassifieds
 * @subpackage Model
 * @since unknown
 */
class Dump extends \DAO
{
	/**
	 * Return all tables from database
	 *
	 * @return array
	 */
	function showTables() 
	{
		$res = $this->dbCommand->query('SHOW TABLES;');
		if ($res) 
		{
			return $res->result();
		}
		else
		{
			return array();
		}
	}
	/**
	 * Dump into path the table structure of $table
	 *
	 * @param string $path
	 * @param string $table
	 * @return bool
	 */
	function table_structure($path, $table) 
	{
		if (!is_writable($path)) return false;
		$_str = "/* Table structure for table `" . $table . "` */\n";
		$sql = 'show create table `' . $table . '`;';
		$result = $this->dbCommand->query($sql);
		if ($result) 
		{
			$result = $result->result();
		}
		else
		{
			$result = array();
		}
		foreach ($result as $_line) 
		{
			$_str.= $_line['Create Table'] . ';';
			$_str.= "\n\n";
		}
		$f = fopen($path, "a");
		fwrite($f, $_str);
		fclose($f);
		return true;
	}
	/**
	 * Dump all table rows into path
	 *
	 * @param type $path
	 * @param type $table
	 * @return bool
	 */
	function table_data($path, $table) 
	{
		if (!is_writable($path)) return false;
		$sql = "select * from `$table`;";
		$res = $this->dbCommand->query($sql);
		if ($res) 
		{
			$result = $res->result();
		}
		else
		{
			$result = array();
		}
		$_str = '';
		if ($res) 
		{
			$num_rows = $res->numRows();
			$num_fields = $res->numFields();
			$fields = $res->resultId->fetch_fields();
			if ($num_rows > 0) 
			{
				$_str.= '/* dumping data for table `' . $table . '` */';
				$_str.= "\n";
				$field_type = array();
				$i = 0;
				while ($meta = $res->resultId->fetch_field()) 
				{
					array_push($field_type, $meta->type);
				}
				$_str.= 'insert into `' . $table . '` values';
				$_str.= "\n";
				$index = 0;
				if ($table == DB_TABLE_PREFIX . 't_category') 
				{
					$this->_dump_table_category($result, $num_fields, $field_type, $fields, $index, $num_rows, $_str);
				}
				else
				{
					foreach ($result as $row) 
					{
						$_str.= "(";
						for ($i = 0; $i < $num_fields; $i++) 
						{
							$v = $row[$fields[$i]->name];
							if (is_null($v)) 
							{
								$_str.= 'null';
							}
							else
							{
								$this->_quotes($fields[$i]->type, $_str, $row[$fields[$i]->name]);
							}
							if ($i < $num_fields - 1) 
							{
								$_str.= ',';
							}
						}
						$_str.= ')';
						if ($index < $num_rows - 1) 
						{
							$_str.= ',';
						}
						else
						{
							$_str.= ';';
						}
						$_str.= "\n";
						$index++;
					}
				}
			}
		}
		$_str.= "\n";
		$f = fopen($path, "a");
		fwrite($f, $_str);
		fclose($f);
		return true;
	}
	/**
	 * Specific dump for t_category table
	 *
	 * @param type $result
	 * @param type $num_fields
	 * @param type $field_type
	 * @param type $fields
	 * @param type $index
	 * @param type $num_rows
	 * @param type $_str
	 */
	private function _dump_table_category($result, $num_fields, $field_type, $fields, $index, $num_rows, &$_str) 
	{
		$short_rows = array();
		$unshort_rows = array();
		foreach ($result as $row) 
		{
			if (($row['fk_i_parent_id']) == NULL) 
			{
				$short_rows[] = $row;
			}
			else
			{
				$unshort_rows[$row['pk_i_id']] = $row;
			}
		}
		while (!empty($unshort_rows)) 
		{
			foreach ($unshort_rows as $k => $v) 
			{
				foreach ($short_rows as $r) 
				{
					if ($r['pk_i_id'] == $v['fk_i_parent_id']) 
					{
						unset($unshort_rows[$k]);
						$short_rows[] = $v;
					}
				}
			}
		}
		foreach ($short_rows as $row) 
		{
			$_str.= "(";
			for ($i = 0; $i < $num_fields; $i++) 
			{
				$v = $row[$fields[$i]->name];
				if (is_null($v)) 
				{
					$_str.= 'null';
				}
				else
				{
					$this->_quotes($fields[$i]->type, $_str, $v);
				}
				if ($i < $num_fields - 1) 
				{
					$_str.= ',';
				}
			}
			$_str.= ')';
			if ($index < $num_rows - 1) 
			{
				$_str.= ',';
			}
			else
			{
				$_str.= ';';
			}
			$_str.= "\n";
			$index++;
		}
	}
	/**
	 * Add quotes if it's necessary
	 *
	 * data type =>  http://www.php.net/manual/es/mysqli-result.fetch-field.php#106064
	 *
	 * @param type $type
	 * @param type $_str
	 * @param type $value
	 */
	private function _quotes($type, &$_str, $value) 
	{
		//            * numeric *
		//            BIT: 16 - TINYINT: 1 - BOOL: 1 - SMALLINT: 2 - MEDIUMINT: 9
		//            INTEGER: 3 - BIGINT: 8 - SERIAL: 8 - FLOAT: 4 - DOUBLE: 5
		//            DECIMAL: 246 - NUMERIC: 246 - FIXED: 246
		//            * dates *
		//            DATE: 10 - DATETIME: 12 - TIMESTAMP: 7 - TIME: 11 - YEAR: 13
		//            * strings & binary *
		//            CHAR: 254 - VARCHAR: 253 - ENUM: 254 - SET: 254 - BINARY: 254
		//            VARBINARY: 253 - TINYBLOB: 252 - BLOB: 252 - MEDIUMBLOB: 252
		//            TINYTEXT: 252 - TEXT: 252 - MEDIUMTEXT: 252 - LONGTEXT: 252
		$aNumeric = array(16, 1, 2, 9, 3, 8, 4, 5, 246);
		$aDates = array(10, 12, 7, 11, 13);
		$aString = array(254, 253, 252);
		if (in_array($type, $aNumeric)) 
		{
			$_str.= $value;
		}
		else if (in_array($type, $aDates)) 
		{
			$_str.= '\'' . $this->dbCommand->connId->real_escape_string($value) . '\'';
		}
		else if (in_array($type, $aString)) 
		{
			$_str.= '\'' . $this->dbCommand->connId->real_escape_string($value) . '\'';
		}
	}
}
