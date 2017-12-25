<?php

namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use api\models\User;
use backend\models\Artist;
use backend\models\Artist_company;
use backend\models\Globalvar;
use backend\models\Category;
use backend\models\Rssfeed;
use backend\models\Setting;
use backend\models\Sticker;
use yii\filters\VerbFilter;
use yii\filters\auth\HttpBasicAuth;
use yii\db\Expression;
use yii\base\ErrorException;

use backend\models\UserArtist;
use backend\models\Member;

require_once '../../backend/models/Artist.php';
require_once '../../backend/models/Artist_company.php';
require_once '../../backend/models/Globalvar.php';
require_once '../../backend/models/Category.php';
require_once '../../backend/models/Post.php';
require_once '../../backend/models/Pushqueue.php';
require_once '../../backend/models/Rssfeed.php';
require_once '../../backend/models/Member.php';
require_once '../../backend/models/Setting.php';
require_once '../../backend/models/Sticker.php';
require_once '../../backend/models/Pushhistorystats.php';
require_once '../../backend/models/Pushqueue.php';
require_once '../../api/models/Commentnotforartist.php';

require_once '../../backend/models/UserArtist.php';
require_once '../../backend/models/Member.php';
/**
 * UserController implements the CRUD actions for User model.
 * */
class UserController extends Controller {
    
    /************ Set constant ***************/
    public $logWrite = true; 

    const
            STATUS_DELETED = 0;
    const
            STATUS_ACTIVE = 10;
    const
            EXPIRE_TIME = 5; // Token expire time in minute
    const
            S3Bucket = S3BUCKET;
    const
            COGNITOID = COGNITO_POOL_ID;
    const
            BoomFolder = BOOM;
    const
            S3BucketAbsolutePath = S3_BUCKETABSOLUTE_PATH;
    const
            S3BucketPath = S3_BUCKETPATH;
    const
            S3BucketArtistImages = ARTIST_IMAGES;
    const
            S3BucketArtistThumb = ARTIST_THUMB_IMAGES;
    const
            S3BucketArtistMedium = ARTIST_MEDIUM_IMAGES;
    const
            S3BucketPostImages = POST_IMAGES;
    const
            S3BucketPostThumbImage = POST_THUMB_IMAGES;
    const
            S3BucketPostMediumImage = POST_MEDIUM_IMAGES;
    const
            S3BucketProfileImages = PROFILE_IMAGES;
    const
            S3BucketProfileThumbImages = PROFILE_THUMB_IMAGES;
    const
            S3BucketProfileMediumImages = PROFILE_MEDIUM_IMAGES;
    const
            S3BucketAppIcons = SIMILAR_APP_MEDIUM;
    const
            S3BucketStickers = STICKER_IMAGES;
    const
            S3BucketStickersThumb = STICKER_THUMB_IMAGES;
    const
            S3BucketStickersSmall = STICKER_SMALL_IMAGES;
    const
            S3BucketStickersMedium = STICKER_MEDIUM_IMAGES;
    const
            S3BucketPostVideos = POST_VIDEOS;
    const
            BoomPath = BOOM;
    const
            EncryptKey = AESKEY;
    const
            Limit = APILIMIT;
    const
            Thumbwidth = THUMB_WIDTH_SIZE;
    const
            Thumbheight = THUMB_HEIGHT_SIZE;
    const
            PostThumbwidth = POST_THUMB_WIDTH_SIZE;
    const
            PostThumbheight = POST_THUMB_HEIGHT_SIZE;
    const
            Mediumwidth = MEDIUM_WIDTH_SIZE;
    const
            Mediumheight = MEDIUM_HEIGHT_SIZE;
    const
            Documentroot = DOCUMENT_ROOT;
    const
            Project = PROJECT;
    const
            Uploads = UPLOADS;
    const
            Postlarge = POSTLARGE;
    const
            Postthumb = POSTTHUMB;
    const
            Blurthumb = POSTBLURTHUMB;
    const
            Postsmall = POSTSMALL;
    const
            Postmedium = POSTMEDIUM;
    const
            Fromemail = FROM_EMAIL;
    const
            Activationpage = ACTIVATIONPAGE;
    const
            Resetpasswordpage = RESETPASSWORDPAGE;
    const
            Postvideosthumb = POST_THUMBIMAGE_VIDEOS;
    const
            Postblurthumb = POST_THUMB_BLUR_IMAGES;
    const
            Postblurthumbvideos = POST_BLURTHUMBIMAGE_VIDEOS;

    private
            $arrSkipAction = ['create',
        'login',
        'addfeed'];

