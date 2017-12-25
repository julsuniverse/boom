<?php
namespace api\models;

use Yii;
use yii\base\ErrorException;
use yii\web\NotFoundHttpException;

class Commentnotforartist
{
    public $deviceToken;
    public $message;
    public $title;
    public $postid;
    public $NotificationType;
    public $time;

    function __construct($deviceToken = "") 
    {
        $this->deviceToken 	= 	$deviceToken;
    }

    //Function for sending push notification to iphone
    function sendToIphone($artistID,$type="artist")
    {
		
		$this->writeLog("\n\n ************************");    
        $this->writeLog("\n Start Send to Iphone push: \n".$this->time);
		
        try {
            // Put your device token here (without spaces):
            $deviceToken            =       $this->deviceToken;
            //$deviceToken            =       "2e0679afd3b5c46c78c3893a364832855f38257f334f143bce5147167d17c959";
            $message                =       $this->message;
            $postid                 =       $this->postid;
            $notificationtype       =       $this->NotificationType;
            $time                   =       $this->time;
            // Put your private key's passphrase here:
            //$passphrase = '1234';
            //$artistID = "1";
            
			$this->writeLog("\n Device Token: ".$this->deviceToken." \n");
			$this->writeLog("\n ArtistID: ".$artistID." \n");
			$this->writeLog("\n Message: ".$this->message." \n");
			$this->writeLog("\n PostID: ".$this->postid." \n");
			$this->writeLog("\n Notification Type: ".$this->NotificationType." \n");
			
            ////////////////////////////////////////////////////////////////////////////////

            $ctx = stream_context_create();
            if($type == 'artist') {
                //stream_context_set_option($ctx, 'ssl', 'local_cert', 'pushkey/artistcommon/ck_artist_production.pem');
                stream_context_set_option($ctx, 'ssl', 'local_cert', '../../api/web/pushkey/artistcommon/ck_artist_production.pem');
                //stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
            } else {
                //stream_context_set_option($ctx, 'ssl', 'local_cert', 'pushkey/artist_'.$artistID.'/ck_user_production.pem');
                stream_context_set_option($ctx, 'ssl', 'local_cert', '../../api/web/pushkey/artist_'.$artistID.'/ck_user_production.pem');
                //stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
            }   
            
            /*if(file_exists('../../api/web/pushkey/artist_'.$artistID.'/ck_user_production.pem')) {
                echo "yes";
            } else {
                echo "no";
            }
            die;*/
            try {
                //$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
                $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
            } catch (ErrorException $e) {
                $this->writeLog("\n Exception: Gateway not connected \n".$e);
                return true;
            }

            if (!$fp){
				$this->writeLog("\n Connection Failed with error: ".$err." - ".$errstr." \n");
				exit("Failed to connect: $err $errstr" . PHP_EOL);
			}else{
				$this->writeLog("\n Connection established \n");
			}

            //echo 'Connected to APNS' . PHP_EOL;

            // Create the payload body
            $body['aps'] = array(
                                                    'alert' => $message,
                                                    'sound' => 'default'
                                            );
            $body['title']="Notification";
            $body['message']=$message;
            $body['postid']=$postid;
            $body['NotificationType']=$notificationtype;
            $body['time']=$time;
            // Encode the payload as JSON

            
            $payload = json_encode($body);
            
            
            

            // Build the binary notification
            $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

            // Send it to the server
            $result = fwrite($fp, $msg, strlen($msg));
			
			$this->writeLog("\n Result: sent ".$result." bytes \n");
			
            if (!$result) {
                    $this->writeLog("\n Message not delivered \n");
					$jsondata['message'] = 'Message not delivered';
                    $jsondata['status'] = 0;
            }
            else {
					$this->writeLog("\n Message successfully delivered \n");
                    $jsondata['message'] = 'Message successfully delivered';
                    $jsondata['status'] = 1;
            }
            // Close the connection to the server
            fclose($fp);
            //echo json_encode((object) $jsondata);
            return true;
        } catch (ErrorException $e) {
                $this->writeLog("\n Exception: Internal error \n".$e);
                return true;
        }    
    }
    
