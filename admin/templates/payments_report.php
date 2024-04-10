<?php
//Recurring Payments Page Content
$page_name = "All Payments Reporting";
$start_date = isset($_GET['sd'])? date('Y-m-d',strtotime(urldecode($_GET['sd']))) : date('Y-m-d', strtotime("-1 weeks"));
$end_date = isset($_GET['ed'])? date('Y-m-d',strtotime(urldecode($_GET['ed']))) : date('Y-m-d');
$dashboard_data = array();
$recurring_data = getRecurringPaymentsReport($start_date,$end_date);
$payments_data = getOverview($start_date,$end_date);
//Combine the two types of payments, ordering by timestamp
foreach($recurring_data AS $data){
	$dashboard_data[$data['payment_date'] . $data['id']] = [
		'user_id' => $data['id'],
		'user_name' => $data['fname'] . " " . $data['lname'],
		'email' => $data['email'],
		'plan' => $data['name'],
		'status' => $data['status'],
		'pt_id' => $data['pt_id'],
		'amount' => $data['amount'],
		'payment_date' => $data['payment_date']
	];	
}
foreach($payments_data AS $data){
	if($data['payment_status'] == "recurring"){//this means we do not have a useful ID number to offer
		$pt_id = "";
	}else $pt_id = $data['pt_id'];
	$dashboard_data[$data['timestamp'] . $data['id']] = [
		'user_id' => $data['id'],
		'user_name' => $data['fname'] . " " . $data['lname'],
		'email' => $data['email'],
		'plan' => $data['name'],
		'status' => $data['payment_status'],
		'pt_id' => $pt_id,
		'amount' => $data['amount'],
		'payment_date' => $data['timestamp']
	];	
}
krsort($dashboard_data);
$html =<<<EOD
<h2 class="subhead">All Payments Report</h2>
<div class="centered_dates">Refine Date Range: <form><input class="datepicker" type="text" placeholder="MM/DD/YYYY" id="start_date" name="sd" maxlength="10" readonly="readonly"/> to <input class="datepicker" type="text" placeholder="MM/DD/YYYY" id="end_date" name="ed" maxlength="10" readonly="readonly"/><input type="submit" value="View Date Range"/></form></div>
<table class="admin">
	<thead>
	<td>User ID</td>	
	<td>Name</td>	
	<td>Email</td>	
	<td>Plan</td>
	<td>Status</td>
	<td>App. Code OR PT_ID</td>
	<td class='currency'>Amount Paid</td>
	<td class='currency'>Amount Declined</td>
	<td class='currency'>Timestamp</td>
	</thead>
EOD;
$total = $failed = 0;

		foreach($dashboard_data AS $data){
			$html .= "<tr>";
			$html .= "<td>PGTV_" . $data['user_id'] . "</td>";
			$html .= "<td>" .$data['user_name']. "</td>";
			$html .= "<td>" .$data['email'] . "</td>";			
			$html .= "<td>" .$data['plan'] . "</td>";
			if(($data['status']=="expiring")&&($data['plan']!="3-Day")){
				$html .= "<td>CANCELLED</td>";
			}else $html .= "<td>" .$data['status'] . "</td>";
			$html .= "<td>" .$data['pt_id'] . "</td>";
			if($data['status']!="failed"){
				$html .= "<td class='currency'>$" .$data['amount'] . "</td>";
				$html .= "<td class='currency'></td>";
				$total += $data['amount'];
			}else{
				$html .= "<td class='currency'></td>";
				$html .= "<td class='currency'>$" .$data['amount'] . "</td>";	
				$failed += $data['amount'];
			}	
			$html .= "<td class='currency'>" .$data['payment_date'] . "</td>";
			$html .= "</tr>";			
		}
$display_total = "$" . number_format($total,2);
$display_total_failed = "$" . number_format($failed,2);
$html .= <<<EOD
	<tr>
	<td colspan="6"></td>	
	<td class='currency'>Total Charged: $display_total </td>
	<td class='currency'>Total Declined: $display_total_failed </td>
	<td></td>	
	</tr>
EOD;
$html .= "</table>";

$sd_js = date("m/d/Y",strtotime($start_date));
$ed_js = date("m/d/Y",strtotime($end_date));
$head = <<<EOD
<link rel="stylesheet" href="{$root}js/jquery-ui-1.12.1.custom/jquery-ui.css">
<script src="{$root}js/jquery-ui-1.12.1.custom/jquery-ui.js"></script>
<script type="text/javascript">
$(document).ready(function() {

    $( ".datepicker" ).datepicker({
      dateFormat: 'mm/dd/yy',
      minDate: new Date("2018-03-26"),
      maxDate: '+1d'
    });
	$('#start_date').datepicker("setDate", "{$sd_js}" );
	$('#end_date').datepicker("setDate", "{$ed_js}" );

	//State switching for non-US countries
	$('#country').change(function(){
		var country_value = $(this).val();
		if(country_value!="US"){
			$('#state').hide();
			$('#state').removeAttr('required');
			$('#state-alt').show();
		}else{
			$('#state').show();
			$('#state').attr('required');
			$('#state-alt').hide();
		}				
	});			
	
	$('#submit_button').click(function (event) {
				event.preventDefault();
				alert_text = "";
				error=false;
				
				username_content = $('input[name=username]').val();
				if (username_content.length==0){
				  alert_text += "Please complete the user's Email Address \\n ";      
				  $('input[name=username]').removeClass('complete').addClass('incomplete');
				  error=true;
				}else if(!(validateEmail(username_content))){
					alert_text+='Please enter a valid Email Address \\n ';
					$('input[name=username]').removeClass('complete').addClass('incomplete');
					error=true;
				}else $('input[name=username]').removeClass('incomplete').addClass("complete");			
				
				if (($('input[name=fname]').val()).length==0){
				  alert_text += "Please complete the user's First Name \\n ";
				  $('input[name=fname]').removeClass('complete').addClass('incomplete');
				  error=true;
				}else $('input[name=fname]').removeClass('incomplete').addClass("complete");
				
				if (($('input[name=lname]').val()).length==0){
				  alert_text += "Please complete the user's Last Name \\n ";
				  $('input[name=lname]').removeClass('complete').addClass('incomplete');
				  error=true;
				}else $('input[name=lname]').removeClass('incomplete').addClass("complete");			
				
				if (($('#plan').val()).length==0){
				  alert_text += "Please choose a Plan \\n ";
				  $('#plan').removeClass('complete').addClass('incomplete');
				  error=true;
				}else $('#plan').removeClass('incomplete').addClass("complete");
				
				if (!error){
				  $(this).prop('disabled', true);
				  $(this).val('Submitting, please wait...');
				  //submit the validated form
				  $('#grant_access').submit();
				}else{
				   event.preventDefault();
				   alert(alert_text);				  
				}
			  });
	
	
});
function validateEmail(email) 
{
    var re = /\S+@\S+\.\S+/;
    return re.test(email);
}
</script>
EOD;
?>



