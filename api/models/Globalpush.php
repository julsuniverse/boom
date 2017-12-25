<?php
namespace api\models;

use Yii;

class Globalpush
{

    //Function for sending push notification to iphone
    function sendToIphone($deviceTokens,$message,$time,$artistID)
    {
        try {
            // Put your private key's passphrase here:
            //$passphrase = '1234';

            ////////////////////////////////////////////////////////////////////////////////
            $res = "";
            $resDelivered = "";
            $resNotDelivered = "";
            foreach ($deviceTokens as $deviceToken) 
            {
                    $ctx = stream_context_create();
                    if(!file_exists('../../api/web/pushkey/artist_'.$artistID.'/ck_user_production.pem')) {
                        /************** Log Data ***********/
                        $filename = '../web/log/PushLog_'.date('Ymd').'.txt';
                        $log_current= "\n ************************";    
                        $log_current.= "\n Time : ".date("Y-m-d H:i:s")." \n";
                        $log_current.= "\n Message : Push File not found \n";
                        file_put_contents($filename, $log_current,FILE_APPEND);
                        /************************************/
                    }
                    stream_context_set_option($ctx, 'ssl', 'local_cert', '../../api/web/pushkey/artist_'.$artistID.'/ck_user_production.pem');
                    //stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);


                    try {
                        $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
                    } catch (ErrorException $e) {
                        /************** Log Data ***********/
                        $filename = '../web/log/PushLog_'.date('Ymd').'.txt';
                        $log_current= "\n ************************";    
                        $log_current.= "\n Time : ".date("Y-m-d H:i:s")." \n";
                        $log_current.= "\n Message : Socket Doesn't connecred \n".$e;
                        file_put_contents($filename, $log_current,FILE_APPEND);
                        /************************************/
                    }    

                    //developement server link
                    //$fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

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
                    if(ctype_xdigit($deviceToken)) {
                        $payload = '{"aps":{"alert":"'.$message.'","sound":"default"},"title":"Notification","time":"'.$time.'","message":"'.$message.'","NotificationType":"global"}';
                        //$payload = json_encode($body);
                        //echo $payload; die;
                        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
                        try { 
                            $result = fwrite($fp, $msg, strlen($msg));
                            if(!$result){				
                                $resNotDelivered.=$deviceToken."\n";
                            } else {
                                $resDelivered.= $deviceToken."\n";
                            }
                        } catch (Exception $e) {
                                sleep(1);
                                $result = fwrite($fp, $msg, strlen($msg));
                                /************** Log Data ***********/
                                $filename = '../web/log/PushLog_'.date('Ymd').'.txt';
                                $log_current= "\n ************************";    
                                $log_current.= "\n Time : ".date("Y-m-d H:i:s")." \n";
                                $log_current.= "\n Message : Exception for multiple push \n".$e;
                                $log_current.= "\n Push Message : ".$message." \n";
                                $log_current.= "\n DeviceType : Iphone";
                                file_put_contents($filename, $log_current,FILE_APPEND);
                                /************************************/
                        }
                    }  
                    fclose($fp);
            }
            /************** Log Data ***********/
            $filename = '../web/log/PushLog_'.date('Ymd').'.txt';
            $log_current= "\n ************************";    
            $log_current.= "\n Time : ".date("Y-m-d H:i:s")." \n";
            $log_current.= "\n Message : Push Is Not Delivered \n";
            $log_current.= "\n Push Message : ".$message." \n";
            $log_current.= "\n DeviceType : Iphone";
            $log_current.= "\n DeviceTOken : ".$resNotDelivered;
            $log_current.= "\n Message : Push Is Delivered \n";
            $log_current.= "\n DeviceTOken : ".$resDelivered;
            file_put_contents($filename, $log_current,FILE_APPEND);
            /************************************/
        } catch (ErrorException $e) {
                /************** Log Data ***********/
                $filename = '../web/log/PushLog_'.date('Ymd').'.txt';
                $log_current= "\n ************************";    
                $log_current.= "\n Time : ".date("Y-m-d H:i:s")." \n";
                $log_current.= "\n Message : Internal error \n".$e;
                $log_current.= "\n Push Message : ".$message." \n";
                $log_current.= "\n DeviceType : Iphone";
                file_put_contents($filename, $log_current,FILE_APPEND);
                /************************************/
                return true;
        }   

            //echo json_encode((object) $jsondata);
    }

    //Function for sending push message to client
    function sendToAndroid($androidtokens,$message,$time,$artistID) 
    {
        try {
        //$fp = fopen('androidpush' . date('dmy') . ".txt", 'a+');
        //fwrite($fp, '------------------------ new record --------------------------------' . "\r\n");

        $registrationIds = $androidtokens;

        // prep the bundle
        $msg = array
        (
                'message' => $message,
                'title' => 'Notification',
                'time'=>$time,
                'NotificationType'=>'comment'
        );

        $fields = array
        (
                'registration_ids' => $registrationIds,
                'data'	=> $msg
        );
        $filename   =   "../../api/web/pushkey/artist_".$artistID."/android_api_key_user.txt";
        $key    =   str_replace("'","",file_get_contents($filename));

        $headers = array
        (
                //'Authorization: key=AIzaSyDpgevlwJnbcYpiwKNOpwWD-adAs4V6CI8',
                //'Authorization: key=AIzaSyBIlDarMom9SRI8F0dmiQmJSwxp-PuXMNI',
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

        if($result)
        {
            /************** Log Data ***********/
            $filename = '../web/log/PushLog_'.date('Ymd').'.txt';
            $log_current= "\n ************************";    
            $log_current.= "\n Time : ".date("Y-m-d H:i:s")." \n";
            $log_current.= "\n Type : Android \n";
            $log_current.= "\n Message : Push Is Delivered \n";
            $log_current.= "\n DeviceTOken : ".print_r($registrationIds);
            file_put_contents($filename, $log_current,FILE_APPEND);
            /************************************/
            return true;
        }
        else
        {
            return false;
        }
        } catch (ErrorException $e) {
                /************** Log Data ***********/
                $filename = '../web/log/PushLog_'.date('Ymd').'.txt';
                $log_current= "\n ************************";    
                $log_current.= "\n Message : Internal error \n".$e;
                $log_current.= "\n DeviceType : Android";
                if(file_exists($filename)) {
                   $log_current.= file_get_contents($filename);
                }
                file_put_contents($filename, $log_current);
                /************************************/
                return true;
        }
        // Close the connection to the server
        //fclose($fp);
    }
}
?>