<?php
define('MYMUSICURL','http://artist.boomcollective.net/index.php/artisttracks/index');
define('SPOTIFY_CLIENT_ID','0decc6d817cc4ea48e4a48066a4f507d');
define('SPOTIFY_CLIENT_SECRET','8a003635a94e43e0ab8e8420db9e8bbd');
define('S3BUCKET','appbeatvideo-in');
define('S3BUCKETFORVIDEO','appbeat-out');
define('STICKER_IMAGES','/stickerimages/');
define('BOOM','boom-');
define('ARTIST_IMAGES','/artistimages/');
define('ARTIST_THUMB_IMAGES','/artistimages/thumb/');
define('ARTIST_MEDIUM_IMAGES','/artistimages/medium/');
define('PROFILE_IMAGES','/profileimages/');
define('PROFILE_THUMB_IMAGES','/profileimages/thumb/');
define('PROFILE_MEDIUM_IMAGES','/profileimages/medium/');

define('ALBUM_IMAGES','/albumimages/');
define('ALBUM_THUMB_IMAGES','/albumimages/thumb/');
define('ALBUM_MEDIUM_IMAGES','/albumimages/medium/');

define('POST_IMAGES','/postimages/');
define('POST_THUMB_IMAGES','/postimages/thumb/');
define('POST_THUMB_BLUR_IMAGES','/postimages/blurthumb/');
define('POST_MEDIUM_IMAGES','/postimages/medium/');
define('STICKER_THUMB_IMAGES','/stickerimages/StickerThumb/');
define('STICKER_MEDIUM_IMAGES','/stickerimages/StickerMedium/');
define('STICKER_SMALL_IMAGES','/stickerimages/StickerSmall/');
define('POST_VIDEOS','/postvideos/');
define('POST_THUMBIMAGE_VIDEOS','/thumb/');
define('POST_BLURTHUMBIMAGE_VIDEOS','/postvideos/blurthumb/');
define('SIMILAR_APP','/similarapp/');
define('SIMILAR_APP_THUMB','/similarapp/thumb/');
define('SIMILAR_APP_MEDIUM','/similarapp/medium/');

define('THUMB_WIDTH_SIZE',150);
define('THUMB_HEIGHT_SIZE',200);
define('POST_THUMB_WIDTH_SIZE',300);
define('POST_THUMB_HEIGHT_SIZE',200);
define('MEDIUM_WIDTH_SIZE',718);
define('MEDIUM_HEIGHT_SIZE',480);
define('FROM_EMAIL','noreply@boomcollective.net');

define('STICKER_SMALL_SIZE',70);

define('PROJECT', '/');
define('UPLOADS', 'uploads/');
define('POSTLARGE', 'postlarge/');
define('POSTTHUMB', 'postthumb/');
define('POSTBLURTHUMB', 'postblurthumb/');
define('POSTMEDIUM', 'postmedium/');
define('POSTSMALL', 'postsmall/');
define('DOCUMENT_ROOT',$_SERVER['DOCUMENT_ROOT']);

define('LOCALIMG', '/backend/web/image/');
define('ACTIVATIONPAGE', 'http://artist.boomcollective.net/mobile/index.php/site/thanksignup?token=');
define('RESETPASSWORDPAGE', 'http://artist.boomcollective.net/mobile/index.php/site/resetpasswordapp?token=');
define('FORGOTPASSWORDPAGEBACKEND', 'http://artist.boomcollective.net/index.php/site/forgotpassword');

define('AWSACCESSKEY','AKIAJASPNBUMHO23BENA');
define('AWSSECRETKEY','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');
define('AWSREGION','us-west-2');

define('AESKEY','boom@123');

define('S3_BUCKETABSOLUTE_PATH','http://d1t89cp2y0xf79.cloudfront.net');
define('S3_BUCKETPATH',S3_BUCKETABSOLUTE_PATH.'/'.BOOM);

//define('S3_VIDEO_BUCKETABSOLUTE_PATH','https://'.S3BUCKETFORVIDEO.'.s3.amazonaws.com/');
define('S3_VIDEO_BUCKETABSOLUTE_PATH','http://d1votgrx00p4vc.cloudfront.net');

define('S3_VIDEO_BUCKETPATH',S3_VIDEO_BUCKETABSOLUTE_PATH.'/'.BOOM);

