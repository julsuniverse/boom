<?php
/**
 * Created by PhpStorm.
 * User: algenepulido
 * Date: 28/09/2017
 * Time: 1:27 PM
 */

namespace api_v3\models;

define("ONESIGNAL_USER_AUTH_KEY","NGE1ZjdlYTYtYjU5NS00ZGQxLWEzZjktOWY4OGM1YTBkYzY5");

class OneSignalPushNotification {

    private function getApp($appid) {
        if (!isset($appid) || trim($appid)==='') {
            //if no onesignal app id, just return empty auth key. It will not send any push notification.
            //just to prevent error.
            return array("basic_auth_key"=>"");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/apps/".$appid);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',
            'Authorization: Basic '.ONESIGNAL_USER_AUTH_KEY));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        $response = json_decode($response, true);
        curl_close($ch);
        return $response;
    }

    function sendMessageToUserID($os_appid, $UserID, $message, $title, $postid, $NotificationType, $time) {

        $os_app_data=$this->getApp($os_appid);
        $app_auth_key=$os_app_data['basic_auth_key'];

        $content = array(
            "en" => $message
        );

        $fields = array(
            'app_id' => $os_appid,
            'filters' => array(array("field" => "tag", "key" => "UserID", "relation" => "=", "value" => $UserID)),
            'data' => array("message" => $message, "title"=>$title, "postid"=>$postid, "NotificationType"=>$NotificationType,"time"=>$time),
            'contents' => $content
        );

        $fields = json_encode($fields);
        //print("\nJSON sent:\n");
        //print($fields);

        $this->onesignalpush($app_auth_key,$fields);
    }

    function sendMessageExceptUserID($os_appid,$UserID,$message,$title,$postid,$NotificationType,$time) {
        $os_app_data=$this->getApp($os_appid);
        $app_auth_key=$os_app_data['basic_auth_key'];

        $content = array(
            "en" => $message
        );

        $fields = array(
            'app_id' => $os_appid,
            'filters' => array(array("field" => "tag", "key" => "UserID", "relation" => "!=", "value" => $UserID)),
            'data' => array("message" => $message, "title"=>$title, "postid"=>$postid, "NotificationType"=>$NotificationType,"time"=>$time),
            'contents' => $content
        );

        $fields = json_encode($fields);
        //print("\nJSON sent:\n");
        //print($fields);

        $this->onesignalpush($app_auth_key,$fields);
    }

    function sendMessageToAll($os_appid,$message,$title,$postid,$NotificationType,$time) {
        $os_app_data=$this->getApp($os_appid);
        $app_auth_key=$os_app_data['basic_auth_key'];

        $content = array(
            "en" => $message
        );

        $fields = array(
            'app_id' => $os_appid,
            'included_segments'=>array('All'),
            'data' => array("message" => $message, "title"=>$title, "postid"=>$postid, "NotificationType"=>$NotificationType,"time"=>$time),
            'contents' => $content
        );

        $fields = json_encode($fields);
        //print("\nJSON sent:\n");
        //print($fields);

        $this->onesignalpush($app_auth_key,$fields);
    }

    private function onesignalpush($app_auth_key,$fields) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
            'Authorization: Basic '.$app_auth_key));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        //print($response);

        return $response;
    }

}