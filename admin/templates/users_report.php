<?php
//Recurring Payments Page Content
$page_name = "User Trends Reporting";
$start_date = isset($_GET['sd'])? date('Y-m-d',strtotime(urldecode($_GET['sd']))) : date('Y-m-d', strtotime("-1 months"));
$end_date = isset($_GET['ed'])? date('Y-m-d',strtotime(urldecode($_GET['ed']))) : date('Y-m-d');
$report_data = (getActiveUserReport($start_date,$end_date));		
	//var_dump($report_data);
	$chart_data = array(	
			'Labels' => "",
			'Yearly Subscription' => "",
			'Monthly Subscription' => "",
			'3Day' => "",
			'One Month' => "",
			'One Year' => ""			
	);
foreach($report_data AS $index_date => $rep){	
	$display_date = date("m/d/y",strtotime($index_date));	
	$chart_data['Labels'] .= "'" . $display_date . "',";	
	$chart_data['Yearly Subscription'] .= ((isset($report_data[$index_date][2])) ? $report_data[$index_date][2] : '0') . ",";
	$chart_data['Monthly Subscription'] .= ((isset($report_data[$index_date][3])) ? $report_data[$index_date][3] : '0') . ",";
	$chart_data['3Day'] .= ((isset($report_data[$index_date][4])) ? $report_data[$index_date][4] : '0') . ",";
	$chart_data['One Month'] .= ((isset($report_data[$index_date][7])) ? $report_data[$index_date][7] : '0') . ",";
	$chart_data['One Year'] .= ((isset($report_data[$index_date][8])) ? $report_data[$index_date][8] : '0') . ",";	
}
$html = <<<EOD
<h2 class="subhead">User Trends Report</h2>
<div class="centered_dates">Refine Date Range: <form><input class="datepicker" type="text" placeholder="MM/DD/YYYY" id="start_date" name="sd" maxlength="10" readonly="readonly"/> to <input class="datepicker" type="text" placeholder="MM/DD/YYYY" id="end_date" name="ed" maxlength="10" readonly="readonly"/> <input type="submit" value="View Date Range"/></form></div>
<canvas id="lineChart"></canvas>
EOD;
$sd_js = date("m/d/Y",strtotime($start_date));
$ed_js = date("m/d/Y",strtotime($end_date));
$head = <<<EOD
<link rel="stylesheet" href="{$root}js/jquery-ui-1.12.1.custom/jquery-ui.css">
<script src="{$root}js/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		
		$( ".datepicker" ).datepicker({
		  dateFormat: 'mm/dd/yy',
		  minDate: new Date("2018-03-26"),
		  maxDate: '+1d'
		});
		$('#start_date').datepicker("setDate", "{$sd_js}" );
		$('#end_date').datepicker("setDate", "{$ed_js}" );
		var ctx2 = document.getElementById('lineChart');
		var myLineChart = new Chart(ctx2,{
			type: 'line',
			data: {
				labels: [{$chart_data['Labels']}],
				datasets: [{
					label: 'Yearly Subscription',
					backgroundColor: "rgba(255, 153, 51,1)",
					borderColor: "rgba(255, 153, 51,1)",
					data: [{$chart_data['Yearly Subscription']}],
					fill: false,
				}, {
					label: 'Monthly Subscription',
					fill: false,
					backgroundColor: 'rgba(255, 255, 102,1)',
					borderColor: 'rgba(255, 255, 102,1)',
					data: [{$chart_data['Monthly Subscription']}]	
				}, {
					label: '3Day',
					fill: false,
					backgroundColor: 'rgba(0, 102, 153,.6)',
					borderColor: 'rgba(0, 102, 153,.6)',
					data: [{$chart_data['3Day']}]	
				}, {
					label: 'One Month',
					fill: false,
					backgroundColor: 'rgba(0, 102, 153,1)',
					borderColor: 'rgba(0, 102, 153,1)',
					data: [{$chart_data['One Month']}]	
				}, {
					label: 'One Year',
					fill: false,
					backgroundColor: 'rgba(51, 204, 51,1)',
					borderColor: 'rgba(51, 204, 51,1)',
					data: [{$chart_data['One Year']}]					
				}]
			},
			options: {
				responsive: true,
				title: {
					display: true,
					text: 'Active Users By Day'
				},
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
				scales: {
					xAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Day'
						}
					}],
					yAxes: [{
						display: true,
						scaleLabel: {
							display: true,
							labelString: 'Users'
						}
					}]
				}
			}
		});
	});
</script>
EOD;
?>



