<?php
include 'DB.php';
	
	$db = DB::getInstance();
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'http://192.168.43.126');
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$data = curl_exec($ch);
	$rfid = substr($data, -12);
	$rfid = substr($rfid, 0, 10);
	curl_close($ch);

	// echo $rfid;

	if(strlen($rfid) == 10){
		$exists = $db->get('car_master', array('rfid', '=', $rfid));
		// print_r($exists);
		$exists = $exists->first();
		// print_r($exists);
		if ($exists) {
			// print_r($exists);
			$user_id = $exists->user_id;
			// print_r($user_id);
			$exists = $db->get('fine_master', array('user_id', '=', $user_id));
			$exists = $exists->result();
			// print_r($exists);
			if ($exists) {
				$prev_amt = $db->get('fine_master', array('user_id', '=', $user_id));
				$prev_amt = $prev_amt->first()->fine_amt;
				$amt = $prev_amt + 500;
				$db->update('fine_master', array('fine_amt' => $amt), $user_id);
			}else{
				$charge = $db->insert('fine_master', array('user_id' => $user_id, 'fine_amt' => 500));

			}
			if($db->error()){
				print_r($db->errorStat());
			}
			mail('shubhrohilla@gmail.com', 'Pay fine', 'Kindly pay the dues');
			echo 'Centeralised database has been populated';
		}else{
			//invalid rfid - no user is registered against this id
		}
	}else{
		//No RFID - no car crosses the line!
	}

?>