<?php
/**
 * OpenSourceClassifieds – software for creating and publishing online classified advertising platforms
 *
 * Copyright (C) 2012 OpenSourceClassifieds
 *
 * This program is free software: you can redistribute it and/or modify it under the terms
 * of the GNU Affero General Public License as published by the Free Software Foundation,
 * either version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
?>
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
			    <div id="content_header" class="content_header">
					<div style="float: left;">
                        <img src="<?php echo osc_current_admin_theme_url('images/settings-icon.png'); ?>" alt="" title="" />
                    </div>
					<div id="content_header_arrow">&raquo; <?php _e('Item'); ?></div>
					<div style="clear: both;"></div>
				</div>
				<div id="content_separator"></div>

                <div>
                    <div style="padding: 20px;">
                        <p>
                            <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&action=items&type_stat=day"><?php _e('Last 10 days'); ?></a>
                            <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&action=items&type_stat=week"><?php _e('Last 10 weeks'); ?></a>
                            <a href="<?php echo osc_admin_base_url(true); ?>?page=stats&action=items&type_stat=month"><?php _e('Last 10 months'); ?></a>
                        </p>
                    </div>
                </div>

                <h3><?php _e('New items'); ?></h3>
                <div id="placeholder" style="width:600px;height:300px;margin:0 auto;padding-bottom: 45px;">
                    <?php
if (count($items) == 0) 
{
	_e('There\'re no statistics yet');
}
?>
                </div>
                <br/>
                <h3><?php
_e('Total number of items\' views'); ?></h3>
                <div id="placeholder_total" style="width:600px;height:300px;margin:0 auto;padding-bottom: 45px;">
                    <?php
if (count($reports) == 0) 
{
	_e('There\'re no statistics yet');
}
?>
                </div>


                <br/>


                <br/>

                <div>
                    <h3><?php
_e('Latest items on the web'); ?></h3>
                    <?php
if (count($latest_items) > 0) 
{ ?>
                    <table border="0">
                        <tr>
                            <th>ID</th>
                            <th><?php
	_e('Title'); ?></th>
                            <th><?php
	_e('Author'); ?></th>
                            <th><?php
	_e('Status'); ?></th>
                        </tr>
                        <?php
	foreach ($latest_items as $i) 
	{ ?>
                        <tr>
                            <td><a href="<?php
		echo osc_admin_base_url(true); ?>?page=item&action=item_edit&amp;id=<?php
		echo $i['pk_i_id']; ?>"><?php
		echo $i['pk_i_id']; ?></a></td>
                            <td><a href="<?php
		echo osc_admin_base_url(true); ?>?page=item&action=item_edit&amp;id=<?php
		echo $i['pk_i_id']; ?>"><?php
		echo $i['s_title']; ?></a></td>
                            <td><a href="<?php
		echo osc_admin_base_url(true); ?>?page=item&action=item_edit&amp;id=<?php
		echo $i['pk_i_id']; ?>"><?php
		echo $i['s_contact_email']; ?></a></td>
                            <td><a href="<?php
		echo osc_admin_base_url(true); ?>?page=item&action=item_edit&amp;id=<?php
		echo $i['pk_i_id']; ?>"><?php
		echo ($i['b_active'] == 1) ? __('Active') : __('Inactive'); ?></a></td>
                        </tr>
                        <?php
	}; ?>
                    </table>
                    <?php
}
else
{ ?>
                        <p><?php
	_e('There\'re no statistics yet'); ?></p>
                    <?php
}; ?>
                </div>

        <?php
if (count($items) > 0) 
{ ?>
            <script type="text/javascript">

            // Load the Visualization API and the piechart package.
            google.load('visualization', '1', {'packages':['corechart']});

            // Set a callback to run when the Google Visualization API is loaded.
            google.setOnLoadCallback(drawChart);

            // Callback that creates and populates a data table, 
            // instantiates the pie chart, passes in the data and
            // draws it.
            function drawChart() {

                var data = new google.visualization.DataTable();
                var data2 = new google.visualization.DataTable();
                data.addColumn('string', '<?php
	_e('Date'); ?>');
                data.addColumn('number', '<?php
	_e('Items'); ?>');
                data2.addColumn('string', '<?php
	_e('Date'); ?>');
                data2.addColumn('number', '<?php
	_e('Views'); ?>');
                <?php
	$k = 0;
	echo "data.addRows(" . count($items) . ");";
	foreach ($items as $date => $num) 
	{
		echo "data.setValue(" . $k . ", 0, \"" . $date . "\");";
		echo "data.setValue(" . $k . ", 1, " . $num . ");";
		$k++;
	};
	$k = 0;
	echo "data2.addRows(" . count($reports) . ");";
	foreach ($reports as $date => $data) 
	{
		echo "data2.setValue(" . $k . ", 0, \"" . $date . "\");";
		echo "data2.setValue(" . $k . ", 1, " . $data['views'] . ");";
		$k++;
	};
?>

                // Instantiate and draw our chart, passing in some options.
                var chart = new google.visualization.LineChart(document.getElementById('placeholder'));
                chart.draw(data, {width: 600, height: 300, vAxis: {maxValue: <?php
	echo ceil($max * 1.1); ?>}});

                var chart2 = new google.visualization.ColumnChart(document.getElementById('placeholder_total'));
                chart2.draw(data2, {width: 600, height: 300, vAxis: {maxValue: <?php
	echo ceil($max_views * 1.1); ?>}});
            }
            </script>
        <?php
} ?>

