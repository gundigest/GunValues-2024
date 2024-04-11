<?php
//--------------------------------------------------------------------------------User Functions
//Log user in. Return user handle on success, failure reason on failure.
function login($email,$password){
	global $db;
	try
       {
		//First, find the user
        $stmt = $db->prepare("SELECT user.id,lname,fname,password,user_admin.status FROM user LEFT JOIN user_admin ON user.id = user_id WHERE email=:email ORDER BY timestamp DESC LIMIT 1");
		$stmt->bindparam(":email", $email);
		$stmt->execute();
		$count = $stmt->rowCount();
		$userArr = array();
		if($count>0){
		  $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
		  $user_id = $user[0]['id'];
		  if(password_verify($password,$user[0]['password'])){//password checks out
			//First, check if Admin
			if((!(is_null($user[0]['status'])))&&($user[0]['status']==1)){//check if admin (no plan)
				error_log($user[0]['status']);
				$userArr['user_id'] = $user[0]['lname']."_".$user[0]['id'];
				$userArr['user_name'] = $user[0]['fname'];
				$userArr['plan'] = 'active';
				$userArr['access'] = 'admin';
				return $userArr;
			}else{//Not an admin, so check for a plan
				$stmt = $db->prepare("SELECT status FROM user_subscription WHERE user_id=:user_id ORDER BY timestamp DESC LIMIT 1");
				$stmt->bindparam(":user_id", $user_id);
				$stmt->execute();
				$count = $stmt->rowCount();
				if($count>0){//A plan exists
					$plan = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$status = $plan[0]['status'];
					if($status==="cancelled"){
						//User cancelled the plan
						$userArr['user_id'] = $user[0]['lname']."_".$user[0]['id'];
						$userArr['user_name'] = $user[0]['fname'];
						$userArr['notice'] = "Your Plan has been cancelled. Please visit your Account page to add or change your Plan.";
						$userArr['plan'] = 'cancelled';
						return $userArr;
					}else if($status==="expiring"){
						//If plan is status "expiring", check for expiration of subscription
						$stmt = $db->prepare("SELECT end_ts FROM expiration WHERE user_id=:user_id ORDER BY timestamp DESC LIMIT 1");
						$stmt->bindparam(":user_id", $user_id);
						$stmt->execute();
						$count = $stmt->rowCount();
						$userArr = array();
						if($count>0){//this email exists
							  $now = date('Y-m-d H:i:s');
							  $expiration=$stmt->fetchAll(PDO::FETCH_ASSOC);
							  if($expiration[0]['end_ts'] > $now){
								$userArr['user_id'] = $user[0]['lname']."_".$user[0]['id'];
								$userArr['user_name'] = $user[0]['fname'];
								$userArr['plan'] = 'active';
							  }else{
								$userArr['user_id'] = $user[0]['lname']."_".$user[0]['id'];
								$userArr['user_name'] = $user[0]['fname'];
								$userArr['notice'] = "Your Plan has expired. Please visit <a href='/account/'>your Account page</a> to add or change your Plan.";//Notice will let the user login but the plan is not active
								$userArr['plan'] = 'expired';
							  }
							  return $userArr;
						}
					}else{//Default is an ongoing plan
						$userArr['user_id'] = $user[0]['lname']."_".$user[0]['id'];
						$userArr['user_name'] = $user[0]['fname'];
						$userArr['plan'] = 'active';
						return $userArr;
					}
				}else{
					  $userArr['user_id'] = $user[0]['lname']."_".$user[0]['id'];
					  $userArr['user_name'] = $user[0]['fname'];
					  $userArr['plan'] = 'expired';
					  $userArr['notice'] = "You do not have a current Plan. Please visit your Account page to add or change your Plan.";
					  return $userArr;
				}
			}//end admin check and plan check
		  }else{//Bad Password
				$userArr['user_id'] = false;
				$userArr['reason'] = "The Username and Password do not match our Records.";
				return $userArr;
		  }
		}else{//No record
			$userArr['user_id'] = false;
			$userArr['reason'] = "The Username and Password do not match our Records.";
			return $userArr;
		}
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
function updateLoggedInUser($user_id){
	global $db;
	try
       {
		//First, find the user
        $stmt = $db->prepare("SELECT lname,fname,user_admin.status FROM user LEFT JOIN user_admin ON user.id = user_id WHERE user.id=:user_id ORDER BY timestamp DESC LIMIT 1");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->execute();
		$count = $stmt->rowCount();
		$userArr = array();
		if($count>0){
		    $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$stmt = $db->prepare("SELECT status FROM user_subscription WHERE user_id=:user_id ORDER BY timestamp DESC LIMIT 1");
				$stmt->bindparam(":user_id", $user_id);
				$stmt->execute();
				$count = $stmt->rowCount();
				if($count>0){//A plan exists
					$plan = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$status = $plan[0]['status'];
					if($status==="cancelled"){
						//User cancelled the plan
						$userArr['user_id'] = $user[0]['lname']."_".$user_id;
						$userArr['user_name'] = $user[0]['fname'];
						$userArr['reason'] = "The Plan on record has been cancelled.";
						$userArr['plan'] = 'cancelled';
						return $userArr;
					}else if($status==="expiring"){
						//If plan is status "expiring", check for expiration of subscription
						$stmt = $db->prepare("SELECT end_ts FROM expiration WHERE user_id=:user_id ORDER BY timestamp DESC LIMIT 1");
						$stmt->bindparam(":user_id", $user_id);
						$stmt->execute();
						$count = $stmt->rowCount();
						$userArr = array();
						if($count>0){//this expiration exists
							  $now = date('Y-m-d H:i:s');
							  $expiration=$stmt->fetchAll(PDO::FETCH_ASSOC);
							  if($expiration[0]['end_ts'] > $now){
								$userArr['user_id'] = $user[0]['lname']."_".$user_id;
								$userArr['user_name'] = $user[0]['fname'];
								$userArr['plan'] = 'active';
							  }else{
								$userArr['user_id'] = $user[0]['lname']."_".$user_id;
								$userArr['user_name'] = $user[0]['fname'];
								$userArr['notice'] = "Your Plan is expired. Click your name in the page header to add or change your Plan.";//Notice will let the user login but the plan is not active
								$userArr['plan'] = 'expired';
							  }
						}else{
							$userArr['user_id'] = $user[0]['lname']."_".$user_id;
							$userArr['user_name'] = $user[0]['fname'];
							$userArr['notice'] = "Your Plan is expired. Click your name in the page header to add or change your Plan.";//Notice will let the user login but the plan is not active
							$userArr['plan'] = 'expired';
						  }
					}else{//Default is an ongoing plan
						$userArr['user_id'] = $user[0]['lname']."_".$user_id;
						$userArr['user_name'] = $user[0]['fname'];
						$userArr['plan'] = 'active';
					}
				}
			//Reset the Session Variables to reflect any changes to the account
			$_SESSION['user_id'] = $userArr['user_id'];
			$_SESSION['user_name'] = $userArr['user_name'];
			$_SESSION['plan'] = $userArr['plan'];
		}
	   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
function addUser($userData){
	global $db;
	try
       {
		$hash = password_hash($userData['password'],PASSWORD_DEFAULT);
		$stmt = $db->prepare("INSERT INTO `user`(`fname`, `lname`, `email`, `password`, `address1`, `city`, `state`, `zipcode`,`country`) VALUES (:fname, :lname, :email, :password, :address1, :city, :state, :zipcode, :country)");
		$stmt->bindparam(":fname", $userData['fname']);
		$stmt->bindparam(":lname", $userData['lname']);
		$stmt->bindparam(":email", $userData['email']);
		$stmt->bindparam(":password", $hash);
		$stmt->bindparam(":address1", $userData['address']);
		$stmt->bindparam(":city", $userData['city']);
		$stmt->bindparam(":state", $userData['state']);
		$stmt->bindparam(":zipcode", $userData['zip']);
		$stmt->bindparam(":country", $userData['country']);
		$stmt->execute();
		$user_id=$db->lastInsertId();
		return $user_id;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/*----------------------------------------------*/
/* Updates User from Adding a Plan or	 		*/
/* updating Payment Info. Only Billing info		*/
/* is affected.									*/
/*----------------------------------------------*/
function updateUser($id,$userData){
	global $db;
	$old_user = getUser($id);
	$old_user = implode(', ', $old_user);
	log_activity($id,"Updated Billing Data","Original Data: " . $old_user);
	try
       {
		$stmt = $db->prepare("UPDATE `user` SET `fname`=:fname, `lname`=:lname,`address1`=:address1, `city`=:city, `state`=:state, `zipcode`=:zipcode,`country`=:country WHERE id=:id");
		$stmt->bindparam(":fname", $userData['fname']);
		$stmt->bindparam(":lname", $userData['lname']);
		$stmt->bindparam(":address1", $userData['address']);
		$stmt->bindparam(":city", $userData['city']);
		$stmt->bindparam(":state", $userData['state']);
		$stmt->bindparam(":zipcode", $userData['zip']);
		$stmt->bindparam(":country", $userData['country']);
		$stmt->bindparam(":id", $id);
		$stmt->execute();
		return true;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/*----------------------------------------------*/
/* Updates User Email from Admin User Page or	*/
/*----------------------------------------------*/
function updateUserEmail($id,$email){
	global $db;
	$old_user = getUser($id);
	$old_user = implode(', ', $old_user);
	log_activity($id,"Updated Billing Data","Original Data: " . $old_user);
	try
       {
		$stmt = $db->prepare("UPDATE `user` SET `email`=:email WHERE id=:id");
		$stmt->bindparam(":email", $email);
		$stmt->bindparam(":id", $id);
		$stmt->execute();
		return true;
	   }
	   catch(PDOException $e)
	   {
		   echo $e->getMessage();
	   }
}
function addPayment($payment,$refund_for = NULL){
	global $db;
	try
       {
		$stmt = $db->prepare("INSERT INTO `payment`(`user_id`, `payment_id`, `amount`, `status`,`refund_for`) VALUES (:user_id, :payment_id, :amount, :status, :refund_for)");
		$stmt->bindparam(":user_id", $payment['user_id']);
		$stmt->bindparam(":payment_id", $payment['payment_id']);
		$stmt->bindparam(":amount", $payment['amount']);
		$stmt->bindparam(":status", $payment['status']);
		$stmt->bindparam(":refund_for", $refund_for);
		$stmt->execute();
	   }
	   catch(PDOException $e)
	   {
		   echo $e->getMessage();
	   }
}
//Update Payment. Use this to set payments as failed in the system when they are declined
function updateFailedPayment($user_id,$payment_id){
	global $db;
	try
       {
		$stmt = $db->prepare("UPDATE `payment` SET `status`='failed' WHERE payment_id=:payment_id AND user_id=:user_id");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->bindparam(":payment_id", $payment_id);
		$stmt->execute();
	   }
	   catch(PDOException $e)
	   {
		   echo $e->getMessage();
	   }
}
//Get payment ID for a given user (for recurring payments)
function getRecurID($user_id){
	global $db;

	try
       {
		$stmt = $db->prepare("SELECT `payment_id` FROM payment WHERE user_id=:user_id AND status!='single' ORDER BY timestamp ASC LIMIT 1");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->execute();
		$count = $stmt->rowCount();
		if($count>0){
		  $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
		  return $payments[0]['payment_id'];
		}else return false;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
//Get all payments for a given user
function getAllPayments($user_id){
	global $db;
	$payments = $payments_recur = array();
	try
       {
		$stmt = $db->prepare("SELECT `payment_id`, `amount`, `status`,`timestamp`,refund_for FROM payment WHERE user_id=:user_id ORDER BY timestamp DESC");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->execute();
		$count = $stmt->rowCount();
		if($count>0){
		  $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		$stmt = $db->prepare("SELECT `payment_id`, `amount`, `timestamp` FROM payment_recurrences WHERE user_id=:user_id ORDER BY timestamp DESC");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->execute();
		$count = $stmt->rowCount();
		if($count>0){
		  $payments_recur = $stmt->fetchAll(PDO::FETCH_ASSOC);
		  foreach($payments_recur AS $key => $pr){
				$payments_recur[$key]['status'] = "recurring";
				$payments_recur[$key]['refund_for'] = "";
		  }

		}
		$ret_array = array_merge($payments,$payments_recur);
		foreach ($ret_array as $key => $node) {
		   $timestamps[$key]    = $node['timestamp'];
		}
		array_multisort($timestamps, SORT_DESC, $ret_array);
		return $ret_array;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
//Get all refunds for a given user
function getAllRefunds($user_id){
	global $db;
	$refunds = array();
	try
       {
		$stmt = $db->prepare("SELECT `refund_for` FROM payment WHERE user_id=:user_id AND refund_for IS NOT NULL ORDER BY timestamp DESC");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->execute();
		$count = $stmt->rowCount();
		if($count>0){
		  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
		  foreach($rows AS $row){
			$refunds[] = $row['refund_for'];
		  }
		}
		return $refunds;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/*----------------------------------------------------------*/
/*	Accepts user_id and length of plan to record in DB      */
/*	Also accepts definitive values for start and end dates  */
/*----------------------------------------------------------*/
function addExpiration($user_id,$length,$start_ts="",$end_ts=""){
	global $db;
	if($start_ts == ""){
		$start_ts = date("Y-m-d H:i:s");
	}
	if($end_ts == ""){
		$end_ts = date("Y-m-d H:i:s", strtotime("+ " . $length));
	}	
	try
       {
		$stmt = $db->prepare("INSERT INTO `expiration`(`user_id`, `start_ts`, `end_ts`) VALUES (:user_id, :start_ts, :end_ts)");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->bindparam(":start_ts", $start_ts);
		$stmt->bindparam(":end_ts", $end_ts);
		$stmt->execute();
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
function updatePlanStatus($user_id,$status){
	global $db;

	try
       {
		$stmt = $db->prepare("UPDATE `user_subscription` SET `status` = :status WHERE `user_id`=:user_id");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->bindparam(":status", $status);
		$stmt->execute();
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
function addUserPlan($user_id,$plan_id,$status){
	global $db;

	try
       {
		$stmt = $db->prepare("INSERT INTO `user_subscription`(`user_id`, `plan_id`, `status`) VALUES (:user_id, :plan_id,:status)");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->bindparam(":plan_id", $plan_id);
		$stmt->bindparam(":status", $status);
		$stmt->execute();
		$user_sub_id=$db->lastInsertId();
		//Get latest payment ID
		$payment_id = getPaymentDBID($user_id);
		addSubPaymentLink($user_id,$user_sub_id,$payment_id);
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
function addSubPaymentLink($user_id,$user_sub_id,$payment_id){
	global $db;

	try
       {
		$stmt = $db->prepare("INSERT INTO `user_sub_to_payment`(`user_id`, `user_subscription_id`,`payment_id`) VALUES (:user_id, :user_sub_id,:payment_id)");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->bindparam(":user_sub_id", $user_sub_id);
		$stmt->bindparam(":payment_id", $payment_id);
		$stmt->execute();
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
function makeUserAdmin($user_id){
	global $db;

	try
       {
		$stmt = $db->prepare("INSERT INTO `user_admin`(`user_id`, `status`) VALUES (:user_id, 1)");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->execute();
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }

}
/*----------------------------------------------------------*/
/*	Accepts username, creates and sets Password Code,       */
/*	and returns Password Code							    */
/*----------------------------------------------------------*/
function getCode($username, $type){
	global $db;
	$user_id = getUserId($username);
	if(!$user_id) return false;
	try
       {
			$code=substr(md5(uniqid(mt_rand(), true)) , 0, 8);
            $stmt = $db->prepare("INSERT INTO account_code(user_id,type,code) VALUES(:user_id,:type,:code)");
            $stmt->bindparam(":user_id", $user_id);
            $stmt->bindparam(":type", $type);
            $stmt->bindparam(":code", $code);
            $stmt->execute();

           return $code;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/*----------------------------------------------------------*/
/*	Accepts code, checks for code and type,       */
/*	and returns user_id							    */
/*----------------------------------------------------------*/
function checkCode($code,$type){
	global $db;

	try
       {
			$stmt = $db->prepare("SELECT user_id FROM account_code WHERE code=:code AND type=:type AND used IS NULL ORDER BY timestamp DESC LIMIT 1");
			$stmt->bindparam(":code", $code);
			$stmt->bindparam(":type", $type);
			$stmt->execute();
			$count = $stmt->rowCount();
			if($count>0){
			  $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
			  return $user[0]['user_id'];
			}else return false;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/*----------------------------------------------------------*/
/*	Accepts code 		    						         */
/*	and returns boolean		       						    */
/*----------------------------------------------------------*/

function disableCode($code){
	global $db;
	try
       {
		$now = date('Y-m-d H:i:s');
		$stmt = $db->prepare("UPDATE `account_code` SET used=:used WHERE code=:code");
		$stmt->bindparam(":used", $now);
		$stmt->bindparam(":code", $code);
		$stmt->execute();
		$count = $stmt->rowCount();
		if($count>0){
		  return true;
		}else return false;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/*----------------------------------------------------------*/
/*	Accepts UserID and password						         */
/*	and returns boolean		       						    */
/*----------------------------------------------------------*/

function resetPassword($user_id,$password){
	global $db;
	try
       {
		$hash = password_hash($password,PASSWORD_DEFAULT);
		$stmt = $db->prepare("UPDATE `user` SET password=:password WHERE id=:user_id");
		$stmt->bindparam(":password", $hash);
		$stmt->bindparam(":user_id", $user_id);
		$stmt->execute();
		$count = $stmt->rowCount();
		if($count>0){
		  return true;
		}else return false;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/*----------------------------------------------------------*/
/*	Accepts username 								         */
/*	and returns User ID		       						    */
/*----------------------------------------------------------*/
function getUserId($email){
	global $db;
	try
       {
		$stmt = $db->prepare("SELECT id FROM user WHERE email=:email ORDER BY timestamp DESC LIMIT 1");
		$stmt->bindparam(":email", $email);
		$stmt->execute();
		$count = $stmt->rowCount();
		if($count>0){
		  $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
		  return $user[0]['id'];
		}else return false;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/*----------------------------------------------------------*/
/*	Accepts ID 								         */
/*	and returns Email Address		       						    */
/*----------------------------------------------------------*/
function getUserEmail($id){
	global $db;
	try
       {
		$stmt = $db->prepare("SELECT email FROM user WHERE id=:id ORDER BY timestamp DESC LIMIT 1");
		$stmt->bindparam(":id", $id);
		$stmt->execute();
		$count = $stmt->rowCount();
		if($count>0){
		  $user = $stmt->fetchAll(PDO::FETCH_ASSOC);
		  return $user[0]['email'];
		}else return false;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
function checkLoggedIn(){
	if((isset($_SESSION['user_name'])) && (strlen($_SESSION['user_name'])>0)){//we are logged in
		return true;
	}else return false;
}
function checkPlanActive(){
	if((isset($_SESSION['user_name'])) && (strlen($_SESSION['user_name'])>0) && $_SESSION['plan']==="active"){//we have an active plan
		return true;
	}else return false;
}
function checkAdmin(){
	if((isset($_SESSION['user_name'])) && (isset($_SESSION['access'])) && (strlen($_SESSION['user_name'])>0) && $_SESSION['access']==="admin"){//we have an admin
		return true;
	}else return false;
}
function checkPlanActiveById($customer_id,$ongoing_only = false){
	global $db;
				$stmt = $db->prepare("SELECT status FROM user_subscription WHERE user_id=:user_id ORDER BY timestamp DESC LIMIT 1");
				$stmt->bindparam(":user_id", $customer_id);
				$stmt->execute();
				$count = $stmt->rowCount();
				if($count>0){//A plan exists
					$plan = $stmt->fetchAll(PDO::FETCH_ASSOC);
					$status = $plan[0]['status'];
					if($status==="cancelled"){
						//User cancelled the plan
						return false;
					}else if($status==="expiring"){
						if($ongoing_only){//only care about ongoing/recurring plans
							return false;
						}else{
							//If plan is status "expiring", check for expiration of subscription
							$stmt = $db->prepare("SELECT end_ts FROM expiration WHERE user_id=:user_id ORDER BY timestamp DESC LIMIT 1");
							$stmt->bindparam(":user_id", $customer_id);
							$stmt->execute();
							$count = $stmt->rowCount();
							$userArr = array();
							if($count>0){//this email exists
								  $now = date('Y-m-d H:i:s');
								  $expiration=$stmt->fetchAll(PDO::FETCH_ASSOC);
								  if($expiration[0]['end_ts'] > $now){
									return true;
								  }else{
									return false;
								  }
							}
						}
					}else{//Default is an ongoing plan
						return true;
					}
				}else return false;
}
/* ------------------------------------------------------------------*/
/*	Get Account Info for Display & Updates to Payment Gateway	     */
/* ------------------------------------------------------------------*/
function getUser($user_id){
	global $db;
	try
       {
		$stmt = $db->prepare("SELECT `fname`, `lname`, `email`, `address1`, `city`, `state`, `zipcode`,`country` FROM `user` WHERE id=:id");
		$stmt->bindparam(":id", $user_id);
		$stmt->execute();
		$count = $stmt->rowCount();
        if($count>0){
		  $userRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
		  return $userRows[0];
	   }else return false;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/* ------------------------------------------------------------------*/
/*	Get Plan Info for Display								     */
/* ------------------------------------------------------------------*/
function getPlan($user_id){
	global $db;
	try
       {
		$stmt = $db->prepare("SELECT p.name,p.amount,.p.frequency,s.status,s.updated,s.timestamp FROM `user` u JOIN `user_subscription` s ON u.id  = s.user_id JOIN `subscription` p ON p.id = s.plan_id WHERE u.id = :id ORDER BY s.timestamp DESC LIMIT 1");
		$stmt->bindparam(":id", $user_id);
		$stmt->execute();
		$count = $stmt->rowCount();
        if($count>0){
		  $userRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
		  return $userRows[0];
	   }else return false;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/* ------------------------------------------------------------------*/
/*	Get Expiration Info for Display								     */
/* ------------------------------------------------------------------*/
function getExpiration($user_id){
	global $db;
	try
       {
		$stmt = $db->prepare("SELECT end_ts FROM `expiration` WHERE user_id = :id ORDER BY timestamp DESC LIMIT 1");
		$stmt->bindparam(":id", $user_id);
		$stmt->execute();
		$count = $stmt->rowCount();
        if($count>0){
		  $expRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
		  return $expRows[0]['end_ts'];
	   }else return false;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/* ------------------------------------------------------------------*/
/*	Get Latest Recurrence Payment ID for Processing cancellations    */
/* ------------------------------------------------------------------*/
function getPaymentID($user_id){
	global $db;
	try
       {
		$stmt = $db->prepare("SELECT payment_id FROM `payment` WHERE user_id = :user_id AND LENGTH(payment_id)>5 AND status='recurring' ORDER BY id DESC LIMIT 1");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->execute();
		$count = $stmt->rowCount();
        if($count>0){
		  $userRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
		  return $userRows[0]['payment_id'];
	   }else return false;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/* ------------------------------------------------------------------*/
/*	Get Latest Payment DB ID for Records								     */
/* ------------------------------------------------------------------*/
function getPaymentDBID($user_id){
	global $db;
	try
       {
		$stmt = $db->prepare("SELECT id FROM `payment` WHERE user_id = :user_id ORDER BY id DESC LIMIT 1");
		$stmt->bindparam(":user_id", $user_id);
		$stmt->execute();
		$count = $stmt->rowCount();
        if($count>0){
		  $userRows=$stmt->fetchAll(PDO::FETCH_ASSOC);
		  return $userRows[0]['id'];
	   }else return false;
   }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
/* ------------------------------------------------------------------*/
/*	Checks that an email used to register is unique and has not      */
/*  been used before, returns FALSE if email is dupe				 */
/* ------------------------------------------------------------------*/
function checkEmailUnique($email){
	global $db;
	try
       {
		$stmt = $db->prepare("SELECT id FROM user WHERE email=:email ORDER BY timestamp DESC LIMIT 1");
		$stmt->bindparam(":email", $email);
		$stmt->execute();
		$count = $stmt->rowCount();
		$userArr = array();
		if($count>0){
			return false;
		}else return true;
	 }
   catch(PDOException $e)
   {
	   echo $e->getMessage();
   }
}
function showAd($unit){
	//Ad layout for DFP Units
	global $defineAdSlots;
	//if(!checkPlanActive()){
		switch($unit){
			case "large-rect1":
				$defineAdSlots .= "googletag.defineSlot('/205002847/gv_sb_1_dt', [300, 250], 'div-gpt-ad-1520377740904-1').addService(googletag.pubads());";
				$adcode = <<<EOD
				<!-- /205002847/gv_sb_1_dt -->
				<div class="large-rect1" id='div-gpt-ad-1520377740904-1'>
				<script>
				googletag.cmd.push(function() { googletag.display('div-gpt-ad-1520377740904-1'); });
				</script>
				</div>
EOD;
				return $adcode;
				break;
			case "large-rect2":
				$defineAdSlots .= "googletag.defineSlot('/205002847/gv_sb_2_dt', [300, 250], 'div-gpt-ad-1520377740904-2').addService(googletag.pubads());";
				$adcode = <<<EOD
				<!-- /205002847/gv_sb_2_dt -->
				<div class="large-rect2" id='div-gpt-ad-1520377740904-2'>
				<script>
				googletag.cmd.push(function() { googletag.display('div-gpt-ad-1520377740904-2'); });
				</script>
				</div>
EOD;
				return $adcode;
				break;
			case "large-rect3":
				$defineAdSlots .= "googletag.defineSlot('/205002847/gv_sb_3_dt', [300, 250], 'div-gpt-ad-1520377740904-3').addService(googletag.pubads());";
				$adcode = <<<EOD
				<!-- /205002847/gv_sb_3_dt -->
				<div id='div-gpt-ad-1520377740904-3' style='height:250px; width:300px;'>
				<script>
				googletag.cmd.push(function() { googletag.display('div-gpt-ad-1520377740904-3'); });
				</script>
				</div>
EOD;
				return $adcode;
				break;
			case "banner":
				//define size mapping for desktop and mobile
				$defineAdSlots .= "var banner_mapping = googletag.sizeMapping()";
				$defineAdSlots .= ".addSize([728, 700], [728, 90]).";
				$defineAdSlots .= "addSize([1050, 200], [[728, 90],[970, 90]]).";
				$defineAdSlots .= "addSize([0, 0], [[300,50],[320,50]]).";
				$defineAdSlots .= "build();";
				$defineAdSlots .= "googletag.defineSlot('/205002847/gv_h_dt', [[300,50],[320,50],[728, 90],[970,90]], 'div-gpt-ad-1625618802567-0').defineSizeMapping(banner_mapping).addService(googletag.pubads());";
				$adcode = <<<EOD
				<!-- /205002847/gv_h_dt -->
				<div id='div-gpt-ad-1625618802567-0'>
				<script>
				googletag.cmd.push(function() { googletag.display('div-gpt-ad-1625618802567-0'); });
				</script>
				</div>
EOD;
				return $adcode;
				break;
			default:
				return "<div class='ad-large-rect'></div>";
				break;
		}
	//}else return "";
}
/* */
function newsletterSubscribe($email,$first_name,$last_name){
	//Bronto Code
	$client = new SoapClient(
			'https://api.bronto.com/v4?wsdl',
			array('trace' => 1,
			'features' => SOAP_SINGLE_ELEMENT_ARRAYS)
	);
	//Bronto uses long fieldIds for these
	$fields = array();
	$fields[] = array(
		'fieldId' => "a73b2f34-d8cf-4559-973a-0b3719c2eb5a",//GD_GVSub
		'content' => "true"
		);
	$fields[] = array(
		'fieldId' => "77d578b4-a61f-4e02-a737-1515a26e82a5",//first_name
		'content' => $first_name
		);
	$fields[] = array(
		'fieldId' => "ea2a42f2-27c4-4358-bd4c-fbc20b3b16d2",//last_name
		'content' => $last_name
		);
	$csource = "GunValues Registration Form";
try {
    $token = "2D668918-48AB-4EFF-B91B-851278E1F0FE";//GD
    $sessionId = $client->login(array('apiToken' => $token))->return;
    $session_header = new SoapHeader(
			"http://api.bronto.com/v4",
            'sessionHeader',
            array('sessionId' => $sessionId)
	);
    $client->__setSoapHeaders(array($session_header));
    // Add a contact. If the contact is new or already exists,
    // you will get back an ID.
	$list_id = array('0bd703ec00000000000000000000001df957',"0bd703ec00000000000000000000001dfb1d","0bd703ec00000000000000000000001dfb21","0bd703ec00000000000000000000001dfb1e");
    $contacts = array(
		'email' => $email,
		'listIds' => $list_id,
		'fields' => $fields,
		'source' => 'webform',
		'customSource' => $csource
		);
    $write_result = $client->addOrUpdateContacts(array($contacts)
                                      )->return;

     // The id returned will be used by the readContacts() call below.
 	 if (!($write_result->results[0]->isError==false)) {
			error_log("There was a problem adding or updating the contact:\n");
			error_log(json_encode($write_result->results));
			return false;
     } else {
        error_log("Success Bronto email add");
     }
} catch (Exception $e) {
  return false;
}
}
function log_activity($user_id,$activity,$details){
	  global $db;
	  if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
		  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
	  $ip_address = "Not Available";
		if(!empty($_SERVER['REMOTE_ADDR']) ){
			$ip_address = $_SERVER['REMOTE_ADDR'];
		}
		else{
			$ip_address = empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? '' : $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
	   $browser = "Not Available";
       if(!empty($_SERVER['HTTP_USER_AGENT'])) $browser=$_SERVER['HTTP_USER_AGENT'];
      try
       {
           $stmt = $db->prepare("INSERT INTO activity_log(user_id,activity,details,ip_address,browser) VALUES(:user_id,:activity,:details,:ip_address,:browser)");

            $stmt->bindparam(":user_id", $user_id);
            $stmt->bindparam(":activity", $activity);
            $stmt->bindparam(":details", $details);
            $stmt->bindparam(":ip_address", $ip_address);
            $stmt->bindparam(":browser", $browser);

            $stmt->execute();

           return $stmt;
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }
}
function log_history($user_id,$page,$search_term=null,$make=null,$model=null,$series=null){

	return true;
	//History Logging Disabled 4/12/2018
	/*global $db;
	  if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
		  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
		}
	  $ip_address=$_SERVER['REMOTE_ADDR'];
      $browser=$_SERVER['HTTP_USER_AGENT'];
	  $user_id = substr($user_id, strpos($user_id, "_") + 1);
      try
       {
           $stmt = $db->prepare("INSERT INTO history_log(user_id,page,search_term,make,model,series,ip_address,browser) VALUES(:user_id,:page,:search_term,:make,:model,:series,:ip_address,:browser)");

            $stmt->bindparam(":user_id", $user_id);
            $stmt->bindparam(":page", $page);
            $stmt->bindparam(":search_term", $search_term);
            $stmt->bindparam(":make", $make);
            $stmt->bindparam(":model", $model);
            $stmt->bindparam(":series", $series);
            $stmt->bindparam(":ip_address", $ip_address);
            $stmt->bindparam(":browser", $browser);

            $stmt->execute();

           return $stmt;
       }
       catch(PDOException $e)
       {
           echo $e->getMessage();
       }*/
}
?>