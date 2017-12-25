<?php
include('include/class.notification.php');
$objNotification = new NOTIFICATION();
			$objNotification->deviceToken ='2e0679afd3b5c46c78c3893a364832855f38257f334f143bce5147167d17c959';
			//$objNotification->deviceToken ='baf281b4d50701abcb88bf50ccff2f78f7189fb478aea43faeaaee65331ea0e5';
			//$objNotification->nid = $nid;
			$objNotification->title = "Test Data";			
			
			//write device details to log file
			//$deviceType = ($value['deviceType'] == 2 ? 'Iphone' : 'Android');
			
			
			
			try {
					/*if($value['deviceType'] == 2) {
							$objNotification->sendToIphone();
						}
						else {
							$objNotification->sendToAndroid();
						} */
						
						$objNotification->sendToIphone();
			} catch (Exception $e) {
				//fwrite($fp, 'Error => ' . $e->getMessage() . "\r\n");
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
			
	

//Close connection


?>