define('PIPELINEID','1508299918111-bmswsf');
define('WEBMP4PRESET','1351620000001-000020');
define('APILIMIT','30');
//define('APILIMIT','30');
//define('APILIMIT','30');
define("COGNITO_POOL_ID","us-west-2:e66cbad6-f7b1-41b9-943a-41125b1493b9");
//define('WEBMPPRESETCUSTOM','1351620000001-000010');


//AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', AWSACCESSKEY);
if (!defined('awsSecretKey')) define('awsSecretKey', AWSSECRETKEY);
			

function getTimezone($dateTime) {
    $session = Yii::$app->session;
    $timezone = $session->get('timezone');
    if($timezone == '') {
        $timezone = 'Australia/Sydney';
    }
    if($dateTime != '') {
        $tz = new DateTimeZone($timezone);
        $date = new DateTime($dateTime);
        $date->setTimezone($tz);
        return $date->format('d, F Y H:i A'); 
    } else {
        return $dateTime;
    }    
}

function smiley($text)
{
        $path = "../../image/smiley"; 
        $width = '24';
        $height = '24';
        
        //$arr['\uD83D\uDE0A']  = 'emoji_1f600.png';
        $arr['\uD83D\uDE00']  = 'emoji_1f600.png';
        $arr['\uD83D\uDE01']  = 'emoji_1f601.png';
        $arr['\uD83D\uDE02']  = 'emoji_1f602.png';
        $arr['\uD83D\uDE03']  = 'emoji_1f603.png';
        $arr['\uD83D\uDE04']  = 'emoji_1f604.png';
        $arr['\uD83D\uDE05']  = 'emoji_1f605.png';
        $arr['\uD83D\uDE06']  = 'emoji_1f606.png';
        $arr['\uD83D\uDE07']  = 'emoji_1f607.png';
        $arr['\uD83D\uDE08']  = 'emoji_1f608.png';
        $arr['\uD83D\uDE09']  = 'emoji_1f609.png';
        $arr['\uD83D\uDE10']  = 'emoji_1f610.png';
        $arr['\uD83D\uDE11']  = 'emoji_1f611.png';
        $arr['\uD83D\uDE12']  = 'emoji_1f612.png';
        $arr['\uD83D\uDE13']  = 'emoji_1f613.png';
        $arr['\uD83D\uDE14']  = 'emoji_1f614.png';
        $arr['\uD83D\uDE15']  = 'emoji_1f615.png';
        $arr['\uD83D\uDE16']  = 'emoji_1f616.png';
        $arr['\uD83D\uDE17']  = 'emoji_1f617.png';
        $arr['\uD83D\uDE18']  = 'emoji_1f618.png';
        $arr['\uD83D\uDE19']  = 'emoji_1f619.png';
        $arr['\uD83D\uDE20']  = 'emoji_1f620.png';
        $arr['\uD83D\uDE21']  = 'emoji_1f621.png';
        $arr['\uD83D\uDE22']  = 'emoji_1f622.png';
        $arr['\uD83D\uDE23']  = 'emoji_1f623.png';
        $arr['\uD83D\uDE24']  = 'emoji_1f624.png';
        $arr['\uD83D\uDE25']  = 'emoji_1f625.png';
        $arr['\uD83D\uDE26']  = 'emoji_1f626.png';
        $arr['\uD83D\uDE27']  = 'emoji_1f627.png';
        $arr['\uD83D\uDE28']  = 'emoji_1f628.png';
        $arr['\uD83D\uDE29']  = 'emoji_1f629.png';
        $arr['\uD83D\uDE30']  = 'emoji_1f630.png';
        $arr['\uD83D\uDE31']  = 'emoji_1f631.png';
        $arr['\uD83D\uDE32']  = 'emoji_1f632.png';
        $arr['\uD83D\uDE33']  = 'emoji_1f633.png';
        $arr['\uD83D\uDE34']  = 'emoji_1f634.png';
        $arr['\uD83D\uDE35']  = 'emoji_1f635.png';
        $arr['\uD83D\uDE36']  = 'emoji_1f636.png';
        $arr['\uD83D\uDE37']  = 'emoji_1f637.png';

        $code_arr = array_keys($arr);
        foreach($code_arr as $code) {
                $text = str_ireplace($code, "<img width = '" . $width . "' height = '" . $height . "' src='$path/" . $arr[$code] . "'>" , $text);
        }
        return $text;			
}     
