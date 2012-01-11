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
function osc_runAlert($type = null) 
{
	if (!in_array($type, array('HOURLY', 'DAILY', 'WEEKLY', 'INSTANT'))) 
	{
		return;
	}
	$internal_name = 'alert_email_hourly';
	switch ($type) 
	{
	case 'HOURLY':
		$internal_name = 'alert_email_hourly';
		break;

	case 'DAILY':
		$internal_name = 'alert_email_daily';
		break;

	case 'WEEKLY':
		$internal_name = 'alert_email_weekly';
		break;

	case 'INSTANT':
		$internal_name = 'alert_email_instant';
		break;
	}
	$active = TRUE;
	$searches = Alerts::newInstance()->findByTypeGroup($type, $active);
	foreach ($searches as $s_search) 
	{
		$a_search = Search::newInstance();
		// Get if there're new ads on this search
		$a_search = osc_unserialize(base64_decode($s_search['s_search']));
		$cron = Cron::newInstance()->getCronByType($type);
		if (is_array($cron)) 
		{
			$last_exec = $cron['d_last_exec'];
		}
		else
		{
			$last_exec = '0000-00-00 00:00:00';
		}
		$a_search->addConditions(sprintf(" %sitem.pub_date > '%s' ", DB_TABLE_PREFIX, $last_exec));
		$totalItems = $a_search->count();
		$items = $a_search->doSearch();
		if (count($items) > 0) 
		{
			//If we have new items from last check
			//Catch the user subscribed to this search
			$users = Alerts::newInstance()->findUsersBySearchAndType($s_search['s_search'], $type, $active);
			if (count($users) > 0) 
			{
				$ads = "";
				foreach ($items as $item) 
				{
					$ads.= '<a href="' . osc_item_url_ns($item['pk_i_id']) . '">' . $item['s_title'] . '</a><br/>';
				}
				foreach ($users as $user) 
				{
					osc_run_hook('hook_' . $internal_name, $user, $ads, $s_search);
				}
			}
		}
	}
}
