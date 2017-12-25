<?php
require_once '../common/config/common.php';
//////
//// CONFIGURATION
//////
//For Debugging.
$logToFile = true;
//define("AMAZON_S3_URL","https://boomvideoqa-out.s3.amazonaws.com/");
define("AMAZON_S3_URL","http://d1votgrx00p4vc.cloudfront.net/");

//Should you need to check that your messages are coming from the correct topicArn
$restrictByTopic = true;
//$allowedTopic = "arn:aws:sns:ap-northeast-1:739390266517:Boomforqa";
//$allowedTopic = "arn:aws:sns:ap-northeast-1:739390266517:BoomTranscodingNotification";
$allowedTopic = "arn:aws:sns:us-west-2:630949593304:AppbeatVIdeoComplete";
//For security you can (should) validate the certificate, this does add an additional time demand on the system.
//NOTE: This also checks the origin of the certificate to ensure messages are signed by the AWS SNS SERVICE.
//Since the allowed topicArn is part of the validation data, this ensures that your request originated from
//the service, not somewhere else, and is from the topic you think it is, not something spoofed.
$verifyCertificate = true;
$sourceDomain = "sns.us-west-2.amazonaws.com";
//////
//// OPERATION
//////
$signatureValid = false;
$safeToProcess = true; //Are Security Criteria Set Above Met? Changed programmatically to false on any security failure.

if($logToFile){
	////LOG TO FILE:
	$dateString = date("Ymdhis");
	$dateString = $dateString."_r.txt";
	$myFile = $dateString;
	$fh = fopen($myFile, 'w') or die("Log File Cannot Be Opened.");
}

//Get the raw post data from the request. This is the best-practice method as it does not rely on special php.ini directives
//like $HTTP_RAW_POST_DATA. Amazon SNS sends a JSON object as part of the raw post body.
$json = json_decode(file_get_contents("php://input"));

	if($logToFile && count($json) > 0){
		$validationString = "";
		$validationString .= "Message\n";
		$validationString .= $json->Message . "\n";
		$validationString .= "MessageId\n";
		$validationString .= $json->MessageId . "\n";
		$validationString .= "SubscribeURL\n";
                if(isset($json->SubscribeURL) && $json->SubscribeURL!='') {
                    $validationString .= $json->SubscribeURL . "\n";
                }    
		$validationString .= "TimeStamp\n";
		$validationString .= $json->Timestamp . "\n";
		$validationString .= "Token\n";
		$validationString .= $json->Token . "\n";
		$validationString .= "TopicArn\n";
		$validationString .= $json->TopicArn . "\n";
		$validationString .= "Type\n";
		$validationString .= $json->Type . "\n";			
		fwrite($fh, "\nNotification Message:\n\n".$validationString);				
	}

//Check for Restrict By Topic
if($restrictByTopic){
	if($allowedTopic != $json->TopicArn){
		$safeToProcess = false;
		if($logToFile){
			fwrite($fh, "ERROR: Allowed Topic ARN: ".$allowedTopic." DOES NOT MATCH Calling Topic ARN: ". $json->TopicArn . "\n");
		}else{
			logError("ERROR: Allowed Topic ARN: ".$allowedTopic." DOES NOT MATCH Calling Topic ARN: ". $json->TopicArn . "\n");
		}
	}
}
//Check for Verify Certificate
if($verifyCertificate){
	
	//Check For Certificate Source
	$domain = getDomainFromUrl($json->SigningCertURL);
	if($domain != $sourceDomain){
		$safeToProcess = false;
		if($logToFile){
			fwrite($fh, "Key domain: " . $domain . " is not equal to allowed source domain:" .$sourceDomain. "\n");
		}else{
			logError("Key domain: " . $domain . " is not equal to allowed source domain:" .$sourceDomain. "\n");
		}
	}
}

