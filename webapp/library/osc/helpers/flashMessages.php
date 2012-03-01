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
/**
 * Helper Flash Messages
 * @package OpenSourceClassifieds
 * @subpackage Helpers
 * @author OpenSourceClassifieds
 */
/**
 * Adds an ephemeral message to the session. (ok style)
 *
 * @param $msg
 * @param $section
 * @return string
 */
function osc_add_flash_ok_message($msg, $section = 'pubMessages') 
{
	ClassLoader::getInstance()->getClassInstance( 'Session' )->_setMessage($section, $msg, 'ok');
}
/**
 * Adds an ephemeral message to the session. (error style)
 *
 * @param $msg
 * @param $section
 * @return string
 */
function osc_add_flash_error_message($msg, $section = 'pubMessages') 
{
	ClassLoader::getInstance()->getClassInstance( 'Session' )->_setMessage($section, $msg, 'error');
}
/**
 * Adds an ephemeral message to the session. (info style)
 *
 * @param $msg
 * @param $section
 * @return string
 */
function osc_add_flash_info_message($msg, $section = 'pubMessages') 
{
	ClassLoader::getInstance()->getClassInstance( 'Session' )->_setMessage($section, $msg, 'info');
}
/**
 * Adds an ephemeral message to the session. (warning style)
 *
 * @param $msg
 * @param $section
 * @return string
 */
function osc_add_flash_warning_message($msg, $section = 'pubMessages') 
{
	ClassLoader::getInstance()->getClassInstance( 'Session' )->_setMessage($section, $msg, 'warning');
}
/**
 * Shows all the pending flash messages in session and cleans up the array.
 *
 * @param $section
 * @param $class
 * @param $id
 * @return void
 */
function osc_show_flash_message( $section = 'pubMessages', $class = "FlashMessage", $id = "FlashMessage" )
{
	$session = ClassLoader::getInstance()->getClassInstance( 'Session' );
	$message = $session->_getMessage( $section );

	if( !empty( $message['msg'] ) )
	{
		echo '<div id="' . $id . '" class="' . $class . ' ' . $message['type'] . '">';
		echo $message['msg'];
		echo '</div>';
		$session->_dropMessage($section);
	}
}