    function sendToBulkIphone($artistID,$type="artist")
    {
        try {
            // Put your device token here (without spaces):
            $deviceToken            =       $this->deviceToken;
            $message                =       $this->message;
            $postid                 =       $this->postid;
            $notificationtype       =       $this->NotificationType;
            $time                   =       $this->time;
            // Put your private key's passphrase here:
            //$passphrase = '1234';
            
            //echo $deviceToken; die;
            
            
            ////////////////////////////////////////////////////////////////////////////////

            $ctx = stream_context_create();
            stream_context_set_option($ctx, 'ssl', 'local_cert', '../../api/web/pushkey/artist_'.$artistID.'/ck_user_production.pem');
            //stream_context_set_option($ctx, 'ssl', 'local_cert', 'pushkey/artist_'.$artistID.'/ck_user_development.pem');
            

            // Open a connection to the APNS server 
            // (Live server link)

            //developement server link
            
            try {
                //$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
                $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
            } catch (ErrorException $e) {
                /************** Log Data ***********/
                $filename = '../web/log/PushLog_'.date('Ymd').'.txt';
                $log_current= "\n ************************";    
                $log_current.= "\n Message : Gateway not connected \n".$e;
                $log_current.= "\n DeviceType : Iphone";
                if(file_exists($filename)) {
                   $log_current.= file_get_contents($filename);
                }
                file_put_contents($filename, $log_current);
                /************************************/
                return true;
            }

            if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);

            //echo 'Connected to APNS' . PHP_EOL;

            // Create the payload body
            $body['aps'] = array(
                                                    'alert' => $message,
                                                    'sound' => 'default'
                                            );
            $body['title']="Notification";
            $body['message']=$message;
            $body['postid']=$postid;
            $body['NotificationType']=$notificationtype;
            $body['time']=$time;
            // Encode the payload as JSON

            
            $payload = json_encode($body);
            
            
            for($n=0;$n<count($deviceToken); $n++) {
                // Build the binary notification
                $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken[$n]) . pack('n', strlen($payload)) . $payload;

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
                
                //echo json_encode((object) $jsondata);
            }
            fclose($fp);
            return true;
        } catch (ErrorException $e) {
                /************** Log Data ***********/
                $filename = '../web/log/PushLog_'.date('Ymd').'.txt';
                $log_current= "\n ************************";    
                $log_current.= "\n Message : Internal error \n".$e;
                $log_current.= "\n DeviceType : Iphone";
                if(file_exists($filename)) {
                   $log_current.= file_get_contents($filename);
                }
                file_put_contents($filename, $log_current);
                /************************************/
                return true;
        }        
    }
	

    //Function for sending push message to client
    function sendToAndroid($artistID,$type="artist") 
    {
		$this->writeLog("\n\n ************************");    
        $this->writeLog("\n Start Send to Android push: \n".$this->time);
		
        $registrationIds = array($this->deviceToken);
        $msg = array (
                'message' => $this->message,
                'title' => 'Notification',
                'postid'=>$this->postid,
                'time'=>$this->time,
                'NotificationType'=>$this->NotificationType
        );
        $fields = array(
                'registration_ids' => $registrationIds,
                'data'	=> $msg
        );
        if($type == 'artist') {
            $filename   =   "../../api/web/pushkey/artistcommon/android_api_key_artist.txt";
        } else {
            $filename   =   "../../api/web/pushkey/artist_".$artistID."/android_api_key_user.txt";
        }   
		
		$this->writeLog("\n Device Token: ".$this->deviceToken." \n");
		$this->writeLog("\n ArtistID: ".$artistID." \n");
		$this->writeLog("\n Message: ".$this->message." \n");
		$this->writeLog("\n PostID: ".$this->postid." \n");
		$this->writeLog("\n Notification Type: ".$this->NotificationType." \n");
		
		
        if(file_exists($filename)) {
            try {
                $key    =   file_get_contents($filename);
                $headers = array(
                        $key,
                        'Content-Type: application/json'
                );
                $ch = curl_init();
                curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
                curl_setopt( $ch,CURLOPT_POST, true );
                curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
                curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
                curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                $result = curl_exec($ch );
                //fwrite($fp, "Error : " .  curl_error($ch) . "\r\n");
                //fwrite($fp, "Result : " .  json_encode($result) . "\r\n");
                curl_close($ch);
				
				$this->writeLog("\n curl response ".$result." \n");
				
                return true;
            } catch (ErrorException $e) {
                $this->writeLog("\n Exception: ".$e." \n");
                return true;
            }
        } else {
			$this->writeLog("\n File not exists: ".$filename." \n");
            return true;
        }
    }
    
    
    function sendToBulkAndroid($artistID,$type="artist") 
    {
        //$fp = fopen('androidpush' . date('dmy') . ".txt", 'a+');
        //fwrite($fp, '------------------------ new record --------------------------------' . "\r\n");

        $registrationIds = $this->deviceToken;
        // prep the bundle
        $msg = array (
                'message' => $this->message,
                'title' => 'Notification',
                'postid'=>$this->postid,
                'time'=>$this->time,
                'NotificationType'=>$this->NotificationType
        );
        $fields = array(
                'registration_ids' => $registrationIds,
                'data'	=> $msg
        );
        /*$headers = array
        (
                'Authorization: key=AIzaSyDpgevlwJnbcYpiwKNOpwWD-adAs4V6CI8',
                'Content-Type: application/json'
        );*/
        if($type == 'artist') {
            $filename   =   "../../api/web/pushkey/artistcommon/android_api_key_artist.txt";
        } else {
            $filename   =   "../../api/web/pushkey/artist_".$artistID."/android_api_key_user.txt";
        }   
        if(file_exists($filename)) {
            try {
                $key    =   file_get_contents($filename);
                $headers = array(
                        $key,
                        'Content-Type: application/json'
                );
                $ch = curl_init();
                curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
                curl_setopt( $ch,CURLOPT_POST, true );
                curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
                curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
                curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                $result = curl_exec($ch );
                //fwrite($fp, "Error : " .  curl_error($ch) . "\r\n");
                //fwrite($fp, "Result : " .  json_encode($result) . "\r\n");
                curl_close($ch);
                return true;
            } catch (ErrorException $e) {
                /************** Log Data ***********/
                $filename = '../web/log/PushLog_'.date('Ymd').'.txt';
                $log_current= "\n ************************";    
                $log_current.= "\n Message : Token Error \n".$e;
                $log_current.= "\n DeviceType : Android";
                if(file_exists($filename)) {
                   $log_current.= file_get_contents($filename);
                }
                file_put_contents($filename, $log_current);
                /************************************/	
                return true;
            }    
        } else {
            return true;
        }
        // Close the connection to the server
        //fclose($fp);
    }
	
	
	//function for write on the log file
	function writeLog($text){
		//$logfile = '../web/log/PushLogDan_'.date('Ymd').'.txt';
		$logfile = '../web/log/CommentnotforartistLog_'.date('Ymd').'.txt';
		$log_current = $text;
		if(file_exists($logfile)) {
			$log_current= file_get_contents($logfile).$log_current;
        }
        file_put_contents($logfile, $log_current);
	}
	
}
?>