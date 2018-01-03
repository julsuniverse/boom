<?php
namespace api\models;

use Yii;
use yii\base\ErrorException;
use yii\web\NotFoundHttpException;

class Globalpush
{

    //Function for sending push notification to iphone
    function sendToIphone($deviceTokens,$message,$time,$artistID)
    {
            // Put your private key's passphrase here:
            //$passphrase = '1234';

            ////////////////////////////////////////////////////////////////////////////////
            try {
                $ctx = stream_context_create();
                stream_context_set_option($ctx, 'ssl', 'local_cert', 'pushkey/artist_'.$artistID.'/ck_user_development.pem');
                //stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

                // Open a connection to the APNS server
                //(Live server link)
                //$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

                //developement server link
                try {
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
                $body['time']=$time;
                $body['message']=$message;
                $body['NotificationType']="global";
                // Encode the payload as JSON
                 $payload = json_encode($body);

                $res = "";
                // Send it to the server
                foreach ($deviceTokens as $deviceToken) 
                {
                    try {
                        // Build the binary notification
                        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

                        // Send it to the server
                        $result = fwrite($fp, $msg, strlen($msg));

                        //echo '-----> Result = ' . $result;

                        if(!$result){					
                                $res = $res . "\n" . $deviceToken;
                        }
                    } catch (ErrorException $e) {
                        /************** Log Data ***********/
                        $filename = '../web/log/PushLog_'.date('Ymd').'.txt';
                        $log_current= "\n ************************";    
                        $log_current.= "\n Message : Token Error \n".$e;
                        $log_current.= "\n DeviceToken : ".$deviceToken;
                        $log_current.= "\n DeviceType : Iphone";
                        if(file_exists($filename)) {
                           $log_current.= file_get_contents($filename);
                        }
                        file_put_contents($filename, $log_current);
                        /************************************/
                        return true;
                    }               
                }
                if ($res != "") {
                    $res = "\n\n-------------Push not delivered on below tokens--------------" . $res;
                    $jsondata['message'] = "Message not delivered on some devices";
                    $jsondata['status'] = 0;
                    $jsondata['error'] = $res;							
                } else {
                    $jsondata['message'] = "Message successfully delivered to all devices";
                    $jsondata['status'] = 1;
                }
                fclose($fp);
            } catch (ErrorException $e) {
                if(isset($fp)) {
                    fclose($fp);
                }    
                return true;
            }   
            //echo json_encode((object) $jsondata);
    }

    //Function for sending push message to client
    function sendToAndroid($androidtokens,$message,$time,$artistID) 
    {
        try {
            $registrationIds = $androidtokens;
            $msg = array(
                    'message' => $message,
                    'title' => 'Notification',
                    'time'=>$time,
                    'NotificationType'=>'comment'
            );
            $fields = array(
                    'registration_ids' => $registrationIds,
                    'data'	=> $msg
            );
            $filename   =   "pushkey/artist_".$artistID."/android_api_key_user.txt";
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
            $result = curl_exec($ch );
            //fwrite($fp, "Error : " .  curl_error($ch) . "\r\n");
            //fwrite($fp, "Result : " .  json_encode($result) . "\r\n");
            curl_close($ch);

            if($result) {
                return true;
            } else {
                return true;
            }
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
    }
}
?>