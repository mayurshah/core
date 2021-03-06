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
require_once 'php-gettext/streams.php';
require_once 'php-gettext/gettext.php';
class Core_Translation
{
	private $messages;
	function __construct() 
	{
		// get user/admin locale
		$locale = '';
		if (defined('OC_ADMIN')) 
		{
			$locale = osc_current_admin_locale();
		}
		else
		{
			$locale = osc_current_user_locale();
		}
		// load core
		$core_file = osc_translations_path() . DIRECTORY_SEPARATOR . $locale . '/core.mo';
		$this->_load($core_file, 'core');
		// load messages
		$messages_file = osc_themes_path() . DIRECTORY_SEPARATOR . osc_theme() . '/languages/' . $locale . '/messages.mo';
		if (!file_exists($messages_file)) 
		{
			$messages_file = osc_translations_path() . $locale . '/messages.mo';
		}
		$this->_load($messages_file, 'messages');
		// load theme
		$domain = osc_theme();
		$theme_file = osc_themes_path() . DIRECTORY_SEPARATOR . $domain . '/languages/' . $locale . '/theme.mo';
		if (!file_exists($theme_file)) 
		{
			if (!file_exists(osc_themes_path() . $domain)) 
			{
				$domain = 'modern';
			}
			$theme_file = osc_translations_path() . $locale . '/theme.mo';
		}
		$this->_load($theme_file, $domain);

		$aPlugins = ClassLoader::getInstance()->getClassInstance( 'PluginManager' )->listInstalled();
		foreach ($aPlugins as $plugin) 
		{
			$domain = preg_replace('|/.*|', '', $plugin);
			$plugin_file = osc_plugins_path() . DIRECTORY_SEPARATOR . $domain . '/languages/' . $locale . '/messages.mo';
			if (file_exists($plugin_file)) 
			{
				$this->_load($plugin_file, $domain);
			}
		}
	}
	function _get($domain) 
	{
		if (!isset($this->messages[$domain])) 
		{
			return false;
		}
		return $this->messages[$domain];
	}
	function _set($domain, $reader) 
	{
		if (isset($messages[$domain])) 
		{
			false;
		}
		$this->messages[$domain] = $reader;
		return true;
	}
	function _load($file, $domain) 
	{
		if (!file_exists($file)) 
		{
			return false;
		}
		$streamer = new FileReader($file);
		$reader = new gettext_reader($streamer);
		return $this->_set($domain, $reader);
	}
}
