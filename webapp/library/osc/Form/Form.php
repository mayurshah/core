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
class Form
{
	public function __construct()
	{
	}

	public function getInputHidden( $name, $value = '' )
	{
		return sprintf( '<input type="hidden" name="%s" value="%s" />', $name, $value ) . PHP_EOL;
	}

	public function getInputSubmit( $name = '', $value = '' )
	{
		return sprintf( '<input type="submit" name="%s" value="%s" />', $name, $value ) . PHP_EOL;
	}

	public function getDecoratedInputCheckbox( $name, $value = '', $label = null )
	{
		static $idCounter = 0;
		$id = $name . '_id' . $idCounter++;
		$input = sprintf( '<input type="checkbox" id="%s" name="%s" value="%s" />', $id, $name, $value );
		if( is_null( $label ) )
			return $input;

		return $input . sprintf( '<label for="%s">%s</label>', $id, $label );
	}

	protected function generic_select($name, $items, $fld_key, $fld_name, $default_item, $id) 
	{
		echo '<select name="' . $name . '" id="' . $name . '">';
		if (isset($default_item))
			echo '<option value="">' . $default_item . '</option>';
		foreach ($items as $i) 
		{
			if (isset($fld_key) && isset($fld_name)) echo '<option value="' . $i[$fld_key] . '"' . (($id == $i[$fld_key]) ? 'selected="selected"' : '') . '>' . $i[$fld_name] . '</option>';
		}
		echo '</select>';
	}
	protected function generic_input_text($name, $value, $maxLength = null, $readOnly = false, $autocomplete = true) 
	{
		echo '<input id="' . $name . '" type="text" name="' . $name . '" value="' . htmlentities($value, ENT_COMPAT, "UTF-8") . '" ';
		if (isset($maxLength))
			echo 'maxlength="' . $maxLength . '" ';
		if (!$autocomplete)
			echo ' autocomplete="off" ';
		if ($readOnly)
			echo 'disabled="disabled" readonly="readonly" ';
		echo '/>';
	}
	protected function generic_password($name, $value, $maxLength = null, $readOnly = false) 
	{
		echo '<input id="' . $name . '" type="password" name="' . $name . '" value="' . htmlentities($value, ENT_COMPAT, "UTF-8") . '" ';
		if (isset($maxLength))
			echo 'maxlength="' . $maxLength . '" ';
		if ($readOnly)
			echo 'disabled="disabled" readonly="readonly" ';
		echo '/>';
	}
	protected function generic_input_hidden($name, $value) 
	{
		echo '<input id="' . $name . '" type="hidden" name="' . $name . '" value="' . htmlentities($value, ENT_COMPAT, "UTF-8") . '" />';
	}
	protected function generic_input_checkbox($name, $value, $checked = false) 
	{
		echo '<input id="' . $name . '" type="checkbox" name="' . $name . '" value="' . htmlentities($value, ENT_COMPAT, "UTF-8") . '" ';
		if ($checked)
			echo 'checked="checked" ';
		echo '/>';
	}
	protected function generic_textarea($name, $value) 
	{
		echo '<textarea id="' . $name . '" name="' . $name . '" rows="10">' . $value . '</textarea>';
	}
}
