<?php
namespace api_ver4\controllers;

use api_ver4\models\OneSignalPushNotification;
use api_ver4\helpers\ActivityHelper;
use api_ver4\jobs\ActivityJob;
use api_ver4\models\User;
use backend\models\Artist;
use backend\models\Artist_company;
use backend\models\Feedarticles;
use backend\models\PostPages;
use backend\models\UserArtist;
use ErrorException;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\auth\HttpBasicAuth;
use yii\web\Controller;
use yii\filters\VerbFilter;
use backend\models\Rssfeed;
use backend\models\Setting;
use backend\models\Sticker;
use backend\models\Member;
use backend\models\Nativeproducts;
use backend\models\Memberpost;
use yii\redis\Connection;

/**
 * User controller
 */
class UserController extends Controller
{
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

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    private $arrSkipAction = [
        'create',
        'login',
        'addfeed'
    ];



    public function behaviors() {
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
                'getartistfrombundle',//Daniele
                'deletecomment',
                'instagrampostlist',//Daniele
                'stickerspaging',//Daniele
                'getartistcompany',
                'searchartistcompany',
                'getnativeproduct',
                'cancelpurchase',
                'getdpinfo', //added by Kate--deeplinking
                'updateusername', //added by Daniele -- updates username
                'getnativesitunes', //added by Daniele
                'testpushdan', //TEST by daniele
                'updatedevicetoken',
                'testonesignalpush',
                'updateosfanappid'
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
                'getartistfrombundle' => ['get'], //Daniele
                'deletecomment' => ['post','get'],
                'instagrampostlist' => ['post'],//Daniele
                'getartistcompany' => ['post'],
                'searchartistcompany' => ['post'],
                'stickerspaging' => ['post'],//Daniele
                'getdpinfo' => ['post'], //added by Kate--deeplinking
                'getnativeproduct'=>['post'], //added by Kate--Native product list
                'cancelpurchase'=>['post'], // added by Kate---cancel subscription purchase
                'updateusername'=>['post'], //Daniele
                'getnativesitunes'=>['get'], //added by Daniele
                'testpushdan' =>['post'], //TEST by Daniele
                'updatedevicetoken' =>['post'],
                'testonesignalpush' =>['post'],
                'updateosfanappid' => ['post'],
                'test' => ['get']
            ]
        ];
        return $behaviors;
    }

    public function beforeAction($event) {
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

    private function setHeader($status) {
        $statusHeader = 'HTTP/1.1' . $status . ' ' . _getStatusCodeMessageForUser($status);
        $contentType = "application/json; charset=utf-8";
        header($statusHeader);
        // header('Content-type: ' . $contentType);
        header('Content-Type: text/html; charset=utf-8');
    }

    public function udate($format, $utimestamp = null) {
        if (is_null($utimestamp)) $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }

    public function actionLogin() {
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

                //OnesignalAppID for artist app only, for fan app, there is another api to update it's os app id
                if (isset($data->OnesignalAppID)) {
                    $OnesignalAppID = $data->OnesignalAppID;
                } else {
                    $OnesignalAppID = "";
                }
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
                //added for show free content ---Kate
                if(isset($data->IsExclusive)){
                    $isExclusive = $data->IsExclusive;
                }else $isExclusive=0;
                $connection = Yii::$app->db;
                $procedure = "CALL Member_Login_API3('" . $username . "','" . $fbmembername . "','" . $fbEmail . "','" . $password . "','" . $devicetype . "','" . $devicetoken . "'," . $usertype . "," . $artistID . "," . $loginType . ",'" . self::EncryptKey . "','" . self::S3BucketPath . "','" . self::S3BucketProfileImages . "','".$OnesignalAppID."',@UserID,@ProfileID,@UserType,@ErrorCode)";
                $logString.="\n SP : ".$procedure.'\n';

                $command = $connection->createCommand($procedure);
                $loginData = $command->queryAll();
                $resultCode = $loginData[0]['ErrorCode'];
                //$resultMessage = 	$loginData[0]['Message'];
                $resultMessage = _getStatusCodeMessageForUser($resultCode);
                $userID = $loginData[0]['UserID'];
                $artistUserID = $loginData[0]['ArtistUserID'];

                //added for migrate user--Kate--17-06-2017
                $loginData[0]['Is_migrate']=0;
                if($resultCode=="404") {
                    $query="select * from user where Username=AES_ENCRYPT('".$username."',PASSWORD('boom@123')) and ArtistID=".$artistID." and Is_migrate=1 and UserType=3";
                    $result= Yii::$app->db->createCommand($query)->execute();
                    if($result) {
                        $loginData[0]['Is_migrate']=1;
                    }
                }

                if ($artistID > 0)
                {
                    $artistProfileID = $artistID;
                }else if($ComID!="0"&&$usertype=="3"){ // Boom Native app--Kate
                    $artists=UserArtist::find()->where(" UserID=".$userID." and isSub=1")->all(); // For Native App need artist to subscribe

                    $artistProfileID=0;
                    if(count($artists)>0){
                        $ndx=0;
                        foreach($artists as $art){
                            $artist=Artist::find()->where(['ArtistID' => $art->ArtistID])->one();
                            if($artist->CompanyID != $ComID){
                                unset($artists[$ndx]);
                            }else{
                                $ndx++;
                                $artistID=$art->ArtistID;
                                $artistProfileID = $art->ArtistID;
                                $artistUserID = $artist->UserID;
                            }
                        }
                    }
                }
                else
                {
                    $artistProfileID = $loginData[0]['ProfileID'];
                }

                //For user to get login info
                $profileID = $loginData[0]['ProfileID'];
                $user_profile_proc = "CALL Member_GetProfile('" . $userID . "','3','" . $profileID . "','" . self::EncryptKey . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "')";
                $logString.="\n Member Profile : ".$user_profile_proc.'\n';
                $command2 = $connection->createCommand($user_profile_proc);
                $user_profileData = $command2->queryAll();

                if($artistProfileID!=0){

                    /************* Get Member Profile Data *************/

                    $artist_profile_proc = "CALL Member_GetProfile('" . $artistUserID . "','2','" . $artistProfileID . "','" . self::EncryptKey . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "')";
                    $logString.="\n Artist Profile : ".$artist_profile_proc.'\n';
                    /*echo $artist_profile_proc;
                    echo '<br>';
                    echo $user_profile_proc; die;*/

                    $command3 = $connection->createCommand($artist_profile_proc);

                    try {
                        $dependency = \Yii::createObject([
                            'class' => '\yii\caching\DbDependency',
                            'sql' => 'SELECT MAX(Updated) FROM artist',
                            'reusable' => true,
                        ]);
                    } catch (InvalidConfigException $e) {
                    }

                    try {
                        $artist_profileData = $connection->cache(function ($db) use ($command3) {
                            return $command3->queryAll();
                        }, $connection->queryCacheDuration, $dependency);
                    } catch (\Exception $e) {
                    }

                    if (count($artist_profileData) > 0)
                    {
                        /*************** Get Artist Image List **************/
                        $postimagesprocedure = "CALL Artist_Image_List(2,0," . $artistProfileID . ",'" . self::S3BucketPath . "','" . self::S3BucketArtistImages . "','" . self::S3BucketArtistThumb . "','" . self::S3BucketArtistMedium . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $commandForImage = $connection->createCommand($postimagesprocedure);
                        //$artistimages = $commandForImage->queryAll();

                        try {
                            $dependency_img = \Yii::createObject([
                                'class' => '\yii\caching\DbDependency',
                                'sql' => 'SELECT MAX(Updated), MAX(Created) COUNT(*) FROM gallery',
                                'reusable' => true,
                            ]);
                        } catch (InvalidConfigException $e) {
                        }

                        try {
                            $artistimages = $connection->cache(function ($db) use ($commandForImage) {
                                return $commandForImage->queryAll();
                            }, $connection->queryCacheDuration, $dependency);
                        } catch (\Exception $e) {
                        }

                        $artist_profileData[0]['ArtistImage'] = $artistimages;
                    }
                    //echo $postimagesprocedure; die;

                    /******************* Get Post List if Member **************/
                    //Boom Native App --Kate
                    if($ComID==0) {
                        //$isExclusive=0;
                        $logString.="\n Fan";  // added by Kate
                        //added by Kate--deeplinking
                        try {
                            $dependency_post = \Yii::createObject([
                                'class' => '\yii\caching\DbDependency',
                                'sql' => 'SELECT MAX(Updated), MAX(Created), SUM(IsDelete) FROM post WHERE artistID=' . $artistID,
                                'reusable' => true,
                            ]);
                        } catch (InvalidConfigException $e) {
                        }
                        $postData_proc = "CALL Post_List_API3(0," .  $artistID . "," . $userID . "," . $profileID . "," . $ComID . "," . $isExclusive . ",'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "',1,20)";//Daniele
                    }else { //For Native App to get multiple artist Post
                        $isExclusive=1;
                        $logString.="\n Native";
                        try {
                            $dependency_post = \Yii::createObject([
                                'class' => '\yii\caching\DbDependency',
                                'sql' => 'SELECT MAX(Updated), MAX(Created), SUM(IsDelete) FROM post WHERE artistID=' . $artistID . 'AND IsExclusive=1',
                                'reusable' => true,
                            ]);
                        } catch (InvalidConfigException $e) {
                        }
                        $postData_proc = "CALL Post_List(0, 0," . $userID . "," . $profileID . "," . $ComID . "," . $isExclusive . ",'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "','','',@o_RecCount)";
                    }


                    $logString.="\n Post List : ".$postData_proc.'\n';

                    //echo $postData_proc; die;
                    $command4 = $connection->createCommand($postData_proc);

                    try {
                        $postData = $connection->cache(function ($db) use ($command4) {
                            return $command4->queryAll();
                        }, $connection->queryCacheDuration, $dependency_post);
                    } catch (\Exception $e) {
                    }

                    $reccommand = $connection->createCommand('SELECT @o_RecCount')->queryOne();
                    $recordcnt = $reccommand['@o_RecCount'];

                    /*************** Get Similarapp list ****************/
                    $similarApp_proc = "CALL SimilarApp_List(" . $artistProfileID . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketAppIcons . "')";
                    //echo $similarApp_proc; die;
                    $command5 = $connection->createCommand($similarApp_proc);
                    $similarApp = $command5->queryAll();

                    /* * ******************************* Bundle Data For Artist **************************** */

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
                                if ($usertype == "3")
                                    $artistID = $value['ArtistID'];
                                /************ Get Post Images **************/

                                $postimageproc = "CALL Post_Image_List(1," . $postID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketPostImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                                $commandForImage = $connection->createCommand($postimageproc);

                                try {
                                    $dependency_post_image = \Yii::createObject([
                                        'class' => '\yii\caching\DbDependency',
                                        'sql' => 'SELECT MAX(Updated), MAX(Created), COUNT(*) FROM gallery WHERE RefTableID=' . $postID,
                                        'reusable' => true,
                                    ]);
                                } catch (InvalidConfigException $e) {
                                }
                                //print_r($dependency_post_image);
                                try {
                                    $imageData = $connection->cache(function ($db) use ($commandForImage) {
                                        return $commandForImage->queryAll();
                                    }, $connection->queryCacheDuration, $dependency_post_image);
                                } catch (\Exception $e) {
                                }
                                //post pages
                                $postData[$key]['Pages'] = array();
                                if($value['PostType'] == 5){
                                    $pages = PostPages::find()->where('PostID='.$postID)->all();
                                    foreach($pages as $page){
                                        $pagel = ['Number' => $page->PageNumber, 'Type' => $page->Type,
                                            'Text' => $page->Text,'VideoUrl' => $page->VideoUrl,
                                            'ImageUrl' => $page->ImageUrl, 'ImageWidth' => $page->ImageWidth, 'ImageHeight' => $page->ImageHeight,
                                            'VideoThumbnailImage' => $page->VideoThumbnailImage, 'VideoThumbnailImageWidth' => $page->VideoThumbnailImageWidth, 'VideoThumbnailImageHeight' => $page->VideoThumbnailImageHeight];
                                        array_push($postData[$key]['Pages'], $pagel);
                                    }
                                }

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

                        //fixes issue when AndroidSKUID is empty in DB but ProductSKUID is set. It will use ProductSKUID.
                        if ($devicetype == "2" && $productSKUID == "" && isset($QAData["0"]["ProductSKUID"]) && $QAData["0"]["ProductSKUID"] != "") {
                            $productSKUID = $QAData["0"]["ProductSKUID"];
                        }
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
                    $companyID = 0; $companyName = "";
                    if($artistID > 0 && $ComID==0){
                        $artist = Artist::find()->where(['ArtistId' => $artistID])->one();

                        if($artist->CompanyID != null){
                            $companyID = $artist->CompanyID;
                            $company = Artist_company::find()->where(['Id' => $companyID])->one();
                            $companyName = $company->Name;
                        }

                        if($artistData != null){
                            $artistData = (object) array_merge((array)$artistData, ['CompanyID' => $companyID, 'CompanyName' => $companyName]);
                        }

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
                            }else{
                                $articles = Feedarticles::find()->where(['ArtistID' => $artistID])->all();
                                if(count($articles) > 0){
                                    $Feeds = array();
                                    foreach($articles as $art){
                                        $feed = $this->getBlogFeeds($art->FeedUrl, $artistID);
                                        $Feeds = array_merge($Feeds, $feed);
                                    }
                                }
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
                            'SimilarApp' => $similarApp,
                            'stickersData' => $stickersData,
                            'IsQa' => $IsQAEnable,
                            'QaName' => $QAModuleName,
                            'QaType' => $QAType,
                            'BucketName'=>self::S3Bucket."/",
                            'CognitoPoolId'=>self::COGNITOID,
                            'paymetInfoQA' => $bundleData,
                            'UnreadQA' => $unreadQAData,
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
                            'SimilarApp' => $similarApp,
                            'stickersData' => $stickersData,
                            'IsQa' => $IsQAEnable,
                            'QaName' => $QAModuleName,
                            'QaType' => $QAType,
                            'BucketName'=>self::S3Bucket."/",
                            'CognitoPoolId'=>self::COGNITOID,
                            'paymetInfoQA' => $bundleData,
                            'UnreadQA' => $unreadQAData,
                            'ProductType_Subscription' => $productType,
                            'ProductPrice_Subscription' => $productPrice,
                            "ProductSKUID_Subscription" => $productSKUID], JSON_PRETTY_PRINT);
                    }

                }
                else { //$artistProfileID is 0
                    $userData = (object) array();
                    if (!empty($user_profileData) && isset($user_profileData[0]))
                    {
                        $userData = $user_profileData[0];
                        if (isset($userData['DOB']) && ($userData['DOB'] == "01-01-1970" || $userData['DOB'] == "0000-00-00"))
                        {
                            $userData['DOB'] = "";
                        }
                    }


                    if ($resultCode == "200")
                    {
                        echo json_encode(['Status' => 1,
                            "Message" => "User haven't subsribe artist yet",
                            'Result' => $loginData,
                            'BucketName'=>self::S3Bucket."/",
                            'CognitoPoolId'=>self::COGNITOID,
                            'MemberProfile' => $userData], JSON_PRETTY_PRINT);
                    }else{
                        $lngmsg = \Yii::t('api', $resultMessage);
                        echo json_encode(['Status' => 0,
                            "Message" => $lngmsg,
                            'Result' => $loginData,
                            'BucketName'=>self::S3Bucket."/",
                            'CognitoPoolId'=>self::COGNITOID,
                            'MemberProfile' => $userData], JSON_PRETTY_PRINT);
                    }
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
            //echo $logString;
        }
    }

    public function actionGetprofile() {
        $logString  = "";
        $language = "";
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

                try {
                    $dependency = \Yii::createObject([
                        'class' => '\yii\caching\DbDependency',
                        'sql' => 'SELECT MAX(Updated) FROM member',
                        'reusable' => true,
                   ]);
                } catch (InvalidConfigException $e) {
                }

                $profileData = $connection->cache(function ($db) use ($command) {
                    $profileData = $command->queryAll();
                    return $profileData;
                }, $connection->queryCacheDuration, $dependency);

                if (count($profileData) > 0)
                {
                    try {
                        $dependency_img = \Yii::createObject([
                            'class' => '\yii\caching\DbDependency',
                            'sql' => 'SELECT MAX(Updated), COUNT(*) FROM gallery',
                            'reusable' => true,
                        ]);
                    } catch (InvalidConfigException $e) {
                    }
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
                    try {
                        $imageData = $connection->cache(function ($db) use ($commandForImage) {
                            $imageData = $commandForImage->queryAll();
                            return $imageData;
                        }, $connection->queryCacheDuration, $dependency_img);
                    } catch (\Exception $e) {
                    }
                    //$imageData = $commandForImage->queryAll();
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
                if (isset($profileData[0]['DOB']) && ($profileData[0]['DOB'] == "01-01-1970" || $profileData[0]['DOB'] == "0000-00-00")) {
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
            echo json_encode(['Status' => 0,
                "Message" => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }

    public function actionPostlist() {

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
                if(isset($data->ComID)){ $ComID=$data->ComID;} // Boom Native app--Kate
                else $ComID=0;
                $connection = Yii::$app->db;

                // //boom Native App --Kate
                if($artistID==0 && $ComID!=0){ // Boom Native app--Kate
                    $isExclusive = 1;
                }

                $procedure = "CALL Post_List_API3(0, " . $artistID . "," . $UserID . "," . $profileID . "," . $ComID . "," . $isExclusive . ",'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "',$pageindex,20)";
                $logString.="\n Post Image List : ".$procedure.'\n';
                $command = $connection->createCommand($procedure);

                //$postData = $command->queryAll();;
                $dependency = \Yii::createObject([
                    'class' => '\yii\caching\DbDependency',
                    'sql' => 'SELECT MAX(Updated), MAX(Created), SUM(IsDelete) FROM post WHERE artistID=' . $artistID,
                    //'sql' => 'SELECT MAX(Updated) FROM post WHERE artistID='.$artistID,
                    'reusable' => true,
                ]);


                $postData = $connection->cache(function ($db) use ($command) {
                    return $command->queryAll();

                }, $connection->queryCacheDuration, $dependency);

                foreach ($postData as $key => $value)
                {
                    if (isset($value['PostID']) && $value['PostID'] != '')
                    {

                        $imageData = array();
                        $postID = $value['PostID'];
                        $artistID = $value['ArtistID'];

                        //algene - only PostType 2 (image post) should call Post_Image_List
                        /************* Get Post images **************/
                        if ($value['PostType'] == 2) {
                            $postimageproc = "CALL Post_Image_List(1," . $postID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketPostImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                            $logString .= "\n Post Image List : " . $postimageproc . '\n';
                            //echo $postimageproc; die;
                            $commandForImage = $connection->createCommand($postimageproc);
                            //$imageData = $commandForImage->queryAll();

                            try {
                                $imageData = $connection->cache(function ($db) use ($commandForImage) {
                                    $imageData = $commandForImage->queryAll();
                                    return $imageData;
                                });
                            } catch (\Exception $e) {
                            }
                        }

                        //post pages
                        $postData[$key]['Pages'] = array();
                        if($value['PostType'] == 5){
                            //$pages = PostPages::find()->where('PostID='.$postID)->all();

                            try {
                                $pages = PostPages::getDb()->cache(function ($db) use ($postID) {
                                    $pages = PostPages::find()->where('PostID=' . $postID)->all();
                                    return $pages;
                                });
                            } catch (\Exception $e) {
                            }

                            try {
                                $imageData = $connection->cache(function ($db) use ($commandForImage) {
                                    $imageData = $commandForImage->queryAll();
                                    return $imageData;
                                });
                            } catch (\Exception $e) {
                            }

                            foreach($pages as $page){
                                $pagel = ['Number' => $page->PageNumber, 'Type' => $page->Type,
                                    'Text' => $page->Text,'VideoUrl' => $page->VideoUrl,
                                    'ImageUrl' => $page->ImageUrl, 'ImageWidth' => $page->ImageWidth, 'ImageHeight' => $page->ImageHeight,
                                    'VideoThumbnailImage' => $page->VideoThumbnailImage, 'VideoThumbnailImageWidth' => $page->VideoThumbnailImageWidth, 'VideoThumbnailImageHeight' => $page->VideoThumbnailImageHeight];
                                array_push($postData[$key]['Pages'], $pagel);
                            }
                        }

                        if (count($imageData) > 0)
                        {
                            $postData[$key]['PostImage'] = $imageData;
                        }
                        else
                        {
                            $postData[$key]['PostImage'] = $imageData;
                        }

                        $postData[$key]['LatestComments'] = array();
                    }
                }

                //added by Kate--Native
                if($ComID==0){ //social posts will ONLY for fan App Not native App
                    //Added by Daniele: append Instagram & Blog images from RSS feed: TEST
                    //$artist = Artist::find()->where(['ArtistId' => $artistID])->one();


                    $artist = Artist::getDb()->cache(function ($db) use ($artistID)  {
                        $artist = Artist::find()->where(['ArtistId' => $artistID])->one();
                        return $artist;
                    });


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
                        }else{
                            //$articles = Feedarticles::find()->where(['ArtistID' => $artistID])->all();
                            try {
                                $articles = Feedarticles::getDb()->cache(function ($db) use ($artistID) {
                                    $articles = Feedarticles::find()->where(['ArtistID' => $artistID])->all();
                                    return $articles;
                                });
                            } catch (\Exception $e) {
                            }
                            if(count($articles) > 0){
                                $Feeds = array();
                                foreach($articles as $art){
                                    $feed = $this->getBlogFeeds($art->FeedUrl, $artistID);
                                    $Feeds = array_merge($Feeds, $feed);
                                }
                            }
                        }

                        //Added by Daniele: append instagram and blog feeds to $postData if available
                        //feeds are appended at the end of the array(last page considering page index) or after of the last of the normal posts, in same page
                        /*if(($recordcnt/self::Limit) < ($pageindex-1) && ($instaFeeds != null || $Feeds != null)){
                            $postData = array();
                        }*/

                        if($instaFeeds != null){
                            //$recordcnt += count($instaFeeds);
                            foreach ($instaFeeds as $instaEl){
                                //if(count($postData) < (self::Limit)){
                                array_push($postData, $instaEl);
                                //}
                            }
                        }

                        if($Feeds != null){
                            //$recordcnt += count($Feeds);
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
                        //$recordcnt = count($postData);
                    }


                }

                if ($pageindex<=1 && count($postData)<=0) {
                    $resultCode = 404;
                    $status = "0";
                } else {
                    $resultCode = 200;
                    $status = "1";
                }

                $resultMessage = _getStatusCodeMessagePostList($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);

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
                        //"RecordCount" => $recordcnt,
                        "Result" => $postData,
                        'UnreadQA' => $unreadQAData);

                    $encodedArray = str_replace("\\", "\\\\", $encodedArray);
                    echo json_encode($encodedArray, JSON_PRETTY_PRINT); //JSON_PRETTY_PRINT
                }
                else
                {
                   echo json_encode(['Status' => $status,
                        "Message" => $lngmsg,
                        //"RecordCount" => $recordcnt,
                        "Result" =>$postData,
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
            //echo $logString;
        }
    }

    public function actionApplist() {
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
                try {
                    $dependency = \Yii::createObject([
                        'class' => '\yii\caching\DbDependency',
                        'sql' => 'SELECT COUNT(*) FROM similarapp',
                        'reusable' => true,
                    ]);
                } catch (InvalidConfigException $e) {
                }
                try {
                    $appData = $connection->cache(function ($db) use ($command) {
                        return $command->queryAll();
                    }, $connection->queryCacheDuration, $dependency);
                } catch (\Exception $e) {
                }

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

    public function actionArtisthomescreen()
    {
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
                $procedure = "CALL Artist_Home_News_Feed_API3(" . $artistID . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "'," . $pageindex . "," . self::Limit . ")";

                $logString.="\n Artist Home News Feed : ".$procedure.'\n';
                $command = $connection->createCommand($procedure);

                $dependency_activity = \Yii::createObject([
                    'class' => '\yii\caching\DbDependency',
                    'sql' => 'SELECT COUNT(*) FROM memberactivity where ArtistID=' . $artistID,
                    'reusable' => true,
                ]);

                $postData = $connection->cache(function ($db) use ($command) {
                    return $command->queryAll();
                }, $connection->queryCacheDuration, $dependency_activity);

                foreach ($postData as $key => $value)
                {
                    if (isset($value['PostID']) && $value['PostID'] != '')
                    {
                        $imageData = array();
                        $postID = $value['PostID'];
                        $postimageproc = "CALL Post_Image_List(1," . $postID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketPostImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $commandForImage = $connection->createCommand($postimageproc);
                        //$imageData = $commandForImage->queryAll();

                        $dependency_post_image = \Yii::createObject([
                            'class'=>'\yii\caching\DbDependency',
                            'sql' => 'SELECT MAX(Updated), MAX(Created), COUNT(*) FROM gallery WHERE RefTableID='.$postID,
                            'reusable' => true,
                        ]);

                        $imageData = $connection->cache(function ($db) use($commandForImage) {
                            return $commandForImage->queryAll();

                        }, $connection->queryCacheDuration, $dependency_post_image);

                        $latestcmntsproc = "CALL Latest_Post_CommentList(" . $postID . "," . $artistID . "," . $artistID . ",2,'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "','" . self::S3BucketStickersSmall . "','" . self::S3BucketStickersMedium . "')";
                        $commandForCmnts = $connection->createCommand($latestcmntsproc);
                        //$cmntsData = $commandForCmnts->queryAll();

                        $dependency_comments = \Yii::createObject([
                            'class' => '\yii\caching\DbDependency',
                            'sql' => 'SELECT COUNT(*) FROM memberactivity where ArtistID='.$artistID,
                            'reusable' => true,
                        ]);

                        $cmntsData = $connection->cache(function ($db) use($commandForCmnts) {
                            return $commandForCmnts->queryAll();
                        }, $connection->queryCacheDuration, $dependency_comments);
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
                else if ($pageindex==1)
                {
                    $resultCode = 404;
                    $status = "0";
                }
                else
                {
                    $resultCode = 200;
                    $status = "1";
                }

                $resultMessage = _getStatusCodeMessageArtistHomescreen($resultCode);
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
                $resultMessage = _getStatusCodeMessageArtistHomescreen(502);
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
                if(isset($data->ComID)&&$data->ComID!=""){
                    $ComID=$data->ComID;
                }else $ComID=0;
                $defaultRes = false;
                if($ComID=="0"){
                    $modelSetting = new \backend\models\Setting();

                    try {
                        $dependency = \Yii::createObject([
                            'class' => '\yii\caching\DbDependency',
                            'sql' => 'SELECT MAX(Created), MAX(Updated) FROM setting WHERE ArtistID=' . $artistID,
                            'reusable' => true,
                        ]);
                    } catch (InvalidConfigException $e) {
                    }

                    try {
                        $bundleData = $modelSetting::getDb()->cache(function ($db) use ($modelSetting, $artistID) {
                            return $modelSetting->find()->where(array(
                                'ArtistID' => $artistID
                            ))->asArray()->all();
                        }, Yii::$app->db->queryCacheDuration, $dependency);
                    } catch (\Exception $e) {
                    }

                    if(count($bundleData) == 0){ $defaultRes = true;}
                    else{
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
                    }
                }else if($ComID!=0){
                    $logString.="For native products".$ComID;
                    //For native Question and Answer product ID
                    $products=array();$pricelist=array();
                    $products[0]=Nativeproducts::find()->where(" Status=1 And Type=2 and ComID=".$ComID )->asArray()->all();
                    $products[1]=Nativeproducts::find()->where(" Status=1 And Type=3 and ComID=".$ComID )->asArray()->all();
                    $products[2]=Nativeproducts::find()->where(" Status=1 And Type=4 and ComID=".$ComID)->asArray()->all();

                    foreach ($products as $key => $product) {
                        if(count($product)>0) {
                            $products[$key]=$product[0]['ProductSKUIOS'];
                            $pricelist[$key]=$product[0]['Price'];
                        }else{
                            $products[$key]="";
                            $pricelist[$key]=0;
                        }
                    }
                    //get Artist QA setting
                    $modelSetting = new \backend\models\Setting();
                    $bundleData = $modelSetting->find()->where(array(
                        'ArtistID' => $artistID))->asArray()->all();
                    if(count($bundleData) == 0){ $defaultRes = true;}
                    else{
                        if($bundleData[0]["QAModuleName"]==null) $bundleData[0]["QAModuleName"] = "";
                        if($bundleData[0]["IsQAEnable"]==null) $bundleData[0]["IsQAEnable"] = "";
                        if($bundleData[0]["QaType"]==null)   $bundleData[0]["QaType"] = "";
                        if($bundleData[0]["ProductPrice"]==null) $bundleData[0]["ProductPrice"] = "0";

                        if($bundleData[0]["TextProductSKUID"]!=null) {
                            $bundleData[0]["TextProductSKUID"]=$products[0];
                            $bundleData[0]["TextPrice"]=$pricelist[0];
                        }else $bundleData[0]["TextProductSKUID"] = "";
                        if($bundleData[0]["VideoProductSKUID"]!=null){
                            $bundleData[0]["VideoProductSKUID"] =$products[1];
                            $bundleData[0]["VideoPrice"] =$pricelist[1];
                        }else $bundleData[0]["VideoProductSKUID"] = "";
                        if($bundleData[0]["PhotoProductSKUID"]!=null){
                            $bundleData[0]["PhotoProductSKUID"] =$products[2];
                            $bundleData[0]["PhotoPrice"] =$pricelist[2];
                        }else $bundleData[0]["PhotoProductSKUID"] = "";

                        unset($bundleData[0]["Created"]);
                        unset($bundleData[0]["CreatedBy"]);
                        unset($bundleData[0]["Updated"]);
                        unset($bundleData[0]["UpdatedBy"]);
                    }

                }

                //QASetting not found: set default response
                if($defaultRes){
                    $defQA = ["SettingID"=> "0",
                        "ArtistID"=> $artistID,
                        "QAModuleName"=> "",
                        "IsQAEnable"=> "0",
                        "QaType"=> "0",
                        "TextPrice"=> null,
                        "TextProductSKUID"=> null,
                        "VideoPrice"=> null,
                        "VideoProductSKUID"=> null,
                        "ProductType"=> null,
                        "ProductPrice"=> null,
                        "ProductSKUID"=> null,
                        "AndroidPrice"=> null,
                        "AndroidSKUID"=> null,
                        "PhotoPrice"=> null,
                        "PhotoProductSKUID"=> null];
                    array_push($bundleData, $defQA);
                }


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

    public function actionGetdpinfo() {
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

                    try {
                        $dependency = \Yii::createObject([
                            'class' => '\yii\caching\DbDependency',
                            'sql' => 'SELECT MAX(Updated) FROM artist',
                            'reusable' => true,
                        ]);
                    } catch (InvalidConfigException $e) {
                    }

                    try {
                        $artist_profileData = $connection->cache(function ($db) use ($command) {
                            return $command->queryAll();
                        }, $connection->queryCacheDuration, $dependency);
                    } catch (\Exception $e) {
                    }

                    if (count($artist_profileData) > 0)
                    {
                        /*************** Get Artist Image List **************/
                        $postimagesprocedure = "CALL Artist_Image_List(2,0," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketArtistImages . "','" . self::S3BucketArtistThumb . "','" . self::S3BucketArtistMedium . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $commandForImage = $connection->createCommand($postimagesprocedure);

                        try {
                            $dependency_img = \Yii::createObject([
                                'class' => '\yii\caching\DbDependency',
                                'sql' => 'SELECT MAX(Updated), COUNT(*) FROM gallery',
                                'reusable' => true,
                            ]);
                        } catch (InvalidConfigException $e) {
                        }

                        try {
                            $artistimages = $connection->cache(function ($db) use ($commandForImage) {
                                return $commandForImage->queryAll();
                            }, $connection->queryCacheDuration, $dependency_img);
                        } catch (\Exception $e) {
                        }

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
                    $postData_proc = "CALL Post_List(" . $reqID . "," . $artistID . "," . $userID . "," . $profileID . ",0,0,'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "','','',@o_RecCount)";

                    $logString.="\n Post List : ".$postData_proc.'\n';
                    $command = $connection->createCommand($postData_proc);;

                    try {
                        $dependency = \Yii::createObject([
                            'class' => '\yii\caching\DbDependency',
                            'sql' => 'SELECT Updated FROM post WHERE PostID=' . $reqID,
                            'reusable' => true,
                        ]);
                    } catch (InvalidConfigException $e) {
                    }

                    try {
                        $postData = $connection->cache(function ($db) use ($command) {
                            return $command->queryAll();
                        }, $connection->queryCacheDuration, $dependency);
                    } catch (\Exception $e) {
                    }

                    if (count($postData) > 0)
                    {
                        /*************** Get Image List **************/
                        $imageData = array();
                        /************* Get Post images **************/
                        $postimageproc = "CALL Post_Image_List(1," . $reqID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketPostImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $logString.="\n Post Image List : ".$postimageproc.'\n';

                        $commandForImage = $connection->createCommand($postimageproc);

                        try {
                            $dependency_post_image = \Yii::createObject([
                                'class' => '\yii\caching\DbDependency',
                                'sql' => 'SELECT MAX(Updated), MAX(Created), COUNT(*) FROM gallery WHERE RefTableID=' . $reqID,
                                'reusable' => true,
                            ]);
                        } catch (InvalidConfigException $e) {
                        }
                        try {
                            $imageData = $connection->cache(function ($db) use ($commandForImage) {
                                return $commandForImage->queryAll();
                            }, $connection->queryCacheDuration, $dependency_post_image);
                        } catch (\Exception $e) {
                        }

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

                    $dependency = \Yii::createObject([
                        'class'=>'\yii\caching\DbDependency',
                        'sql' => 'SELECT Updated FROM sticker WHERE IsDelete=0',
                        'reusable' => true,
                    ]);
                    $stickersData = $connection->cache(function ($db) use ($command) {
                        return $command->queryAll();
                    }, $connection->queryCacheDuration, $dependency);

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

    public function unreadQA($artistID) {
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

            try {
                $dependency = \Yii::createObject([
                    'class' => '\yii\caching\DbDependency',
                    'sql' => 'SELECT COUNT(*) from post WHERE (MemberID NOT IN ( SELECT MemberID FROM blockuser AS mp WHERE ArtistID=' . $artistID . ')) AND PostType=4 AND Reply IS NULL AND (QAIgnore IS NULL  OR QAIgnore=2) AND IsDelete=0 AND ArtistID=' . $artistID,
                    'reusable' => true,
                ]);
            } catch (InvalidConfigException $e) {
            }

            try {
                $unreadQAData = \Yii::$app->db->cache(function ($db) use ($command)  {
                    $unreadQAData = $command->queryAll();
                    return $unreadQAData;
                },  Yii::$app->db->queryCacheDuration, $dependency);
            } catch (\Exception $e) {
            }

            return $unreadQAData[0]['total'];
        } catch (ErrorException $e) {
            $this->addLog($logString,$e);
        }
    }

    public function addLog($logString,$e) {
        if($this->logWrite) {
            $filename = '../web/log/'.Yii::$app->controller->action->id.'Log_' . date('Y-m-d-H:i:s') . '.txt';
            $log_current = "\n ************************";
            $log_current.= "\n DateTime : " .date("Y-m-d H:i:s");
            $log_current.= "\n Message : " . $e;
            $log_current.= "\n Data: " . $logString;
            file_put_contents($filename, $log_current,FILE_APPEND);
        }
    }

    public function actionUpdatedevicetoken() {
        /*UPDATE artist SET DeviceType=v_DeviceType, DeviceToken=v_DeviceToken WHERE UserID=v_UserID;
			UPDATE member SET DeviceType=v_DeviceType, DeviceToken=v_DeviceToken WHERE UserID=v_UserID;
			*/
        try {
            $arrParams=Yii::$app->request->post();
            $data = json_decode($arrParams['params']);
            $userType=$data->UserType;
            $userId=$data->UserID;
            $deviceToken=$data->DeviceToken;
            $deviceType=$data->DeviceType;
            if ($userType=="2") {
                //artist
                $artistModel=Artist::findOne(['UserID'=>$userId]);
                if ($artistModel!=null) {
                    $artistModel->DeviceToken=$deviceToken;
                    $artistModel->DeviceType=$deviceType;
                    $artistModel->save(false);
                    $this->setHeader(200);
                    echo json_encode(["Status"=>1,"Message"=>"Device Token Updated."]);
                } else {
                    $this->setHeader(200);
                    echo json_encode(["Status"=>0,"Message"=>"Device Token Update Failed. Artist not found."]);
                }
            } else if ($userType=="3") {
                //normal user
                $memberModel=Member::findOne(['UserID'=>$userId]);
                if ($memberModel!=null) {
                    $memberModel->DeviceToken=$deviceToken;
                    $memberModel->DeviceType=$deviceType;
                    $memberModel->save(false);
                    $this->setHeader(200);
                    echo json_encode(["Status"=>1,"Message"=>"Device Token Updated."]);
                } else {
                    $this->setHeader(200);
                    echo json_encode(["Status"=>0,"Message"=>"Device Token Update Failed. Member not found."]);
                }
            }

        } catch (Exception $e) {

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

    public function actionCheckusername() {
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
                $comID = 0;
                if(isset($data->ComID)){ $comID = $data->ComID;}
                $connection = Yii::$app->db;
                $command = $connection->createCommand("CALL Check_Username('" . $userName . "'," . $artistID . "," . $userType . ",'" . self::EncryptKey . "',".$comID.")");
                $logString.=" \n Check Username : CALL Check_Username('" . $userName . "'," . $artistID . "," . $userType . ",'" . self::EncryptKey . "',".$comID.")n";
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
                        $artistname = "";
                        if($artistID != 0){
                            if ($userType == 2) $artistname = $getArtistData->getArtistName($o_ArtistID);
                            else if ($userType == 3) $artistname = $getArtistData->getArtistName($artistID);
                        }
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
        //\Yii::$app->controller->layout = 'l';
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
                $procedure = "CALL Member_Add_Activity('" . $postID . "','" . $artistID . "','" . $profileID . "','" . $activityTypeID . "','" . $refTable . "','" . $refTableID . "','" . $comment . "','" . $activityID . "'," . $userType . ",0,'" . self::S3BucketPath . "','')";
                $logString.="\n Member Add Activity : ".$procedure.'\n';
                $command = $connection->createCommand($procedure);

                $activityData = Yii::$app->queue->push(new ActivityJob([
                    'command' => $command,
                ]));

                //if (count($activityData) > 0)
                if(isset($activityData))
                {
                    $activityData = ActivityHelper::getSuccess();
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $activityData = ActivityHelper::getError();
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
                /*$res = json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "Result" => $activityData], JSON_PRETTY_PRINT);
                return $this->render('user', [
                    'res' => $res,
                ]);*/
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
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';
            $data = json_decode($arrParams['params']);
            $activityData = "";
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
                $procedure = "CALL Member_Add_Activity('" . $postID . "','" . $artistID . "','" . $userID . "','" . $activityTypeID . "','" . $refTable . "','" . $refTableID . "','" . $comment . "','" . $activityID . "','" . $userType . "','0','" . self::S3BucketPath . "','')";
                $logString.="\n Member Add Activity : ".$procedure.'\n';
                $command = $connection->createCommand($procedure);

                $activityData = Yii::$app->queue->push(new ActivityJob([
                    'command' => $command,
                ]));

                if (isset($activityData))
                {
                    $activityData = ActivityHelper::getSuccess();
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $activityData = ActivityHelper::getError();
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

    public function actionShare() {
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
                $procedure = "CALL Member_Add_Activity('" . $postID . "','" . $artistID . "','" . $profileID . "','" . $activityTypeID . "','" . $refTable . "','" . $refTableID . "','" . $comment . "'," . $activityID . "," . $userType . ",0,'" . self::S3BucketPath . "','')";
                $logString.="\n Member Add Activity : ".$procedure.'\n';
                $command = $connection->createCommand($procedure);

                $activityData = Yii::$app->queue->push(new ActivityJob([
                    'command' => $command,
                ]));


                if (isset($activityData))
                {
                    $activityData = ActivityHelper::getSuccess();
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $activityData = ActivityHelper::getSuccess();
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
                $this->setHeader(400);
                $resultMessage = _getStatusCodeMessageCommon(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                echo json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "Result" => $activityData], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        {
            $this->addLog($logString,$e);
        }
    }

    public function actionLikecomment() {
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';
            $data = json_decode($arrParams['params']);
            $activityData = "";
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
                $procedure = "CALL Member_Add_Activity('" . $postID . "','" . $artistID . "','" . $userID . "','" . $activityTypeID . "','" . $refTable . "','" . $refTableID . "','" . $comment . "','" . $activityID . "','" . $userType . "','0','" . self::S3BucketPath . "','')";
                $logString.="\n Member Add Activity : ".$procedure.'\n';
                //echo $procedure; die;
                $command = $connection->createCommand($procedure);
                //$activityData = $command->queryAll();

                $activityData = Yii::$app->queue->push(new ActivityJob([
                    'command' => $command,
                ]));

                if (isset($activityData))
                {
                    $activityData = ActivityHelper::getSuccess();
                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $activityData = ActivityHelper::getError();
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

    public function actionAddcomment() {
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';
            $data = json_decode($arrParams['params']);
            $activityData = "";
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
                $procedure = "CALL Member_Add_Activity(:postID,:artistID,:userID,:activityTypeID,:refTable,:refTableID,:comment,:activityID,:userType,:stickerID,:buckerPath,:CustomStickerUrl)";
                $bindPostParams = [':postID' => $postID,':artistID'=>$artistID,':userID'=>$userID,':activityTypeID'=>$activityTypeID,':refTable'=>$refTable,':refTableID'=>$refTableID,':comment'=>$comment,':activityID'=>$activityID,':userType'=>$userType,':stickerID'=>$stickerID,':buckerPath'=>self::S3BucketPath,':CustomStickerUrl'=>$customStickerUrl];

                $logString.="\n Member Add Activity : ".$procedure.'\n';
                $command = $connection->createCommand($procedure)->bindValues($bindPostParams);

                $activityData = Yii::$app->queue->push(new ActivityJob([
                    'command' => $command,
                ]));

                if (isset($activityData))
                {
                    $activityData = ActivityHelper::getSuccess();
                    $resultCode = 200;
                    $status = "1";
                    $artistobj = Artist::findOne(["ArtistID"=>$artistID]);
                    //$commentactivityid = $activityData[0]['PostCommentActivityID'];
                    /*
                     * Onesignal Push Notification for Addcomment api
                     * 1. artist added comment, send push to fanapp
                     * 2. fan added comment send push to artist app (logged in artist, check ArtistID)
                     *
                     * */
                    /*                     * ************************ Push ********************* */
                    if($userType == "2") {
                        //artist added a comment, send a push notification to fan app
                        $onesignalpush=new OneSignalPushNotification();
                        $getmsgforartist = $artistobj->getNotificationMessageForComment(NULL, $artistID, $postID);
                        $osappid = $artistobj->OSFanAppID;
                        $onesignalpush->sendMessageToAll($osappid,$getmsgforartist,"",$postID,"comment",$time);
                    } else if ($userType == "3") {
                        $getmsgforartist        =   $artistobj->getNotificationMessageForArtist($userType,$userID,$artistID,$postID);
                        $onesignal=new OneSignalPushNotification();
                        $osartistappid=$artistobj->OSArtistAppID;
                        //send message to artist
                        $onesignal->sendMessageToUserID($osartistappid,$artistobj->UserID,$getmsgforartist,"",$postID,"comment",$time);
                        //send message to all fan except this fan who added comment
                        //$osfanappid=$artistobj->OSFanAppID;
                        //$onesignal->sendMessageExceptUserID($osfanappid,)
                    }
                }
                else
                {
                    $activityData = ActivityHelper::getError();
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

    public function actionSignup() {
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
            if (count($compareField) == 0 && isset($data->Email))
            {
                $artistID = $data->ArtistID;
                $email = $data->Email;

                $name = "";
                if(isset($data->Name)){
                    $name = str_replace("'", "", $data->Name);
                }

                //if username null set username with email
                $username = $email;
                if(isset($data->Username)){
                    $username = str_replace("'", "", $data->Username);
                }

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
                //algene nov 27 2017
                $v_country="";
                $v_city="";
                if (isset($data->Country)) {
                    $v_country=$data->Country;
                }
                if (isset($data->City)) {
                    $v_city=$data->City;
                }

                $password = $data->Password;
                $language = $data->Language;
                $connection = Yii::$app->db;
                /************** Registr Member ****************/
                $procedure="CALL Member_Registration('" . $name . "','" . $image . "','" . $email . "','" . $birthDate . "','" . $zipCode . "','" . $mobile . "','" . $gender . "','" . $artistID . "','" . $username . "','" . $devicetype . "','" . self::EncryptKey . "','" . $password . "', " . $ComID . ",'".$v_country."','".$v_city."')";
                $command = $connection->createCommand($procedure);
                $logString.="\n ".$procedure;
                $registrationData = $command->queryAll();
                $resultCode = $registrationData[0]['ErrorCode'];
                $resultMessage = $registrationData[0]['Message'];

                //convert image --> added by Daniele
                if ($image != "")
                {
                    try{
                        /*********** Save Image **************/
                        $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY);
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
                    }catch(ErrorException $ie){
                        $resultMessage = "User created but image profile not uploaded correctly.";
                    }
                }

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
                $language = $data->Language;
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

    public function actionUpdateusername() {
        /************** Update username of an existing user *****************/
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'UserID',
                'UserName',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $userID = $data->UserID;
                $userName = $data->UserName;
                $language = $data->Language;

                //validates username
                $resultCode = ""; $resultMessage ="";
                if(isset($userName) and $userName != ""){
                    $connection = Yii::$app->db;
                    $command = $connection->createCommand("CALL Check_Username('" . $userName . "',0,3,'" . self::EncryptKey . "',1)");
                    $logString.=" \n Check Username : CALL Check_Username('" . $userName . "',0,3,'" . self::EncryptKey . "',1))n";
                    $userData = $command->queryAll();
                    $resultCode = $userData[0]['ErrorCode'];
                    $resultMessage = _getStatusCodeMessageForUsername($resultCode);
                }

                //if valid and available updates it
                if($resultCode == 200){
                    $user = User::find()->where(['UserID' => $userID])->one();
                    if($user != null){
                        $connection = Yii::$app->db;
                        $command = $connection->createCommand("SELECT Encrypt_UserName('" . $userName . "','" . self::EncryptKey . "')");
                        $userData = $command->queryAll();
                        foreach($userData[0] as $encname){
                            $user->UserName = $encname;
                        }
                        $user->save(false);
                        $resultMessage = "UserName updated for user ".$user->UserID;
                        $resultCode = 200;
                    }else{
                        $resultMessage = "User not found";
                        $resultCode = 404;
                    }
                }

                if ($resultCode == 200)
                {
                    \Yii::$app->language = $language;
                    $lngmsg = \Yii::t('api', $resultMessage);
                    echo json_encode(['Status' => 1,
                        "Message" => $lngmsg,
                        'Result' => '[]'], JSON_PRETTY_PRINT);
                }
                else
                {
                    \Yii::$app->language = $language;
                    $lngmsg = \Yii::t('api', $resultMessage);
                    $this->setHeader(400);
                    echo json_encode(['Status' => 0,
                        "Message" => $lngmsg,
                        'Result' => '[]'], JSON_PRETTY_PRINT);
                }
            }
            else
            {
                $language = $data->Language;
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

    /*
 * This will update OSFanAppID field on artist table
 * */
    public function actionUpdateosfanappid() {
        $httpstatus=400;
        try {
            $arrParams=Yii::$app->request->post();
            $data=json_decode($arrParams['params']);
            $requiredParams=array('ArtistID','OnesignalAppID');
            $paramsValid=$this->validateRequiredParams((array)$data,$requiredParams);

            if ($paramsValid==1) {
                $artistID = $data->ArtistID;
                $osfanappid = $data->OnesignalAppID;

                $con = Yii::$app->db;
                $command = $con->createCommand("UPDATE artist SET OSFanAppID='$osfanappid' WHERE ArtistID=$artistID");
                $result = $command->execute();
                $httpstatus = 200;
            }
        } catch (Exception $e) {
        }
        $this->setHeader($httpstatus);
        $resultMessage = _getStatusCodeMessageForUser($httpstatus);

        $status=0;
        if ($httpstatus==200) {
            $status=1;
        }
        echo json_encode(['Status' => $status,
            "Message" => $resultMessage], JSON_PRETTY_PRINT);
    }

    /*
     * @return 1 if all keys from $requiredArrayKeys are present in $inputArray. else return 0.
     * */
    private function validateRequiredParams($inputArray,$requiredArrayKeys) {
        $result=1;
        foreach ($requiredArrayKeys as $key=>$value) {
            if (!array_key_exists($value,$inputArray)) {
                $result=0;
                break;
            }
        }
        return $result;
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
                $username = "";
                if (isset($data->Username) && $data->Username != "")
                {
                    //check if Username is available
                    $connection = Yii::$app->db;
                    $command = $connection->createCommand("CALL Check_Username('" . $data->Username . "',0,3,'" . self::EncryptKey . "',1)");
                    $logString.=" \n Check Username : CALL Check_Username('" . $data->Username . "',0,3,'" . self::EncryptKey . "',1))n";
                    $userData = $command->queryAll();
                    $resultCode = $userData[0]['ErrorCode'];
                    if($resultCode == 200){ $username = $data->Username; }
                }
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
                $v_Country = "";
                $v_City = "";
                if (isset($data->Country) && $data->Country!="") {
                    $v_Country = $data->Country;
                }
                if (isset($data->City) && $data->City!="") {
                    $v_City = $data->City;
                }
                $mobile = $data->Mobile;
                $password = $data->Password;
                $image = $data->Image;
                $language = $data->Language;
                $showNotification = $data->ShowNotification;
                $connection = Yii::$app->db;
                $procedure = "CALL Member_Edit_Profile_new('". $username . "','" . $name . "','" . $image . "','" . $email . "','" . $password . "','" . $birthDate . "','" . $zipCode . "','" . $mobile . "','" . $gender . "','" . $artistID . "','" . $profileID . "','" . $userID . "'," . $showNotification . ",'" . self::EncryptKey . "','".$v_Country."','".$v_City."')";
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
            //$this->addLog($logString,$e);
            $this->setHeader(400);
            echo json_encode(['Status'=>1,'Message'=>'','Result'=>$registrationData,'Error'=>$e,'Log'=>$logString],JSON_PRETTY_PRINT);
        }
    }

    public function actionArtisteditprofile() {
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

    public function actionNewcommentlist() {
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
                $procedure = "CALL Ver2_Post_CommentList(" . $postID . "," . $artistID . "," . $profileID . "," . $userType . ",'" . self::S3BucketAbsolutePath . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "','" . self::S3BucketStickers . "','" . self::S3BucketStickersSmall . "','" . self::S3BucketStickersMedium . "',10," . $pageindex . ",@o_RecCount)";
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

    public function actionCommentlist() {
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

    public function actionLatestcommentlist() {
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

    public function actionAddfeed() {
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

    public function actionQuestionanswerlist() {
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
                $comID = 0;
                if (isset($data->Unanswered)){$unanswered = $data->Unanswered;}
                if (isset($data->MemberID) && $usertype == 2){$MemberID = $data->MemberID;}
                if (isset($data->ComID)){$comID = $data->ComID;}
                $connection = Yii::$app->db;
                $procedure = "CALL Question_Answer_List(" . $artistID . "," . $UserID . "," . $profileID . "," . $MemberID . ",'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "'," . $unanswered . "," . $comID . "," . self::Limit . "," . $pageindex . ",@o_RecCount)";
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

    public function actionStickers() {
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

    public function actionAddpost() {
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
                $ReplyWidth = "";
                $ReplyHeight = "";
                $TextReply = "";
                //add for video post with link not upload videos
                $viedolink_u="";
                if (isset($data->mobilevideolink) && $data->mobilevideolink != ""){
                    $viedolink_u=$data->mobilevideolink;

                }else
                    $viedolink_u="";
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
                if (isset($data->ReplyWidth) && $data->ReplyWidth != "")
                {
                    $ReplyWidth = $data->ReplyWidth;
                }
                if (isset($data->ReplyHeight) && $data->ReplyHeight != "")
                {
                    $ReplyHeight = $data->ReplyHeight;
                }
                if (isset($data->TextReply) && $data->TextReply != "")
                {
                    $TextReply = $data->TextReply;
                }
                //added for scheduled
                if(isset($data->scheduled)&& $data->scheduled != ""){
                    $scheduled=$data->scheduled;
                }else $scheduled=Date("Y-m-d H:i:s",time() - 5 * 60);

                //added conditions for Article type
                if($postType == "5"){
                    $description = "";
                }

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
                $proc = "CALL Post_Add(:PostTitle,:PostType,:Description,:ArtistID,:IsExclusive,:IsShared,:price,:productID,:memberID,:reqisPublic,:qatype,:postID,:reply,:replyThumbImage,:ignore,:transactionDetail,:token,:vidoePostWidth,:vidoePostHeight,:ReplyWidth,:ReplyHeight,:TextReply,:scheduled)"; //add by schedule
                $bindPostParams = [':PostTitle' => $postTitle,':PostType'=>$postType,':Description'=>$description,':ArtistID'=>$artistID,':IsExclusive'=>$isExclusive,':IsShared'=>$isShared,':price'=>$price,':productID'=>$productID,':memberID'=>$memberID,':reqisPublic'=>$reqisPublic,':qatype'=>$qatype,':postID'=>$postID,':reply'=>$reply,':replyThumbImage'=>$replyThumbImage,':ignore'=>$ignore,':transactionDetail'=>$transactionDetail,':token'=>$token,':vidoePostWidth'=>$vidoePostWidth,':vidoePostHeight'=>$vidoePostHeight,':ReplyWidth'=>$ReplyWidth,':ReplyHeight'=>$ReplyHeight,':TextReply'=>$TextReply,':scheduled'=>$scheduled];
                $logString.="\n Post Add : ".$proc.'\n';
                $command = $connection->createCommand($proc)->bindValues($bindPostParams);
                $postData = $command->queryAll();
                if (count($postData) > 0)
                {
                    if ($postType == '4')
                    {
                        $artistobj = Artist::findOne(["ArtistID"=>$artistID]);

                        $ignoreStatus = "";

                        //Onesignal push
                        $onesignal=new OneSignalPushNotification();
                        if ($postID == "")
                        {
                            //fan asked question
                            $msgforartist=$artistobj->getNotificationMessageForPost($memberID,$artistID,"",$ignoreStatus);
                            //send message to artist
                            $osartistappid=$artistobj->OSArtistAppID;
                            $onesignal->sendMessageToUserID($osartistappid,$artistobj->UserID,$msgforartist,"",$postID,"addQue",$time);

                        }
                        else
                        {
                            //artist interacted with the question
                            if (isset($data->Ignore) && $data->Ignore != "")
                            {
                                $ignoreStatus = $ignore;
                            }
                            $msgforfan = $artistobj->getNotificationMessageForPost($memberID, $artistID, $postID, $ignoreStatus);

                            if (isset($data->Ignore) && $data->Ignore != "")
                            {
                                $notificationType = "ignoreQue";
                            }
                            else
                            {
                                $notificationType = "replyAns";
                            }
                            //send message to that user
                            $osfanappid=$artistobj->OSFanAppID;
                            $memberObj=Member::findOne(["MemberID"=>$memberID]);
                            $onesignal->sendMessageToUserID($osfanappid,$memberObj->UserID,$msgforfan,"",$postID,$notificationType,$time);

                        }
                    }


                    $postID = $postData[0]['PostID'];
                    //for video upload only--Kate--22 Aug 2017
                    if($postType == "3"&&$viedolink_u!=""){
                        if (isset($data->mobilevideoThumbImage) && $data->mobilevideoThumbImage != ""){
                            $VideoThumbImage_u=$data->mobilevideoThumbImage; //echo $VideoThumbImage_u;
                        }else
                            $VideoThumbImage_u="";

                        $connection2 = \Yii::$app->db;
                        $r2=$connection2->createCommand()->update('post', array("MobileStreamUrl"=>$viedolink_u, "VideoThumbImage"=>$VideoThumbImage_u),'PostID = '.$postID)->execute();
                        //$r2=$connection2->createCommand()->update('post', array("Description"=>"Updatre"),'PostID = '.$postID)->execute();
                        $resultCode = 200;
                        $status = "1";

                    }else
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
                                    $thumburl = S3_VIDEO_BUCKETPATH.$artistID.POST_THUMBIMAGE_VIDEOS.$videothumb;
                                    if($postType == "4" && $qatype == "1"){ $thumburl = S3_BUCKETPATH.$artistID.POST_THUMBIMAGE_VIDEOS.$videothumb; }
                                    $connection = \Yii::$app->db;
                                    $connection->createCommand()->update('post', array("ThumbImage"=>$thumburl),'PostID = '.$postID)->execute();

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




                    //Article
                    if($postType == "5"){
                        if (isset($data->PostID) && $data->PostID != ""){
                            //Edit Post
                            if (isset($data->PageNumber) && $data->PageNumber != ""){
                                //Edit existing page
                                //get the page using number
                                $page = PostPages::find()->where('PostID='.$data->PostID.' AND PageNumber='.$data->PageNumber)->one();
                                if(isset($data->Description)){ $page->Text = $data->Description; }
                                $page->Type = 1;
                                if(isset($data->VideoThumbImage) && $data->VideoThumbImage != ''){
                                    $page->Type = 2;
                                    $page->ImageUrl = $data->VideoThumbImage;
                                }
                                if(!empty($media)){
                                    $page->Type = 3;
                                    $page->VideoUrl = $media[0];
                                }
                                $page->save(false);
                            }else{
                                //Add new page
                                $newpage = new PostPages();
                                $newpage->PostID = $data->PostID;
                                //get the existing pages to calculate the page number
                                $pages = PostPages::find()->where('PostID='.$data->PostID)->all();
                                $numpage = count($pages)+1;
                                $newpage->PageNumber = $numpage;
                                if(isset($data->Description)){ $newpage->Text = $data->Description; }
                                $newpage->Type = 1;
                                if(isset($data->VideoThumbImage) && $data->VideoThumbImage != ''){
                                    $newpage->Type = 2;
                                    $newpage->ImageUrl = $data->VideoThumbImage;
                                }
                                if(!empty($media)){
                                    $newpage->Type = 3;
                                    $newpage->VideoUrl = $media[0];
                                }
                                $newpage->save(false);
                            }
                        }else{
                            //New Post
                            $newpage = new PostPages();
                            $newpage->PostID = $postID;
                            $newpage->PageNumber = 1;
                            if(isset($data->Description)){ $newpage->Text = $data->Description; }
                            $newpage->Type = 1;
                            if(isset($data->VideoThumbImage) && $data->VideoThumbImage != ''){
                                $newpage->Type = 2;
                                $newpage->ImageUrl = $data->VideoThumbImage;
                            }
                            if(!empty($media)){
                                $newpage->Type = 3;
                                $newpage->VideoUrl = $media[0];
                            }
                            $newpage->save(false);
                        }

                        $resultCode = 200;
                        $status = "1";
                    }
                    else
                    {
                        $resultCode = 200;
                        $status = "1";

                        //send push to fan app (to all users) to tell that artist added a new post
                        if ($postType!="4") {
                            $artistobj=Artist::findOne(["ArtistID"=>$artistID]);
                            $msg=$artistobj->ArtistName." added a new post.";
                            $osfanappid=$artistobj->OSFanAppID;
                            $onesignal=new OneSignalPushNotification();
                            $onesignal->sendMessageToAll($osfanappid,$msg,"","","addPost","");
                        }
                    }
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }

                //set the push notification flag to false(means push notification needs to be scheduled for this post)
                if ($postType == "1" || $postType == "2" || $postType == "3"){
                    $post = \backend\models\Post::findOne($postID);
                    $post->PushNotification_Sched = 0;
                    $post->save(false);
                }

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

    //function that checks all the posts that needs to be added to pushqueue
    private function addToPushQueue(){
        $logString  = "";
        try{
            $memberObj = new \backend\models\Member();
            $push_obj = new \backend\models\Pushqueue();
            $posts = \backend\models\Post::find()->where(['PushNotification_Sched'=>0])->all();
            foreach($posts as $post){
                $iosToken = $memberObj->getBulkMemberDeviceToken(1, $post->ArtistID);
                $androidToken = $memberObj->getBulkMemberDeviceToken(2, $post->ArtistID);
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
                $getmsgforartist = $artistobj->getBulkNotificationMessageForPost($post->MemberID, $post->ArtistID, $post->PostType, $post->PostTitle, $post->Description);

                $modelPushHistoryStats = new \backend\models\Pushhistorystats();
                $modelPushHistoryStats->ArtistID = $post->ArtistID;
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

                //set the flag to true
                $post->PushNotification_Sched = 1;
                $post->save(false);
            }
        }catch(Exception $e){
            $this->addLog($logString,$e);
        }
    }


    public function actionGlobaliospush() {
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

        //fill the pushqueue with new added posts
        $this->addToPushQueue();

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

    public function actionGlobalandroidpush() {
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

    public function actionPurchasesticker() {
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
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ArtistID',
                'ProfileID',
                'PostID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            if (count($compareField) == 0)
            {
                $artistID = $data->ArtistID;
                $memberID = $data->ProfileID;
                $postID = $data->PostID;
                $language = $data->Language;
                $deviceType = "";
                $productSKUDetails = "";
                $productSKU=""; //added for purchase for Native
                if (isset($data->ProductSKUDetails) && $data->ProductSKUDetails != "")
                {
                    $productSKUDetails = json_encode($data->ProductSKUDetails);
                    try{ //added for purchase for Native
                        $productSKU=$data->ProductSKUDetails->SKU;
                    }catch (ErrorException $e){

                    }
                }
                if (isset($data->DeviceType) && $data->DeviceType != "")
                {
                    $deviceType = $data->DeviceType;
                }

                $price = -1;
                if(isset($data->Price) && $data->Price != ""){$price = $data->Price;}
                $ProductID = '';
                if(isset($data->ProductID) && $data->ProductID != ""){$ProductID = $data->ProductID;}
                $isNative = 0;
                if(isset($data->isNative) && $data->isNative != ""){$isNative = $data->isNative;}

                $connection = Yii::$app->db;
                $procedure = "CALL Post_Purchase(" . $artistID . "," . $memberID . "," . $postID . "," . $deviceType . ",'" . $productSKUDetails . "'," . $price . ",'" . $ProductID . "'," .$isNative. ")";
                $logString.="\n Post Purchase : ".$procedure."\n";
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

                //for Boom Fan app-> Add User follow
                $artists=UserArtist::find()->where(['and','MemberID ='.$memberID,'ArtistID='.$artistID,'isSub=1'])->all();
                if(count($artists)>0) {
                    $resultCode = 502;
                    $status = "0";
                    $resultMessage="User has subscribed";
                }else{
                    $member=Member::findOne(array("MemberID"=>$memberID));
                    $userID=$member->UserID;
                    if($userID!=0 &&$artistID!=0 && $memberID!=0){
                        // purchase for Native
                        $query='insert into user_artist(UserID, MemberID, ArtistID, isSub, ProductSKU) values ("'.$userID.'", "'.$memberID.'", "'.$artistID.'", 1, "'.$productSKU.'")';
                        $result= Yii::$app->db->createCommand($query)->execute();
                        if($result){
                            $resultCode = 200;
                            $status = "1";
                        }else{
                            $resultCode = 404;
                            $status = "0";
                        }
                        $resultMessage = _getStatusCodeMessageForPurchase($resultCode);
                    }else{
                        $resultCode = 502;
                        $status = "0";
                        $resultMessage="Invalid Param";
                    }
                }


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

    public function actionArtisteditpost() {
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
                //added for scheduled
                if(isset($data->scheduled)&& $data->scheduled != ""){
                    $scheduled=$data->scheduled;
                }else $scheduled=Date("Y-m-d H:i:s");

                $connection = Yii::$app->db;
                /*$procedure = 'CALL Artist_Edit_Post(' . $artistID . ',' . $profileID . ',' . $postID . ',
					  "' . $postType . '","' . str_replace("'", "", $postTitle) . '","' . str_replace("'", "", $description) . '","' . $fullVideoLink . '",
					  ' . $isExclusive . ',' . $isShared . ',' . $price . ',@o_ErrorCode)';*/
                //echo $procedure; die;
                $proc = "CALL Artist_Edit_Post(:ArtistID,:ProfileID,:PostID,:PostType,:PostTitle,:Description,:FullVideoLink,:IsExclusive,:IsShared,:Price,@o_ErrorCode,:scheduled)";
                $logString.="\n Member Artist Edit Post : ".$proc.'\n';
                $bindPostParams = [':ArtistID' => $artistID,':ProfileID'=>$profileID,':PostID'=>$postID,':PostType'=>$postType,':PostTitle'=>$postTitle,':Description'=>$description,':FullVideoLink'=>$fullVideoLink,':IsExclusive'=>$isExclusive,':IsShared'=>$isShared,':Price'=>$price,':scheduled'=>$scheduled];
                $command = $connection->createCommand($proc)->bindValues($bindPostParams);

                $postData = $command->queryAll();
                if (count($postData) > 0)
                {
                    $resultCode = 200;
                    $status = "1";
                    if (count($mediaDeleteID) > 0)
                    {
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

    public function actionArtistdeletepost() {
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

    public function actionLikelist() {
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

    public function actionSharelist() {
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

    public  function actionFlaglist() {
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

    public function actionMymusic() {
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

    public function actionMymusictv() {
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

    public function actionBlock() {
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

                $artistobj = new Artist();
                $time = date("d M 'y H:i A");
                $unreadQAData = $this->unreadQA($artistID);

                //Onesignal push
                try {
                    $onesignal = new OneSignalPushNotification();
                    $sendToMember = Member::findOne(['MemberID' => $profileID]);
                    $msgforfan = $artistobj->getNotificationMessageForBlockUser($profileID, $artistID, $postID, $status);
                    $artistobj=Artist::findOne(["ArtistID"=>$artistID]);
                    $osfanappid=$artistobj->OSFanAppID;
                    $onesignal->sendMessageToUserID($osfanappid,$sendToMember->UserID,$msgforfan,"",$postID,"blockUser",$time);

                } catch(Exception $e) {}

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

    public function loadModel($id) {
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

    public function actionOldstickers() {
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

    public function resizeimage() {

    }

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
                    //'AudioReplyUrl' => '',
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
                if (isset($item['content:encoded']) && $item['content:encoded'] != ''){
                    //set the  full content in description if available
                    $xmldesc = substr(json_encode(strip_tags($item['content:encoded'])), 1, -1);
                }else{
                    //if content not available, uses description
                    if (isset($item['description']) && $item['description'] != '') $xmldesc = substr(json_encode(strip_tags(str_replace("[& # 8230;]","[...]",$item['description']))),1,-1);
                }
                //if (isset($item['description']) && $item['description'] != '') $xmldesc = strip_tags(substr(json_encode($item['description']), 1, -1));
                if (isset($item['link']) && $item['link'] != '') $xmllink = $item['link'];
                if (isset($item['media:title']) && $item['media:title'] != '') $xmlmediatitle = $item['media:title'];
                if (isset($item['media:thumbnail']['url']) && $item['media:thumbnail']['url'] != '') $xmlmediathumb = $item['media:thumbnail']['url'];
                if (isset($item['media:content']['url']) && $item['media:content']['url'] != '') $xmlcontenturl = $item['media:content']['url'];
                $modelRssFeed = new Rssfeed;
                $modelRssFeed->title = $xmltitle;
                $modelRssFeed->description = $xmldesc;
                $img_split = explode("/", $xmlmediathumb);
                $img_name = end($img_split);

                $modelRssFeed->media_title = $img_name;
                $modelRssFeed->media_thumbnail = $xmlmediathumb;
                $modelRssFeed->media_content = $xmlcontenturl;

                //get images from html
                $images = array();
                if (isset($item['content:encoded']) && $item['content:encoded'] != ''){
                    $image_urls = $this->searchImagesFeed($item['content:encoded']);
                    foreach($image_urls as $image_url){
                        $url_img_split = explode('/', $image_url);
                        $image_name = end($url_img_split);
                        $postImgEl = (object) array('ID' => '1',
                            'Title' => $modelRssFeed->title,
                            'ImageName' => $image_name,
                            'Image' => $image_url,
                            'ThumbImage' => $image_url,
                            'MediumImage' => $image_url,
                            'Type' => '1',
                            'Width' => '',
                            'Height' => '');
                        array_push($images, $postImgEl);
                    }
                }

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

                //create pages
                $pages = array();
                if($modelRssFeed->description != null && $modelRssFeed->description != ""){
                    $text = $modelRssFeed->description;
                    $pagenum = 1;
                    $lenght = 999;
                    $imgurl = "";
                    $type = 1;

                    while($text != ""){
                        //$pg_text = substr($text, 0, $lenght);
                        $ndx = $lenght;
                        if(strlen($text) > $lenght){
                            $ndx = strpos($text, ' ', $lenght);//search end of word first
                            if($ndx == false){ $ndx = strpos($text, '\n', $lenght); }//then tries with end of line
                            if($ndx == false){ $ndx = $lenght; }//otherwise just use lenght
                        }else{
                            //set thumb on last page
                            $imgurl = $modelRssFeed->media_thumbnail;
                            if($imgurl != ""){ $type = 2;}
                        }
                        $pg_text = trim(substr($text, 0, $ndx));
                        $text = substr($text, $ndx);
                        $newpage = ['Number' => $pagenum, 'Type' => $type,
                            'Text' => $pg_text,'VideoUrl' => "", 'ImageUrl' => $imgurl,
                            'ImageWidth' => null, 'ImageHeight' => null,
                            'VideoThumbnailImage' => null, 'VideoThumbnailImageWidth' => null, 'VideoThumbnailImageHeight' => null];
                        array_push($pages, $newpage);
                        $pagenum++;
                    }
                }

                //create Post element
                $postEl = (object) array('ArtistID' => $artistId,
                    'PostID' => '0',
                    'PostTitle' => $modelRssFeed->title,
                    'ReplyStreamURL' => '',
                    'PostType' => '5',
                    'VideoUrl' => '',
                    //'ThumbImage' => $modelRssFeed->media_thumbnail,
                    'ThumbImage' => '',

                    'ProductID' => '',
                    'Price' => '0.00',
                    'VideoThumbImage' => '',
                    //'Description' => $modelRssFeed->description,
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
                    'PostImage' => array(),
                    'LatestComments' => array());
                if($postImgEl != null){ array_push($postEl->PostImage, $postImgEl);}
                foreach($images as $image){
                    array_push($postEl->PostImage, $image);
                }
                $postEl->Pages = $pages;

                array_push($res, $postEl);

                //fwrite($myfile, $postImgEl->ImageName."\n");
            }
        }
        catch (ErrorException $e)
        {
            $this->addLog($logString,$e);
            //echo $e;
        }

        return $res;
    }

    //Method for searching images inside post feed html content
    private function searchImagesFeed($content){
        $images = array();
        try{
            $index = strpos($content, "<img", 0);
            while($index != false){
                $srcstart = strpos($content, 'src="', $index)+5;
                $srcend = strpos($content, '"', $srcstart);
                $src = substr($content, $srcstart, ($srcend - $srcstart));
                //echo $src."<br>";
                array_push($images, $src);

                $index = strpos($content, "<img", $srcend);
            }
        }
        catch (ErrorException $e)
        {
            $this->addLog($logString,$e);
            //echo $e;
        }
        return $images;
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

    //Method that given iTunes Bundle Id returns first Artist Id that mathces in settings or stickers
    //http://artist.boomcollective.net/api_ver2/web/user/getartistfrombundle&bundle=test
    //Added by Daniele
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
                'Language',
                'PageIndex');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $cmpyID = $data->CompanyID;
                $language = $data->Language;
                $page = 1;
                if(isset($data->PageIndex) && $data->PageIndex != ''){
                    $page = $data->PageIndex;
                }
                $maxpage = 8;

                $bundleData = null;

                $products=Nativeproducts::find()->where(" Status=1 " )->asArray()->all();

                if(count($products)>0){
                    $productID='';
                    $ProductPrice=$products[0]['Price'];
                    $AndroidSKUID='';
                }else{
                    $productID='';
                    $ProductPrice=0;
                    $AndroidSKUID='';
                }
                $products=array('ProductPrice'=>$ProductPrice,'ProductSKUID'=>$productID,'AndroidSKUID'=>$AndroidSKUID);

                //get Artist Company data
                $CompanyData = Artist_company::find()->where(['Id' => $cmpyID])->one();
                if($CompanyData != null){

                    //get all Artists for the group
                    $Artists = Artist::find()->where(['CompanyID' => $cmpyID])->all();
                    $totArtists = count($Artists);
                    $Artists = array_slice($Artists, ($page-1)*$maxpage, $maxpage);

                    $artistList = array();

                    //added subsription product info for Artist  --Kate
                    $modelSetting = new \backend\models\Setting();
                    foreach($Artists as $art){
                        //added ArtistUSerID---Kate
                        //$artobj = ['ArtistID' => $art->ArtistID, 'ArtistName' => $art->ArtistName, 'ProfileThumb' => $art->ProfileThumb, 'Genre' => str_replace('&', 'and', $art->Genre), 'ArtistUserID' => $art->UserID,'products'=>$products];
                        $artobj = ['ArtistID' => $art->ArtistID, 'ArtistName' => $art->ArtistName, 'ProfileThumb' => $art->ProfileThumb,'ArtistUserID' => $art->UserID,'products'=>$products];
                        array_push($artistList, $artobj);
                    }

                    $bundleData = ['CompanyName' => $CompanyData->Name,
                        'CompanyID' => $CompanyData->Id,
                        'AppDescription' => $CompanyData->AppDescription,
                        'ProfileImage' => $CompanyData->Profile_Image,
                        'Website' => $CompanyData->Website,
                        'ErrorMsgSubscribe' => $CompanyData->Error_Msg_Subscribe,
                        'RecordCount' => $totArtists,
                        'Artists' => $artistList];

                    $resultMessage = _getStatusCodeMessageForUser(200);
                    \Yii::$app->language = $language;
                    $lngmsg = \Yii::t('api', $resultMessage);
                    //$this->setHeader(200);
                    echo json_encode(['Status' => 1,
                        "Message" => $lngmsg,
                        'Company' => $bundleData,
                        'BucketName'=>self::S3Bucket."/",
                        'CognitoPoolId'=>self::COGNITOID], JSON_PRETTY_PRINT);

                }else{
                    $resultMessage = _getStatusCodeMessageForPurchase(202);
                    \Yii::$app->language = $language;
                    $lngmsg = \Yii::t('api', $resultMessage);
                    $this->setHeader(400);
                    echo json_encode(['Status' => 0,
                        "Message" => $lngmsg,
                        'BucketName'=>self::S3Bucket."/",
                        'CognitoPoolId'=>self::COGNITOID], JSON_PRETTY_PRINT);
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
            echo $logString;
        }
    }

    public function actionSearchartistcompany() {
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ComID',
                'Language',
                'ArtistName');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);
            if (count($compareField) == 0)
            {
                $cmpyID = $data->ComID;
                $language = $data->Language;
                $artistname = $data->ArtistName;

                $bundleData = null;
                $products=Nativeproducts::find()->where(" Status=1 " )->asArray()->all();

                if(count($products)>0){
                    $productID='';
                    $ProductPrice=$products[0]['Price'];
                    $AndroidSKUID='';
                }else{
                    $productID='';
                    $ProductPrice=0;
                    $AndroidSKUID='';
                }
                $products=array('ProductPrice'=>$ProductPrice,'ProductSKUID'=>$productID,'AndroidSKUID'=>$AndroidSKUID);

                //get Artist Company data
                $CompanyData = Artist_company::find()->where(['Id' => $cmpyID])->one();
                if($CompanyData != null){

                    //get all Artists for the group
                    $Artists = Artist::find()->where(['and', 'CompanyID='.$cmpyID, ['like', 'ArtistName', $artistname]])->all();
                    $totArtists = count($Artists);
                    $artistList = array();

                    //added subsription product info for Artist  --Kate
                    $modelSetting = new \backend\models\Setting();
                    foreach($Artists as $art){
                        //added ArtistUSerID---Kate
                        $artobj = ['ArtistID' => $art->ArtistID, 'ArtistName' => $art->ArtistName, 'ProfileThumb' => $art->ProfileThumb, 'Genre' => str_replace('&', 'and', $art->Genre), 'ArtistUserID' => $art->UserID,'products'=>$products];
                        array_push($artistList, $artobj);
                    }

                    $bundleData = ['CompanyName' => $CompanyData->Name,
                        'CompanyID' => $CompanyData->Id,
                        'AppDescription' => $CompanyData->AppDescription,
                        'ProfileImage' => $CompanyData->Profile_Image,
                        'Website' => $CompanyData->Website,
                        'ErrorMsgSubscribe' => $CompanyData->Error_Msg_Subscribe,
                        'RecordCount' => $totArtists,
                        'Artists' => $artistList];

                    $resultMessage = _getStatusCodeMessageForUser(200);
                    \Yii::$app->language = $language;
                    $lngmsg = \Yii::t('api', $resultMessage);
                    $this->setHeader(200);
                    echo json_encode(['Status' => 1,
                        "Message" => $lngmsg,
                        'Company' => $bundleData,
                        'BucketName'=>self::S3Bucket."/",
                        'CognitoPoolId'=>self::COGNITOID], JSON_PRETTY_PRINT);

                }else{
                    $resultMessage = _getStatusCodeMessageForPurchase(202);
                    \Yii::$app->language = $language;
                    $lngmsg = \Yii::t('api', $resultMessage);
                    $this->setHeader(400);
                    echo json_encode(['Status' => 0,
                        "Message" => $lngmsg,
                        'BucketName'=>self::S3Bucket."/",
                        'CognitoPoolId'=>self::COGNITOID], JSON_PRETTY_PRINT);
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
            //$this->addLog($logString,$e);
            echo $logString;
        }
    }

    public function actionGetnativeproduct(){ //added by Kate--Native product list
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'UserID',
                'DeviceType',
                'ComID');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            if (count($compareField) == 0)
            {
                $UserID=0;
                $ComID=0;  //Add to get ONlY Product ID for that company
                if(isset($data->DeviceType)&&$data->DeviceType!=""){
                    $DeviceType=$data->DeviceType;
                }
                if($DeviceType==2) {
                    $columnarray=['Price','ProductSKUANdr As ProductID'];
                    $colum="ProductSKUANdr";
                }else {
                    $columnarray=['Price','ProductSKUIOS As ProductID'];
                    $colum="ProductSKUIOS";
                }
                $condition=" and ".$colum. " IS NOT NULL ";
                if(isset($data->UserID)&&$data->UserID!=""){
                    //for User, get subscribe list ID and remove the subscribed product ID in the product list
                    $UserID=$data->UserID;
                    $ua=UserArtist::find()->select(['ArtistID','ProductSKU'])->where(" isSub=1 and ProductSKU IS NOT NULL and UserID=".$UserID)->asArray()->all();
                    if(count($ua)>0){
                        $productlist=array();
                        foreach ($ua as $pr) {
                            $productlist[]=$pr['ProductSKU'];
                        }
                        $condition=" and ".$colum." NOT IN ('" . implode("','", $productlist) . "')";
                    }else $ua="";
                }else $ua="";

                if(isset($data->ComID)&&$data->ComID!=""){ //Add to get ONlY Product ID for that company
                    $ComID=$data->ComID;
                    $condition.=" and ComID=".$ComID;
                }
                $logString.=$condition;

                //find all product list for subscription
                $products=Nativeproducts::find()->select($columnarray)->where(" Status=1 And Type=1 ".$condition )->asArray()->all();
                /*if(count($products)>0){
                   $resultCode = 200;
                   $status = "1";
                }else{
                   $resultCode = 404;
                   $status = "0";
                }*/

                $resultCode = 200;
                $status = "1";

                $resultMessage = _getStatusCodeMessageCommon($resultCode);
                echo json_encode(['Status' => $status,
                    "Message" => $resultMessage,
                    "Productlist"=> $products,
                    "Subscribed" =>$ua
                ], JSON_PRETTY_PRINT);
            }else{
                $resultMessage = _getStatusCodeMessageForUser(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }catch (ErrorException $e)
        {
            $this->addLog($logString,$e);
            echo $logString;
        }
    }

    public function actionCancelpurchase(){
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';
            $data = json_decode($arrParams['params']);
            $availableParams = array(
                'ArtistID',
                'ProfileID',
                'ProductSKUID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            if (count($compareField) == 0)
            {
                $artistID = $data->ArtistID;
                $memberID = $data->ProfileID;
                $language = $data->Language;
                $productSKU=$data->ProductSKUID; //added for purchase for Native
                $ua=UserArtist::find()->where(['and','MemberID ='.$memberID,'ArtistID='.$artistID,'isSub=1'])->one();
                if(count($ua)>0){
                    $ua->isSub=0;
                    $ua->CancelDate=date("Y-m-d H:i:s");
                    if ($ua->save(false) !== false) {
                        // update successful
                        $status="1";
                        $message="Successful";
                    } else {
                        // update failed
                        $status="0";
                        $message="Cancel failed, please try agin later";
                    }
                }else{
                    //     //can't find the record in DB
                    $status="0";
                    $message="Can't find this Purchase";
                }

                echo json_encode(['Status' => 0,
                    "Message" => $message], JSON_PRETTY_PRINT);

            }else{
                $resultMessage = _getStatusCodeMessageForUser(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);

            }
        }catch (ErrorException $e)
        {
            $this->addLog($logString,$e);
            echo $logString;
        }
    }

    //ws that returns iTunes data for Natives(for CRM iTunes report)
    public function actionGetnativesitunes($StartDate, $EndDate){
        $logString  = "";
        try
        {
            if ($StartDate != null && $EndDate != null)
            {
                $bundleData = array();

                $artistposts = Memberpost::find()->where(['AND','isNative=1',['between', 'Created', $StartDate, $EndDate]])->all();
                foreach($artistposts as $post){
                    $artistID = $post->ArtistID;
                    $cost = $post->Cost;
                    $productID = $post->ProductID;
                    if($artistID != null){
                        //if ProductID null try to extract it from Transaction details
                        if($productID == null && $post->TransactionData != null && $post->TransactionData != ""){
                            $tdatastr = $post->TransactionData;
                            if($tdatastr[0] == '"'){ $tdatastr = substr($tdatastr,1,-1);} //trim the " chars at beginning and end of the json string
                            $transdat = json_decode(stripslashes($tdatastr));
                            $productID = $transdat->SKU;
                        }

                        //if ProductID is set retrieves the product type from nativeproducts table
                        $productType = null;
                        if($productID != null && $productID != ""){
                            $nativeprod = Nativeproducts::find()->where("ProductSKUIOS='".$productID."'")->one();
                            if($nativeprod != null){ $productType = $nativeprod->Type; }
                        }

                        if($productType != null){
                            //search record in bundleData
                            $found = false;
                            for($i=0; $i<count($bundleData); $i++){
                                if($bundleData[$i]['ArtistID'] == $artistID && $bundleData[$i]['ProductType'] == $productType){
                                    $bundleData[$i]['Cost'] += $cost;
                                    $found = true;
                                    break;
                                }
                            }
                            if(!$found){
                                //create new record for bundledata
                                $dat = ['ArtistID' => $artistID, 'Cost' => $cost, 'ProductType' => $productType];
                                array_push($bundleData, $dat);
                            }
                        }
                    }
                }

                echo json_encode($bundleData, JSON_PRETTY_PRINT);

            }else{
                $lngmsg = \Yii::t('api', "StartDate or EndDate missing");
                $this->setHeader(400);
                echo json_encode($lngmsg, JSON_PRETTY_PRINT);

            }
        }catch (ErrorException $e)
        {
            //$this->addLog($logString,$e);
            //echo $e;
            echo "Error";
        }
    }

    //test Push notification Dan
    public function actionTestpushdan(){

        $arrParams = Yii::$app->request->post();
        $data = json_decode($arrParams['params']);
        $devicetoken = $data->deviceToken;
        $message = $data->message;
        $artistID = $data->artistID;
        $postID = $data->postid;
        $notificationType = $data->notificationType;
        $deviceType = $data->deviceType;

        $sendpushtoartist = new \api\models\Commentnotforartist();
        $sendpushtoartist->deviceToken = $devicetoken;
        $sendpushtoartist->message = $message;
        $sendpushtoartist->time = date("d M 'y H:i A");;
        $sendpushtoartist->postid = $postID;
        $sendpushtoartist->NotificationType = $notificationType;

        $res = "";
        //android
        if($deviceType == 1){
            //$res = $sendpushtoartist->sendToAndroidDan($artistID, 'user');
            $res = $sendpushtoartist->sendToAndroid($artistID, 'user');
        }else{
            //$res = $sendpushtoartist->sendToIphoneDan($artistID, 'user');
            $res = $sendpushtoartist->sendToIphone($artistID, 'artist');
        }
        echo $res;
    }

    //Push Notification test for onesignal
    public function actionTestonesignalpush() {

        $arrParams = Yii::$app->request->post();
        $data = json_decode($arrParams['params']);


        $artistObj=Artist::findOne(["ArtistID"=>1]);

    }

}
