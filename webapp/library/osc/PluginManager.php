<?php
/*
 *      OpenSourceClassifieds – software for creating and publishing online classified
 *                           advertising platforms
 *
 *                        Copyright (C) 2011 OpenSourceClassifieds
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

class PluginManager
{
	private $hooks;

	public function __construct()
	{
		$this->hooks = array();
	}

	public function runHook( $hook )
	{
		$args = func_get_args();
		array_shift($args);
		if( isset( $this->hooks[$hook] ) )
		{
			for ($priority = 0; $priority <= 10; $priority++) 
			{
				if (isset( $this->hooks[$hook][$priority]) && is_array( $this->hooks[$hook][$priority])) 
				{
					foreach ( $this->hooks[$hook][$priority] as $fxName) 
					{
						if (function_exists($fxName)) 
						{
							call_user_func_array($fxName, $args);
						}
					}
				}
			}
		}
	}

	public function applyFilter( $hook, $content )
	{
		if( isset( $this->hooks[$hook] ) )
		{
			for ($priority = 0; $priority <= 10; $priority++) 
			{
				/* @TODO FIX */
				if (false&&isset( $this->hooks[$hook][$priority]) && is_array( $this->$hooks[$hook][$priority])) 
				{
					foreach ( $this->hooks[$hook][$priority] as $fxName) 
					{
						if (function_exists($fxName)) 
						{
							$content = call_user_func($fxName, $content);
						}
					}
				}
			}
		}
		return $content;
	}

	public function isInstalled($plugin) 
	{
		$p_installed = $this->listInstalled();
		foreach ($p_installed as $p) 
		{
			if ($p == $plugin) 
			{
				return true;
			}
		}
		return false;
	}

	public function isEnabled($plugin) 
	{
		$p_installed = $this->listEnabled();
		foreach ($p_installed as $p) 
		{
			if ($p == $plugin) 
			{
				return true;
			}
		}
		return false;
	}

	public function listAll() 
	{
		$plugins = array();
		$pluginsPath = osc_plugins_path();
		$dir = opendir($pluginsPath);
		while ($file = readdir($dir)) 
		{
			if (preg_match('/^[a-zA-Z0-9_]+$/', $file, $matches)) 
			{
				// This has to change in order to catch any .php file
				$pluginPath = $pluginsPath . "/$file/index.php";
				if (file_exists($pluginPath)) 
				{
					$plugins[] = $file . "/index.php";
				}
				else
				{
					trigger_error(sprintf(__('Plugin %s is missing the index.php file %s'), $file, $pluginPath));
				}
			}
		}
		closedir($dir);
		return $plugins;
	}

	public function loadActive() 
	{
		$data['s_value'] = osc_active_plugins();
		$plugins_list = unserialize($data['s_value']);
		if (is_array($plugins_list)) 
		{
			foreach ($plugins_list as $plugin_name) 
			{
				$pluginPath = osc_plugins_path() . DIRECTORY_SEPARATOR . $plugin_name;
				if( file_exists( $pluginPath ) ) 
				{
					//This should include the file and adds the hooks
					include_once $pluginPath;
				}
				else
				{
					trigger_error( 'Plugin path not found: ' . $pluginPath, E_USER_WARNING );
				}
			}
		}
	}

	public function listInstalled() 
	{
		$p_array = array();
		try
		{
			$data['s_value'] = osc_installed_plugins();
			$plugins_list = unserialize($data['s_value']);
			if (is_array($plugins_list)) 
			{
				foreach ($plugins_list as $plugin_name) 
				{
					$p_array[] = $plugin_name;
				}
			}
		}
		catch(Exception $e) 
		{
			echo $e->getMessage();
		}
		return $p_array;
	}

	public function listEnabled() 
	{
		$p_array = array();
		try
		{
			$data['s_value'] = osc_active_plugins();
			$plugins_list = unserialize($data['s_value']);
			if (is_array($plugins_list)) 
			{
				foreach ($plugins_list as $plugin_name) 
				{
					$p_array[] = $plugin_name;
				}
			}
		}
		catch(Exception $e) 
		{
			echo $e->getMessage();
		}
		return $p_array;
	}

	public function resource($path) 
	{
		$fullPath = osc_plugins_path() . $path;
		return file_exists($fullPath) ? $fullPath : false;
	}

	public function activate($path) 
	{
		try
		{
			$data['s_value'] = osc_active_plugins();
			$plugins_list = unserialize($data['s_value']);
			$found_it = false;
			if (is_array($plugins_list)) 
			{
				foreach ($plugins_list as $plugin_name) 
				{
					// Check if the plugin is already installed
					if ($plugin_name == $path) 
					{
						$found_it = true;
						break;
					}
				}
			}
			if (!$found_it) 
			{
				$plugins_list[] = $path;
				$data['s_value'] = serialize($plugins_list);
				$condition = array('s_section' => 'osclass', 's_name' => 'active_plugins');
				Preference::newInstance()->update($data, $condition);
				unset($condition);
				unset($data);
				$this->reload();
				return true;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $e) 
		{
			echo $e->getMessage();
		}
	}

	public function register($path, $function) 
	{
		$path = str_replace(osc_plugins_path(), '', $path);
		$this->addHook('install_' . $path, $function);
	}

	public function deactivate($path) 
	{
		try
		{
			$data['s_value'] = osc_active_plugins();
			$plugins_list = unserialize($data['s_value']);
			$path = str_replace(osc_plugins_path(), '', $path);
			if (is_array($plugins_list)) 
			{
				foreach ($plugins_list as $key => $value) 
				{
					if ($value == $path) 
					{
						unset($plugins_list[$key]);
					}
				}
				$data['s_value'] = serialize($plugins_list);
				$condition = array('s_section' => 'osclass', 's_name' => 'active_plugins');
				Preference::newInstance()->update($data, $condition);
				unset($condition);
				unset($data);
				$this->reload();
			}
		}
		catch(Exception $e) 
		{
			echo $e->getMessage();
		}
	}

	public function install($path) 
	{
		try
		{
			$data['s_value'] = osc_installed_plugins();
			$plugins_list = unserialize($data['s_value']);
			$found_it = false;
			if (is_array($plugins_list)) 
			{
				foreach ($plugins_list as $plugin_name) 
				{
					// Check if the plugin is already installed
					if ($plugin_name == $path) 
					{
						$found_it = true;
						break;
					}
				}
			}
			$this->activate($path);
			if (!$found_it) 
			{
				$plugins_list[] = $path;
				$data['s_value'] = serialize($plugins_list);
				$condition = array('s_section' => 'osclass', 's_name' => 'installed_plugins');
				Preference::newInstance()->update($data, $condition);
				unset($condition);
				unset($data);
				return true;
			}
			else
			{
				return false;
			}
		}
		catch(Exception $e) 
		{
			echo $e->getMessage();
		}
	}

	public function uninstall($path) 
	{
		try
		{
			$data['s_value'] = osc_installed_plugins();
			$plugins_list = unserialize($data['s_value']);
			$this->deactivate($path);
			$path = str_replace(osc_plugins_path(), '', $path);
			if (is_array($plugins_list)) 
			{
				foreach ($plugins_list as $key => $value) 
				{
					if ($value == $path) 
					{
						unset($plugins_list[$key]);
					}
				}
				$data['s_value'] = serialize($plugins_list);
				$condition = array('s_section' => 'osclass', 's_name' => 'installed_plugins');
				Preference::newInstance()->update($data, $condition);
				unset($condition);
				unset($data);
				$plugin = $this->getInfo($path);
				$this->cleanCategoryFromPlugin($plugin['short_name']);
			}
		}
		catch(Exception $e) 
		{
			echo $e->getMessage();
		}
	}

	public function isThisCategory($name, $id) 
	{
		return PluginCategory::newInstance()->isThisCategory($name, $id);
	}

	public function getInfo($plugin) 
	{
		$s_info = file_get_contents(osc_plugins_path() . DIRECTORY_SEPARATOR . $plugin);
		$info = array();
		if (preg_match('|Plugin Name:([^\\r\\t\\n]*)|i', $s_info, $match)) 
		{
			$info['plugin_name'] = trim($match[1]);
		}
		else
		{
			$info['plugin_name'] = $plugin;
		};
		if (preg_match('|Plugin URI:([^\\r\\t\\n]*)|i', $s_info, $match)) 
		{
			$info['plugin_uri'] = trim($match[1]);
		}
		else
		{
			$info['plugin_uri'] = "";
		};
		if (preg_match('|Plugin update URI:([^\\r\\t\\n]*)|i', $s_info, $match)) 
		{
			$info['plugin_update_uri'] = trim($match[1]);
		}
		else
		{
			$info['plugin_update_uri'] = "";
		};
		if (preg_match('|Description:([^\\r\\t\\n]*)|i', $s_info, $match)) 
		{
			$info['description'] = trim($match[1]);
		}
		else
		{
			$info['description'] = "";
		};
		if (preg_match('|Version:([^\\r\\t\\n]*)|i', $s_info, $match)) 
		{
			$info['version'] = trim($match[1]);
		}
		else
		{
			$info['version'] = "";
		};
		if (preg_match('|Author:([^\\r\\t\\n]*)|i', $s_info, $match)) 
		{
			$info['author'] = trim($match[1]);
		}
		else
		{
			$info['author'] = "";
		};
		if (preg_match('|Author URI:([^\\r\\t\\n]*)|i', $s_info, $match)) 
		{
			$info['author_uri'] = trim($match[1]);
		}
		else
		{
			$info['author_uri'] = "";
		};
		if (preg_match('|Short Name:([^\\r\\t\\n]*)|i', $s_info, $match)) 
		{
			$info['short_name'] = trim($match[1]);
		}
		else
		{
			$info['short_name'] = $info['plugin_name'];
		};
		$info['filename'] = $plugin;
		return $info;
	}

	public function checkUpdate( $plugin )
	{
		$info = $this->getInfo( $plugin );
		if( !empty( $info['plugin_update_uri'] ) )
		{
			$str = osc_file_get_contents( $info['plugin_update_uri'] );
			if( false === $str ) 
			{
				return false;
			}
			else
			{
				if (preg_match('|\?\(([^\)]+)|', preg_replace('/,\s*([\]}])/m', '$1', $str), $data)) 
				{
					$json = json_decode($data[1], true);
					if ($json['version'] > $info['version']) 
					{
						return true;
					}
				}
			}
		}
		return false;
	}

	public function configureView($path) 
	{
		$plugin = str_replace(osc_plugins_path(), '', $path);
		if (stripos($plugin, ".php") === FALSE) 
		{
			$plugins_list = unserialize(osc_active_plugins());
			if (is_array($plugins_list)) 
			{
				foreach ($plugins_list as $p) 
				{
					$data = $this->getInfo($p);
					if ($plugin == $data['plugin_name']) 
					{
						$plugin = $p;
						break;
					}
				}
			}
		}
		header('Location: ' . osc_plugin_configure_url($plugin));
		exit;
	}
	static function cleanCategoryFromPlugin($plugin) 
	{
		$dao_pluginCategory = ClassLoader::getInstance()->getClassInstance( 'PluginCategory' );
		$dao_pluginCategory->delete(array('s_plugin_name' => $plugin));
		unset($dao_pluginCategory);
	}
	static function addToCategoryPlugin($categories, $plugin) 
	{
		$dao_pluginCategory = new PluginCategory();
		$dao_category = new Category();
		if (!empty($categories)) 
		{
			foreach ($categories as $catId) 
			{
				$result = $dao_pluginCategory->isThisCategory($plugin, $catId);
				if ($result == 0) 
				{
					$fields = array();
					$fields['s_plugin_name'] = $plugin;
					$fields['fk_i_category_id'] = $catId;
					$dao_pluginCategory->insert($fields);
					$subs = $dao_category->findSubcategories($catId);
					if (is_array($subs) && count($subs) > 0) 
					{
						$cats = array();
						foreach ($subs as $sub) 
						{
							$cats[] = $sub['pk_i_id'];
						}
						$this->addToCategoryPlugin($cats, $plugin);
					}
				}
			}
		}
		unset($dao_pluginCategory);
		unset($dao_category);
	}

	// Add a hook
	public function addHook($hook, $function, $priority = 5) 
	{
		$hook = preg_replace('|/+|', '/', str_replace('\\', '/', $hook));
		$plugin_path = str_replace('\\', '/', osc_plugins_path());
		$hook = str_replace($plugin_path, '', $hook);
		$found_plugin = false;
		if (isset( $this->hooks[$hook])) 
		{
			if (is_array( $this->hooks[$hook])) 
			{
				foreach ( $this->hooks[$hook] as $fxName) 
				{
					if ($fxName == $function) 
					{
						$found_plugin = true;
						break;
					}
				}
			}
		}
		if (!$found_plugin) 
		{
			$this->hooks[$hook][$priority][] = $function;
		}
	}

	public function removeHook($hook, $function) 
	{
		unset( $this->hooks[$hook][$function]);
	}

	public function getActive() 
	{
		return $this->hooks;
	}

	public function reload() 
	{
		Preference::newInstance()->toArray();
		$this->init();
	}

	public function init() 
	{
		$this->loadActive();
	}
}

