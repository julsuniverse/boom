<?php
        $registrationIds = array('djrB0dtj4Pg:APA91bG1O-_HC6CxhBnjJ9kvlvBun1pW-zqfnURDYaNfTArW4leX2wYVii4vDaiJ_-lvz0OyGA2KFgHvlVfyBqjf4SVTfXVw6dJfk3QtuLt5ZOqZSw90zMKbxWZUud7mktQ8Hgk_wRk7');

        // prep the bundle
        $msg = array
        (
                'message' => 'test message',
                'title' => 'Notification',
                'time'=> date("Y-m-d H:i:s"),
                'NotificationType'=>'comment'
        );

        $fields = array
        (
                'registration_ids' => $registrationIds,
                'data'	=> $msg
        );
        $filename   =   $_SERVER['DOCUMENT_ROOT']."/api/web/pushkey/artist_92/android_api_key_user.txt";
        
        if(!file_exists($filename)) {
            echo  "file not exists"; 
            die;
        }
        $key    =  str_replace("'","",file_get_contents($filename));

        $headers = array
        (
                //$key,
                'Authorization: key = AIzaSyCYbQYLh9e6iFTKI45_UWluVDCVxWNO9YA',
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
        curl_close($ch);
        echo '<pre>';
        print_r($result);
        die;

        
?>