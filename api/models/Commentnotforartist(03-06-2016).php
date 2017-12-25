<?php
namespace api\models;

use Yii;

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
    function sendToIphone($artistID)
    {
            // Put your device token here (without spaces):
            $deviceToken            =       $this->deviceToken;
            $message                =       $this->message;
            $postid                 =       $this->postid;
            $notificationtype       =       $this->NotificationType;
            $time                   =       $this->time;
            // Put your private key's passphrase here:
            //$passphrase = '1234';

            ////////////////////////////////////////////////////////////////////////////////

            $ctx = stream_context_create();
            stream_context_set_option($ctx, 'ssl', 'local_cert', 'pushkey/artistcommon/ck_artist_production.pem');
            //stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

            // Open a connection to the APNS server 
            // (Live server link)
            $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

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
            //echo json_encode((object) $jsondata);
    }

    //Function for sending push message to client
    function sendToAndroid($artistID) 
    {
        //$fp = fopen('androidpush' . date('dmy') . ".txt", 'a+');
        //fwrite($fp, '------------------------ new record --------------------------------' . "\r\n");

        $registrationIds = array($this->deviceToken);

        // prep the bundle
        $msg = array
        (
                'message' => $this->message,
                'title' => 'Notification',
                'postid'=>$this->postid,
                'time'=>$this->time,
                'NotificationType'=>$this->NotificationType
        );

        $fields = array
        (
                'registration_ids' => $registrationIds,
                'data'	=> $msg
        );
        /*$headers = array
        (
                'Authorization: key=AIzaSyDpgevlwJnbcYpiwKNOpwWD-adAs4V6CI8',
                'Content-Type: application/json'
        );*/
        $filename   =   "pushkey/artistcommon/android_api_key_artist.txt";
        $key    =   file_get_contents($filename);

        $headers = array
        (
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
            return true;
        }
        else
        {
            return false;
        }
        // Close the connection to the server
        //fclose($fp);
    }
    
    function sendToBulkIphone($artistID,$type="artist")
    {
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
            //stream_context_set_option($ctx, 'ssl', 'local_cert', 'pushkey/artistcommon/ck_artist_production.pem');
            stream_context_set_option($ctx, 'ssl', 'local_cert', 'pushkey/artist_'.$artistID.'/ck_user_production.pem');
            stream_context_set_option($ctx, 'ssl', 'passphrase', '1234');
            //stream_context_set_option($ctx, 'ssl', 'local_cert', 'pushkey/artistcommon/ck_artist_production.pem');
            
            // Open a connection to the APNS server 
            // (Live server link)
            //$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

            //developement server link
            //$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
            $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

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

            for($n=0;$n<count($deviceToken); $n++) {
                // Build the binary notification
                $payload = json_encode($body);
                $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken[$n]). pack('n', strlen($payload)) . $payload;
                

                // Send it to the server
                $result = fwrite($fp, $msg, strlen($msg));//, strlen($msg)
                
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
            $filename   =   "pushkey/artistcommon/android_api_key_artist.txt";
        } else {
            $filename   =   "pushkey/artist_".$artistID."/android_api_key_user.txt";
        }   
        if(file_exists($filename)) {
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
        } else {
            return false;
        }
        // Close the connection to the server
        //fclose($fp);
    }
}
?>