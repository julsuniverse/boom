<?php
$servername = "boomlive.cqzxkjgfttvc.ap-southeast-2.rds.amazonaws.com";
$username = "boomvideo";
$password = "H93BMDJrCWYRYE8J";
$dbname = "boomvideo";


$deviceToken            =       '63d00ac1317323ca103d967a59a536dd1e083fcf3b7889923809aae71c7b4dbc';
$message                =       "Your video was uploaded successfully";
$postid                 =       '1';
$notificationtype       =       "videopost";
$time                   =       date("d M 'y H:i A");

$conn = new mysqli($servername, $username, $password,$dbname);
////////////////////////////////////////////////////////////////////////////////


        $getartistqry   =   "SELECT p.ArtistID,a.DeviceToken,a.DeviceType,p.PostID,p.MobileStreamUrl 
                                                FROM post p 
                                                JOIN artist a 
                                                ON (a.ArtistID=p.ArtistID AND a.DeviceToken != '')
                                                 WHERE p.URL='boom-1/postvideos/post_2_20161404172517.mp4' ";
            
            $res = $conn->query($getartistqry);
            if($res->num_rows > 0)
            {
                while($obj = $res->fetch_object())
                { 
                    $devicetype  =   $obj->DeviceType;
                    $token       =   $obj->DeviceToken;
                    $artistID    =   $obj->ArtistID;
                    $postID      =   $obj->PostID;
                    $streamingurl=   $obj->MobileStreamUrl;
                } 
            }
            if($token != "" && $artistID != "")
            {
                if($devicetype == 1)
                {
                    $deviceToken            =       $token;
                    $message                =       "Your video was uploaded successfully";
                    $postid                 =       $postID;
                    $notificationtype       =       "videopost";
                    $time                   =       date("d M 'y H:i A");
                    // Put your private key's passphrase here:
                    //$passphrase = '1234';

                    ////////////////////////////////////////////////////////////////////////////////

                    $ctx = stream_context_create();
                    stream_context_set_option($ctx, 'ssl', 'local_cert', '../api_ver1/web/pushkey/artistcommon/ck_artist_production.pem');
                    //stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

                    // Open a connection to the APNS server
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
                    $body['MobileStreamUrl']=$streamingurl;
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
                else if($devicetype == 2)
                {
                    $registrationIds = array($token);

                    // prep the bundle
                    $msg = array
                    (
                            'message' => 'Your video was uploaded successfully',
                            'title' => 'Notification',
                            'postid'=>$postID,
                            'time'=>date("d M 'y H:i A"),
                            'NotificationType'=>'videopost',
                            'MobileStreamUrl'=>$streamingurl
                    );

                    $fields = array
                    (
                            'registration_ids' => $registrationIds,
                            'data'	=> $msg
                    );
                    $filename   =   "artist/android_api_key_artist.txt";
                    $key = file_get_contents($filename);

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
                }
            }
        $conn->close();