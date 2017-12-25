<?php

//////
//// CONFIGURATION
//////
//For Debugging.
$logToFile = true;
//define("AMAZON_S3_URL","https://boomvideoqa-out.s3.amazonaws.com/");
define("AMAZON_S3_URL","http://d2iws8kucyrriz.cloudfront.net/");

//Should you need to check that your messages are coming from the correct topicArn
$restrictByTopic = true;
//$allowedTopic = "arn:aws:sns:ap-northeast-1:739390266517:Boomforqa";
$allowedTopic = "arn:aws:sns:ap-northeast-1:739390266517:BoomTranscodingError";
//For security you can (should) validate the certificate, this does add an additional time demand on the system.
//NOTE: This also checks the origin of the certificate to ensure messages are signed by the AWS SNS SERVICE.
//Since the allowed topicArn is part of the validation data, this ensures that your request originated from
//the service, not somewhere else, and is from the topic you think it is, not something spoofed.
$verifyCertificate = true;
$sourceDomain = "sns.ap-northeast-1.amazonaws.com";
//////
//// OPERATION
//////
$signatureValid = false;
$safeToProcess = true; //Are Security Criteria Set Above Met? Changed programmatically to false on any security failure.

if($logToFile){
	////LOG TO FILE:
	$dateString = date("Ymdhis");
	$dateString = "error_".$dateString."_r.txt";
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
			
		if($jsonMessage->state == "ERROR") {
		
			$errorcode = $jsonMessage->errorCode;
			$errormsg = $jsonMessage->messageDetails;
			
			if($logToFile){
				fwrite($fh, "Error Code: ".$errorcode."\nError Message: ".$errormsg."\n");
			}else{
				logError("Error Code: ".$errorcode."\nError Message: ".$errormsg."\n");
			}
			if($errorcode == "3002"){
				//insert anyway value on db
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
				$queryString = "";
							
				$mobileStreamUrl = $jsonMessage->outputs[0]->key;
				$mobileThumbUrl = $jsonMessage->outputs[0]->thumbnailPattern;
				$replacedThumbUrl = str_replace('{count}','00001.png',$mobileThumbUrl);
				$imageWidth = $jsonMessage->outputs[0]->width;
				$imageHeight = $jsonMessage->outputs[0]->height;
				if (strpos($fileName, 'reply_') !== false) {
					$queryString.=",ReplyStreamURL = '".AMAZON_S3_URL.$mobileStreamUrl."', ReplyImage = '".AMAZON_S3_URL.$replacedThumbUrl."' ";	
				} else {
					$queryString.=",MobileStreamUrl = '".AMAZON_S3_URL.$mobileStreamUrl."', ThumbImage = '".AMAZON_S3_URL.$replacedThumbUrl."', VideoThumbImage = '".AMAZON_S3_URL.$replacedThumbUrl."'";	
				}    
				
				
				$queryString = substr($queryString,1);					
							if (strpos($fileName, 'reply_') !== false) {
								$sql = "UPDATE post SET ".$queryString." WHERE Reply = '".$fileName."' ";
							} else {
								$sql = "UPDATE post SET ".$queryString." WHERE URL= '".$fileName."' ";
							}    

				if ($conn->query($sql) === false) {
					if($logToFile){
						fwrite($fh, "Error in update\n".mysqli_error($conn)."\n");
					}else{
						logError("Error in update\n".mysqli_error($conn)."\n");
					}
				}else{
					if($logToFile){
						fwrite($fh, "MobileStreamUrl updated for: ".$mobileStreamUrl."\n");
					}else{
						logError("MobileStreamUrl updated for: ".$mobileStreamUrl."\n");
					}
				}
			}
			
		} else {
			if($logToFile){
				fwrite($fh, "Error in transcoding\n".$json."\n");
			}else{
				logError("Error in transcoding\n".$json."\n");
			}
		}
	}
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
