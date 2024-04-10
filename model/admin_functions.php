<?php
//Functions for Gun Values
//get guns by Seach Terms
function getOverview($start_date,$end_date){
	global $db;
	$start_date = $start_date . " 00:00:00";
	$end_date = $end_date . " 23:59:59";
	try{					
	
		    $stmt = $db->prepare("SELECT u.`id`,p.payment_id AS pt_id,p.status AS payment_status, `fname`, `lname`, `email`, `city`, `state`,us.`status`,us.timestamp,`name`,p.`amount`,usp.payment_id FROM `user_sub_to_payment` usp LEFT JOIN `user` u ON usp.user_id=u.id JOIN`user_subscription` us ON usp.user_subscription_id=us.id JOIN `subscription` s ON s.id = us.plan_id JOIN `payment` p on p.id=usp.payment_id WHERE us.timestamp >= :start_date AND us.timestamp <= :end_date ORDER BY us.timestamp");            
			$stmt->bindparam(":start_date", $start_date);
			$stmt->bindparam(":end_date", $end_date);
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $dataRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
              return $dataRows;
           }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//Get recurring payments
function getRecurringPaymentsReport($start_date,$end_date){
	global $db;
	$start_date = $start_date . " 00:00:00";
	$end_date = $end_date . " 23:59:59";
	try{
		    $stmt = $db->prepare("SELECT u.`id`,p.pt_id, `fname`, `lname`, `email`, `city`, `state`,us.`status`,p.payment_date,`name`,p.`amount`,usp.payment_id FROM `user_sub_to_payment` usp LEFT JOIN `user` u ON usp.user_id=u.id JOIN `user_subscription` us ON usp.user_subscription_id=us.id JOIN `subscription` s ON s.id = us.plan_id JOIN `payment_recurrences` p on p.payment_id=usp.payment_id WHERE p.payment_date >= :start_date AND p.payment_date <= :end_date ORDER BY p.payment_date");            
			$stmt->bindparam(":start_date", $start_date);
			$stmt->bindparam(":end_date", $end_date);
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $dataRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
              return $dataRows;
           }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
//get Active users on a daily basis
function getActiveUserReport($start_date,$end_date){
	global $db;
	$begin = new DateTime($start_date);
	//add a day to end to include the last day
	$end = date("Y-m-d",strtotime($end_date . " + 1 day"));
	$end = new DateTime($end);

	$interval = DateInterval::createFromDateString('1 day');
	$period = new DatePeriod($begin, $interval, $end);
	$chart_data = array();	
	foreach ($period as $dt) {		
		$this_day = $dt->format("Y-m-d");
		$this_timestamp = $dt->format("Y-m-d 12:00:00");
		try{
			$stmt = $db->prepare('SELECT COUNT(*) as total,plan_id FROM user_subscription WHERE timestamp < :this_day AND user_id NOT IN (SELECT user_id FROM expiration WHERE end_ts < :this_day) AND user_id NOT IN (SELECT user_id FROM activity_log WHERE activity="Automatic Plan Cancellation" AND timestamp < :this_day)GROUP BY plan_id');            
			$stmt->bindparam(":this_day", $this_timestamp);
			$stmt->execute(); 
			$count = $stmt->rowCount();
			if($count>0){				
				$results = $stmt->fetchAll(PDO::FETCH_ASSOC);				
				foreach($results AS $res){
					$chart_data[$this_day][$res['plan_id']] = $res['total'];				
				}	
			}				
		}	
		  catch(PDOException $e)
		   {
			   echo $e->getMessage();
		   }
	}
	return $chart_data;
}
//get users by search terms
function searchUsers($email,$fname,$lname){
	global $db;

	$qString = "";
	
	$match_string = array();	
	$where_string = array();	
	if($email != ""){//add Email to search
		$match_string[] = "MATCH (email) AGAINST (:email IN NATURAL LANGUAGE MODE) AS email_score";
		$where_string[] = " MATCH (email) AGAINST (:email IN NATURAL LANGUAGE MODE) > 0 ";
	}	
	if($fname != ""){//add fname to search
		$match_string[] = "MATCH (fname) AGAINST (:fname IN NATURAL LANGUAGE MODE) AS fname_score";		
		$where_string[] = " MATCH (fname) AGAINST (:fname IN NATURAL LANGUAGE MODE) > 0 ";		
	}	
	if($lname != ""){//add lname to search
		$match_string[] = "MATCH (lname) AGAINST (:lname IN NATURAL LANGUAGE MODE) AS lname_score";		
		$where_string[] = " MATCH (lname) AGAINST (:lname IN NATURAL LANGUAGE MODE) > 0 ";		
	}	
	if(count($match_string)>0){
		$match_string_final = "," . implode(",",$match_string);	
		$where_string_final = "WHERE" . implode("OR",$where_string);	
	}else{
		return false;		
	}
	try{				
		    $stmt = $db->prepare("SELECT id, fname, lname, email, timestamp" . $match_string_final . "  from user " . $where_string_final . " order by timestamp DESC");
			if($email != ""){
				$stmt->bindparam(":email", $email);
			}
			if($fname != ""){
				$stmt->bindparam(":fname", $fname);
			}
			if($lname != ""){
				$stmt->bindparam(":lname", $lname);
			}
            $stmt->execute(); 
            $count = $stmt->rowCount();
            if($count>0){
              $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
              return $users;
           }else return false;       
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }    
}
?>