<?php
            $registrationIds = array("dXgdcpzBxWA:APA91bGSyqJpaRUFvtf9KIKnWDJGdXnhu12e65F-2FUC9YTfgrHQYm1Ra8LN5sw6aFoEYpv1Ei7jMdPYWFIpR_45I-r3yyZLGWcJnM01eAzRb3qCAbKUD_O9V94mRmsNAIWK-cw80Am2","dXgdcpzBxWA:APA91bGSyqJpaRUFvtf9KIKnWDJGdXnhu12e65F-2FUC9YTfgrHQYm1Ra8LN5sw6aFoEYpv1Ei7jMdPYWFIpR_45I-r3yyZLGWcJnM01eAzRb3qCAbKUD_O9V94mRmsNAIWK-cw80Am2");
            $msg = array(
                    'message' => "High Five, bro \u270B",
                    'title' => 'Notification',
                    'time'=>date("Y-m-d H:i:s"),
                    'NotificationType'=>'comment'
            );
            $fields = array(
                    'registration_ids' => $registrationIds,
                    'data'	=> $msg
            );
            $artistID = "10";
            $androidID = json_encode($registrationIds);
            
            $fields = '{"registration_ids":'.$androidID.',"data":{"message":"High Five, bro \ue012","title":"Notification","time":"'.$time.'","NotificationType":"comment"}}';
            //$fields = json_encode($fields);
            //echo $fields; die;
            
            $filename   =   "boom_staging/api/web/pushkey/artist_".$artistID."/android_api_key_user.txt";
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
            curl_setopt( $ch,CURLOPT_POSTFIELDS,$fields);
            $result = curl_exec($ch );
            echo '<pre>';
            print_r($result);
            die;
            //fwrite($fp, "Error : " .  curl_error($ch) . "\r\n");
            //fwrite($fp, "Result : " .  json_encode($result) . "\r\n");
            curl_close($ch);
?>			

