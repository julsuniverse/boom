<?php
namespace api\models;

use Yii;

class Commentnotification
{
    public $deviceToken;
    public $message;
    public $title;

    function __construct($deviceToken = "") 
    {
        $this->deviceToken 	= 	$deviceToken;
    }

    //Function for sending push notification to iphone
    function sendToIphone()
    {
            // Put your device token here (without spaces):
            $deviceToken = $this->deviceToken;
            $message	=	$this->message;
            // Put your private key's passphrase here:
            //$passphrase = '1234';

            ////////////////////////////////////////////////////////////////////////////////

            $ctx = stream_context_create();
            stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck_Artist.pem');
            //stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

            // Open a connection to the APNS server
            //$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

            //developement server link
            $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

            if (!$fp)
            exit("Failed to connect: $err $errstr" . PHP_EOL);

            echo 'Connected to APNS' . PHP_EOL;

            // Create the payload body
            $body['aps'] = array(
                                                    'alert' => $message,
                                                    'sound' => 'default'
                                            );
            $body['title']="Test Boom";
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
    function sendToAndroid() 
    {
        //$fp = fopen('androidpush' . date('dmy') . ".txt", 'a+');
        //fwrite($fp, '------------------------ new record --------------------------------' . "\r\n");

        $registrationIds = array($this->deviceToken);

        // prep the bundle
        $msg = array
        (
                'message' => $this->message,
                'title' => $this->title
        );

        $fields = array
        (
                'registration_ids' => $registrationIds,
                'data'	=> $msg
        );
        $headers = array
        (
                'Authorization: key=' . AIzaSyDpgevlwJnbcYpiwKNOpwWD-adAs4V6CI8,
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
}
?>