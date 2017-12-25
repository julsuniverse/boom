<?php
            $deviceToken            =       "E83BE0209FA1C72A6D295FC4286A4A2BD7A4D618889EEAFE1CDC76AD905687B7";
            $message                =       "test message using p12";
            $notificationtype       =       "test";
            $time                   =       date("Y-m-d H:i:s");
            // Put your private key's passphrase here:
            //$passphrase = '1234';
            $artistID = "1";
            
            ////////////////////////////////////////////////////////////////////////////////

            $ctx = stream_context_create();
			stream_context_set_option($ctx, 'ssl', 'local_cert', '../api/web/pushkey/test.p12');
            //stream_context_set_option($ctx, 'ssl', 'local_cert', '../api/web/pushkey/artist_'.$artistID.'/ck_user_production.pem');
            //stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
             
            
            /*if(file_exists('../api/web/pushkey/test.pem')) {
                echo "yes";
            } else {
                echo "no";
            }
            die;*/
            try {
                $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
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

            echo 'Connected to APNS' . PHP_EOL;

            // Create the payload body
            $body['aps'] = array(
                                                    'alert' => $message,
                                                    'sound' => 'default'
                                            );
            $body['title']="Notification";
            $body['message']=$message;
            $body['NotificationType']=$notificationtype;
            $body['time']=$time;
            $payload = json_encode($body);
            $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
            $result = fwrite($fp, $msg, strlen($msg));
            if (!$result) {
                    $jsondata['message'] = 'Message not delivered';
                    $jsondata['status'] = 0;
            }
            else {
                    $jsondata['message'] = 'Message successfully delivered';
                    $jsondata['status'] = 1;
            }
            fclose($fp);
			
			print_r($jsondata);

?>