    public
            function behaviors() {
        /*********** If any ws added then you need to put it's action here **********/
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::className(),
            'except' => ['create',
                'login',
                'getprofile',
                'forgotpassword',
                'activity',
                'addpost',
                'signup',
                'editprofile',
                'commentlist',
                'newcommentlist',
                'postlist',
                'checkusername',
                'likepost',
                'addcomment',
                'likecomment',
                'flag',
                'stickers',
                'applist',
                'purchasesticker',
                'purchasepost',
                'artisteditpost',
                'artistdeletepost',
                'likelist',
                'sharelist',
                'share',
                'flaglist',
                'artisteditprofile',
                'artisthomescreen',
                'mymusic',
                'globaliospush',
                'globalandroidpush',
                'latestcommentlist',
                'addfeed',
                'mymusictv',
                'questionanswerlist',
                'block',
                'getqasettings',
                'updateqasettings',
                'oldstickers',
                'blockusercomment',
                'allpostlist',
                'deletecomment',
                'productsku',
				'instagrampostlist',//Daniele
				'youtubepostlist',//Daniele
				'getartistfrombundle',//Daniele
				'stickerspaging',
				'getartistcompany',
                'getdpinfo' //added by Kate--deeplinking
            ],
        ];
        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'view' => ['get'],
                'create' => ['put'],
                'update' => ['put'],
                'delete' => ['delete'],
                'deleteall' => ['post'],
                'login' => ['post'],
                'getprofile' => ['post'],
                'forgotpassword' => ['post'],
                'activity' => ['post'],
                'addpost' => ['post','get'],
                'signup' => ['post'],
                'editprofile' => ['post'],
                'commentlist' => ['post'],
                'postlist' => ['post'],
                'checkusername' => ['post'],
                'likepost' => ['post'],
                'addcomment' => ['post'],
                'likecomment' => ['post'],
                'flag' => ['post'],
                'stickers' => ['post'],
                'applist' => ['post'],
                'purchasesticker' => ['post'],
                'purchasepost' => ['post'],
                'artisteditpost' => ['post'],
                'artistdeletepost' => ['post'],
                'likelist' => ['post'],
                'sharelist' => ['post'],
                'share' => ['post'],
                'flaglist' => ['post'],
                'artisteditprofile' => ['post'],
                'artisthomescreen' => ['post'],
                'mymusic' => ['post'],
                'globaliospush' => ['get'],
                'globalandroidpush' => ['get'],
                'latestcommentlist' => ['post'],
                'addfeed' => ['get'],
                'mymusictv' => ['post'],
                'questionanswerlist' => ['post'],
                'block' => ['post'],
                'getqasettings' => ['post'],
                'updateqasettings' => ['post'],
                'oldstickers' => ['post'],
                'blockusercomment' => ['post','get'],
                'newcommentlist' => ['post','get'],
                'allpostlist' => ['post','get'],
                'deletecomment' => ['post','get'],
                'productsku' => ['post','get'],
				'instagrampostlist' => ['post'],//Daniele
				'youtubepostlist' => ['post'],//Daniele
				'getartistfrombundle' => ['get'],//Daniele
				'stickerspaging' => ['post'],
                'getartistcompany' => ['post'],
                'getdpinfo' => ['post'] //added by Kate--deeplinking
            ]
        ];
        return $behaviors;
    }

    public function beforeAction($event) {
        /*************** Before any ws called this is first called : Added log ***********/
        $log_current = "";
        //if($this->logWrite) {
            $apiURL = $_SERVER['REQUEST_URI'];
            $paramsData = "";
            if(isset($_POST['params'])) {
                $paramsData = $_POST['params'];
            }
            $arrParams = json_encode($paramsData);
            $headerRequest = getallheaders();
            //str_replace('\/','/',json_encode($arrParams));
            //$filename = '../web/log/Log_'.date('Y-m-d').'.txt';
            $filename = '../web/log/Log.txt';
            $log_current = "\n ************************";
            $log_current.= "\n DateTime : " .date("Y-m-d H:i:s");
            $log_current.= "\n WebService : ".$apiURL;
            $log_current.= "\n Params: " . stripslashes($arrParams);
            $log_current .= "\n Request Method: ".$_SERVER['REQUEST_METHOD']."\n";
            //$log_current .= "\n Request: ".print_r($_REQUEST, true);
            $log_current .= "\n Header Data: ".print_r($headerRequest, true);
            file_put_contents($filename, $log_current,FILE_APPEND);
            //echo "sdsd"; die;
        //}
        $action = $event->id;
        if (isset($this->actions[$action]))
        {
            $verbs = $this->actions[$action];
        }
        elseif (isset($this->actions['*']))
        {
            $verbs = $this->actions['*'];
        }
        else
        {
            return $event->isValid;
        }
        $verb = Yii::$app->getRequest()->getMethod();
        $allowed = array_map('strtoupper', $verbs);
        if (!in_array($verb, $allowed))
        {
            $this->setHeader(400);
            echo json_encode(array(
                'status' => 0,
                'error_code' => 400,
                'message' => 'Method not allowed'), JSON_PRETTY_PRINT);
            exit;
        }
        return true;
    }

    private
            function setHeader($status) {
        /********** Added Heder **************/
        $statusHeader = 'HTTP/1.1' . $status . ' ' . _getStatusCodeMessageForUser($status);
        $contentType = "application/json; charset=utf-8";
        header($statusHeader);
        // header('Content-type: ' . $contentType);
        header('Content-Type: text/html; charset=utf-8');
    }

    public
            function udate($format, $utimestamp = null) {
        if (is_null($utimestamp)) $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }

    public function actionLogin() {
        /************** Artist and Member Login **********/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'Username',
                'Password',
                'DeviceType',
                'DeviceToken',
                'UserType',
                'ArtistID',
                'LoginType',
                'MemberName',
                'Email',
                'Language',
                'IsQa',
                'QaName',
                'QaType',
                'ComID' // Boom Native app--Kate
                );
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $fbmembername = "";
                $fbEmail = "";
                $password = "";
                $username = "";
                if(isset($data->Username)) {
                    $username = str_replace("'", "", $data->Username);
                }    
                if (isset($data->Password))
                {
                    $password = str_replace("'", "", $data->Password);
                }
                $devicetype = $data->DeviceType;
                $devicetoken = $data->DeviceToken;
                $usertype = $data->UserType;
                $artistID = $data->ArtistID;
                $loginType = $data->LoginType;
                // Boom Native app--Kate
                if (isset($data->ComID))
                    $ComID=$data->ComID;
                else
                    $ComID=0;
                if($loginType == "0") {
                    $loginType = "1";
                }
                $language = $data->Language;
                if (isset($data->MemberName))
                {
                    $fbmembername = $data->MemberName;
                }
                if (isset($data->Email))
                {
                    $fbEmail = $data->Email;
                }
                $connection = Yii::$app->db;
                /************* Check if member exists *************/
                $procedure = "CALL Member_Login('" . $username . "','" . $fbmembername . "','" . $fbEmail . "','" . $password . "','" . $devicetype . "','" . $devicetoken . "'," . $usertype . "," . $artistID . "," . $loginType . ",'" . self::EncryptKey . "','" . self::S3BucketPath . "','" . self::S3BucketProfileImages . "',@UserID,@ProfileID,@UserType,@ErrorCode)";
                $logString.="\n SP : ".$procedure.'\n';
                // echo $procedure; 
                $command = $connection->createCommand($procedure);
                $loginData = $command->queryAll();

                $resultCode = $loginData[0]['ErrorCode'];
                //$resultMessage = 	$loginData[0]['Message'];
                $resultMessage = _getStatusCodeMessageForUser($resultCode);
                $userID = $loginData[0]['UserID'];
                $artistUserID = $loginData[0]['ArtistUserID'];
                if ($artistID > 0)
                {
                    $artistProfileID = $artistID;
                }else if($ComID!="0"&&$usertype=="3"){ // Boom Native app--Kate
                    
                    $artists=UserArtist::find()->where(" UserID=".$userID." and isSub=1")->all(); // For Native App need artist to subscribe
                    if(count($artists)>0) {
                        $artistID=$artists[0]['ArtistID'];
                        $artistProfileID = $artists[0]['ArtistID'];
                        $artist=Artist::find()->where(['ArtistID' => $artistID])->all();
                        if(count($artist)>0) $artistUserID = $artist[0]['UserID'];
                    } 
                    else $artistProfileID=0;
                }
                else
                {
                    $artistProfileID = $loginData[0]['ProfileID'];
                }
				if($artistProfileID!=0){
                 //Get Categories for Artist
                    $categories = array();
                    if($artistID > 0){
                        $cats = Category::find()->where(['ArtistID' => $artistID])->all();
                        foreach($cats as $cat){
                            $catel = ['Id'=>$cat->Id, 'Name'=>$cat->Name];
                            array_push($categories, $catel);
                        }
                    }

                    $profileID = $loginData[0]['ProfileID'];
                    /************* Get Member Profile Data *************/    
                    $user_profile_proc = "CALL Member_GetProfile('" . $userID . "','3','" . $profileID . "','" . self::EncryptKey . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "')";
                    $logString.="\n Member Profile : ".$user_profile_proc.'\n';
                    $artist_profile_proc = "CALL Member_GetProfile('" . $artistUserID . "','2','" . $artistProfileID . "','" . self::EncryptKey . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "')";
                    $logString.="\n Artist Profile : ".$artist_profile_proc.'\n';
                    /*echo $artist_profile_proc; 
                    echo '<br>';
                    echo $user_profile_proc; die;*/
                    $command2 = $connection->createCommand($user_profile_proc);
                    $user_profileData = $command2->queryAll();
                    $command3 = $connection->createCommand($artist_profile_proc);
                    $artist_profileData = $command3->queryAll();

                    if (count($artist_profileData) > 0)
                    {
                        /*************** Get Artist Image List **************/
                        $postimagesprocedure = "CALL Artist_Image_List(2,0," . $artistProfileID . ",'" . self::S3BucketPath . "','" . self::S3BucketArtistImages . "','" . self::S3BucketArtistThumb . "','" . self::S3BucketArtistMedium . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $commandForImage = $connection->createCommand($postimagesprocedure);
                        $artistimages = $commandForImage->queryAll();
                        $artist_profileData[0]['ArtistImage'] = $artistimages;
                    }
                    //echo $postimagesprocedure; die;

                    if ($usertype == "3")
                    {
                        /******************* Get Post List if Member **************/
                        //Boom Native App --Kate
                        if($ComID==0) $isExclusive=0;
                        else $isExclusive=1;
                        //added by Kate--deeplinking
                        $postData_proc = "CALL Post_List(0," .  $artistID . "," . $userID . "," . $profileID . "," . $isExclusive . ",-1,'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "','','',@o_RecCount)";//Daniele
                        $logString.="\n Post List : ".$postData_proc.'\n';
                    }
                    else
                    {
                        /**************** If artist login get artist feed ************/
                        $postData_proc = "CALL Artist_Home_News_Feed(" . $artistProfileID . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "'," . self::Limit . ",1,@o_RecCount)";
                        $logString.="\n Artist_Home_News_Feed : ".$postData_proc.'\n';
                    }
                    //echo $postData_proc; die;
                    $command4 = $connection->createCommand($postData_proc);
                    $postData = $command4->queryAll();

                    $reccommand = $connection->createCommand('SELECT @o_RecCount')->queryOne();
                    $recordcnt = $reccommand['@o_RecCount'];

                    /*************** Get Similarapp list ****************/
                    $similarApp_proc = "CALL SimilarApp_List(" . $artistProfileID . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketAppIcons . "')";
                    //echo $similarApp_proc; die;
                    $command5 = $connection->createCommand($similarApp_proc);
                    $similarApp = $command5->queryAll();

                    /*                 * ******************************* Bundle Data For Artist **************************** */

                    $modelSetting = new \backend\models\Setting();
                    $bundleData = $modelSetting->find()->where(array(
                                'ArtistID' => $artistID))->asArray()->all();

                    /*                 * ******************************* Unread QA **************************** */
                    $unreadQAData = $this->unreadQA($artistID);
                    /*                 * ********************************************************************************** */

                    if (count($postData) > 0)
                    {
                        foreach ($postData as $key => $value)
                        {
                            if (isset($value['PostID']) && $value['PostID'] != '')
                            {
                                $imageData = array();
                                $postID = $value['PostID'];
                                /************ Get Post Images **************/
                                $postimageproc = "CALL Post_Image_List(1," . $postID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketPostImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                                $commandForImage = $connection->createCommand($postimageproc);
                                $imageData = $commandForImage->queryAll();

                                /*$latestcmntsproc = "CALL Latest_Post_CommentList(" . $postID . "," . $artistID . "," . $profileID . "," . $usertype . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "','" . self::S3BucketStickersSmall . "','" . self::S3BucketStickersMedium . "')";
                                $commandForCmnts = $connection->createCommand($latestcmntsproc);
                                $cmntsData = $commandForCmnts->queryAll();*/

                                if (count($imageData) > 0)
                                {
                                    $postData[$key]['PostImage'] = $imageData;
                                }
                                else
                                {
                                    $postData[$key]['PostImage'] = $imageData;
                                }

                                /*if (count($cmntsData) > 0)
                                {
                                    $postData[$key]['LatestComments'] = $cmntsData;
                                }
                                else
                                {
                                    $postData[$key]['LatestComments'] = array();
                                }*/
                                $postData[$key]['LatestComments'] = array();
                            }
                        }
                    }               
                    
                    \Yii::$app->language = $language;
                    $lngmsg = \Yii::t('api', $resultMessage);
                    $this->setHeader(400);
                    $userData = (object) array();
                    if (!empty($user_profileData) && isset($user_profileData[0]))
                    {
                        $userData = $user_profileData[0];
                        if (isset($userData['DOB']) && ($userData['DOB'] == "01-01-1970" || $userData['DOB'] == "0000-00-00"))
                        {
                            $userData['DOB'] = "";
                        }
                    }

                    $artistData = (object) array();
                    if (!empty($artist_profileData) && isset($artist_profileData[0]))
                    {
                        $artistData = $artist_profileData[0];
                    }
                    /*                 * ********************** Sticker List ************************** */
                    $stickersData = array();
                    //added by Kate--deeplinking
                    $procedure = "CALL Sticker_List(0," . $artistID . "," . $data->DeviceType . ",'" . self::S3BucketAbsolutePath . "/','" . self::BoomFolder . $artistID . self::S3BucketStickers . "','" . self::BoomFolder . $artistID . self::S3BucketStickersSmall . "','" . self::BoomFolder . $artistID . self::S3BucketStickersMedium . "')";
                    $logString.="\n Sticker_List : ".$procedure.'\n';
                    $command = $connection->createCommand($procedure);
                    $stickersData = $command->queryAll();
                    if(count($stickersData)>0) :
                        foreach ($stickersData as $key => $value)
                        {
                                $stickerImages = explode(',', $stickersData[$key]['StickerImage']);
                                $stickerThumbImages = explode(',', $stickersData[$key]['StickerThumbImage']);
                                $stickerMediumImages = explode(',', $stickersData[$key]['StickerMediumImage']);
                                $stickerImagesID = explode(',', $stickersData[$key]['StickerImageID']);
                                $stickerImage = array();
                                for ($n = 0; $n < count($stickerImagesID); $n++)
                                {
                                        $stickerImage[$n]['StickerImageID'] = $stickerImagesID[$n];
                                        $stickerImage[$n]['StickerImage'] = $stickerImages[$n];
                                        $stickerImage[$n]['StickerThumbImage'] = $stickerThumbImages[$n];
                                        $stickerImage[$n]['StickerMediumImage'] = $stickerMediumImages[$n];
                                }
                                $stickersData[$key]['StickerImage'] = $stickerImage;
                                unset($stickersData[$key]['StickerThumbImage']);
                                unset($stickersData[$key]['StickerMediumImage']);
                                unset($stickersData[$key]['StickerImageID']);
                        }
                    endif;    
                    /*                 * ********************** QA Flag ***************** */
                    $QAData = array();
                    $procedure = "SELECT SettingID,ArtistID,QAModuleName,IsQAEnable,QaType,TextPrice,TextProductSKUID,VideoPrice,VideoProductSKUID,
                                    PhotoPrice,PhotoProductSKUID,ProductType,ProductPrice,ProductSKUID,AndroidPrice,AndroidSKUID  FROM setting WHERE ArtistID =" . $artistProfileID;
                    $command = $connection->createCommand($procedure);
                    $QAData = $command->queryAll();
                    $QAModuleName = "Q&A";
                    $IsQAEnable = "0"; // 1-Enable, 0-Disable
                    $QAType = "0"; // 1-Video, 2-Text,3-Both
                    $productPrice = "0";
                    $productSKUID = "";
                    $productType = "1";
                    $androidPrice = "0";
                    $androidSKUID = "";
                    if (!empty($QAData))
                    {
                        if (isset($QAData["0"]["QAModuleName"]) && $QAData["0"]["QAModuleName"] != "") 
                            $QAModuleName = $QAData["0"]["QAModuleName"];
                        if (isset($QAData["0"]["IsQAEnable"]) && $QAData["0"]["IsQAEnable"] != "") 
                            $IsQAEnable = $QAData["0"]["IsQAEnable"];
                        if (isset($QAData["0"]["QaType"]) && $QAData["0"]["QaType"] != "") 
                            $QAType = $QAData["0"]["QaType"];
                        if (isset($QAData["0"]["ProductType"]) && $QAData["0"]["ProductType"] != "") 
                            $productType = $QAData["0"]["ProductType"];
                        if (isset($QAData["0"]["ProductPrice"]) && $QAData["0"]["ProductPrice"] != "" && $devicetype == "1") 
                            $productPrice = $QAData["0"]["ProductPrice"];
                        if (isset($QAData["0"]["ProductSKUID"]) && $QAData["0"]["ProductSKUID"] != ""  && $devicetype == "1") 
                            $productSKUID = $QAData["0"]["ProductSKUID"];
                        if (isset($QAData["0"]["AndroidPrice"]) && $QAData["0"]["AndroidPrice"] != "" && $devicetype == "2") 
                            $productPrice = $QAData["0"]["AndroidPrice"];
                        if (isset($QAData["0"]["AndroidSKUID"]) && $QAData["0"]["AndroidSKUID"] != ""  && $devicetype == "2") 
                            $productSKUID = $QAData["0"]["AndroidSKUID"];
                    }
                    /*************************************************** */
                    
                    //$modelPost = new \backend\models\Post();
                    //$modelSticker = new \backend\models\Sticker();
                    //$postSKUData = $modelPost->find()->select('ProductSKUID')->where(array('ArtistID' => $artistProfileID))->asArray()->all();
                    //$stickerSKUData = $modelSticker->find()->select('IOSSKUID')->where(array('ArtistID' => $artistProfileID))->asArray()->all();
                    
                    $productSKUdata = array();
                    
                    if(isset($QAData["0"]["ProductSKUID"]) && $QAData["0"]["ProductSKUID"]!="") {
                        $productSKUdata[] = $QAData["0"]["ProductSKUID"];
                    }
                    foreach($postData as $key => $value) {
                        if(isset($value['ProductID']) && $value['ProductID']!='') {
                            $productSKUdata[] = $value['ProductID'];
                        }
                    }
                    foreach($stickersData as $key => $value) {
                        if(isset($value['ProductID']) && $value['ProductID']!='') {
                            $productSKUdata[] = $value['ProductID'];
                        }
                    }
                    
                    
                    
                    //Added by Daniele: append Instagram & Blog images from RSS feed: TEST
                if($artistID > 0){
                    $artist = Artist::find()->where(['ArtistId' => $artistID])->one();
                    $instaUrl = $artist->InstagramPageURL;
                    $blogUrl = $artist->BlogFeedUrl;
                    $Feeds = null;
                    $instaFeeds = null;
                    
                    if($artist->SocialPostEnabled){             
                        //Instagram feed: extract username and generates feed using rssbridge service
                        if($instaUrl != null && $instaUrl != ""){
                                    
                            $spliurl = explode('/', $instaUrl);
                            $instaUserName = "";
                            if($spliurl[count($spliurl)-1] != ""){ $instaUserName = $spliurl[count($spliurl)-1];}else{ $instaUserName = $spliurl[count($spliurl)-2];}
                                    
                                    
                            if($instaUserName != ""){
                                $instaFeeds = $this->getInstagramFeeds('http://rssbridge.buddylist.co/?action=display&bridge=InstagramBridge&u='.$instaUserName.'&format=MrssFormat', $artistID);
                            }
                                    
                        }       
                        //Blog feed: get feed url from database and get feed
                        if($blogUrl != null && $blogUrl != ""){
                            $Feeds = $this->getBlogFeeds($blogUrl, $artistID);
                        }
                        
                        //Added by Daniele: append instagram and blog feeds to $postData if available
                        if($instaFeeds != null){
                            $recordcnt += count($instaFeeds);
                            foreach ($instaFeeds as $instaEl){
                                //if(count($postData) < (self::Limit)){
                                    array_push($postData, $instaEl);
                                //}
                            }
                        }
                        if($Feeds != null){
                            $recordcnt += count($Feeds);
                            foreach ($Feeds as $feedEl){
                                //if(count($postData) < (self::Limit)){
                                    array_push($postData, $feedEl);
                                //}
                            }
                        }
                    }
                    
                    //If 24h flag enabled, only the last 24h posts are filtered
                    if($artist->Display24h){
                        $curDate = time();
                        $startDate = date('Y-m-d H:i:s', strtotime('-1 day', $curDate));
                        $postData24 = array();
                        foreach ($postData as $post){
                            $date;
                            if(is_array($post)){
                                $date = $post['DateTime'];
                            }else{
                                $date = $post->DateTime;
                            }
                            if($date > $startDate){
                                array_push($postData24, $post);
                            }
                        }
                        $postData = $postData24;
                        $recordcnt = count($postData);
                    }
                }
                    
                    //Sort based on DateTime
                    usort($postData, function($a, $b)
                    {
                        $ao = get_object_vars((object)$a);
                        $bo = get_object_vars((object)$b);
                        $date1=$ao['DateTime']; $date2=$bo['DateTime'];
                        
                        return strtotime($date1)<strtotime($date2);
                    });
                    $postData = array_slice($postData, 0, self::Limit);
                    
                    
                    //get appversion
                    $AppVData = Globalvar::find()->where(['Name' => "AppVersion"])->one();
                    $appversion = "";
                    if($AppVData != null){ $appversion = $AppVData->Value;}   
                                    //Daniele
                
                    if ($resultCode == "200")
                    {
                        echo json_encode(['Status' => 1,
                            "Message" => $lngmsg,
                            'Result' => $loginData,
                            'RecordCount' => $recordcnt,
                            'productSKUdata'=>$productSKUdata,
                            'MemberProfile' => $userData,
                            'ArtistProfile' => $artistData,
                            'PostList' => $postData,
                            'Categories' => $categories,
                            'SimilarApp' => $similarApp,
                            'stickersData' => $stickersData,
                            'IsQa' => $IsQAEnable,
                            'QaName' => $QAModuleName,
                            'QaType' => $QAType,
                            'BucketName'=>self::S3Bucket."/",
                            'CognitoPoolId'=>self::COGNITOID,
                            'paymetInfoQA' => $bundleData,
                            'UnreadQA' => $unreadQAData,
                            'AppVersion' => $appversion,
                            'ProductType_Subscription' => $productType,
                            'ProductPrice_Subscription' => $productPrice,
                            "ProductSKUID_Subscription" => $productSKUID], JSON_PRETTY_PRINT);
                    }
                    else
                    {
                        echo json_encode(['Status' => 0,
                            "Message" => $lngmsg,
                            'Result' => $loginData,
                            'RecordCount' => $recordcnt,
                            'productSKUdata'=>$productSKUdata,
                            'MemberProfile' => $userData,
                            'ArtistProfile' => $artistData,
                            'PostList' => $postData,
                            'Categories' => $categories,
                            'SimilarApp' => $similarApp,
                            'stickersData' => $stickersData,
                            'IsQa' => $IsQAEnable,
                            'QaName' => $QAModuleName,
                            'QaType' => $QAType,
                            'BucketName'=>self::S3Bucket."/",
                            'CognitoPoolId'=>self::COGNITOID,
                            'paymetInfoQA' => $bundleData,
                            'UnreadQA' => $unreadQAData,
                            'AppVersion' => $appversion,
                            'ProductType_Subscription' => $productType,
                            'ProductPrice_Subscription' => $productPrice,
                            "ProductSKUID_Subscription" => $productSKUID], JSON_PRETTY_PRINT);
                    }

                }else{
                    echo json_encode(['Status' => 0,
                    "Message" => "User haven't subsribe artist yet"], JSON_PRETTY_PRINT);
                }
            }
            else
            {
                //$this->setHeader(502);
                $resultMessage = _getStatusCodeMessageForUser(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
                //echo $e; die;
                $this->addLog($logString,$e);
        }
    }
    
    public function addLog($logString,$e) {
        /************* Add log in text file **************/
        if($this->logWrite) {
            $filename = '../web/log/'.Yii::$app->controller->action->id.'Log_' . date('Y-m-d-H:i:s') . '.txt';
            $log_current = "\n ************************";
            $log_current.= "\n DateTime : " .date("Y-m-d H:i:s");
            $log_current.= "\n Message : " . $e;
            $log_current.= "\n Data: " . $logString;
            file_put_contents($filename, $log_current,FILE_APPEND);
        }
    }

    public function actionGetqasettings() {
        /************* Get QA Settings data ****************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ArtistID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $artistID = $data->ArtistID;
                $language = $data->Language;
                $modelSetting = new \backend\models\Setting();
                $bundleData = $modelSetting->find()->where(array(
                            'ArtistID' => $artistID))->asArray()->all();
                if($bundleData[0]["QAModuleName"]==null) :
                    $bundleData[0]["QAModuleName"] = "";
                endif;
                if($bundleData[0]["IsQAEnable"]==null) :
                    $bundleData[0]["IsQAEnable"] = "";
                endif;
                if($bundleData[0]["QaType"]==null) :
                    $bundleData[0]["QaType"] = "";
                endif;
                if($bundleData[0]["TextProductSKUID"]==null) :
                    $bundleData[0]["TextProductSKUID"] = "";
                endif;
                if($bundleData[0]["VideoProductSKUID"]==null) :
                    $bundleData[0]["VideoProductSKUID"] = "";
                endif;
                if($bundleData[0]["ProductPrice"]==null) :
                    $bundleData[0]["ProductPrice"] = "0";
                endif;
                unset($bundleData[0]["Created"]);
                unset($bundleData[0]["CreatedBy"]);
                unset($bundleData[0]["Updated"]);
                unset($bundleData[0]["UpdatedBy"]);
                $resultMessage = _getStatusCodeMessageForUser(200);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(200);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    'QAData' => $bundleData], JSON_PRETTY_PRINT);
            }
            else
            {
                $resultMessage = _getStatusCodeMessageForUser(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);    
        }
    }

    public function actionUpdateqasettings() {
        /*************** Update QA Settings data ***************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ArtistID',
                'Language',
                'TabName',
                'QAType',
                'TextPrice',
                'TextProductSKUID',
                'VideoPrice',
                'VideoProductSKUID',
				'PhotoPrice',
                'PhotoProductSKUID');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $artistID = $data->ArtistID;
                $language = $data->Language;
                $tabName = $data->TabName;
                $QAType = $data->QAType;
                $textPrice = $data->TextPrice;
                $textProductSKUID = $data->TextProductSKUID;
                $videoPrice = $data->VideoPrice;
                $videoProductSKUID = $data->VideoProductSKUID;
				$photoPrice = $data->PhotoPrice;
                $photoProductSKUID = $data->PhotoProductSKUID;
                $model = \backend\models\Setting::findOne(['ArtistID' => $artistID]);
                if ($model == "")
                {
                    $model = new \backend\models\Setting();
                    $model->ArtistID = $artistID;
                }

                if ($QAType != '')
                {
                    $model->IsQAEnable = 1;
                }
                else
                {
                    $model->IsQAEnable = 0;
                }
                $model->QaType = $QAType;
                $model->QAModuleName = $tabName;
                $model->TextPrice = $textPrice;
                $model->TextProductSKUID = $textProductSKUID;
                $model->VideoPrice = $videoPrice;
                $model->VideoProductSKUID = $videoProductSKUID;
				$model->PhotoPrice = $photoPrice;
                $model->PhotoProductSKUID = $photoProductSKUID;
                $model->save(false);

                /************** Get settings data ************/
                $modelSetting = new \backend\models\Setting();
                $bundleData = $modelSetting->find()->where(array(
                            'ArtistID' => $artistID))->asArray()->all();
                unset($bundleData[0]['Updated']);
                unset($bundleData[0]['UpdatedBy']);
                unset($bundleData[0]['Created']);
                unset($bundleData[0]['CreatedBy']);
                $resultMessage = _getStatusCodeMessageForUser(200);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(200);
                echo json_encode(['Status' => 1,
                    "Message" => $lngmsg,
                    'QAData' => $bundleData], JSON_PRETTY_PRINT);
            }
            else
            {
                $resultMessage = _getStatusCodeMessageForUser(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);    
        }
    }

    public function unreadQA($artistID) {
        /********** Unread QA Data **************/
        $logString  = "";
        try {
            $modelPost = new \backend\models\Post();
            $query = new \yii\db\Query;
            $query->select(['count(p.PostID) AS total'])
                                ->from('post AS p')
                                //->join('INNER JOIN','memberpost AS mp','p.post_id=mp.post_id')    
                                ->where(" p.MemberID NOT IN ( SELECT mp.MemberID FROM blockuser AS mp WHERE p.ArtistID=" . $artistID . ") AND p.ArtistID=" . $artistID . " AND p.PostType = 4 AND p.Reply IS NULL AND (p.QAIgnore IS NULL  OR p.QAIgnore='2') ");
            //$unreadQAData = $modelPost->find()->where("ArtistID=" . $artistID . " AND PostType = 4 AND Reply IS NULL AND (QAIgnore IS NULL  OR QAIgnore='2')  ")->asArray()->all();
            //return count($unreadQAData);
            $command = $query->createCommand();
            $unreadQAData = $command->queryAll();		
            return $unreadQAData[0]['total'];
        } catch (ErrorException $e) { 
            $this->addLog($logString,$e);
        }
    }

    public function actionCheckusername() {
        /************* check username if it is exists in database ***************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'UserName',
                'ArtistID',
                'UserType',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $userName = str_replace("'", "", $data->UserName);
                $artistID = $data->ArtistID;
                $userType = $data->UserType;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $command = $connection->createCommand("CALL Check_Username('" . $userName . "'," . $artistID . "," . $userType . ",'" . self::EncryptKey . "')");
                $logString.=" \n Check Username : CALL Check_Username('" . $userName . "'," . $artistID . "," . $userType . ",'" . self::EncryptKey . "')n";	
                $userData = $command->queryAll();
                $resultCode = $userData[0]['ErrorCode'];
                $resultMessage = _getStatusCodeMessageForUsername($resultCode);
                //$this->setHeader($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                if ($resultCode == 200)
                {
                    echo json_encode(['Status' => 1,
                        "Message" => $lngmsg], JSON_PRETTY_PRINT);
                }
                else
                {
                    echo json_encode(['Status' => 4,
                        "Message" => $lngmsg], JSON_PRETTY_PRINT);
                }
            }
            else
            {
                //$this->setHeader(502);
                $resultMessage = _getStatusCodeMessageForUsername(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e); 
        }
    }

    public function actionGetprofile() {
        /******************* Get login user profile data *****************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'UserID',
                'UserType',
                'ProfileID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $userID = $data->UserID;
                $profileID = $data->ProfileID;
                $userType = $data->UserType;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Member_GetProfile('" . $userID . "','" . $userType . "','" . $profileID . "','" . self::EncryptKey . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "')";
                $logString.="\n Member Get Profile : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $profileData = $command->queryAll();
                if (count($profileData) > 0)
                {
                    /********** Get image list *************/
                    if ($userType == "2")
                    {
                        $postimagesprocedure = "CALL Artist_Image_List(2,0," . $profileID . ",'" . self::S3BucketPath . "','" . self::S3BucketArtistImages . "','" . self::S3BucketArtistThumb . "','" . self::S3BucketArtistMedium . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                    }
                    else
                    {
                        $postimagesprocedure = "CALL Post_Image_List(2,0," . $profileID . ",'" . self::S3BucketPath . "','" . self::S3BucketArtistImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                    }
                    $logString.="\n Post image List : ".$postimagesprocedure.'\n';	
                    $commandForImage = $connection->createCommand($postimagesprocedure);
                    $imageData = $commandForImage->queryAll();
                    $profileData[0]['ArtistImage'] = $imageData;
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageGetProfile($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                if (isset($profileData[0]['DOB']) && ($profileData[0]['DOB'] == "01-01-1970" || $profileData[0]['DOB'] == "0000-00-00"))
                {
                    $profileData[0]['DOB'] = "";
                }
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
                    'Result' => $profileData], JSON_PRETTY_PRINT);
            }
            else
            {
                //$this->setHeader(502);
                $resultMessage = _getStatusCodeMessageGetProfile(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e); 
        }
    }

    public function actionForgotpassword() {
        /************* Get Reset password link *************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'Username',
                'UserType',
                'ArtistID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            if (count($compareField) == 0)
            {
                $username = str_replace("'", "", $data->Username);
                $artistID = $data->ArtistID;
                $userType = $data->UserType;
                $language = $data->Language;
                $appname = "Boom Video - Reset password link";
                if (isset($data->MailSubject) && $data->MailSubject != "")
                {
                    $appname = $data->MailSubject;
                }
                $connection = Yii::$app->db;
                $procedure = "CALL Forgot_Password('" . $userType . "','" . $username . "','" . $artistID . "','" . self::EncryptKey . "')";
                $logString.="\n Forgot Pasword : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $profileData = $command->queryAll();
                if (count($profileData) > 0)
                {
                    $resultCode = $profileData[0]['ErrorCode'];
                    $resultMessage = _getStatusCodeMessageForForgotPassword($resultCode);
                    if ($resultCode == "200")
                    {
                        $email = $profileData[0]['Email'];
                        $token = $profileData[0]['Token'];
                        $name = $profileData[0]['Name'];
                        $o_ArtistID = $profileData[0]['ArtistID'];
                        $getArtistData = new \backend\models\Artist();
                        if ($userType == 2) $artistname = $getArtistData->getArtistName($o_ArtistID);
                        else if ($userType == 3) $artistname = $getArtistData->getArtistName($artistID);
                        $link = self::Resetpasswordpage . $token . '&appname=' . $appname;
                        $getcontent = $getArtistData->getForgotemailtemplate($name, $artistname, $link);
                        /************ Send Email ***************/
                        if ($email != "")
                        {
                            Yii::$app->mailer->compose()
                                    ->setFrom(self::Fromemail)
                                    ->setTo($email)
                                    ->setSubject($appname)
                                    ->setHtmlBody($getcontent)
                                    ->send();
                        }
                        $status = "1";
                    }
                    else
                    {
                        $status = "0";
                        /* $mailmodel      =    new Sendmail();
                          echo $sendmail  =    $mailmodel->sendSesEmail("rosemary@e2logy.com","Test","","");die; */
                    }
                }
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
                    'Result' => $profileData], JSON_PRETTY_PRINT);
            }
            else
            {
                $resultMessage = _getStatusCodeMessageForUser(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public function actionLikepost() {
        /****************** Like particular post **********/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostID',
                'ArtistID',
                'ActivityTypeID',
                'ProfileID',
                'RefTable',
                'RefTableID',
                'Comment',
                'Language',
                'ActivityID',
                'UserType');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            if (count($compareField) == 0)
            {
                $postID = $data->PostID;
                $artistID = $data->ArtistID;
                $activityTypeID = $data->ActivityTypeID;
                $profileID = $data->ProfileID;
                $refTable = $data->RefTable;
                $refTableID = $data->RefTableID;
                $comment = $data->Comment;
                $activityID = $data->ActivityID;
                $userType = $data->UserType;
                $language = $data->Language;
                $connection = Yii::$app->db;
                /******** Add like activity data ***************/
                $procedure = "CALL Member_Add_Activity('" . $postID . "','" . $artistID . "','" . $profileID . "','" . $activityTypeID . "','" . $refTable . "','" . $refTableID . "','" . $comment . "','" . $activityID . "'," . $userType . ",0,'" . self::S3BucketPath . "','')";
                $logString.="\n Member Add Activity : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $activityData = $command->queryAll();
                if (count($activityData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageCommon($resultCode);
                //$this->setHeader($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "Result" => $activityData], JSON_PRETTY_PRINT);
            }
            else
            {
                //$this->setHeader(502);
                $resultMessage = _getStatusCodeMessageCommon(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(["Status" => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);  
        }
    }

    public function actionFlag() {
        /****************** Flag particular post **********/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostID',
                'ArtistID',
                'ActivityTypeID',
                'ProfileID',
                'RefTable',
                'RefTableID',
                'Language',
                'Comment',
                'ActivityID',
                'UserType');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $postID = $data->PostID;
                $artistID = $data->ArtistID;
                $activityTypeID = $data->ActivityTypeID;
                $userID = $data->ProfileID;
                $refTable = $data->RefTable;
                $refTableID = $data->RefTableID;
                $comment = $data->Comment;
                $activityID = $data->ActivityID;
                $userType = $data->UserType;
                $language = $data->Language;
                $connection = Yii::$app->db;
                /*************  Add flag activity data *****************/
                $procedure = "CALL Member_Add_Activity('" . $postID . "','" . $artistID . "','" . $userID . "','" . $activityTypeID . "','" . $refTable . "','" . $refTableID . "','" . $comment . "'," . $activityID . "," . $userType . ",0,'" . self::S3BucketPath . "','')";
                $logString.="\n Member Add Activity : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $activityData = $command->queryAll();
                if (count($activityData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageCommon($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "Result" => $activityData], JSON_PRETTY_PRINT);
            }
            else
            {
                //$this->setHeader(502);
                $resultMessage = _getStatusCodeMessageCommon(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(["Status" => 0,
                    "Message" => $lngmsg,
                    "Result" => $activityData], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e); 
        }
    }

    public
            function actionShare() {
        /****************** Share particular post **********/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostID',
                'ArtistID',
                'ActivityTypeID',
                'ProfileID',
                'Language',
                'UserType');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $postID = $data->PostID;
                $artistID = $data->ArtistID;
                $activityTypeID = $data->ActivityTypeID;
                $profileID = $data->ProfileID;
                $userType = $data->UserType;
                $language = $data->Language;
                $refTable = 1;
                $refTableID = $postID;
                $comment = "";
                $activityID = 0;
                $connection = Yii::$app->db;
                /*************** Add share activity data **************/
                $procedure = "CALL Member_Add_Activity('" . $postID . "','" . $artistID . "','" . $profileID . "','" . $activityTypeID . "','" . $refTable . "','" . $refTableID . "','" . $comment . "'," . $activityID . "," . $userType . ",0,'" . self::S3BucketPath . "','')";
                $logString.="\n Member Add Activity : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $activityData = $command->queryAll();
                if (count($activityData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageCommon($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(["Status" => 1,
                    "Message" => $lngmsg,
                    "Result" => $activityData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageCommon(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(["Status" => 0,
                    "Message" => $lngmsg,
                    "Result" => $activityData], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionLikecomment() {
        /*************** Like particular post *******************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostID',
                'ArtistID',
                'ActivityTypeID',
                'ProfileID',
                'RefTable',
                'Language',
                'RefTableID',
                'Comment',
                'ActivityID',
                'UserType');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            if (count($compareField) == 0)
            {
                $postID = $data->PostID;
                $artistID = $data->ArtistID;
                $activityTypeID = $data->ActivityTypeID;
                $userID = $data->ProfileID;
                $refTable = $data->RefTable;
                $refTableID = $data->RefTableID;
                $comment = str_replace("'", "", $data->Comment);
                $activityID = $data->ActivityID;
                $userType = $data->UserType;
                $language = $data->Language;
                $connection = Yii::$app->db;
                /************* Add like comment data ********************/
                $procedure = "CALL Member_Add_Activity('" . $postID . "','" . $artistID . "','" . $userID . "','" . $activityTypeID . "','" . $refTable . "','" . $refTableID . "','" . $comment . "'," . $activityID . "," . $userType . ",0,'" . self::S3BucketPath . "','')";
                $logString.="\n Member Add Activity : ".$procedure.'\n';	
                //echo $procedure; die;
                $command = $connection->createCommand($procedure);
                $activityData = $command->queryAll();
                if (count($activityData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageCommon($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "Result" => $activityData], JSON_PRETTY_PRINT);
            }
            else
            {
                //$this->setHeader(502);
                $resultMessage = _getStatusCodeMessageCommon(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    "Result" => $activityData], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionAddcomment() {
        /************** Add comment to the particular post **************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostID',
                'ArtistID',
                'ActivityTypeID',
                'ProfileID',
                'RefTable',
                'Language',
                'RefTableID',
                'Comment',
                'ActivityID',
                'UserType',
                'StickerID',
				'CustomStickerUrl');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $postID = $data->PostID;
                $artistID = $data->ArtistID;
                $activityTypeID = $data->ActivityTypeID;
                $userID = $data->ProfileID;
                $refTable = $data->RefTable;
                $refTableID = $data->RefTableID;
                $comment = $data->Comment;
                $activityID = $data->ActivityID;
                $userType = $data->UserType;
                $language = $data->Language;
                $stickerID = $data->StickerID;
				$customStickerUrl = null;//Daniele
				if(property_exists($data, 'CustomStickerUrl')){ $customStickerUrl = $data->CustomStickerUrl; }//Daniele
                $time = date("d M 'y H:i A");
                $connection = Yii::$app->db;
                /******* Add comment data to the activity table *****************/
                $procedure = "CALL Member_Add_Activity(:postID,:artistID,:userID,:activityTypeID,:refTable,:refTableID,:comment,:activityID,:userType,:stickerID,:buckerPath,:CustomStickerUrl)";
                $bindPostParams = [':postID' => $postID,':artistID'=>$artistID,':userID'=>$userID,':activityTypeID'=>$activityTypeID,':refTable'=>$refTable,':refTableID'=>$refTableID,':comment'=>$comment,':activityID'=>$activityID,':userType'=>$userType,':stickerID'=>$stickerID,':buckerPath'=>self::S3BucketPath,':CustomStickerUrl'=>$customStickerUrl];
                //echo $procedure; die;
                $logString.="\n Member Add Activity : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure)->bindValues($bindPostParams);
                $activityData = $command->queryAll();

                if (count($activityData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                    $totalcomments = 0;
                    $artistobj = new \backend\models\Artist();
                    $commentactivityid = $activityData[0]['PostCommentActivityID'];
                    $totalcomments = $activityData[0]['TotalComments'];
                    $gettokens = $artistobj->getAllDevicesForCommentPush($postID, $artistID, $commentactivityid);
                    $artistdevicetype = $artistobj->getArtistDeviceType($artistID);
                    $artistdevicetoken = $artistobj->getArtistDeviceToken($artistID);

                    /*                     * ************************ Push ********************* */
                    $memberObj = new \backend\models\Member();
                    $postObj = new \backend\models\Post();
                    //echo "asas"; die;
                    if ($userType == "2")
                    {
                        $memberID = $postObj->getMemberID($postID);
                    }
                    else if ($userType == "3")
                    {
                        $memberID = $userID;
                    }
                    if($memberID != "0" && $memberID!='') {
                        $memberDeviceType = $memberObj->getMemberDeviceType($memberID);
                        $memberDeviceToken = $memberObj->getMemberDeviceToken($memberID);
                        if ($memberDeviceType != "" && $memberDeviceToken != "" && $userType == "2")
                        {
                            $sendpushtoartist = new \api\models\Commentnotforartist();
                            $getmsgforartist = $artistobj->getNotificationMessageForComment($memberID, $artistID, $postID);
                            $sendpushtoartist->deviceToken = $memberDeviceToken;
                            $sendpushtoartist->message = $getmsgforartist;
                            $sendpushtoartist->time = $time;
                            $sendpushtoartist->postid = $postID;

                            $sendpushtoartist->NotificationType = "comment";
                            if ($memberDeviceType == 1)
                            {
                                try
                                {
                                    $sendpushtoartist->sendToIphone($artistID, 'user');
                                }
                                catch (Exception $e)
                                {
                                    echo "cat message" . $e->getMessage() . " \n";
                                }
                            } if ($memberDeviceType == 2)
                            {
                                $sendpushtoartist->sendToAndroid($artistID, 'user');
                            }
                        }
                    }    
                    /*                     * ***************************************************** */
                    /* if($userType == "3")
                      {
                      if($artistdevicetype != "" && $artistdevicetoken != "")
                      {
                      $sendpushtoartist       =   new \api\models\Commentnotforartist();
                      $getmsgforartist        =   $artistobj->getNotificationMessageForArtist($userType,$userID,$artistID,$postID);

                      $sendpushtoartist->deviceToken  =    $artistdevicetoken;
                      $sendpushtoartist->message      =    $getmsgforartist;
                      $sendpushtoartist->time         =    $time;
                      $sendpushtoartist->postid       =    $postID;
                      $sendpushtoartist->NotificationType       =    "comment";

                      if($artistdevicetype == 1)
                      {
                      try
                      {
                      $sendpushtoartist->sendToIphone($artistID,'artist');
                      }
                      catch(Exception $e)
                      {
                      echo "cat message".$e->getMessage()." \n";
                      }
                      }
                      if($artistdevicetype == 2)
                      {
                      $sendpushtoartist->sendToAndroid($artistID,'artist');
                      }
                      }
                      }
                      if(count($gettokens) > 0)
                      {
                      $sendpushtousers        =   new \api\models\Commentnotforuser();
                      $androidtokens          =   array();
                      $iostokens              =   array();
                      foreach($gettokens as $key=>$val)
                      {
                      if($val['MemberDeviceType'] == "1")
                      {
                      $iostokens[]        =   $val['MemberDeviceToken'];
                      }
                      if($val['MemberDeviceType'] == "2")
                      {
                      $androidtokens[]    =   $val['MemberDeviceToken'];
                      }
                      }
                      $message            =   $artistobj->getNotificationMessage($userType,$userID,$artistID,$postID);

                      if(count($iostokens) > 0)
                      {
                      try
                      {
                      $sendpushtousers->sendToIphone($iostokens,$message,$time,$postID,$totalcomments,$artistID);
                      }
                      catch(Exception $e)
                      {
                      echo "cat message".$e->getMessage()." \n";
                      }
                      }
                      else if(count($androidtokens) > 0)
                      {
                      $sendpushtousers->sendToAndroid($androidtokens,$message,$time,$postID,$totalcomments,$artistID);
                      }
                      } */
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageCommon($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
                    "Result" => $activityData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageCommon(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    "Result" => $activityData], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionSignup() {
        /************** Member Signup *****************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';   
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ArtistID',
                'Name',
                'Username',
                'Email',
                'Password',
                'Language',
                'ComID'); // Boom Native app--Kate
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $artistID = $data->ArtistID;
                $name = str_replace("'", "", $data->Name);
                $username = str_replace("'", "", $data->Username);
                $email = $data->Email;
                if (isset($data->Birthdate) && $data->Birthdate != "")
                {
                    $birthDate = date("d-m-Y", strtotime($data->Birthdate));
                }
                else
                {
                    $birthDate = "0000-00-00";
                }
                if (isset($data->Gender) && $data->Gender != "")
                {
                    $gender = $data->Gender;
                }
                else
                {
                    $gender = "3";
                }
                if (isset($data->DeviceType) && $data->DeviceType != "")
                {
                    $devicetype = $data->DeviceType;
                }
                else
                {
                    $devicetype = "";
                }
                if (isset($data->Zipcode) && $data->Zipcode != "")
                {
                    $zipCode = $data->Zipcode;
                }
                else
                {
                    $zipCode = "";
                }
                if (isset($data->Mobile) && $data->Mobile != "")
                {
                    $mobile = $data->Mobile;
                }
                else
                {
                    $mobile = "";
                }

                if (isset($data->Image) && $data->Image != "")
                {
                    $image = $data->Image;
                }
                else
                {
                    $image = "";
                }
                if (isset($data->MailSubject) && $data->MailSubject != "")
                {
                    $subject = $data->MailSubject;
                }
                else
                {
                    $subject = "Boom Fan App";
                }
                // Boom Native app--Kate
                if(isset($data->ComID)){
                    $ComID=$data->ComID;
                }else{
                    $ComID=0;
                }
                $password = $data->Password;
                $language = $data->Language;
                $connection = Yii::$app->db;
                //echo "CALL Member_Registration('".$name."','".$image."','".$email."','".$birthDate."','".$zipCode."','".$mobile."','".$gender."','".$artistID."','".$username."','".$devicetype."','".self::EncryptKey."','".$password."')"; die;
                /************** Registr Member ****************/
                $procedure="CALL Member_Registration_N('" . $name . "','" . $image . "','" . $email . "','" . $birthDate . "','" . $zipCode . "','" . $mobile . "','" . $gender . "','" . $artistID . "','" . $username . "','" . $devicetype . "','" . self::EncryptKey . "','" . $password . "', " . $ComID . ")";
                $command = $connection->createCommand($procedure);
                $logString.="\n ".$procedure;    
                $registrationData = $command->queryAll();
                $resultCode = $registrationData[0]['ErrorCode'];
                $resultMessage = $registrationData[0]['Message'];

                //$this->setHeader($resultCode);

                if ($resultCode == 200)
                {
                    $activationcode = $registrationData[0]['ActivationCode'];
                    if ($email != "")
                    {
                    }
                    \Yii::$app->language = $language;
                    $lngmsg = \Yii::t('api', $resultMessage);
                    echo json_encode(['Status' => 1,
                        "Message" => $lngmsg,
                        'Result' => $registrationData], JSON_PRETTY_PRINT);
                }
                else
                {
                    \Yii::$app->language = $language;
                    $lngmsg = \Yii::t('api', $resultMessage);
                    $this->setHeader(400);
                    echo json_encode(['Status' => 0,
                        "Message" => $lngmsg,
                        'Result' => $registrationData], JSON_PRETTY_PRINT);
                }
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageForUserRegistration(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public function actionEditprofile() {
        /*************** Update profile ********************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'UserID',
                'ProfileID',
                'ArtistID',
                'Name',
                'Email',
                'Password',
                'ShowNotification',
                'Language',
                'Mobile',
                'Password',
                'Image');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY);
            if (count($compareField) == 0)
            {
                $userID = $data->UserID;
                $profileID = $data->ProfileID;
                $artistID = $data->ArtistID;
                $name = str_replace("'", "", $data->Name);
                $email = $data->Email;
                if (isset($data->Birthdate) && $data->Birthdate != "")
                {
                    $birthDate = date("d-m-Y", strtotime($data->Birthdate));
                }
                else
                {
                    $birthDate = "0000-00-00";
                }
                if (isset($data->Gender) && $data->Gender != "")
                {
                    $gender = $data->Gender;
                }
                else
                {
                    $gender = "3";
                }
                if (isset($data->DeviceType) && $data->DeviceType != "")
                {
                    $devicetype = $data->DeviceType;
                }
                else
                {
                    $devicetype = "";
                }
                if (isset($data->Zipcode) && $data->Zipcode != "")
                {
                    $zipCode = $data->Zipcode;
                }
                else
                {
                    $zipCode = "";
                }
                $mobile = $data->Mobile;
                $password = $data->Password;
                $image = $data->Image;
                $language = $data->Language;
                $showNotification = $data->ShowNotification;
                $connection = Yii::$app->db;
                $procedure = "CALL Member_Edit_Profile_new('" . $name . "','" . $image . "','" . $email . "','" . $password . "','" . $birthDate . "','" . $zipCode . "','" . $mobile . "','" . $gender . "','" . $artistID . "','" . $profileID . "','" . $userID . "'," . $showNotification . ",'" . self::EncryptKey . "')";
                $logString.="\n Member Edit Profile : ".$procedure.'\n';	
                //echo $procedure; die;
                $command = $connection->createCommand($procedure);
                $registrationData = $command->queryAll();
                if ($image != "")
                {
                    /*********** Save Image **************/
                    $content = file_get_contents(self::S3BucketAbsolutePath . '/' . self::BoomFolder . $artistID . self::S3BucketProfileImages . $image);
                    $commonthumb = self::Documentroot . self::Project . self::Uploads . self::Postlarge . $image;
                    $createthumb = self::Documentroot . self::Project . self::Uploads . self::Postthumb . $image;
                    $createmedium = self::Documentroot . self::Project . self::Uploads . self::Postmedium . $image;
                    file_put_contents($commonthumb, $content);

                    $thumbimage = Yii::$app->image->load($commonthumb);
                    $thumbimage->resize("", self::Thumbheight);
                    $thumbimage->save($createmedium, 100);

                    $mediumimage = Yii::$app->image->load($commonthumb);
                    $mediumimage->resize("", self::Mediumheight);
                    $mediumimage->save($createthumb, 100);

                    \S3::putObjectFile($createthumb, self::S3Bucket, self::BoomFolder . $artistID . self::S3BucketProfileThumbImages . $image, \S3::ACL_PUBLIC_READ);
                    \S3::putObjectFile($createmedium, self::S3Bucket, self::BoomFolder . $artistID . self::S3BucketProfileMediumImages . $image, \S3::ACL_PUBLIC_READ);
                }
                $resultCode = $registrationData[0]['ErrorCode'];
                $resultMessage = _getStatusCodeMessageForEditprofile($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                if ($resultCode == 200)
                {
                    echo json_encode(['Status' => 1,
                        "Message" => $lngmsg,
                        'Result' => $registrationData], JSON_PRETTY_PRINT);
                }
                else
                {
                    echo json_encode(['Status' => 0,
                        "Message" => $lngmsg,
                        'Result' => $registrationData], JSON_PRETTY_PRINT);
                }
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageForUserRegistration(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);  
        }
    }

    public
            function actionArtisteditprofile() {
        /******************** Artist update profile *******************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ArtistID',
                'UserID',
                'ArtistName',
                'ProfileThumb',
                'Email',
                'Birthdate',
                'Nationality',
                'Residence',
                'Website',
                'YouTubeChannelName',
                'YearsActive',
                'Genre',
                'AboutMe',
                'YouTubePageUrl',
                'InstagramPageUrl',
                'TwitterPageUrl',
                'FacebookPageUrl',
                'Language',
                'DeleteMediaIDs',
                'ArtistImages',
                'Password');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY);
            if (count($compareField) == 0)
            {
                $artistID = $data->ArtistID;
                $userID = $data->UserID;
                $artistName = str_replace("'", "", $data->ArtistName);
                $profileThumb = $data->ProfileThumb;
                $email = $data->Email;
                $birthDate = date("d-m-Y", strtotime($data->Birthdate));
                $nationality = str_replace("'", "", $data->Nationality);
                $residence = str_replace("'", "", $data->Residence);
                $website = $data->Website;
                $youTubeChannelName = $data->YouTubeChannelName;
                $yearsActive = $data->YearsActive;
                $genre = $data->Genre;
                $aboutMe = str_replace("'", "", $data->AboutMe);
                $youTubePageUrl = $data->YouTubePageUrl;
                $instagramPageUrl = $data->InstagramPageUrl;
                $twitterPageUrl = $data->TwitterPageUrl;
                $facebookPageUrl = $data->FacebookPageUrl;
                $deleteMediaIDs = $data->DeleteMediaIDs;
                $artistImages = $data->ArtistImages;
                $password = $data->Password;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Artist_Edit_Profile(" . $artistID . "," . $userID . ",'" . $artistName . "','" . $profileThumb . "',
				       '" . $email . "','" . $birthDate . "','" . $nationality . "','" . $residence . "','" . $website . "','" . $youTubeChannelName . "',
				       '" . $yearsActive . "','" . $genre . "','" . str_replace("'", "", $aboutMe) . "','" . $youTubePageUrl . "','" . $instagramPageUrl . "',
				       '" . $twitterPageUrl . "','" . $facebookPageUrl . "','" . $password . "','" . self::EncryptKey . "')";
                $logString.="\n Artist Edit Profile : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $editData = $command->queryAll();
                if ($profileThumb != "")
                {
                    /*********** Save Image **************/
                    $content = file_get_contents(self::S3BucketAbsolutePath . '/' . self::BoomFolder . $artistID . self::S3BucketProfileImages . $profileThumb);
                    $commonthumb = self::Documentroot . self::Project . self::Uploads . self::Postlarge . $profileThumb;
                    $createthumb = self::Documentroot . self::Project . self::Uploads . self::Postthumb . $profileThumb;
                    $createmedium = self::Documentroot . self::Project . self::Uploads . self::Postmedium . $profileThumb;
                    file_put_contents($commonthumb, $content);

                    $thumbimage = Yii::$app->image->load($commonthumb);
                    $thumbimage->resize("", self::Thumbheight);
                    $thumbimage->save($createmedium, 100);

                    $mediumimage = Yii::$app->image->load($commonthumb);
                    $mediumimage->resize("", self::Mediumheight);
                    $mediumimage->save($createthumb, 100);

                    \S3::putObjectFile($createthumb, self::S3Bucket, self::BoomFolder . $artistID . self::S3BucketProfileThumbImages . $profileThumb, \S3::ACL_PUBLIC_READ);
                    \S3::putObjectFile($createmedium, self::S3Bucket, self::BoomFolder . $artistID . self::S3BucketProfileMediumImages . $profileThumb, \S3::ACL_PUBLIC_READ);
                }
                $resultCode = $editData[0]['ErrorCode'];
                $resultMessage = _getStatusCodeMessageForEditprofile($resultCode);
                $this->setHeader(400);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                if ($resultCode == 200)
                {
                    if (isset($deleteMediaIDs) && count($deleteMediaIDs) > 0)
                    {
                        /******** Delete image *************/
                        foreach ($deleteMediaIDs as $val)
                        {
                            $delProc = "CALL Artist_Delete_Images(" . $val . ",@o_ErrorCode)";
                            $delCmnd = $connection->createCommand($delProc)->execute();
                        }
                    }
                    if (isset($artistImages) && count($artistImages) > 0)
                    {
                        foreach ($artistImages as $keyval)
                        {
                            /* $content = file_get_contents(self::S3BucketAbsolutePath.'/'.self::BoomFolder.$artistID.self::S3BucketArtistImages.$keyval);
                              $commonthumb = self::Documentroot.self::Project.self::Uploads.self::Postlarge.$keyval;
                              $createthumb = self::Documentroot.self::Project.self::Uploads.self::Postthumb.$keyval;
                              $createmedium = self::Documentroot.self::Project.self::Uploads.self::Postmedium.$keyval;
                              file_put_contents($commonthumb, $content);

                              $thumbimage=Yii::$app->image->load($commonthumb);
                              $thumbimage->resize("",self::Thumbheight);
                              $thumbimage->save($createmedium,100);

                              $mediumimage=Yii::$app->image->load($commonthumb);
                              $mediumimage->resize("",self::Mediumheight);
                              $mediumimage->save($createthumb,100);

                              \S3::putObjectFile($createthumb,self::S3Bucket,self::BoomFolder.$artistID.self::S3BucketArtistThumb.$keyval, \S3::ACL_PUBLIC_READ);
                              \S3::putObjectFile($createmedium,self::S3Bucket,self::BoomFolder.$artistID.self::S3BucketArtistMedium.$keyval, \S3::ACL_PUBLIC_READ); */
                            $addProc = "CALL Artist_Edit_Images(" . $artistID . ",'" . $keyval . "')";
                            $addCmnd = $connection->createCommand($addProc)->execute();
                        }
                    }
                    echo json_encode(['Status' => 1,
                        "Message" => $lngmsg,
                        'Result' => $editData], JSON_PRETTY_PRINT);
                }
                else
                {
                    echo json_encode(['Status' => 0,
                        "Message" => $lngmsg,
                        'Result' => $editData], JSON_PRETTY_PRINT);
                }
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageForUserRegistration(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }
    
    public
            function actionNewcommentlist() {
        /********* New comment list ***********/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostID',
                'ArtistID',
                'ProfileID',
                'UserType',
                'PageIndex',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $postID = $data->PostID;
                $artistID = $data->ArtistID;
                $profileID = $data->ProfileID;
                $userType = $data->UserType;
                $pageindex = $data->PageIndex;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Ver2_Post_CommentList(" . $postID . "," . $artistID . "," . $profileID . "," . $userType . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "','" . self::S3BucketStickersSmall . "','" . self::S3BucketStickersMedium . "',200," . $pageindex . ",@o_RecCount)";
                //echo $procedure; die;
                $logString.="\n Member Post Comment List : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $commentData = $command->queryAll();
                $reccommand = $connection->createCommand('SELECT @o_RecCount')->queryOne();
                $recordcnt = $reccommand['@o_RecCount'];
                if (count($commentData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageCommentList($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
                    "RecordCount" => $recordcnt,
                    "Result" => $commentData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageCommentList(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionCommentlist() {
        /************** Comment list this is for old ver ****************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostID',
                'ArtistID',
                'ProfileID',
                'UserType',
                'PageIndex',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $postID = $data->PostID;
                $artistID = $data->ArtistID;
                $profileID = $data->ProfileID;
                $userType = $data->UserType;
                $pageindex = $data->PageIndex;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Post_CommentList(" . $postID . "," . $artistID . "," . $profileID . "," . $userType . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "','" . self::S3BucketStickersSmall . "','" . self::S3BucketStickersMedium . "'," . self::Limit . "," . $pageindex . ",@o_RecCount)";
                //echo $procedure; die;
                $logString.="\n Member Post Comment List : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $commentData = $command->queryAll();
                $reccommand = $connection->createCommand('SELECT @o_RecCount')->queryOne();
                $recordcnt = $reccommand['@o_RecCount'];
                if (count($commentData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageCommentList($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
                    "RecordCount" => $recordcnt,
                    "Result" => $commentData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageCommentList(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }
    
    public function actionDeletecomment() {
        /************** Delete comment **********/
        $logString = "";
        try {    
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ActivityID',
                'PostID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if(count($compareField) == 0)
            {
                $activityID = $data->ActivityID;
                $postID = $data->PostID;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Delete_Comment(".$activityID.",".$postID.",@o_ErrorCode)";
                $logString.="\n Delete Post Comment : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure)->execute();
                $reccommand = $connection->createCommand('SELECT @o_ErrorCode')->queryOne();
                $statusCode = $reccommand['@o_ErrorCode'];
                if ($statusCode == 200)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageCommentList($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => $status,"Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
            else
            {
                echo "sdsd"; die;
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageCommentList(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }     
        } catch (ErrorException $e) { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionLatestcommentlist() {
        /********** Latest commment list this for latest ver **********/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostID',
                'ArtistID',
                'ProfileID',
                'UserType');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $postID = $data->PostID;
                $artistID = $data->ArtistID;
                $profileID = $data->ProfileID;
                $userType = $data->UserType;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $latestcmntsproc = "CALL Latest_Post_CommentList(" . $postID . "," . $artistID . "," . $profileID . "," . $userType . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "','" . self::S3BucketStickersSmall . "','" . self::S3BucketStickersMedium . "')";
                $logString.="\n Latest Post Comment List : ".$latestcmntsproc.'\n';	
                $command = $connection->createCommand($latestcmntsproc);
                $commentData = $command->queryAll();

                if (count($commentData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageCommentList($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
                    "Result" => $commentData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageCommentList(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionAddfeed() {
        /**************  Add feed ************/
        $logString  = "";
        try
        {
            $xml = "http://boom.boomvideo.tv/alpha/test/vast/rss_feed.php?PGUID=37ff8226-25ad-4b55-814a-75316b9cdefc";
            $userModel = new User;
            $items = $userModel->xmlparsing($xml);
            $xmltitle = '';
            $xmldesc = '';
            $xmlmediatitle = '';
            $xmlmediathumb = '';
            $xmlcontenturl = '';
            if (isset($items[0]['title']) && $items[0]['title'] != '') $xmltitle = $items[0]['title'];
            if (isset($items[0]['description']) && $items[0]['description'] != '') $xmldesc = $items[0]['description'];
            if (isset($items[0]['media:title']) && $items[0]['media:title'] != '') $xmlmediatitle = $items[0]['media:title'];
            if (isset($items[0]['media:thumbnail']['url']) && $items[0]['media:thumbnail']['url'] != '') $xmlmediathumb = $items[0]['media:thumbnail']['url'];
            if (isset($items[0]['media:content']['url']) && $items[0]['media:content']['url'] != '') $xmlcontenturl = $items[0]['media:content']['url'];
            $modelRssFeed = new Rssfeed;
            $modelRssFeed->title = $xmltitle;
            $modelRssFeed->description = $xmldesc;
            $modelRssFeed->media_title = $xmlmediatitle;
            $modelRssFeed->media_thumbnail = $xmlmediathumb;
            $modelRssFeed->media_content = $xmlcontenturl;
            $modelRssFeed->save();
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionPostlist() {
        /****************** Get post list ***************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'UserID',
                'ArtistID',
                'ProfileID',
                'IsExclusive',
                'PageIndex',
                'UserType',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {

                $postData = array();
                if(isset($data->UserID))
                    $UserID = $data->UserID;
                else
                    $UserID =0;
                $profileID = $data->ProfileID;
                $artistID = $data->ArtistID;
                $isExclusive = $data->IsExclusive;
                $pageindex = $data->PageIndex;
                $usertype = $data->UserType;
                $language = $data->Language;
				$categoryID = -1;
				if(isset($data->CategoryID)){ $categoryID = $data->CategoryID; }
                if(isset($data->ComID)){ $ComID=$data->ComID;$isExclusive =1;} // Boom Native app--Kate
                else $ComID=0;
                $connection = Yii::$app->db;

                // //boom Native App --Kate
                // if($artistID==0 && $ComID!=0){ // Boom Native app--Kate
                //     $artists=UserArtist::find()->where(['UserID' => $UserID])->all();
                //     if(count($artists)>0) {
                //         $artistID=$artists[0]['ArtistID'];
                //         $isExclusive = 1;
                //     }else $artistID=0;                    
                // }
                // //added by Kate--deeplinking
                // $procedure = "CALL Post_List(0, " . $artistID . "," . $UserID . "," . $profileID . "," . $isExclusive . ",".$categoryID.",'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "','','',@o_RecCount)";//Daniele
                
                $procedure = "CALL Post_List_N(0, " . $artistID . "," . $UserID . "," . $profileID . "," . $isExclusive . ",".$categoryID.",'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "','','',@o_RecCount)";//Daniele
                $logString.="\n Post Image List : ".$procedure.'\n';    

                $command = $connection->createCommand($procedure);
                $postData = $command->queryAll();
                $reccommand = $connection->createCommand('SELECT @o_RecCount')->queryOne();
                $recordcnt = $reccommand['@o_RecCount'];
                foreach ($postData as $key => $value)
                {
                    if (isset($value['PostID']) && $value['PostID'] != '')
                    {

                        $imageData = array();
                        $postID = $value['PostID'];
                        /************* Get Post images **************/
                        $postimageproc = "CALL Post_Image_List(1," . $postID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketPostImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $logString.="\n Post Image List : ".$postimageproc.'\n';	
                        //echo $postimageproc; die;
                        $commandForImage = $connection->createCommand($postimageproc);
                        $imageData = $commandForImage->queryAll();

                        $latestcmntsproc = "CALL Latest_Post_CommentList(" . $postID . "," . $artistID . "," . $profileID . "," . $usertype . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "','" . self::S3BucketStickersSmall . "','" . self::S3BucketStickersMedium . "')";
                        $logString.="\n Latest Post Comment List : ".$latestcmntsproc.'\n';	
                        $commandForCmnts = $connection->createCommand($latestcmntsproc);
                        $cmntsData = $commandForCmnts->queryAll();


                        if (count($imageData) > 0)
                        {
                            $postData[$key]['PostImage'] = $imageData;
                        }
                        else
                        {
                            $postData[$key]['PostImage'] = $imageData;
                        }
                        /*if (count($cmntsData) > 0)
                        {
                            $postData[$key]['LatestComments'] = $cmntsData;
                        }
                        else
                        {
                            $postData[$key]['LatestComments'] = array();
                        }*/
                        $postData[$key]['LatestComments'] = array();
                    }
                }
				
				//added by Kate--Native
                if($ComID==0){ //social posts will ONLY for fan App Not native App
    				//Added by Daniele: append Instagram & Blog images from RSS feed: TEST
    				$artist = Artist::find()->where(['ArtistId' => $artistID])->one();
    				$instaUrl = $artist->InstagramPageURL;
    				$blogUrl = $artist->BlogFeedUrl;
    				$Feeds = null;
    				$instaFeeds = null;
    				
    				if($artist->SocialPostEnabled){
    					//Instagram feed: extract username and generates feed using rssbridge service
    					if($instaUrl != null && $instaUrl != "" && $isExclusive != "1"){
    								
    						$spliurl = explode('/', $instaUrl);
    						$instaUserName = "";
    						if($spliurl[count($spliurl)-1] != ""){ $instaUserName = $spliurl[count($spliurl)-1];}else{ $instaUserName = $spliurl[count($spliurl)-2];}
    								
    								
    						if($instaUserName != ""){
    							$instaFeeds = $this->getInstagramFeeds('http://rssbridge.buddylist.co/?action=display&bridge=InstagramBridge&u='.$instaUserName.'&format=MrssFormat', $artistID);
    						}
    								
    					}		
    					//Blog feed: get feed url from database and get feed
    					if($blogUrl != null && $blogUrl != "" && $isExclusive != "1"){
    						$Feeds = $this->getBlogFeeds($blogUrl, $artistID);
    					}
    					
    					//Added by Daniele: append instagram and blog feeds to $postData if available
    					//feeds are appended at the end of the array(last page considering page index) or after of the last of the normal posts, in same page
    					/*if(($recordcnt/self::Limit) < ($pageindex-1) && ($instaFeeds != null || $Feeds != null)){
    						$postData = array();
    					}*/
    					
    					if($instaFeeds != null){
    						$recordcnt += count($instaFeeds);
    						foreach ($instaFeeds as $instaEl){
    							//if(count($postData) < (self::Limit)){
    								array_push($postData, $instaEl);
    							//}
    						}
    					}
    					if($Feeds != null){
    						$recordcnt += count($Feeds);
    						foreach ($Feeds as $feedEl){
    							//if(count($postData) < (self::Limit)){
    								array_push($postData, $feedEl);
    							//}
    						}
    					}
    				}
    				
    				//Sort based on DateTime
    				usort($postData, function($a, $b)
    				{
    					$ao = get_object_vars((object)$a);
    					$bo = get_object_vars((object)$b);
    					$date1=$ao['DateTime']; $date2=$bo['DateTime'];
    					
    					return strtotime($date1)<strtotime($date2);
    				});

                    //If 24h flag enabled, only the last 24h posts are filtered
                    if($artist->Display24h){
                        $curDate = time();
                        $startDate = date('Y-m-d H:i:s', strtotime('-1 day', $curDate));
                        $postData24 = array();
                        foreach ($postData as $post){
                            $date;
                            if(is_array($post)){
                                $date = $post['DateTime'];
                            }else{
                                $date = $post->DateTime;
                            }
                            if($date > $startDate){
                                array_push($postData24, $post);
                            }else{ break;}
                        }
                        $postData = $postData24;
                        $recordcnt = count($postData);
                    }

                }
				

				
				
				//Re-calculate paging
				$stndx = 0;
				$maxpage = ceil($recordcnt / self::Limit);
				if($pageindex > $maxpage){ $pageindex = $maxpage;}
				$stndx = ($pageindex-1) * self::Limit;
				$postData = array_slice($postData, $stndx, self::Limit);
				
				
				//Daniele
				
				
				
				
				
                /* $procedure3              =	"CALL AddQueryLog(0,0,'Post_List- postData','".print_r($postData,true)."')";
                  $command3                =	$connection->createCommand($procedure3);
                  $querylog3               = 	$command3->execute(); */
                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessagePostList($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $rssaray = array();
                $i = 0;
                $j = 0;
                /*                 * ******************************* Unread QA **************************** */
                $unreadQAData = $this->unreadQA($artistID);
                /*                 * ********************************************************************************** */
                if ($usertype == "3")
                {
                    $this->setHeader(400);
                    //$recordcnt = count($postData);
                    $encodedArray = array(
                        'Status' => $status,
                        "Message" => $lngmsg,
                        "RecordCount" => $recordcnt,
                        "Result" => $postData,
                        'UnreadQA' => $unreadQAData);
                    
                    $encodedArray = str_replace("\\", "\\\\", $encodedArray);
                    echo json_encode($encodedArray, JSON_PRETTY_PRINT); //JSON_PRETTY_PRINT
                }
                else
                {
                    echo json_encode(['Status' => $status,
                        "Message" => $lngmsg,
                        "RecordCount" => $recordcnt,
                        "Result" => $postData,
                        'UnreadQA' => $unreadQAData], JSON_PRETTY_PRINT);
                }
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessagePostList(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    "RecordCount" => "0"], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
            echo $logString;
        }
    }
    
    public function actionAllpostlist() {
        /************* this is for testing purpose *************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'UserID',
                'ArtistID',
                'ProfileID',
                'IsExclusive',
                'PageIndex',
                'UserType',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $postData = array();
                $UserID = $data->UserID;
                $profileID = $data->ProfileID;
                $artistID = $data->ArtistID;
                $isExclusive = $data->IsExclusive;
                $pageindex = $data->PageIndex;
                $usertype = $data->UserType;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL AllPost_List(" . $artistID . "," . $UserID . "," . $profileID . "," . $isExclusive . ",'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "'," . self::Limit . "," . $pageindex . ",@o_RecCount)";
                //echo $procedure; die;
                $command = $connection->createCommand($procedure);
                $postData = $command->queryAll();
                $reccommand = $connection->createCommand('SELECT @o_RecCount')->queryOne();
                $recordcnt = $reccommand['@o_RecCount'];
                foreach ($postData as $key => $value)
                {
                    if (isset($value['PostID']) && $value['PostID'] != '')
                    {

                        $imageData = array();
                        $postID = $value['PostID'];
                        $postimageproc = "CALL Post_Image_List(1," . $postID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketPostImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $logString.="\n Post Image List : ".$postimageproc.'\n';	
                        //echo $postimageproc; die;
                        $commandForImage = $connection->createCommand($postimageproc);
                        $imageData = $commandForImage->queryAll();

                        /*$latestcmntsproc = "CALL Latest_Post_CommentList(" . $postID . "," . $artistID . "," . $profileID . "," . $usertype . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "','" . self::S3BucketStickersSmall . "','" . self::S3BucketStickersMedium . "')";
                        $logString.="\n Latest Post Comment List : ".$latestcmntsproc.'\n';	
                        $commandForCmnts = $connection->createCommand($latestcmntsproc);
                        $cmntsData = $commandForCmnts->queryAll();*/


                        if (count($imageData) > 0)
                        {
                            $postData[$key]['PostImage'] = $imageData;
                        }
                        else
                        {
                            $postData[$key]['PostImage'] = $imageData;
                        }
                        /*if (count($cmntsData) > 0)
                        {
                            $postData[$key]['LatestComments'] = $cmntsData;
                        }
                        else
                        {
                            $postData[$key]['LatestComments'] = array();
                        }*/
                        $postData[$key]['LatestComments'] = array();
                    }
                }
                /* $procedure3              =	"CALL AddQueryLog(0,0,'Post_List- postData','".print_r($postData,true)."')";
                  $command3                =	$connection->createCommand($procedure3);
                  $querylog3               = 	$command3->execute(); */
                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessagePostList($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $rssaray = array();
                $i = 0;
                $j = 0;
                /*                 * ******************************* Unread QA **************************** */
                $unreadQAData = $this->unreadQA($artistID);
                /*                 * ********************************************************************************** */
                if ($usertype == "3")
                {
                    $this->setHeader(400);
                    //$recordcnt = count($postData);
                    $encodedArray = array(
                        'Status' => $status,
                        "Message" => $lngmsg,
                        "RecordCount" => $recordcnt,
                        "Result" => $postData,
                        'UnreadQA' => $unreadQAData);
                    
                    $encodedArray = str_replace("\\", "\\\\", $encodedArray);
                    echo json_encode($encodedArray, JSON_PRETTY_PRINT); //JSON_PRETTY_PRINT
                }
                else
                {
                    echo json_encode(['Status' => $status,
                        "Message" => $lngmsg,
                        "RecordCount" => $recordcnt,
                        "Result" => $postData,
                        'UnreadQA' => $unreadQAData], JSON_PRETTY_PRINT);
                }
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessagePostList(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    "RecordCount" => "0"], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionQuestionanswerlist() {
        /*****************  Get QA list ***************/
        $logString  = "";
        try
        {

            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'UserID',
                'ArtistID',
                'ProfileID',
                'PageIndex',
                'UserType',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $postData = array();
                $UserID = $data->UserID;
                $profileID = $data->ProfileID;
                $artistID = $data->ArtistID;
                $pageindex = $data->PageIndex;
                $usertype = $data->UserType;
                $language = $data->Language;
				$unanswered = -1;
				$MemberID = -1;
				if (isset($data->Unanswered)){$unanswered = $data->Unanswered;}
				if (isset($data->MemberID) && $usertype == 2){$MemberID = $data->MemberID;}
                $connection = Yii::$app->db;
                $procedure = "CALL Question_Answer_List(" . $artistID . "," . $UserID . "," . $profileID . "," . $MemberID . ",'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "'," . $unanswered . "," . self::Limit . "," . $pageindex . ",@o_RecCount)";
                $logString.="\n Question Answer List : ".$procedure.'\n';	
                //echo $procedure; die;
                $command = $connection->createCommand($procedure);
                $postData = $command->queryAll();
                $reccommand = $connection->createCommand('SELECT @o_RecCount')->queryOne();
                $recordcnt = $reccommand['@o_RecCount'];
                foreach ($postData as $key => $value)
                {
                    if (isset($value['PostID']) && $value['PostID'] != '')
                    {

                        $imageData = array();
                        $postID = $value['PostID'];
                        /************* Get post images list **************/
                        $postimageproc = "CALL Post_Image_List(1," . $postID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketPostImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $commandForImage = $connection->createCommand($postimageproc);
                        $imageData = $commandForImage->queryAll();

                        /*************** GEt Latest post comment list ***********/
                        $latestcmntsproc = "CALL Latest_Post_CommentList(" . $postID . "," . $artistID . "," . $profileID . "," . $usertype . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "','" . self::S3BucketStickersSmall . "','" . self::S3BucketStickersMedium . "')";
                        $commandForCmnts = $connection->createCommand($latestcmntsproc);
                        $cmntsData = $commandForCmnts->queryAll();


                        if (count($imageData) > 0)
                        {
                            $postData[$key]['PostImage'] = $imageData;
                        }
                        else
                        {
                            $postData[$key]['PostImage'] = $imageData;
                        }
                        if (count($cmntsData) > 0)
                        {
                            $postData[$key]['LatestComments'] = $cmntsData;
                        }
                        else
                        {
                            $postData[$key]['LatestComments'] = array();
                        }
                    }
                }
                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                /*                 * ******************************* Unread QA **************************** */
                $unreadQAData = $this->unreadQA($artistID);
                /*                 * ********************************************************************************** */
                $resultMessage = _getStatusCodeMessagePostList($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $rssaray = array();
                $i = 0;
                $j = 0;
                $isBlocked = "";
                if ($usertype == "3")
                {
                    $userBlocked = $connection->createCommand('CALL Check_Member_Blocked(' . $profileID . ',' . $artistID . ')')->queryAll();
                    $isBlocked = $userBlocked[0]['v_ErrorCode'];
                    $this->setHeader(400);
                    $recordcnt = count($postData);
                    $encodedArray = array(
                        'Status' => $status,
                        "Message" => $lngmsg,
                        "RecordCount" => $recordcnt,
                        "Result" => $postData,
                        'IsBlocked' => $isBlocked,
                        'UnreadQA' => $unreadQAData);
                    echo json_encode($encodedArray, JSON_PRETTY_PRINT); //JSON_PRETTY_PRINT
                }
                else
                {
                    echo json_encode(['Status' => $status,
                        "Message" => $lngmsg,
                        "RecordCount" => $recordcnt,
                        "Result" => $postData,
                        'IsBlocked' => $isBlocked,
                        'UnreadQA' => $unreadQAData], JSON_PRETTY_PRINT);
                }
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessagePostList(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    "RecordCount" => "0"], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionStickers() {
        /*************** Get stickers list *************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ProfileID',
                'StickerType',
                'DeviceType',
                'Language',
                'ArtistID');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            $artistID = "";
			$type = "";
            $stickersData = array();
            if (count($compareField) == 0)
            {
                $memberID = $data->ProfileID;
                $stickerType = $data->StickerType;
                $deviceType = $data->DeviceType;
                $language = $data->Language;
				if (isset($data->Type)){ $type = $data->Type; }
                if (isset($data->ArtistID) && $data->ArtistID != '') :
                    $artistID = $data->ArtistID;
                    $connection = Yii::$app->db;
                    /********** Get member sticker list *************/
                    $procedure = "CALL Member_Sticker_List(" . $memberID . "," . $artistID . "," . $stickerType . "," . $deviceType . ",'" . self::S3BucketAbsolutePath . "/','" . self::BoomFolder . $artistID . self::S3BucketStickers . "','" . self::BoomFolder . $artistID . self::S3BucketStickersSmall . "','" . self::BoomFolder . $artistID . self::S3BucketStickersMedium . "','".$type."')";
                    $logString.="\n Member Sticker List : ".$procedure.'\n';	
                    //echo $procedure; die;
                    $command = $connection->createCommand($procedure);
                    $stickersData = $command->queryAll();
                endif;


                foreach ($stickersData as $key => $value)
                {
                    $stickerImages = explode(',', $stickersData[$key]['StickerImage']);
                    $stickerThumbImages = explode(',', $stickersData[$key]['StickerThumbImage']);
                    $stickerMediumImages = explode(',', $stickersData[$key]['StickerMediumImage']);
                    $stickerImagesID = explode(',', $stickersData[$key]['StickerImageID']);
                    $stickerImage = array();
                    for ($n = 0; $n < count($stickerImagesID); $n++)
                    {
                        $stickerImage[$n]['StickerImageID'] = $stickerImagesID[$n];
                        $stickerImage[$n]['StickerImage'] = $stickerImages[$n];
                        $stickerImage[$n]['StickerThumbImage'] = $stickerThumbImages[$n];
                        $stickerImage[$n]['StickerMediumImage'] = $stickerMediumImages[$n];
                    }
                    $stickersData[$key]['StickerImage'] = $stickerImage;
                    unset($stickersData[$key]['StickerThumbImage']);
                    unset($stickersData[$key]['StickerMediumImage']);
                    unset($stickersData[$key]['StickerImageID']);
                }

                if (count($stickersData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageForStickers($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
                    "Result" => $stickersData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageForStickers(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e); 
        }
    }

    public
            function actionApplist() {
        /************** Get app list of artist ********/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ArtistID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $artistID = $data->ArtistID;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $command = $connection->createCommand("CALL SimilarApp_List(" . $artistID . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketAppIcons . "')");
                $appData = $command->queryAll();

                if (count($appData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageApplist($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 1,
                    "Message" => $lngmsg,
                    "Result" => $appData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageApplist(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionAddpost() {
        /************ Add post *************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostTitle',
                'PostType',
                'Language',
                'Description',
                'ArtistID',
                'IsExclusive',
                'IsShared',
                'Media',
                'VideoThumbImage',
                'scheduled' //added for scheduled
                );
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY);

            if (count($compareField) == 0)
            {
                $postTitle = $data->PostTitle;
                $postType = $data->PostType;
                $description = $data->Description;
                $artistID = $data->ArtistID;
                $isExclusive = $data->IsExclusive;
                $isShared = $data->IsShared;
                //$price 		= 	$data->Price;
                $media = $data->Media;
                $videothumb = $data->VideoThumbImage;
                $replyThumbImage = "";
                $language = $data->Language;
                $price = '0.00';
                $productID = "";
                $userID = "";
                $reqisPublic = "2";
                $qatype = "";
                $reply = "";
                $postID = "";
                $ignore = "2";
                $memberID = "0";
                $time = date("d M 'y H:i A");
                $token = "";
                $transactionDetail = "";
                $width = "";
                $height = "";
                $vidoePostWidth = "";
                $vidoePostHeight = "";
				$audioReplyUrl = "";
				$ReplyWidth = "";
				$ReplyHeight = "";
				$CategoryID = null;
				$TextReply = "";
                if (isset($data->Width) && $data->Width != "")
                {
                    $width = $data->Width;
                    if (isset($width[0]) && $width[0] != '')
                    {
                        $vidoePostWidth = $width[0];
                    }
                    //$vidoePostWidth = $width;
                }
                if (isset($data->Height) && $data->Height != "")
                {
                    $height = $data->Height;
                    if (isset($height[0]) && $height[0] != '')
                    {
                        $vidoePostHeight = $height[0];
                    }
                    //$vidoePostHeight = $height;
                }
                if (isset($data->TransactionDetail) && $data->TransactionDetail)
                {
                    $transactionDetail = json_encode($data->TransactionDetail);
                }
                if (isset($data->Token) && $data->Token)
                {
                    $token = $data->Token;
                }
                if (isset($data->Price) && $data->Price)
                {
                    $price = $data->Price;
                }
                if (isset($data->ReplyThumbImage) && $data->ReplyThumbImage)
                {
                    $replyThumbImage = $data->ReplyThumbImage;
                }
                if (isset($data->ProductID) && $data->ProductID)
                {
                    $productID = $data->ProductID;
                }
                if (isset($data->MemberID) && $data->MemberID)
                {
                    $memberID = $data->MemberID;
                }
                if (isset($data->IsPublic) && $data->IsPublic)
                {
                    $reqisPublic = $data->IsPublic;
                }
                if (isset($data->QAType) && $data->QAType)
                {
                    $qatype = $data->QAType;
                }
                if (isset($data->PostID) && $data->PostID != "")
                {
                    $postID = $data->PostID;
                }
                if (isset($data->Reply) && $data->Reply != "")
                {
                    $reply = str_replace("'", "", $data->Reply);
                }
                if (isset($data->Ignore) && $data->Ignore != "")
                {
                    $ignore = $data->Ignore;
                }
				if (isset($data->AudioReplyUrl) && $data->AudioReplyUrl != "")
                {
                    $audioReplyUrl = $data->AudioReplyUrl;
                }
				if (isset($data->ReplyWidth) && $data->ReplyWidth != "")
                {
                    $ReplyWidth = $data->ReplyWidth;
                }
				if (isset($data->ReplyHeight) && $data->ReplyHeight != "")
                {
                    $ReplyHeight = $data->ReplyHeight;
                }
				if (isset($data->CategoryID) && $data->CategoryID != "")
                {
                    $CategoryID = $data->CategoryID;
                }
				if (isset($data->TextReply) && $data->TextReply != "")
                {
                    $TextReply = $data->TextReply;
                }
                //added for scheduled
                if(isset($data->scheduled)&& $data->scheduled != ""){
                    $scheduled=$data->scheduled;
                }else $scheduled=Date("Y-m-d H:i:s");
                
                /************** Check if QA is enable or not for the particular artist **************/
                $QAData = array();
                if($postType == "4") {
                    $connection = Yii::$app->db;
                    $procedure = "SELECT IsQAEnable,QaType FROM setting WHERE ArtistID =".$artistID;
                    $command = $connection->createCommand($procedure);
                    $QAData = $command->queryAll();
                    if(isset($QAData[0]['IsQAEnable']) && $QAData[0]['IsQAEnable'] == "1") {
                        if(isset($QAData[0]['QaType']) && ($QAData[0]['QaType'] == "1" && $qatype == "2")) {
                            $resultCode = 504;
                            $status = "1";
                            $this->setHeader(400);
                            $resultMessage = _getStatusCodeMessageCommon($resultCode);
                            \Yii::$app->language = $language;
                            $lngmsg = \Yii::t('api', $resultMessage);
                            echo json_encode(['Status' => $status,"Message" => $lngmsg], JSON_PRETTY_PRINT);
                            exit;
                        }
                        if(isset($QAData[0]['QaType']) && ($QAData[0]['QaType'] == "2" && $qatype == "1")) {
                            $resultCode = 503;
                            $status = "1";
                            $this->setHeader(400);
                            $resultMessage = _getStatusCodeMessageCommon($resultCode);
                            \Yii::$app->language = $language;
                            $lngmsg = \Yii::t('api', $resultMessage);
                            echo json_encode(['Status' => $status,"Message" => $lngmsg], JSON_PRETTY_PRINT);
                            exit;
                        }
                    } else {
                        $resultCode = 505;
                        $status = "1";
                        $this->setHeader(400);
                        $resultMessage = _getStatusCodeMessageCommon($resultCode);
                        \Yii::$app->language = $language;
                        $lngmsg = \Yii::t('api', $resultMessage);
                        echo json_encode(['Status' => $status,"Message" => $lngmsg], JSON_PRETTY_PRINT);
                        exit;
                    }    
                }

                $memberObj = new \backend\models\Member();
                /*if ($postID == "")
                {
                    $memberObj = new \backend\models\Member();
                    $memberDeviceType = $memberObj->getMemberDeviceType($memberID);
                }*/

                $connection = Yii::$app->db;
                //$proc = "CALL Post_Add('".$postTitle."','" . $postType . "','".$description."','" . $artistID . "','" . $isExclusive . "','" . $isShared . "','" . $price . "','" . $productID . "'," . $memberID . ",'" . $reqisPublic . "','" . $qatype . "','" . $postID . "','" . $reply . "','" . $replyThumbImage . "','" . $ignore . "','" . $transactionDetail . "','" . $token . "','".$vidoePostWidth."','".$vidoePostHeight."')";
                /******* Add post by post type **************/
                $proc = "CALL Post_Add(:PostTitle,:PostType,:Description,:ArtistID,:IsExclusive,:IsShared,:price,:productID,:memberID,:reqisPublic,:qatype,:postID,:reply,:replyThumbImage,:ignore,:transactionDetail,:token,:vidoePostWidth,:vidoePostHeight,:audioReplyUrl,:ReplyWidth,:ReplyHeight,:CategoryID,:TextReply,:scheduled)";
                $bindPostParams = [':PostTitle' => $postTitle,':PostType'=>$postType,':Description'=>$description,':ArtistID'=>$artistID,':IsExclusive'=>$isExclusive,':IsShared'=>$isShared,':price'=>$price,':productID'=>$productID,':memberID'=>$memberID,':reqisPublic'=>$reqisPublic,':qatype'=>$qatype,':postID'=>$postID,':reply'=>$reply,':replyThumbImage'=>$replyThumbImage,':ignore'=>$ignore,':transactionDetail'=>$transactionDetail,':token'=>$token,':vidoePostWidth'=>$vidoePostWidth,':vidoePostHeight'=>$vidoePostHeight,':audioReplyUrl'=>$audioReplyUrl,':ReplyWidth'=>$ReplyWidth,':ReplyHeight'=>$ReplyHeight,':CategoryID'=>$CategoryID,':TextReply'=>$TextReply,':scheduled'=>$scheduled];

                $logString.="\n Post Add : ".$proc.'\n';	
                $command = $connection->createCommand($proc)->bindValues($bindPostParams);
                $postData = $command->queryAll();
                if (count($postData) > 0)
                {
                    if ($postType == '4')
                    {
                        $artistobj = new \backend\models\Artist();
                        $ignoreStatus = "";
                        if ($postID == "")
                        {
                            
                        }
                        else
                        {
                            $memberObj = new \backend\models\Member();
                            $memberDeviceType = $memberObj->getMemberDeviceType($memberID);
                            $memberDeviceToken = $memberObj->getMemberDeviceToken($memberID);
                            //$memberDeviceToken  = "dHlGk8zp5so:APA91bF7iHX6shOyGeRnrURCleAeza_LezOr06-0vMFzJ-qAaVVHa84hrC6Ae3kwtBuXHDWcXAeS8uvYU7C4bqalvl7Yty4yXtx0HjIRMlHvsUk6vZs6t58Zvr0_DSexGk7URMViip-E";
                            if ($memberDeviceType != "" && $memberDeviceToken != "")
                            {
                                $sendpushtoartist = new \api\models\Commentnotforartist();
                                if (isset($data->Ignore) && $data->Ignore != "")
                                {
                                    $ignoreStatus = $ignore;
                                }
                                $getmsgforartist = $artistobj->getNotificationMessageForPost($memberID, $artistID, $postID, $ignoreStatus);
                                $sendpushtoartist->deviceToken = $memberDeviceToken;
                                $sendpushtoartist->message = $getmsgforartist;
                                $sendpushtoartist->time = $time;
                                $sendpushtoartist->postid = $postID;
                                if (isset($data->Ignore) && $data->Ignore != "")
                                {
                                    $sendpushtoartist->NotificationType = "ignoreQue";
                                }
                                else
                                {
                                    $sendpushtoartist->NotificationType = "replyAns";
                                }

                                if ($memberDeviceType == 1)
                                {
                                    try
                                    {
                                        if (isset($data->Ignore) && $data->Ignore != "")
                                        {
                                            $sendpushtoartist->sendToIphone($artistID, 'user');
                                        }
                                        else if ($qatype == "1")
                                        {
                                            $sendpushtoartist->sendToIphone($artistID, 'user');
                                        }
                                    }
                                    catch (Exception $e)
                                    {
                                        echo "cat message" . $e->getMessage() . " \n";
                                    }
                                }
                                if ($memberDeviceType == 2)
                                {
                                    //echo $memberDeviceToken; die;
                                    if (isset($data->Ignore) && $data->Ignore != "")
                                    {
                                        $sendpushtoartist->sendToAndroid($artistID, 'user');
                                    }
                                    else if ($qatype == "1")
                                    {
                                        $sendpushtoartist->sendToAndroid($artistID, 'user');
                                    }
                                }
                            }
                        }
                    }


                    $postID = $postData[0]['PostID'];
                    if ($postType == "2" || $postType == "3" || $postType == "4")
                    {
                        //In gallery table Type 1- Image,2-Video, so here type is passed one for image                 
                        if (!empty($media))
                        {
                            foreach ($media as $key => $value)
                            {
                                if ($key == 0)
                                {
                                    $thumbvalue = $value;
                                }
                                else
                                {
                                    $thumbvalue = "";
                                }
                                $postWidth = "";
                                $postHeight = "";
                                if (isset($width[$key]) && $width[$key] != '') $postWidth = $width[$key];
                                if (isset($height[$key]) && $height[$key] != '') $postHeight = $height[$key];
                                /************ ADd media ***********/
                                $gallComm = "CALL Post_Add_Media(" . $artistID . "," . $postID . "," . $postType . ",'" . $value . "','" . $thumbvalue . "','" . $videothumb . "','" . self::BoomPath . "','" . self::S3BucketPostVideos . "','" . $postWidth . "','" . $postHeight . "')";
                                //echo $gallComm; die;
                                $gallcommand = $connection->createCommand($gallComm);
                                $mediadata = $gallcommand->queryAll();

                                if ($postType == "2")
                                {
                                    $content = file_get_contents(self::S3BucketAbsolutePath . '/' . self::BoomFolder . $artistID . self::S3BucketPostImages . $value);
                                    $thumbcontent = file_get_contents(self::S3BucketAbsolutePath . '/' . self::BoomFolder . $artistID . self::S3BucketPostThumbImage . $value);
                                    $commonthumb = self::Documentroot . self::Project . self::Uploads . self::Postlarge . $value;
                                    $createthumb = self::Documentroot . self::Project . self::Uploads . self::Postthumb . $value;
                                    //$blurthumb      =   self::Documentroot.self::Project.self::Uploads.self::Blurthumb.$value;
                                    $createmedium = self::Documentroot . self::Project . self::Uploads . self::Postmedium . $value;
                                    file_put_contents($commonthumb, $content);
                                    file_put_contents($createthumb, $thumbcontent);

                                    //$thumbimage=Yii::$app->image->load($createthumb);
                                    //$thumbimage->resize("",100);    
                                    //$thumbimage->save($blurthumb,10);

                                    $mediumimage = Yii::$app->image->load($commonthumb);
                                    $mediumimage->resize(self::Mediumwidth, self::Mediumheight);
                                    $mediumimage->save($createmedium, 100);

                                    //\S3::putObjectFile($blurthumb,self::S3Bucket,self::BoomFolder.$artistID.self::Postblurthumb.$value, \S3::ACL_PUBLIC_READ);
                                    \S3::putObjectFile($createmedium, self::S3Bucket, self::BoomFolder . $artistID . self::S3BucketPostMediumImage . $value, \S3::ACL_PUBLIC_READ);
                                }
                            }
                        }
                        if ($postType == "3" || $postType == "4")
                        {
                            if ($videothumb != "")
                            {
							
								//Added by Dan: if QAType = image, save url for Thumbimage too
								//if($qatype == "4"){
									$thumburl = S3_VIDEO_BUCKETPATH.$artistID.POST_THUMBIMAGE_VIDEOS.$videothumb;
									if($postType == "4" && $qatype == "1"){ $thumburl = S3_BUCKETPATH.$artistID.POST_THUMBIMAGE_VIDEOS.$videothumb; }
									$connection = \Yii::$app->db;
									$connection->createCommand()->update('post', array("ThumbImage"=>$thumburl),'PostID = '.$postID)->execute();
								//}
							
							
                                //echo self::S3BucketAbsolutePath.'/'.self::BoomFolder.$artistID.self::Postvideosthumb.$videothumb; die;
                                $content = file_get_contents(self::S3BucketAbsolutePath . '/' . self::BoomFolder . $artistID . self::Postvideosthumb . $videothumb);
                                $createthumb = self::Documentroot . self::Project . self::Uploads . self::Postthumb . $videothumb;
                                $blurthumb = self::Documentroot . self::Project . self::Uploads . self::Blurthumb . $videothumb;

                                file_put_contents($createthumb, $content);

                                $thumbimage = Yii::$app->image->load($createthumb);
                                //$thumbimage->resize("",self::PostThumbheight);
                                $thumbimage->resize("", 100);
                                $thumbimage->save($blurthumb, 10);


                                \S3::putObjectFile($blurthumb, self::S3Bucket, self::BoomFolder . $artistID . self::Postblurthumbvideos . $videothumb, \S3::ACL_PUBLIC_READ);
                            }
                        }

                        if (count($mediadata) > 0)
                        {
                            $resultCode = 200;
                            $status = "1";
                        }
                        else
                        {
                            $resultCode = 404;
                            $status = "0";
                        }
                    }
                    else
                    {
                        $resultCode = 200;
                        $status = "1";
                    }
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }

                /*                 * **************************** Push for Video and Images ************************ */
                $push_obj = new \backend\models\Pushqueue();
                if ($postType == "1" || $postType == "2" || $postType == "3")
                {
                    $iosToken = $memberObj->getBulkMemberDeviceToken(1, $artistID);
                    $androidToken = $memberObj->getBulkMemberDeviceToken(2, $artistID);
                    $ios = array();
                    $android = array();
                    $memberIOSID = array();
                    $memberAndroidID = array();
                    foreach ($iosToken as $key => $value)
                    {
                        if (isset($value['DeviceToken']) && $value['DeviceToken'] != '' && $value['DeviceToken'] != 'null' && $value['DeviceToken'] != '1' && $value['DeviceToken'] != '(null)')
                        {
                            $ios[] = $value['DeviceToken'];
                            $memberIOSID[] = $value['MemberID'];
                        }
                    }
                    foreach ($androidToken as $key => $value)
                    {
                        if (isset($value['DeviceToken']) && $value['DeviceToken'] != '' && $value['DeviceToken'] != 'null' && $value['DeviceToken'] != '1' && $value['DeviceToken'] != '(null)')
                        {
                            $android[] = trim($value['DeviceToken']);
                            $memberAndroidID[] = $value['MemberID'];
                        }
                    }
                    $artistobj = new \backend\models\Artist();
                    $sendpushtoartist = new \api\models\Commentnotforartist();
                    $getmsgforartist = $artistobj->getBulkNotificationMessageForPost($memberID, $artistID, $postType, $postTitle, $description);

                    $modelPushHistoryStats = new \backend\models\Pushhistorystats();
                    $modelPushHistoryStats->ArtistID = $artistID;
                    $modelPushHistoryStats->Message = $getmsgforartist;
                    $modelPushHistoryStats->TotalDevices = count($ios) + count($android);
                    $modelPushHistoryStats->save();
                    foreach ($ios as $key => $token)
                    {
                        $modelPushQueue = new \backend\models\Pushqueue();
                        $modelPushQueue->BatchID = $modelPushHistoryStats->BatchID;
                        $modelPushQueue->MemberID = $memberIOSID[$key];
                        $modelPushQueue->DeviceToken = $token;
                        $modelPushQueue->DeviceType = '1';
                        $modelPushQueue->Status = '1';
                        $modelPushQueue->Message = $getmsgforartist;
                        $modelPushQueue->save();
                    } foreach ($android as $key => $token)
                    {
                        $modelPushQueue = new \backend\models\Pushqueue();
                        $modelPushQueue->BatchID = $modelPushHistoryStats->BatchID;
                        $modelPushQueue->MemberID = $memberAndroidID[$key];
                        $modelPushQueue->DeviceToken = $token;
                        $modelPushQueue->DeviceType = '2';
                        $modelPushQueue->Status = '1';
                        $modelPushQueue->Message = $getmsgforartist;
                        $modelPushQueue->save();
                    }
                }
                /*                 * ****************************************************************************** */

                $unreadQAData = $this->unreadQA($artistID);
                $resultMessage = _getStatusCodeMessageCommon($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);

                
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
                    'UnreadQA' => $unreadQAData,
                    "Result" => $postData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageCommon(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public function actionGlobaliospush() {
        /************** Send IOS Push **************/
        /************** Log Data ***********/
        $filename = '../web/log/PushLog_'.date('Ymd').'.txt';
        $log_current= "\n *****Push Started*******";    
        $log_current.= "\n Time : ".date("Y-m-d H:i:s")." \n";
        if(file_exists($filename)) {
           $log_current.= file_get_contents($filename);
        }
        file_put_contents($filename, $log_current);
        /************************************/
        $logString  = "";
        try
        {
            $push_obj = new \backend\models\Pushqueue();
            $sendpush = new \api\models\Globalpush();
            $iostokens = $push_obj->getIosDevices();
            $batchID = array();
            $batchMessage = array();
            $batchMemberid = array();

            if (count($iostokens) > 0)
            {
                foreach ($iostokens as $key => $val)
                {
                    if (!in_array($val['BatchID'], $batchID))
                    {
                        $batchID[] = $val['BatchID'];
                        //$batchMessage[] = htmlspecialchars_decode($val['Message'], ENT_QUOTES);
                        $batchMessage[] = $val['Message'];
                        $batchMemberid[] = $val['MemberID'];
                    }
                }
                $uniquebatch = array();
                foreach ($batchID as $value)
                {
                    $uniquebatch[] = $push_obj->searchForId($value, $iostokens);
                }
                $batches = array();
                foreach ($batchID as $value)
                {
                    $batches[] = $value;
                }
                $currenttime = date("d M 'y H:i A");
                for ($j = 0; $j < count($iostokens); $j++)
                {
                    $memberiddb = $iostokens[$j]['MemberID'];
                    $batchiddb = $iostokens[$j]['BatchID'];
                    $dbdevicetoken = $iostokens[$j]['DeviceToken'];
                    $push_obj->addPushHistory($memberiddb, 1, $dbdevicetoken, $batchiddb);
                }
                for ($i = 0; $i < count($uniquebatch); $i++)
                {
                    $getartistID = \backend\models\Pushhistorystats::findAll(array("BatchID" => $batches[$i]));
                    $sendpush->sendToIphone($uniquebatch[$i], $batchMessage[$i], $currenttime, $getartistID[0]['ArtistID']);
                }
                echo json_encode(["Status" => "1",
                    "Message" => "Success"], JSON_PRETTY_PRINT);
            }
            else
            {
                echo json_encode(["Status" => "0",
                    "Message" => "No notification"], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        {
            $this->addLog($logString,$e);
        }
    }

    public
            function actionGlobalandroidpush() {
        /*************** Send android push *******************/
        $logString  = "";
        try
        {
            $push_obj = new \backend\models\Pushqueue();
            $sendpush = new \api\models\Globalpush();
            $androidtokens = $push_obj->getAndroidDevices();
            $batchID = array();
            $batchMessage = array();
            $batchMemberid = array();

            if (count($androidtokens) > 0)
            {
                foreach ($androidtokens as $key => $val)
                {
                    if (!in_array($val['BatchID'], $batchID))
                    {
                        $batchID[] = $val['BatchID'];
                        //$batchMessage[] = htmlspecialchars_decode($val['Message'], ENT_QUOTES);
                        $batchMessage[] = $val['Message'];
                        $batchMemberid[] = $val['MemberID'];
                    }
                }

                $uniquebatch = array();
                foreach ($batchID as $value)
                {
                    $uniquebatch[] = $push_obj->searchForId($value, $androidtokens);
                }
                $batches = array();
                foreach ($batchID as $value)
                {
                    $batches[] = $value;
                }
                $currenttime = date("d M 'y H:i A");

                for ($j = 0; $j < count($androidtokens); $j++)
                {
                    $memberiddb = $androidtokens[$j]['MemberID'];
                    $batchiddb = $androidtokens[$j]['BatchID'];
                    $dbdevicetoken = $androidtokens[$j]['DeviceToken'];
                    $push_obj->addPushHistory($memberiddb, 2, $dbdevicetoken, $batchiddb);
                }
                for ($i = 0; $i < count($uniquebatch); $i++)
                {
                    $getartistID = \backend\models\Pushhistorystats::findAll(array("BatchID" => $batches[$i]));
                    $sendpush->sendToAndroid($uniquebatch[$i], $batchMessage[$i], $currenttime, $getartistID[0]['ArtistID']);
                }

                echo json_encode(["Status" => "1",
                    "Message" => "Success"], JSON_PRETTY_PRINT);
            }
            else
            {
                echo json_encode(["Status" => "0",
                    "Message" => "No notification"], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        {
            $this->addLog($logString,$e);
        }
    }

    public
            function actionPurchasesticker() {
        /************** Purchase sticker ***************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ProfileID',
                'StickerID',
                'DeviceType',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $memberID = $data->ProfileID;
                $stickerID = $data->StickerID;
                $deviceType = $data->DeviceType;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Sticker_Purchase(" . $memberID . "," . $stickerID . "," . $deviceType . ")";
                $logString.="\n Sticker Purchase : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $postData = $command->queryAll();
                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageForPurchase($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
                    'Result' => $postData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageForPurchase(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public function actionPurchasepost() {
        /************** Purchase post *****************/
        $logString  = "";
        try
        {
            // $arrParams = Yii::$app->request->post();
            // $logString.="\n Params : ".$arrParams['params'].'\n';	
            // $data = json_decode($arrParams['params']);
            // $availableParams = array(
            //     'ArtistID',
            //     'ProfileID',
            //     'PostID',
            //     'Language');
            // $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            // if (count($compareField) == 0)
            // {
            //     // $artistID = $data->ArtistID;
            //     // $memberID = $data->ProfileID;
            //     // $postID = $data->PostID;
            //     // $language = $data->Language;
            //     // $deviceType = "";
            //     // $productSKUDetails = "";
            //     // if (isset($data->ProductSKUDetails) && $data->ProductSKUDetails != "")
            //     // {
            //     //     try{
            //     //         $productSKUDetails = json_encode($data->ProductSKUDetails);
            //     //     }catch(ErrorException $e){
            //     //         $productSKUDetails ='';
            //     //     }
                    
            //     // }
            //     // if (isset($data->DeviceType) && $data->DeviceType != "")
            //     // {
            //     //     $deviceType = $data->DeviceType;
            //     // }
                
            //     // $connection = Yii::$app->db;
            //     // $procedure = "CALL Post_Purchase(" . $artistID . "," . $memberID . "," . $postID . "," . $deviceType . ",'" . $productSKUDetails . "')";
            //     // $logString.="\n Post Purchase : ".$procedure."\n";	
            //     //  echo $logString;
            //     // try{
            //     //     $command = $connection->createCommand($procedure);
            //     //     $postData = $command->queryAll();
            //     // }catch(ErrorException $e){
            //     //         echo $logString;
            //     // }
                
                
            //     // if (count($postData) > 0)
            //     // {
            //     //     $resultCode = 200;
            //     //     $status = "1";
            //     // }
            //     // else
            //     // {
            //     //     $resultCode = 404;
            //     //     $status = "0";
            //     // }

            //     // //for Boom Fan app-> Add User follow
            //     // $artists=UserArtist::find()->where(['and','MemberID ='.$memberID,'ArtistID='.$artistID])->all();
            //     // if(count($artists)>0) {
            //     //     $resultCode = 502;
            //     //     $status = "0";
            //     //     $resultMessage="User has subscribed";
            //     // }else{
            //     //     // echo "Not Find";
            //     //    $member=Member::findOne(array("MemberID"=>$memberID));
            //     //    $userID=$member->UserID;
            //     //    if($userID!=0 &&$artistID!=0 && $memberID!=0){
            //     //         $query='insert into user_artist(UserID, MemberID, ArtistID, isSub) values ("'.$userID.'", "'.$memberID.'", "'.$artistID.'", 1)';
            //     //         $result= Yii::$app->db->createCommand($query)->execute();
            //     //         if($result){
            //     //             $resultCode = 200;
            //     //             $status = "1";
            //     //         }else{
            //     //             $resultCode = 404;
            //     //             $status = "0";
            //     //         }
            //     //    }else{

            //     //    }
            //     // }

            //     // $resultMessage = _getStatusCodeMessageForPurchase($resultCode);
            //     // \Yii::$app->language = $language;
            //     // $lngmsg = \Yii::t('api', $resultMessage);
            //     // $this->setHeader(400);
            //     // echo json_encode(['Status' => $status,
            //     //     "Message" => $lngmsg,
            //     //     'Result' => $postData], JSON_PRETTY_PRINT);
            // }
            // else
            // {
            //     // $this->setHeader(400);
            //     // $resultMessage = _getStatusCodeMessageForPurchase(502);
            //     // \Yii::$app->language = $language;
            //     // $lngmsg = \Yii::t('api', $resultMessage);
            //     // echo json_encode(['Status' => 0,
            //     //     "Message" => $lngmsg], JSON_PRETTY_PRINT);
            //     echo $logString;
            // }
            echo "s";
        }
        catch (ErrorException $e)
        { 
            // $this->addLog($logString,$e);
            echo $logString;
        }
    }

    public
            function actionArtisteditpost() {
        /*************** Artist update post ***************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ArtistID',
                'ProfileID',
                'PostID',
                'PostType',
                'PostTitle',
                'Description',
                'FullVideoLink',
                'IsExclusive',
                'IsShared',
                'Price',
                'MediaDeleteID',
                'Media');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            if (count($compareField) == 0)
            {
                $artistID = $data->ArtistID;
                $profileID = $data->ProfileID;
                $postID = $data->PostID;
                $postType = $data->PostType;
                $postTitle = $data->PostTitle;
                $description = $data->Description;
                $fullVideoLink = $data->FullVideoLink;
                $isExclusive = $data->IsExclusive;
                $isShared = $data->IsShared;
                $price = $data->Price;
                $mediaDeleteID = $data->MediaDeleteID;
                $media = $data->Media;
                $language = $data->Language;
                $connection = Yii::$app->db;
                /*$procedure = 'CALL Artist_Edit_Post(' . $artistID . ',' . $profileID . ',' . $postID . ',
					  "' . $postType . '","' . str_replace("'", "", $postTitle) . '","' . str_replace("'", "", $description) . '","' . $fullVideoLink . '",
					  ' . $isExclusive . ',' . $isShared . ',' . $price . ',@o_ErrorCode)';*/
                //echo $procedure; die;
                $proc = "CALL Artist_Edit_Post(:ArtistID,:ProfileID,:PostID,:PostType,:PostTitle,:Description,:FullVideoLink,:IsExclusive,:IsShared,:Price,@o_ErrorCode)";
                $logString.="\n Member Artist Edit Post : ".$proc.'\n';	
                $bindPostParams = [':ArtistID' => $artistID,':ProfileID'=>$profileID,':PostID'=>$postID,':PostType'=>$postType,':PostTitle'=>$postTitle,':Description'=>$description,':FullVideoLink'=>$fullVideoLink,':IsExclusive'=>$isExclusive,':IsShared'=>$isShared,':Price'=>$price];
                $command = $connection->createCommand($proc)->bindValues($bindPostParams);

                $postData = $command->queryAll();
                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                    if (count($mediaDeleteID) > 0)
                    {
                        /************ Delete Media ************/
                        foreach ($mediaDeleteID as $deleteval)
                        {
                            $delProc = "CALL Artist_EditPost_DeleteMedia(" . $postID . "," . $artistID . ",
							       '" . $postType . "'," . $deleteval . ")";
                            $connection->createCommand($delProc)->execute();
                        }
                    }
                    if (count($media) > 0)
                    {
                        foreach ($media as $mediaval)
                        {
                            //$mediaProc 	=	"CALL Post_Add_Media(".$artistID.",".$postID.",".$postType.",'".$mediaval."','','','".self::BoomPath."','".self::S3BucketPostVideos."')";
                            //$connection->createCommand($mediaProc)->queryAll(); 
                        }
                    }
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageForEditpost($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
                    'Result' => $postData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageForEditpost(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionArtistdeletepost() {
        /******************* Delete artist post ***************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            if (count($compareField) == 0)
            {
                $postID = $data->PostID;
                $language = $data->Language;

                $connection = Yii::$app->db;
                $procedure = "CALL Artist_Delete_Post(" . $postID . ",@o_ErrorCode)";
                $logString.="\n Artist Delete Post : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $postData = $command->queryAll();
                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageCommon($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageCommon(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionLikelist() {
        /******** Get like list for the particular post **********/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostID',
                'ArtistID',
                'RefTable',
                'RefTableID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            if (count($compareField) == 0)
            {
                $postID = $data->PostID;
                $artistID = $data->ArtistID;
                $reftable = $data->RefTable;
                $reftableID = $data->RefTableID;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Post_Like_Detail_List(" . $postID . "," . $artistID . "," . $reftable . "," . $reftableID . ",'" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "')";
                $logString.="\n Post Like Detail : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $postData = $command->queryAll();
                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageLikelist($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "Result" => $postData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageLikelist(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);  
        }
    }

    public
            function actionSharelist() {
        /************ Get share list for the particular post *************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostID',
                'ArtistID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            if (count($compareField) == 0)
            {
                $postID = $data->PostID;
                $artistID = $data->ArtistID;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Post_Share_Detail_List(" . $postID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "')";
                $logString.="\n Post Share Detail List : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $postData = $command->queryAll();
                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageSharelist($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "Result" => $postData], JSON_PRETTY_PRINT);
            }
            else
            {
                //$this->setHeader(502);
                $resultMessage = _getStatusCodeMessageSharelist(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionFlaglist() {
        /************* Get flag list for the particular post **************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'PostID',
                'ArtistID',
                'RefTable',
                'RefTableID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            if (count($compareField) == 0)
            {
                $postID = $data->PostID;
                $artistID = $data->ArtistID;
                $reftable = $data->RefTable;
                $reftableID = $data->RefTableID;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Post_Flag_Detail_List(" . $postID . "," . $artistID . "," . $reftable . "," . $reftableID . ",'" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "')";
                $logString.="\n Post Flag Detail List : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $postData = $command->queryAll();
                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageFlaglist($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);

                $this->setHeader(400);
                echo json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "Result" => $postData], JSON_PRETTY_PRINT);
            }
            else
            {
                //$this->setHeader(502);
                $resultMessage = _getStatusCodeMessageFlaglist(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);    
        }
    }

    public
            function actionArtisthomescreen() {
        /***************** Get artist feed ****************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ArtistID',
                'UserType',
                'PageIndex',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $artistID = $data->ArtistID;
                $pageindex = $data->PageIndex;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Artist_Home_News_Feed(" . $artistID . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "'," . self::Limit . "," . $pageindex . ",@o_RecCount)";
                $logString.="\n Artist Home News Feed : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $postData = $command->queryAll();
                $reccommand = $connection->createCommand('SELECT @o_RecCount')->queryOne();
                $recordcnt = $reccommand['@o_RecCount'];
                foreach ($postData as $key => $value)
                {
                    if (isset($value['PostID']) && $value['PostID'] != '')
                    {
                        $imageData = array();
                        $postID = $value['PostID'];
                        /***************** Get image list **************/
                        $postimageproc = "CALL Post_Image_List(1," . $postID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketPostImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $commandForImage = $connection->createCommand($postimageproc);
                        $imageData = $commandForImage->queryAll();
                        
                        /************** Get latest post comment list **************/
                        $latestcmntsproc = "CALL Latest_Post_CommentList(" . $postID . "," . $artistID . "," . $artistID . ",2,'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "','" . self::S3BucketStickersSmall . "','" . self::S3BucketStickersMedium . "')";
                        $commandForCmnts = $connection->createCommand($latestcmntsproc);
                        $cmntsData = $commandForCmnts->queryAll();
                        if (count($cmntsData) > 0)
                        {
                            $postData[$key]['LatestComments'] = $cmntsData;
                        }
                        else
                        {
                            $postData[$key]['LatestComments'] = array();
                        }
                        if (count($imageData) > 0)
                        {
                            $postData[$key]['PostImage'] = $imageData;
                        }
                        else
                        {
                            $postData[$key]['PostImage'] = $imageData;
                        }
                    }
                }

                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }

                $resultMessage = _getStatusCodeMessageArtistHomescreen($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "RecordCount" => $recordcnt,
                    "Result" => $postData], JSON_PRETTY_PRINT);
            }
            else
            {
                //$this->setHeader(502);
                $resultMessage = _getStatusCodeMessageArtistHomescreen(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    "RecordCount" => "0"], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionMymusic() {
        /************** Get my music list ********************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ArtistID',
                'PageIndex',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $artistID = $data->ArtistID;
                $pageindex = $data->PageIndex;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Mymusic_List(" . $artistID . "," . self::Limit . "," . $pageindex . ",@o_RecCount)";
                $logString.="\n Mymusic List : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $trackData = $command->queryAll();
                $reccommand = $connection->createCommand('SELECT @o_RecCount')->queryOne();
                $recordcnt = $reccommand['@o_RecCount'];

                if (count($trackData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageMymusic($resultCode);
                $model = \backend\models\Artist::find()->where('ArtistID =' . $artistID)->one();
                if ($model->Header != "")
                {
                    $headerString = $model->Header;
                }
                else
                {
                    $headerString = "This is what I am listening while travelling";
                }
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "RecordCount" => $recordcnt,
                    "Result" => $trackData,
                    "Header" => $headerString], JSON_PRETTY_PRINT);
            }
            else
            {
                $resultMessage = _getStatusCodeMessageMymusic(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    "Result" => array(),
                    "RecordCount" => "0"], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function actionMymusictv() {
        /************** Get my music list ****************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ArtistID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $artistID = $data->ArtistID;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Mymusictv_List(" . $artistID . ",'" . self::S3BucketPath . "','" . ALBUM_THUMB_IMAGES . "')";
                $logString.="\n My music tv list : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure);
                $trackData = $command->queryAll();

                if (count($trackData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageMymusic($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "Result" => $trackData], JSON_PRETTY_PRINT);
            }
            else
            {
                $resultMessage = _getStatusCodeMessageMymusic(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    "Result" => array()], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);    
        }
    }

    public
            function actionBlock() {
        /******************* Block user  *********************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'UserID',
                'ArtistID',
                'ProfileID',
                'PostID',
                'UserType',
                'Type',
                'Status',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $postData = array();
                $UserID = $data->UserID;
                $profileID = $data->ProfileID;
                $artistID = $data->ArtistID;
                $postID = $data->PostID;
                $usertype = $data->UserType;
                $type = $data->Type;
                $language = $data->Language;
                $status = $data->Status;
                $connection = Yii::$app->db;
                $procedure = "CALL Block(" . $artistID . "," . $UserID . "," . $profileID . "," . $postID . "," . $usertype . "," . $type . "," . $status . ")";
                $logString.="\n Block : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure)->queryAll();
                $resultCode = $command[0]['o_ErrorCode'];
                $resultMessage = _getStatusCodeMessageMymusic($resultCode);
                /*                 * ********************************* Push For Block User ****************************** */

                $memberObj = new \backend\models\Member();
                $memberDeviceType = $memberObj->getMemberDeviceType($profileID);
                $memberDeviceToken = $memberObj->getMemberDeviceToken($profileID);
                $artistobj = new Artist();
                $time = date("d M 'y H:i A");
                $unreadQAData = $this->unreadQA($artistID);
                /************* Send push *************/
                if ($memberDeviceType != "" && $memberDeviceToken != "")
                {
                    $sendpushtoartist = new \api\models\Commentnotforartist();
                    $getmsgforartist = $artistobj->getNotificationMessageForBlockUser($profileID, $artistID, $postID, $status);
                    $sendpushtoartist->deviceToken = $memberDeviceToken;
                    $sendpushtoartist->message = $getmsgforartist;
                    $sendpushtoartist->time = $time;
                    $sendpushtoartist->postid = $postID;
                    $sendpushtoartist->NotificationType = "blockUser";


                    if ($memberDeviceType == 1)
                    {
                        try
                        {
                            $sendpushtoartist->sendToIphone($artistID, 'user');
                        }
                        catch (Exception $e)
                        {
                            echo "cat message" . $e->getMessage() . " \n";
                        }
                    } if ($memberDeviceType == 2)
                    {
                        $sendpushtoartist->sendToAndroid($artistID, 'user');
                    }
                }

                /*                 * *********************************************************************************** */
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(200);
                echo json_encode(["Status" => 1,
                    'UnreadQA' => $unreadQAData,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessagePostList(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    "RecordCount" => "0"], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);   
        }
    }
    
    public function actionBlockusercomment() {
        /***************** Blcok the user comment **************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ArtistID',
                'ProfileID',
                'ActivityID',
                'Block',
                'BlockUserProfileID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $postData = array();
                $profileID = $data->ProfileID;
                $artistID = $data->ArtistID;
                $block = $data->Block;
                $language = $data->Language;
                $to = $data->BlockUserProfileID;
                $activityID = $data->ActivityID;
                $connection = Yii::$app->db;
                $procedure = "CALL BlockComment(" . $artistID . "," . $profileID . "," . $activityID . "," . $block . "," . $to . ")";
                $logString.="\n Block Comment : ".$procedure.'\n';	
                $command = $connection->createCommand($procedure)->queryAll();
                $resultCode = $command[0]['o_ErrorCode'];
                $resultMessage = _getStatusCodeMessageFlaglist($resultCode);                
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(200);
                echo json_encode(["Status" => 1,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessagePostList(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    "RecordCount" => "0"], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);   
        }
    }


    public
            function loadModel($id) {
        $model = User::findOne($id);
        if (is_null($model))
        {
            //$this->setHeader(400);
            echo json_encode(['status' => 0,
                'error_code' => 400,
                'message' => 'Bed Request'], JSON_PRETTY_PRINT);
            exit;
        }
        return $model;
    }

    public
            function actionOldstickers() {
        /************** Get old stickers list ****************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ProfileID',
                'StickerType',
                'DeviceType',
                'Language',
                'ArtistID');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            $artistID = "";
            $stickersData = array();
            if (count($compareField) == 0)
            {
                $memberID = $data->ProfileID;
                $stickerType = $data->StickerType;
                $deviceType = $data->DeviceType;
                $language = $data->Language;
                if (isset($data->ArtistID) && $data->ArtistID != '') :
                    $artistID = $data->ArtistID;
                    $connection = Yii::$app->db;
                    $procedure = "CALL Old_Member_Sticker_List(" . $memberID . "," . $artistID . "," . $stickerType . "," . $deviceType . ",'" . self::S3BucketAbsolutePath . "/','" . self::BoomFolder . $artistID . self::S3BucketStickers . "','" . self::BoomFolder . $artistID . self::S3BucketStickersSmall . "','" . self::BoomFolder . $artistID . self::S3BucketStickersMedium . "')";
                    $logString.="\n Old Member Sticker List : ".$procedure.'\n';	
                    //echo $procedure; die;
                    $command = $connection->createCommand($procedure);
                    $stickersData = $command->queryAll();
                endif;
                if (count($stickersData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageForStickers($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
                    "Result" => $stickersData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageForStickers(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }

    public
            function resizeimage() {
        
    }

	
	//Daniele
	
	//Daniele: function that given the instagram feed url(in Media RSS format), returns the array of feeds in the postImage format
	protected function getInstagramFeeds($url, $artistId){
		$res = array();
		
		$logString  = "";
        try
        {		
            $xml = $url;
            $userModel = new User;
			ini_set('max_execution_time', 120);
            $items = $userModel->xmlparsingDan($xml);
			foreach($items as $item){
				$xmltitle = '';
				$xmldesc = '';
				$xmllink = '';
				$xmlmediatitle = '';
				$xmlmediathumb = '';
				$xmlcontenturl = '';
				if (isset($item['title']) && $item['title'] != '') $xmltitle = $item['title'];
				if (isset($item['description']) && $item['description'] != '') $xmldesc = $item['description'];
				if (isset($item['link']) && $item['link'] != '') $xmllink = $item['link'];
				if (isset($item['media:title']) && $item['media:title'] != '') $xmlmediatitle = $item['media:title'];
				if (isset($item['media:thumbnail']['url']) && $item['media:thumbnail']['url'] != '') $xmlmediathumb = $item['media:thumbnail']['url'];
				if (isset($item['media:content']['url']) && $item['media:content']['url'] != '') $xmlcontenturl = $item['media:content']['url'];
				$modelRssFeed = new Rssfeed;
				$modelRssFeed->title = $xmltitle;
				$modelRssFeed->description = substr($xmldesc, 10, (strlen($xmldesc) - 14));
				$img_name = explode(".jpg?ig_cache_key", $xmldesc)[0];
				$img_name = end(explode("/", $img_name));
				$modelRssFeed->media_title = $img_name.".jpg";
				$modelRssFeed->media_thumbnail = $xmlmediathumb;
				$modelRssFeed->media_content = $xmlcontenturl;
				
				//if title = image url, set title empty
				$tmp_dsc = explode("/", $xmldesc);
				if($tmp_dsc != null){
					$tit = $tmp_dsc[count($tmp_dsc)-2];
					$tit = explode("\"", $tit)[0];
					if($xmltitle == $tit){
						$modelRssFeed->title = "";
					}
				}
				
				//create PostImage element
				$postImgEl = (object) array('ID' => '0', 
											'Title' => "[Repost From Instagram] ".$modelRssFeed->title,
											'ImageName' => $modelRssFeed->media_title,
											'Image' => $modelRssFeed->description,
											'ThumbImage' => $modelRssFeed->description,
											'MediumImage' => $modelRssFeed->description,
											'Type' => '1',
											'Width' => '',
											'Height' => '');
				
				//create Post element
				$postEl = (object) array('ArtistID' => $artistId,
										'PostID' => '0',
										'PostTitle' => "[Repost From Instagram] ".$modelRssFeed->title,
										'ReplyStreamURL' => '',
										'PostType' => '2',
										'VideoUrl' => '',
										'ThumbImage' => $modelRssFeed->description,
										'ProductID' => '',
										'Price' => '0.00',
										'VideoThumbImage' => '',
										'Description' => '',
										'IsExclusive' => '0',
										'FullVideoLink' => '',
										'IsPurchased' => '0',
										'PostLikeActivityID' => '0',
										'PostFlagActivityID' => '0',
										'DateTime' => date("Y-m-d h:m:s", strtotime($item['pubDate'])),
										'AnsTime' => '0',
										'TotalComments' => '0',
										'TotalLikes' => '0',
										'TotalFlags' => '0',
										'EnableSharing' => '0',
										'TotalShares' => '0',
										'IsAdvertisement' => '0',
										'DeviceType' => '',
										'QAType' => '',
										'IsIgnored' => null,
										'MemberName' => '',
										'Reply' => '',
										'ReplyThumbImage' => '',
										'IsPublic' => null,
										'RequestedPrivacy' => null,
										'ProfileThumb' => '',
										'Width' => '',
										'Height' => '',
										'SocialPost' => '1',
										'Link' => $xmllink,
										'AudioReplyUrl' => '',
										'PostImage' => array($postImgEl),
										'LatestComments' => array());										
				
				array_push($res, $postEl);
				
				//fwrite($myfile, $postImgEl->ImageName."\n");
			}
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
		
		return $res;
	}
	
	
	//Daniele: function that given a general Blog/Website feed url(in Media RSS format), returns the array of feeds in the postImage format
	protected function getBlogFeeds($url, $artistId){
		$res = array();
		
		$logString  = "";
        try
        {
		
            $xml = $url;
            $userModel = new User;
			ini_set('max_execution_time', 60);
            $items = $userModel->xmlparsingDan($xml);
			foreach($items as $item){
				$xmltitle = '';
				$xmldesc = '';
				$xmllink = '';
				$xmlmediatitle = '';
				$xmlmediathumb = '';
				$xmlcontenturl = '';
				if (isset($item['title']) && $item['title'] != '') $xmltitle = $item['title'];
				if (isset($item['description']) && $item['description'] != '') $xmldesc = $item['description'];
				if (isset($item['link']) && $item['link'] != '') $xmllink = $item['link'];
				if (isset($item['media:title']) && $item['media:title'] != '') $xmlmediatitle = $item['media:title'];
				if (isset($item['media:thumbnail']['url']) && $item['media:thumbnail']['url'] != '') $xmlmediathumb = $item['media:thumbnail']['url'];
				if (isset($item['media:content']['url']) && $item['media:content']['url'] != '') $xmlcontenturl = $item['media:content']['url'];
				$modelRssFeed = new Rssfeed;
				$modelRssFeed->title = $xmltitle;
				$modelRssFeed->description = $xmldesc;
				$img_name = end(explode("/", $xmlmediathumb));
				$modelRssFeed->media_title = $img_name;
				$modelRssFeed->media_thumbnail = $xmlmediathumb;
				$modelRssFeed->media_content = $xmlcontenturl;
				
				
				//create PostImage element
				$postImgEl = null;
				if($modelRssFeed->media_thumbnail != null && $modelRssFeed->media_thumbnail != ""){
				$postImgEl = (object) array('ID' => '1', 
											'Title' => $modelRssFeed->title,
											'ImageName' => $modelRssFeed->media_title,
											'Image' => $modelRssFeed->media_thumbnail,
											'ThumbImage' => $modelRssFeed->media_thumbnail,
											'MediumImage' => $modelRssFeed->media_thumbnail,
											'Type' => '1',
											'Width' => '',
											'Height' => '');
				}
				
				//create Post element
				$postEl = (object) array('ArtistID' => $artistId,
										'PostID' => '0',
										'PostTitle' => $modelRssFeed->title,
										'ReplyStreamURL' => '',
										'PostType' => '1',
										'VideoUrl' => '',
										'ThumbImage' => $modelRssFeed->media_thumbnail,
										'ProductID' => '',
										'Price' => '0.00',
										'VideoThumbImage' => '',
										'Description' => $modelRssFeed->description,
										'IsExclusive' => '0',
										'FullVideoLink' => '',
										'IsPurchased' => '0',
										'PostLikeActivityID' => '0',
										'PostFlagActivityID' => '0',
										'DateTime' => date("Y-m-d h:m:s", strtotime($item['pubDate'])),
										'AnsTime' => '0',
										'TotalComments' => '0',
										'TotalLikes' => '0',
										'TotalFlags' => '0',
										'EnableSharing' => '0',
										'TotalShares' => '0',
										'IsAdvertisement' => '0',
										'DeviceType' => '',
										'QAType' => '',
										'IsIgnored' => null,
										'MemberName' => '',
										'Reply' => '',
										'ReplyThumbImage' => '',
										'IsPublic' => null,
										'RequestedPrivacy' => null,
										'ProfileThumb' => '',
										'Width' => '',
										'Height' => '',
										'SocialPost' => '1',
										'Link' => $xmllink,
										'AudioReplyUrl' => '',
										'PostImage' => array(),
										'LatestComments' => array());	
				if($postImgEl != null){ array_push($postEl->PostImage, $postImgEl);}
				
				array_push($res, $postEl);
				
				//fwrite($myfile, $postImgEl->ImageName."\n");
			}
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
		
		return $res;
	}
	
	
	
	//Method that returns Instagram posts only
	public function actionInstagrampostlist() {
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'UserID',
                'ArtistID',
                'ProfileID',
                'IsExclusive',
                'PageIndex',
                'UserType',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $postData = array();
                $UserID = $data->UserID;
                $profileID = $data->ProfileID;
                $artistID = $data->ArtistID;
                $isExclusive = $data->IsExclusive;
                $pageindex = $data->PageIndex;
                $usertype = $data->UserType;
                $language = $data->Language;
                $recordcnt = 0;
				
				
				//Added by Daniele: append Instagram & Blog images from RSS feed: TEST
				$artist = Artist::find()->where(['ArtistId' => $artistID])->one();
				$instaUrl = $artist->InstagramPageURL;
				$instaFeeds = null;
						
				//Instagram feed: extract username and generates feed using rssbridge service
				if($instaUrl != null && $instaUrl != ""){
							
					$spliurl = explode('/', $instaUrl);
					$instaUserName = "";
					if($spliurl[count($spliurl)-1] != ""){ $instaUserName = $spliurl[count($spliurl)-1];}else{ $instaUserName = $spliurl[count($spliurl)-2];}
							
							
					if($instaUserName != ""){
						$instaFeeds = $this->getInstagramFeeds('http://rssbridge.buddylist.co/?action=display&bridge=InstagramBridge&u='.$instaUserName.'&format=MrssFormat', $artistID);
					}
							
				}		
				
				if($instaFeeds != null){
					$recordcnt += count($instaFeeds);
					foreach ($instaFeeds as $instaEl){
						if(count($postData) < (self::Limit)){
							array_push($postData, $instaEl);
						}
					}
				}

				
                /* $procedure3              =	"CALL AddQueryLog(0,0,'Post_List- postData','".print_r($postData,true)."')";
                  $command3                =	$connection->createCommand($procedure3);
                  $querylog3               = 	$command3->execute(); */
                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessagePostList($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $rssaray = array();
                $i = 0;
                $j = 0;
                /*                 * ******************************* Unread QA **************************** */
                $unreadQAData = $this->unreadQA($artistID);
                /*                 * ********************************************************************************** */
                if ($usertype == "3")
                {
                    $this->setHeader(400);
                    //$recordcnt = count($postData);
                    $encodedArray = array(
                        'Status' => $status,
                        "Message" => $lngmsg,
                        "RecordCount" => $recordcnt,
                        "Result" => $postData,
                        'UnreadQA' => $unreadQAData);
                    
                    $encodedArray = str_replace("\\", "\\\\", $encodedArray);
                    echo json_encode($encodedArray, JSON_PRETTY_PRINT); //JSON_PRETTY_PRINT
                }
                else
                {
                    echo json_encode(['Status' => $status,
                        "Message" => $lngmsg,
                        "RecordCount" => $recordcnt,
                        "Result" => $postData,
                        'UnreadQA' => $unreadQAData], JSON_PRETTY_PRINT);
                }
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessagePostList(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    "RecordCount" => "0"], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }
	
	
	//Method that returns Youtube posts only
	public function actionYoutubepostlist() {
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'UserID',
                'ArtistID',
                'ProfileID',
                'IsExclusive',
                'PageIndex',
                'UserType',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $postData = array();
                $UserID = $data->UserID;
                $profileID = $data->ProfileID;
                $artistID = $data->ArtistID;
                $isExclusive = $data->IsExclusive;
                $pageindex = $data->PageIndex;
                $usertype = $data->UserType;
                $language = $data->Language;
                $recordcnt = 0;
				
				
				$artist = Artist::find()->where(['ArtistId' => $artistID])->one();
				$ytUrl = $artist->YouTubePageURL;
				$ytFeeds = null;
						
				//YT feed: extract channelID or channelName and get feed
				if($ytUrl != null && $ytUrl != ""){
							
					$spliurl = explode('/', $ytUrl);
					$channelId = $spliurl[count($spliurl)-1];
							
							
					if($channelId != ""){
						//tries with channelName first
						$ytFeeds = $this->getYoutubeFeeds('https://www.youtube.com/feeds/videos.xml?user='.$channelId, $artistID);
						//if empty tries with channelID
						if($ytFeeds == null){
							$ytFeeds = $this->getYoutubeFeeds('https://www.youtube.com/feeds/videos.xml?channel_id='.$channelId, $artistID);
						}
					}
							
				}		
				
				if($ytFeeds != null){
					$recordcnt += count($ytFeeds);
					foreach ($ytFeeds as $ytEl){
						if(count($postData) < (self::Limit)){
							array_push($postData, $ytEl);
						}
					}
				}

				
                /* $procedure3              =	"CALL AddQueryLog(0,0,'Post_List- postData','".print_r($postData,true)."')";
                  $command3                =	$connection->createCommand($procedure3);
                  $querylog3               = 	$command3->execute(); */
                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessagePostList($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $rssaray = array();
                $i = 0;
                $j = 0;
                /*                 * ******************************* Unread QA **************************** */
                $unreadQAData = $this->unreadQA($artistID);
                /*                 * ********************************************************************************** */
                if ($usertype == "3")
                {
                    $this->setHeader(400);
                    //$recordcnt = count($postData);
                    $encodedArray = array(
                        'Status' => $status,
                        "Message" => $lngmsg,
                        "RecordCount" => $recordcnt,
                        "Result" => $postData,
                        'UnreadQA' => $unreadQAData);
                    
                    $encodedArray = str_replace("\\", "\\\\", $encodedArray);
                    echo json_encode($encodedArray, JSON_PRETTY_PRINT); //JSON_PRETTY_PRINT
                }
                else
                {
                    echo json_encode(['Status' => $status,
                        "Message" => $lngmsg,
                        "RecordCount" => $recordcnt,
                        "Result" => $postData,
                        'UnreadQA' => $unreadQAData], JSON_PRETTY_PRINT);
                }
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessagePostList(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg,
                    "RecordCount" => "0"], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }
	
	
	//Daniele: function that given the YT feed url(in Media RSS format), returns the array of feeds in the postImage format
	protected function getYoutubeFeeds($url, $artistId){
		$res = array();
		
		$logString  = "";
        try
        {
		
            $xml = $url;
            $userModel = new User;
			ini_set('max_execution_time', 120);
            $items = $userModel->xmlparsingDanYT($xml);
			
			foreach($items as $item){
				$xmltitle = '';
				$xmldesc = '';
				$xmllink = '';
				$xmlmediatitle = '';
				$xmlmediathumb = '';
				$xmlcontenturl = '';
				if (isset($item['title']) && $item['title'] != '') $xmltitle = $item['title'];
				if (isset($item['media:description']) && $item['media:description'] != '') $xmldesc = $item['media:description'];
				if (isset($item['link']) && $item['link'] != '') $xmllink = $item['link'];
				if (isset($item['media:title']) && $item['media:title'] != '') $xmlmediatitle = $item['media:title'];
				if (isset($item['media:thumbnail']['url']) && $item['media:thumbnail']['url'] != '') $xmlmediathumb = $item['media:thumbnail']['url'];
				if (isset($item['media:content']['url']) && $item['media:content']['url'] != '') $xmlcontenturl = $item['media:content']['url'];
				$modelRssFeed = new Rssfeed;
				$modelRssFeed->title = $xmltitle;
				$modelRssFeed->description = $xmldesc;
				$img_name = end(explode("/", $xmlmediathumb));
				$modelRssFeed->media_title = $img_name;
				$modelRssFeed->media_thumbnail = $xmlmediathumb;
				$modelRssFeed->media_content = $xmlcontenturl;
				
				
				//create PostImage element
				$postImgEl = (object) array('ID' => '0', 
											'Title' => "[Repost From Youtube] ".$modelRssFeed->title,
											'ImageName' => $modelRssFeed->media_title,
											'Image' => $modelRssFeed->media_thumbnail,
											'ThumbImage' => $modelRssFeed->media_thumbnail,
											'MediumImage' => $modelRssFeed->media_thumbnail,
											'Type' => '1',
											'Width' => '',
											'Height' => '');
				
				//create Post element
				$postEl = (object) array('ArtistID' => $artistId,
										'PostID' => '0',
										'PostTitle' => "[Repost From Youtube] ".$modelRssFeed->title,
										'ReplyStreamURL' => '',
										'PostType' => '2',
										'VideoUrl' => $modelRssFeed->media_content,
										'ThumbImage' => $modelRssFeed->media_thumbnail,
										'ProductID' => '',
										'Price' => '0.00',
										'VideoThumbImage' => '',
										'Description' => $modelRssFeed->description,
										'IsExclusive' => '0',
										'FullVideoLink' => '',
										'IsPurchased' => '0',
										'PostLikeActivityID' => '0',
										'PostFlagActivityID' => '0',
										'DateTime' => date("Y-m-d h:m:s", strtotime($item['published'])),
										'AnsTime' => '0',
										'TotalComments' => '0',
										'TotalLikes' => '0',
										'TotalFlags' => '0',
										'EnableSharing' => '0',
										'TotalShares' => '0',
										'IsAdvertisement' => '0',
										'DeviceType' => '',
										'QAType' => '',
										'IsIgnored' => null,
										'MemberName' => '',
										'Reply' => '',
										'ReplyThumbImage' => '',
										'IsPublic' => null,
										'RequestedPrivacy' => null,
										'ProfileThumb' => '',
										'Width' => '',
										'Height' => '',
										'SocialPost' => '1',
										'Link' => $xmllink,
										'AudioReplyUrl' => '',
										'PostImage' => array($postImgEl),
										'LatestComments' => array());										
				
				array_push($res, $postEl);
				
				//fwrite($myfile, $postImgEl->ImageName."\n");
			}
        }
        catch (ErrorException $e)
        { 
			$this->addLog($logString,$e);
        }
		
		return $res;
	}
	
	
	
	
	//Method that given iTunes Bundle Id returns first Artist Id that mathces in settings or stickers
	//http://192.168.0.109:888/api_ver2/web/?r=user/get-artist-from-bundle&bundle=test
	public function actionGetartistfrombundle($bundle) {
        $logString  = "";
        try
        {
			$found = false;
			$ArtistID = null;
			
			//try to search on settings table
			//$setting_tb = Setting::find()->where(['ProductSKUID' => $bundle])->one();
			$setting_tb = Setting::find()->where(['or', ['like', 'ProductSKUID', $bundle], ['like', 'TextProductSKUID', $bundle], ['like', 'VideoProductSKUID', $bundle], ['like', 'AndroidSKUID', $bundle]])->one();
			
			
			if($setting_tb != null){
				$found = true;
				$ArtistID = $setting_tb->ArtistID;
			}else{
				//try on stickers table
				//$sticker_tb = Sticker::find()->where(['IOSSKUID' => $bundle])->one();
				$sticker_tb = Sticker::find()->where(['like', 'IOSSKUID', $bundle])->one();
				if($sticker_tb != null){
					$found = true;
					$ArtistID = $sticker_tb->ArtistID;
				}
			}
		
            echo json_encode(['Found' => $found,
							"Bundle" => $bundle,
							"ArtistId" => $ArtistID], JSON_PRETTY_PRINT);
               
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);
        }
    }	
	
	
	
	public function actionStickerspaging() {
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ProfileID',
                'PageIndex',
                'DeviceType',
                'Language',
                'ArtistID');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            $artistID = "";
            $stickersData = array();
			$totalNumber = 0;
			$prodList = array();
			$type = "";
			if(isset($data->Type) && $data->Type != ''){ $type = $data->Type;}
            if (count($compareField) == 0)
            {
                $memberID = $data->ProfileID;
                $deviceType = $data->DeviceType;
                $language = $data->Language;
				$page = $data->PageIndex;
                if (isset($data->ArtistID) && $data->ArtistID != '') :
                    $artistID = $data->ArtistID;
                    $connection = Yii::$app->db;
                    //added by Kate--deeplinking
                    $procedure = "CALL Sticker_List(0," . $artistID . "," . $deviceType . ",'" . self::S3BucketAbsolutePath . "/','" . self::BoomFolder . $artistID . self::S3BucketStickers . "','" . self::BoomFolder . $artistID . self::S3BucketStickersSmall . "','" . self::BoomFolder . $artistID . self::S3BucketStickersMedium . "')";
                    $logString.="\n Sticker List : ".$procedure.'\n';	
                    //echo $procedure; die;
                    $command = $connection->createCommand($procedure);
                    $stickersData = $command->queryAll();
                endif;
				

                foreach ($stickersData as $key => $value)
                {
                    $stickerImages = explode(',', $stickersData[$key]['StickerImage']);
                    $stickerThumbImages = explode(',', $stickersData[$key]['StickerThumbImage']);
                    $stickerMediumImages = explode(',', $stickersData[$key]['StickerMediumImage']);
                    $stickerImagesID = explode(',', $stickersData[$key]['StickerImageID']);
                    $stickerImage = array();
                    for ($n = 0; $n < count($stickerImagesID); $n++)
                    {
                        $stickerImage[$n]['StickerImageID'] = $stickerImagesID[$n];
                        $stickerImage[$n]['StickerImage'] = $stickerImages[$n];
                        $stickerImage[$n]['StickerThumbImage'] = $stickerThumbImages[$n];
                        $stickerImage[$n]['StickerMediumImage'] = $stickerMediumImages[$n];
                    }
                    $stickersData[$key]['StickerImage'] = $stickerImage;
                    unset($stickersData[$key]['StickerThumbImage']);
                    unset($stickersData[$key]['StickerMediumImage']);
                    unset($stickersData[$key]['StickerImageID']);
                }
				
				//set unavailableTypes
				$unavailableTypes = array(1,2,3,5);
				foreach ($stickersData as $sticker){
					//image
					if($sticker['Type'] == 1){
						//check if gif
						$foundGif = false; $foundImg = false;
						foreach ($sticker['StickerImage'] as $image){
							$ext = end(explode('.', $image['StickerImage']));
							if($ext == "gif"){ $foundGif = true;}else{ $foundImg = true;}
						}
						if($foundGif){
							if (($key = array_search(5, $unavailableTypes)) !== false) {
								unset($unavailableTypes[$key]);
							}
						}
						if($foundImg){
							if (($key = array_search(1, $unavailableTypes)) !== false) {
								unset($unavailableTypes[$key]);
							}
						}
					}
					//video
					if($sticker['Type'] == 2){
						if (($key = array_search($sticker['Type'], $unavailableTypes)) !== false) {
							unset($unavailableTypes[$key]);
						}
					}
					//wallpaper+photo
					if($sticker['Type'] == 3 || $sticker['Type'] == 4){
						if (($key = array_search(3, $unavailableTypes)) !== false) {
							unset($unavailableTypes[$key]);
						}
					}
					
				}
				
				//Type: 1-Image, 2-Video, 3-wallpaper+photo, 4-photo, 5-Gif, 6-All
				if($type != "" && $type != "6"){
					if($type == "1" || $type == "2" || $type == "3" || $type == "4"){
						$remndx = array();
						$i=0;
						foreach ($stickersData as $sticker){
							//case: 3-wallpaper+photo
							if($type == "3"){
								if($sticker['Type'] != "3" && $sticker['Type'] != "4"){
									array_push($remndx, $i);
								}
							}else{
								//case: 1,2,4
								if($sticker['Type'] != $type){
									array_push($remndx, $i);
								}
							}
							$i++;
						}
						foreach ($remndx as $ndx){
							unset($stickersData[$ndx]);
						}
					}
					//case: 1-Image - excludes gif
					if($type == "1"){
						$remndx = array();
						$j = 0;
						$keys = array_keys($stickersData);
						foreach ($stickersData as $sticker){
							$remimgndx = array();
							$i=0;
							foreach ($sticker['StickerImage'] as $image){
								$ext = end(explode('.', $image['StickerImage']));
								if($ext == "gif"){ array_push($remimgndx, $i);}
								$i++;
							}
							foreach ($remimgndx as $ndx){
								unset($stickersData[$keys[$j]]['StickerImage'][$ndx]);
							}
							if(count($stickersData[$keys[$j]]['StickerImage']) == 0){ array_push($remndx, $keys[$j]); }
							$j++;
						}
						foreach ($remndx as $ndx){
							unset($stickersData[$ndx]);
						}
					}
					//case: 5-Gif
					if($type == "5"){
						$remndx = array();
						$j = 0;
						$keys = array_keys($stickersData);
						foreach ($stickersData as $sticker){
							$remimgndx = array();
							$i=0;
							foreach ($sticker['StickerImage'] as $image){
								$ext = end(explode('.', $image['StickerImage']));
								if($ext != "gif"){ array_push($remimgndx, $i);}
								$i++;
							}
							foreach ($remimgndx as $ndx){
								unset($stickersData[$keys[$j]]['StickerImage'][$ndx]);
							}
							if(count($stickersData[$keys[$j]]['StickerImage']) == 0){ array_push($remndx, $keys[$j]); }
							$j++;
						}
						foreach ($remndx as $ndx){
							unset($stickersData[$ndx]);
						}
					}
				}
				
				//calculates product list
				foreach ($stickersData as $sticker){
					$prodId = $sticker['ProductID'];
					if($prodId != null && $prodId != ""){
						$found = false;
						foreach ($prodList as $product){
							if($prodId == $product){ $found=true; break;}
						}
						if(!$found){ array_push($prodList, $prodId);}
					}
				}
				
				//calculates total and paging
				$totalNumber = count($stickersData);
				$maxret = 12;
				if($totalNumber > 0){
					$stickersData = array_slice($stickersData, ($maxret * ($page-1)), $maxret);
				}
				

                if (count($stickersData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageForStickers($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
					"RecordCount" => $totalNumber,
					"unavailableTypes" => array_values($unavailableTypes),
					"ProductList" => array_values($prodList),
                    "Result" => $stickersData], JSON_PRETTY_PRINT);
            }
            else
            {
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageForStickers(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(['Status' => 0,
					"RecordCount" => $totalNumber,
					"unavailableTypes" => array_values($unavailableTypes),
					"ProductList" => array_values($prodList),
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e); 
        }
    }
	
	
	public function actionGetartistcompany() {
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';	
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'CompanyID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $cmpyID = $data->CompanyID;
                $language = $data->Language;
				
				$bundleData = null;
				
				//get Artist Company data
				$CompanyData = Artist_company::find()->where(['Id' => $cmpyID])->one();
				if($CompanyData != null){
					
					//get all Artists for the group
					$Artists = Artist::find()->where(['CompanyID' => $cmpyID])->all();
					
					$artistList = array();

                    //added subsription product info for Artist  --Kate
                    $modelSetting = new \backend\models\Setting();
					foreach($Artists as $art){
                        $bundleData = $modelSetting->find()->where(array(
                            'ArtistID' => $art->ArtistID))->asArray()->all();
                        if(count($bundleData)>0){
                            foreach ($bundleData as $key => $products) {
                                if($products['ProductSKUID']!='')
                                    $productID=$products['ProductSKUID'];
                                $ProductPrice=$products['ProductPrice'];
                                $AndroidSKUID=$products['AndroidSKUID'];
                            }
                        }else{
                                $productID='';
                                $ProductPrice=0;
                                $AndroidSKUID='';
                        }

                        $products=array('ProductPrice'=>$ProductPrice,'ProductSKUID'=>$productID,'AndroidSKUID'=>$AndroidSKUID);
                        //added ArtistUSerID---Kate
						$artobj = ['ArtistID' => $art->ArtistID, 'ArtistName' => $art->ArtistName, 'ProfileThumb' => $art->ProfileThumb, 'Genre' => $art->Genre, 'ArtistUserID' => $art->UserID,'products'=>$products];
						array_push($artistList, $artobj);
					}
					
					$bundleData = ['CompanyName' => $CompanyData->Name,
								'CompanyID' => $CompanyData->Id,
								'RecordCount' => count($Artists),
								'Artists' => $artistList];
								
					$resultMessage = _getStatusCodeMessageForUser(200);
					\Yii::$app->language = $language;
					$lngmsg = \Yii::t('api', $resultMessage);
					$this->setHeader(200);
					echo json_encode(['Status' => 1,
						"Message" => $lngmsg,
						'Company' => $bundleData], JSON_PRETTY_PRINT);
							
				}else{
					$resultMessage = _getStatusCodeMessageForPurchase(202);
					\Yii::$app->language = $language;
					$lngmsg = \Yii::t('api', $resultMessage);
					$this->setHeader(400);
					echo json_encode(['Status' => 0,
						"Message" => $lngmsg], JSON_PRETTY_PRINT);
				}
				
				

            }
            else
            {
                $resultMessage = _getStatusCodeMessageForUser(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);    
        }
    }
	
	//Daniele
    //added by Kate--deeplinking
    public function actionGetdpinfo() {
    /***
    * Example Input {"ReqID":"2508","APIType":"1","DeviceType":"1","ArtistID":"10"}
    * Example Response
    **/  
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ReqID',
                'APIType',
                'DeviceType',
                'ArtistID');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $artistID = $data->ArtistID;
                $reqID = $data->ReqID;
                $APIType = $data->APIType;
                $deviceType=$data->DeviceType;

                $connection = Yii::$app->db;

                $artistData = (object) array();
                $postData = (object) array();
                $stickersData = (object) array();

                if($APIType=='2'){
                    $procedure = "CALL Member_GetProfile('" . $reqID . "','2','" . $artistID . "','" . self::EncryptKey . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "')";
                    $logString.="\n Artist Profile : ".$procedure.'\n';
                    $command = $connection->createCommand($procedure);
                    $artist_profileData = $command->queryAll();

                    if (count($artist_profileData) > 0)
                    {
                    /*************** Get Artist Image List **************/
                        $postimagesprocedure = "CALL Artist_Image_List(2,0," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketArtistImages . "','" . self::S3BucketArtistThumb . "','" . self::S3BucketArtistMedium . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $commandForImage = $connection->createCommand($postimagesprocedure);
                        $artistimages = $commandForImage->queryAll();
                        $artist_profileData[0]['ArtistImage'] = $artistimages;

                        $resultCode = 200;
                        $status = "1";
                    }else{
                        $resultCode = 404;
                        $status = "0";
                    }

                    $artistData = (object) array();
                    if (!empty($artist_profileData) && isset($artist_profileData[0]))
                    {
                        $artistData = $artist_profileData[0];
                    }
                }else if($APIType=='1'){
                    $userID=0;
                    $profileID=0;
                    $postData_proc = "CALL Post_List(" . $reqID . "," . $artistID . "," . $userID . "," . $profileID . ",0,-1,'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "','','',@o_RecCount)";
                    // echo $postData_proc;
                    $logString.="\n Post List : ".$postData_proc.'\n';
                    $command = $connection->createCommand($postData_proc);
                    $postData = $command->queryAll();
                    if (count($postData) > 0)
                    {
                    /*************** Get Image List **************/
                        $imageData = array();
                        /************* Get Post images **************/
                        $postimageproc = "CALL Post_Image_List(1," . $reqID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketPostImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $logString.="\n Post Image List : ".$postimageproc.'\n';    
                        //echo $postimageproc; die;
                        $commandForImage = $connection->createCommand($postimageproc);
                        $imageData = $commandForImage->queryAll();

                        if (count($imageData) > 0)
                        {
                            $postData[0]['PostImage'] = $imageData;
                        }
                        else
                        {
                            $postData[0]['PostImage'] = $imageData;
                        }
                        $postData[0]['LatestComments'] = array();

                        $resultCode = 200;
                        $status = "1";
                    }else{
                        $resultCode = 404;
                        $status = "0";
                    }
                }
                else if($APIType=='3'){

                    $procedure = "CALL Sticker_List(" . $reqID . "," . $artistID . "," . $deviceType . ",'" . self::S3BucketAbsolutePath . "/','" . self::BoomFolder . $artistID . self::S3BucketStickers . "','" . self::BoomFolder . $artistID . self::S3BucketStickersSmall . "','" . self::BoomFolder . $artistID . self::S3BucketStickersMedium . "')";
                    $logString.="\n Sticker List : ".$procedure.'\n';    
                    $command = $connection->createCommand($procedure);
                    $stickersData = $command->queryAll();
                    
                    if (count($stickersData) > 0)
                    {
                        $stickerImages = explode(',', $stickersData[0]['StickerImage']);
                        $stickerThumbImages = explode(',', $stickersData[0]['StickerThumbImage']);
                        $stickerMediumImages = explode(',', $stickersData[0]['StickerMediumImage']);
                        $stickerImagesID = explode(',', $stickersData[0]['StickerImageID']);
                        $stickerImage = array();
                        for ($n = 0; $n < count($stickerImagesID); $n++)
                        {
                            $stickerImage[$n]['StickerImageID'] = $stickerImagesID[$n];
                            $stickerImage[$n]['StickerImage'] = $stickerImages[$n];
                            $stickerImage[$n]['StickerThumbImage'] = $stickerThumbImages[$n];
                            $stickerImage[$n]['StickerMediumImage'] = $stickerMediumImages[$n];
                        }
                        $stickersData[0]['StickerImage'] = $stickerImage;
                        unset($stickersData[0]['StickerThumbImage']);
                        unset($stickersData[0]['StickerMediumImage']);
                        unset($stickersData[0]['StickerImageID']);

                        $resultCode = 200;
                        $status = "1";
                    }else{
                        $resultCode = 404;
                        $status = "0";
                    }
                
                }else{
                    $resultCode = 404;
                    $status = "0";
                }


                $resultMessage = _getStatusCodeMessageCommon($resultCode);
                echo json_encode(['Status' => $status,
                        "Message" => $resultMessage,
                        // 'Result' => $loginData,
                        'PostData' => $postData,
                        'ArtistProfile' => $artistData,
                        'stickersData' => $stickersData
                        // 'QaData' => $qaData,
                        ], JSON_PRETTY_PRINT);
            }else{
                $resultMessage = _getStatusCodeMessageForUser(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        { 
            $this->addLog($logString,$e);    
        }
    }



    
	
	
}

?>