<?php
    include("../model/config.php");   
    include("../model/payment_functions.php");   
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
	global $db;	

	/*$customer_id = 19998;
	$token = getPayTraceToken();   
	$userExists = getPaytraceUser($token,$customer_id);
	echo "User Exists check for " . $customer_id . " result is " . $userExists; */
	
//$customers = getRecurTodayPayments();
//var_dump($customers);
echo checkPlanActiveById(7374);
/*if(!$customers){//None for today
	error_log("No recurring payments for today");
	exit;
}else{
	echo "hello";
	$token = getPayTraceToken();	
	$today = date("Y-m-d");
	$start_range = 	date("Y-m-d",strtotime("-5 days"));
	foreach($customers AS $cust){
		$payment_date = "";
		$pt_payment = getLastRecurringPayment($token,$cust['user_id']);
		var_dump($pt_payment);
		if($pt_payment['success']){
			//check that date matches
			$payment_date = date("Y-m-d",strtotime($pt_payment['created']['at']));
			if(strtotime($payment_date) > strtotime($start_range)){//All good!			
				//If it exists, add to DB
				$payment_date = date("Y-m-d H:i:s",strtotime($pt_payment['created']['at']));
				$payment = array(
					"user_id" 	=> $cust['user_id'],
					"payment_id" 	=> $cust['payment_id'],
					"pt_id"			=> $pt_payment['approval_code'],
					"payment_date"	=> $payment_date,
					"amount"		=> $pt_payment['amount']	
				);
				echo $cust['user_id'];
				$recur = addRecurPayment($payment);
				
				if(!$recur) echo "Recurring cron attempted duplicate recurring payment recording for " . $cust['user_id'] . ", PT Approval ID: " . $pt_payment['approval_code'];
			}else{//A past payment was last, so today's failed				
				$recur_id = $pt_payment['recurrence']['id'];
				echo "Will delete user " .$cust['user_id']. " recurrence ID #:" . $recur_id;
				deleteRecurringProcess($token,$recur_id,$cust['user_id']);
				updatePlanStatus($cust['user_id'],"cancelled");
				log_activity($cust['user_id'],"Automatic Plan Cancellation recurrence " . $recur_id,"Recurring Payment Failed on " . $today);
			}			
		}else{//No payments registered at all, so nothing to cancel at PT
			echo "Will delete user " .$cust['user_id'];
			updatePlanStatus($cust['user_id'],"cancelled");
			log_activity($cust['user_id'],"Automatic Plan Cancellation","Recurring Payment Failed on " . $today);
		}
	}
}*/	
/*	$start_date = "2021-04-01";
	$end_date = "2021-06-30";
	
	$report_data = (getActiveUserReport($start_date,$end_date));		
	//var_dump($report_data);
	$chart_data = array(	
			'Labels' => "",
			'Yearly Subscription' => "",
			'Monthly Subscription' => "",
			'3-Day' => "",
			'One Month' => "",
			'One Year' => ""			
	);
foreach($report_data AS $index_date => $rep){	
	$display_date = date("m/d/y",strtotime($index_date));	
	$chart_data['Labels'] .= "'" . $display_date . "',";	
	$chart_data['Yearly Subscription'] .= ((isset($report_data[$index_date][2])) ? $report_data[$index_date][2] : '0') . ",";
	$chart_data['Monthly Subscription'] .= ((isset($report_data[$index_date][3])) ? $report_data[$index_date][3] : '0') . ",";
	$chart_data['3-Day'] .= ((isset($report_data[$index_date][4])) ? $report_data[$index_date][4] : '0') . ",";
	$chart_data['One Month'] .= ((isset($report_data[$index_date][7])) ? $report_data[$index_date][7] : '0') . ",";
	$chart_data['One Year'] .= ((isset($report_data[$index_date][8])) ? $report_data[$index_date][8] : '0') . ",";	
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <!-- Mobile Specific Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="/js/jquery-3.2.1.min.js"></script>
 <script src="\js\jquery-ui-1.12.1.custom\jquery-ui.js"></script>
   <link rel="stylesheet" href="\js\lib\jquery-ui-1.12.1.custom\jquery-ui.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		var ctx2 = document.getElementById('lineChart');
		var myLineChart = new Chart(ctx2,{
			type: 'line',
			data: {
				labels: [<?php echo $chart_data['Labels']?>],
				datasets: [{
					label: 'Yearly Subscription',
					backgroundColor: "rgba(255, 153, 51,1)",
					borderColor: "rgba(255, 153, 51,1)",
					data: [<?php echo $chart_data['Yearly Subscription']?>],
					fill: false,
				}, {
					label: 'Monthly Subscription',
					fill: false,
					backgroundColor: 'rgba(255, 255, 102,1)',
					borderColor: 'rgba(255, 255, 102,1)',
					data: [<?php echo $chart_data['Monthly Subscription']?>]	
				}, {
					label: '3-Day',
					fill: false,
					backgroundColor: 'rgba(0, 102, 153,.6)',
					borderColor: 'rgba(0, 102, 153,.6)',
					data: [<?php echo $chart_data['3-Day']?>]	
				}, {
					label: 'One Month',
					fill: false,
					backgroundColor: 'rgba(0, 102, 153,1)',
					borderColor: 'rgba(0, 102, 153,1)',
					data: [<?php echo $chart_data['One Month']?>]	
				}, {
					label: 'One Year',
					fill: false,
					backgroundColor: 'rgba(51, 204, 51,1)',
					borderColor: 'rgba(51, 204, 51,1)',
					data: [<?php echo $chart_data['One Year']?>]					
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

</head>
<body>
<canvas id="lineChart"></canvas>
</body>
</html>
*/