if($safeToProcess){
	
	//Handle A Subscription Request Programmatically
	if($json->Type == "SubscriptionConfirmation"){
		
		//RESPOND TO SUBSCRIPTION NOTIFICATION BY CALLING THE URL
		
		if($logToFile){
			fwrite($fh, $json->SubscribeURL);
		}
		
		$curl_handle=curl_init();
		curl_setopt($curl_handle,CURLOPT_URL,$json->SubscribeURL);
		curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
		curl_exec($curl_handle);
		curl_close($curl_handle);	
		
	} else if($json->Type == "Notification"){
		
		// PROCESS NOTIFICATION MESSAGE
		
		$jsonMessage = json_decode($json->Message);
			
		if($jsonMessage->state == "COMPLETED") {
						
			$servername = "boomcollective.csjdzy41kbcn.us-west-2.rds.amazonaws.com";
			$username = "boomvideo";
			$password = "G9J4g00xiMSFakXQ";
			$dbname = "boomvideo"; //boomvideo
			
			// Create connection
			$conn = new mysqli($servername, $username, $password,$dbname);
	
			// Check connection
			if ($conn->connect_error) {
				if($logToFile){
					fwrite($fh, "\n DB Connection Error \n". $conn->connect_error);
				}else{
					logError("\n DB Connection Error \n". $conn->connect_error);
				}
				die("Connection failed: " . $conn->connect_error);
			} 
			
			$fileName = $jsonMessage->input->key;
			$outpustStatusForMobile = $jsonMessage->outputs[0]->status;
			$queryString = "";
			$isPage = false;
                        
			if($outpustStatusForMobile == 'Complete') {
				//check if it's for post or page
				$key = $jsonMessage->input->key;
				$keysplit = explode('/', $key);
				if($keysplit[1] == "postvideos"){
					//save urls for post
					$mobileStreamUrl = $jsonMessage->outputs[0]->key;
					$mobileThumbUrl = $jsonMessage->outputs[0]->thumbnailPattern;
					$replacedThumbUrl = str_replace('{count}','00001.png',$mobileThumbUrl);
									if (strpos($fileName, 'reply_') !== false) {
										$queryString.=",ReplyStreamURL = '".AMAZON_S3_URL.$mobileStreamUrl."', ReplyImage = '".AMAZON_S3_URL.$replacedThumbUrl."' ";	
									} else {
										$width = $jsonMessage->outputs[0]->width;
										$height = $jsonMessage->outputs[0]->height;
										$queryString.=",MobileStreamUrl = '".AMAZON_S3_URL.$mobileStreamUrl."', ThumbImage = '".AMAZON_S3_URL.$replacedThumbUrl."', VideoThumbImage = '".AMAZON_S3_URL.$replacedThumbUrl."', Width = ".$width.", Height = ".$height." ";	
									}  
				}else{
					//save urls for pages
					$isPage = true;
					$pageStreamUrl = $jsonMessage->outputs[0]->key;
					$pageThumbUrl = $jsonMessage->outputs[0]->thumbnailPattern;
					$replacedPageThumbUrl = str_replace('{count}','00001.png',$pageThumbUrl);
					$width = $jsonMessage->outputs[0]->width;
					$height = $jsonMessage->outputs[0]->height;
					$queryString.=",VideoUrl = '".AMAZON_S3_URL.$pageStreamUrl."'";
					$queryString.=",VideoThumbnailImage = '".AMAZON_S3_URL.$replacedPageThumbUrl."'";
					$queryString.=",VideoThumbnailImageWidth = '".$width."'";
					$queryString.=",VideoThumbnailImageHeight = '".$height."'";
				}
			} else {
				if($logToFile){
					fwrite($fh, "Error in transcoding for mobile\n".$jsonMessage->outputs[0]."\n");
				}else{
					logError("Error in transcoding for mobile\n".$jsonMessage->outputs[0]."\n");
				}
			}
			
			
			$queryString = substr($queryString,1);
			if(!$isPage){
				if (strpos($fileName, 'reply_') !== false) {
					$sql = "UPDATE post SET ".$queryString." WHERE Reply = '".$fileName."' ";
                } else {
					$sql = "UPDATE post SET ".$queryString." WHERE URL= '".$fileName."' ";
                } 
			}else{
				$sql = "UPDATE postpages SET ".$queryString." WHERE VideoUrl = '".S3_BUCKETABSOLUTE_PATH."/".$fileName."' ";
			}
			
			fwrite($fh, "Query: ".$sql."\n"); 
			$token="";
            $artistID="";
            $postID= "";
			$streamingurl="";
                        
			//fwrite($fh, "\n Query: \n".$sql);

			if ($conn->query($sql) === false) {
				if($logToFile){
					fwrite($fh, "Error in update\n".mysqli_error($conn)."\n");
				}else{
					logError("Error in update\n".mysqli_error($conn)."\n");
				}
			}
                        else
                        {
                            fwrite($fh, "file name \n".$fileName);
                            if (strpos($fileName, 'reply_') !== false) {
                                $postData   =   "SELECT a.ArtistName,p.ArtistID,a.DeviceToken,a.DeviceType,p.PostID,p.MobileStreamUrl,p.MemberID,p.PostType 
                                                 FROM post p 
                                                 JOIN artist a 
                                                 ON (a.ArtistID=p.ArtistID)
                                                 WHERE p.Reply='".$fileName."'";
                                                 //AND a.DeviceToken != ''  (Removed it due to stream URL was not update when token is blank)   
                            } else {
                                $postData   =   "SELECT a.ArtistName,p.ArtistID,a.DeviceToken,a.DeviceType,p.PostID,p.MobileStreamUrl,p.MemberID,p.PostType 
                                                 FROM post p 
                                                 JOIN artist a 
                                                 ON (a.ArtistID=p.ArtistID)
                                                 WHERE p.URL='".$fileName."'";
                                                //AND a.DeviceToken != ''  (Removed it due to stream URL was not update when token is blank)    
                            }    
                            fwrite($fh, "\n Query: \n".$postData);
                            $res = $conn->query($postData);
                            fwrite($fh, "Number of records \n\n".$res->num_rows.'\n');
                            if($res->num_rows > 0)
                            {
                                while($obj = $res->fetch_object())
                                { 
                                    $artistDevicetype  =   $obj->DeviceType;
                                    $artistToken       =   $obj->DeviceToken;
                                    $artistID    =   $obj->ArtistID;
                                    $MemberID    =   $obj->MemberID;
                                    $artistName  =  $obj->ArtistName;
                                    $postID      =   $obj->PostID;
                                    $postType    =   $obj->PostType;
                                    $postTtitle    =   $obj->PostTitle;
                                    $postDescription    =   $obj->Description;
                                    $streamingurl=   $obj->MobileStreamUrl;
                                }
                                //fwrite($fh,json_encode($obj)."\n");
                                fwrite($fh,"ArtistID=".$artistID."\n");
                            }
                            $memberData   =   "SELECT * FROM member WHERE MemberID = ".$MemberID;
                            $res = $conn->query($memberData);
                            if($res->num_rows > 0)
                            {
                                while($obj = $res->fetch_object())
                                { 
                                    $memberDevicetype  =   $obj->DeviceType;
                                    $memberToken       =   $obj->DeviceToken;
                                    $MemberID    =   $obj->MemberID;
                                    $MemberName    =   $obj->MemberName;
                                    $postid      =  "";
                                    $streamingurl= "";
                                }
                                $postid = $postID;
                            }
                            
                            //fwrite($fh, "postID \n");    
                            if (strpos($fileName, 'reply_') !== false) {
                                if($memberDevicetype == 1) {
                                    fwrite($fh, "artist reply \n");
                                    fwrite($fh,$memberToken."\n");
                                    sendToIphone($memberToken,$postid,$fileName,$artistID,'member','answer',$artistName,$myFile,$fh,$postType);
                                } else {
                                    fwrite($fh, "artist reply \n");
                                    fwrite($fh,$memberToken."\n");
                                    sendToAndroid($memberToken,$postid,$fileName,$artistID,'member','answer',$artistName,$myFile,$fh,$postType);
                                }
                                /*if($memberDevicetype == 1) {
                                    fwrite($fh, "member reply \n");
                                    sendToIphone($memberToken,$postid,$fileName,$artistID,'member','answer',$artistName,$myFile,$fh,$postType);
                                } else {
                                    sendToAndroid($memberToken,$postid,$fileName,$artistID,'member','answer',$artistName,$myFile,$fh,$postType);
                                }*/
                            } 
                            
                            fwrite($fh," Video Member \n");
                            if($postType != "4") {
                                $res = $conn->query("SELECT * FROM member WHERE ArtistID = '".$artistID."' AND IsPushEnabled = '1' AND DeviceToken IS NOT NULL ");
                                fwrite($fh,"Total Rows=".mysqli_num_rows($res)."\n");
                                $message = getNotificationMessageForPost($MemberID,$artistID,$postid,$artistName,$postTtitle,$postDescription);
                                $conn->query("INSERT INTO pushhistorystats (ArtistID,Message,TotalDevices) VALUES (".$artistID.",'".$message."',".mysqli_num_rows($res).") ");
                                $lastInsertID = $conn->insert_id;
                                while($obj = $res->fetch_object()) {
                                    $memberDevicetype  =   $obj->DeviceType;
                                    $memberToken       =   $obj->DeviceToken;
                                    $MemberID    =   $obj->MemberID;
                                    $conn->query("INSERT INTO pushqueue (BatchID,MemberID,DeviceToken,DeviceType,Status,Message) VALUES (".$lastInsertID.",".$MemberID.",".$memberToken.",".$memberDevicetype.",'1','".$message."')");
                                }
                            }
                        }
			$conn->close();
		} else {
			if($logToFile){
				fwrite($fh, "Error in transcoding\n".$json."\n");
			}else{
				logError("Error in transcoding\n".$json."\n");
			}
		}
	}
}
function getNotificationMessageForPost($MemberID,$artistID,$postid,$artistName,$postTtitle,$postDescription) {
    if($postTtitle == "" && $postDescription=="") {
        $message = trim($artistName)." has added new video post";
    } else {
        $postString = "";
        if($postTitle != "") {
            if(strlen($postTitle) >60) {
                $postString = substr($postTitle,0,60).'...';
            } else {
                $postString = $postTitle;
            }
        } else if($description != "") {
            if(strlen($description) >60) {
                $postString = substr($description,0,60).'...';
            } else {
                $postString = $description;
            }
        }
        $message =  trim($artistName).' has added new post "'.$postString.'" ';
    }
    return $message;
}
function  sendToIphone($token,$postid,$fileName,$artistID,$usertype,$type,$name,$myFile,$fh="",$postType) {
    //$fh = fopen($myFile, 'w');
    //fwrite($fh,"All Data :".$token,$postid,$fileName,$artistID,$usertype,$type,$name,$myFile,$fh="",$postType);
    fwrite($fh, "Token ".$token."\n");
    $deviceToken            =       $token;
    
    if($postType == "4") {
        if($type == 'question' && $usertype == 'member') {
            $message = "Your question is uploaded successfully";
        } 
        if($type == 'question' && $usertype == 'artist') {
            $message = $name." has asked a question";
        }

        if($type == 'answer' && $usertype == 'member') {
            $message = $name." has answered your question";
        } 
        if($type == 'answer' && $usertype == 'artist') {
            $message = "Your answer is uploaded successfully";
        }
    } else if($postType == "3") {
        $message = "Your video is uploaded successfully";
    }     
    fwrite($fh, "Message:".$message."\n");
    if($postType == "4") {
        $notificationtype = "replyAns";
    } else if($postType == "3") {
        $notificationtype = "videopost";
    }    
    $time = date("d M 'y H:i A");
    // Put your private key's passphrase here:
    //$passphrase = '1234';
    
    fwrite($fh, "NotificationType:".$notificationtype."\n");
    fwrite($fh, "Type:".$usertype."\n");

    ////////////////////////////////////////////////////////////////////////////////

    $ctx = stream_context_create();
    if ( ($type == 'answer' && $usertype == 'artist') || ($type == 'question' && $usertype == 'artist') ) {
        stream_context_set_option($ctx, 'ssl', 'local_cert', '../api/web/pushkey/artistcommon/ck_artist_production.pem');
        //stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    } else if ( ($type == 'answer' && $usertype == 'member') || ($type == 'question' && $usertype == 'member') ) { 
        stream_context_set_option($ctx, 'ssl', 'local_cert', '../api/web/pushkey/artist_'.$artistID.'/ck_user_production.pem');
        //stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
    }    

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
    $body['MobileStreamUrl']=""; //$streamingurl
    // Encode the payload as JSON
     $payload = json_encode($body);
     
     fwrite($fh,"Payload Data :".$payload);

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

function sendToAndroid($token,$postid,$fileName,$artistID,$usertype,$type,$name,$myFile,$fh="",$postType) {
    $registrationIds = array($token);
    if($postType == "4") {
        if($type == 'question' && $usertype == 'member') {
            $message = "Your question is uploaded successfully";
        } 
        if($type == 'question' && $usertype == 'artist') {
            $message = $name." has asked a question";
        }

        if($type == 'answer' && $usertype == 'member') {
            $message = $name." has answered your question";
        } 
        if($type == 'answer' && $usertype == 'artist') {
            $message = "Your answer is uploaded successfully";
        }
    }
    $msg = array
    (
            'message' => $message,
            'title' => 'Notification',
            'time'=>date("d M 'y H:i A"),
            'NotificationType'=>'replyAns',
    );

    $fields = array
    (
            'registration_ids' => $registrationIds,
            'data'	=> $msg
    );

    if (strpos($fileName, 'reply_') !== false) {
        $filename   =   "../api/web/pushkey/artist_".$artistID."/android_api_key_user.txt";
    } else {
        $filename   =   "../api/web/pushkey/artistcommon/android_api_key_artist.txt";
    }
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

//Clean Up For Debugging.
if($logToFile){
	ob_start();
	print_r( $json );
	$output = ob_get_clean();
	fwrite($fh, $output);
	fclose($fh);
}

if(isset($fh)){
	fclose($fh);
}

function logError($str){
	
	if(!isset($fh)){
		$dateString = date("Ymdhis");
		$dateString = $dateString."_r.txt";
		$myFile = $dateString;
		$fh = fopen($myFile, 'w') or die("Log File Cannot Be Opened.");
	}
	
	fwrite($fh, $str);
}

//A Function that takes the key file, signature, and signed data and tells us if it all matches.
function validateCertificate($keyFileURL, $signatureString, $data){
	$signature = base64_decode($signatureString);
	// fetch certificate from file and ready it
	$fp = fopen($keyFileURL, "r");
	$cert = fread($fp, 8192);
	fclose($fp);
	$pubkeyid = openssl_get_publickey($cert);
	$ok = openssl_verify($data, $signature, $pubkeyid, OPENSSL_ALGO_SHA1);
	if ($ok == 1) {
	    return true;
	} elseif ($ok == 0) {
	    return false;
	    
	} else {
	    return false;
	}	
}
//A Function that takes a URL String and returns the domain portion only
function getDomainFromUrl($urlString){
	$domain = "";
	$urlArray = parse_url($urlString);
	if($urlArray == false){
		$domain = "ERROR";
	}else{
		$domain = $urlArray['host'];
	}
	return $domain;
}

?>
