<?php
	
	CLASS NOTIFICATION {
		
		public $deviceToken;
		public $title;
		//public $nid;
		
		//Function for sending push notification to iphone
		function sendToIphone($salutation = array()) {
			
			// Put your device token here (without spaces):
			$deviceToken = $this->deviceToken;

			// Put your private key's passphrase here:
			//$passphrase = '1234';
			 
			// Put your alert message here:
			$message = "Test demo";
			
			////////////////////////////////////////////////////////////////////////////////
		
			$ctx = stream_context_create();
                        
			stream_context_set_option($ctx, 'ssl', 'local_cert', $_SERVER['DOCUMENT_ROOT'].'/push/include/ck_user_production.pem');
			if(file_exists($_SERVER['DOCUMENT_ROOT'].'/push/include/ck_user_production.pem')) {
				echo "sdsdsd";
			} else {
				echo "no";
			}
			//stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
			 
			// Open a connection to the APNS server
			$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
			
			//developement server link
			 //$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
			
			if (!$fp)
			exit("Failed to connect: $err $errstr" . PHP_EOL);
			 
			echo 'Connected to APNS' . PHP_EOL;
			 
			// Create the payload body
			$body['aps'] = array(
								'alert' => $message,
								'sound' => 'default'
							);
			//$body['nid'] =  $this->nid;
			$body['title'] = $this->title;
			 
			// Encode the payload as JSON
			 $payload = json_encode($body);
			 
			// Build the binary notification
			$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
			 
			// Send it to the server
			$result = fwrite($fp, $msg, strlen($msg));
			 
			if (!$result) {
				$jsondata['message'] = 'Message not delivered';
				$jsondata['status'] = 0;
			}
			else {
				$jsondata['message'] = 'Message successfully delivered';
				$jsondata['status'] = 1;
			}
			// Close the connection to the server
			fclose($fp);

			echo json_encode((object) $jsondata);
		}
		
		//Function for sending push message to client
		function sendToAndroid() {
			$fp = fopen('androidpush' . date('dmy') . ".txt", 'a+');
			fwrite($fp, '------------------------ new record --------------------------------' . "\r\n");
			// API access key from Google API's Console
			//define('API_ACCESS_KEY', 'AIzaSyAatvtDp47IO1pEI1ha60HrMS0gk_BpwmY' );
			define('API_ACCESS_KEY', 'AIzaSyAbFPxpENDpe4UBsCZB7wkZPKXNuviL68s');
			 
			$registrationIds = array($this->deviceToken);
			 
			// prep the bundle
			$msg = array
			(
				//'nid' => $this->nid,
				'title' => $this->title
			);
		
			$fields = array
			(
				'registration_ids' => $registrationIds,
				'data'	=> $msg
			);
			
			$headers = array
			(
				'Authorization: key=' . API_ACCESS_KEY,
				'Content-Type: application/json'
			);

			$ch = curl_init();
			curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
			curl_setopt( $ch,CURLOPT_POST, true );
			curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
			$result = curl_exec($ch );
			fwrite($fp, "Error : " .  curl_error($ch) . "\r\n");
			fwrite($fp, "Result : " .  json_encode($result) . "\r\n");
			curl_close($ch);
			
			if (!$result) {
				$jsondata['message'] = 'Message not delivered';
				$jsondata['status'] = 0;
			}
			else {
				$jsondata['message'] = 'Message successfully delivered';
				$jsondata['status'] = 1;
			}
			// Close the connection to the server
			fclose($fp);

			echo json_encode((object) $jsondata);
		}
	}
?>