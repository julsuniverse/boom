<?php
namespace api_ver4\controllers;

use backend\models\Artist;
use backend\models\Artist_company;
use backend\models\Feedarticles;
use backend\models\Member;
use backend\models\Nativeproducts;
use backend\models\PostPages;
use backend\models\UserArtist;
use ErrorException;
use Yii;
use yii\filters\auth\HttpBasicAuth;
use yii\web\Controller;
use yii\filters\VerbFilter;

/**
 * Site controller
 */
class TestController extends Controller
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

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::className(),
            'except' => [
                'getprofile',
                'artisthomescreen',
                'getqasettings',
                'getdpinfo',
                'login',
            ],
        ];
        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'getprofile' => ['post'],
                'test' => ['post'],
                'artisthomescreen' => ['get'],
                'getqasettings' => ['post'],
                'getdpinfo' => ['post'],
                'login' => ['post'],
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

    public function actionLogin() {
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->post();
            $logString.="\n Params : ".$arrParams['params'].'\n';
            $data = json_decode($arrParams['params']);
            //print_r($data); die;
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
                    //$artist_profileData = $command3->queryAll();

                    $artist_profileData = $connection->cache(function ($db) use ($command3) {
                        return $command3->queryAll();
                    });

                    if (count($artist_profileData) > 0)
                    {
                        /*************** Get Artist Image List **************/
                        $postimagesprocedure = "CALL Artist_Image_List(2,0," . $artistProfileID . ",'" . self::S3BucketPath . "','" . self::S3BucketArtistImages . "','" . self::S3BucketArtistThumb . "','" . self::S3BucketArtistMedium . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $commandForImage = $connection->createCommand($postimagesprocedure);
                        //$artistimages = $commandForImage->queryAll();

                        $artistimages = $connection->cache(function ($db) use ($commandForImage) {
                            return $commandForImage->queryAll();
                        });

                        $artist_profileData[0]['ArtistImage'] = $artistimages;
                    }
                    //echo $postimagesprocedure; die;

                    /******************* Get Post List if Member **************/
                    //Boom Native App --Kate
                    if($ComID==0) {
                        //$isExclusive=0;
                        $logString.="\n Fan";  // added by Kate
                        //added by Kate--deeplinking
                        $postData_proc = "CALL Post_List_API3(0," .  $artistID . "," . $userID . "," . $profileID . "," . $ComID . "," . $isExclusive . ",'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "',1,20)";//Daniele
                    }else { //For Native App to get multiple artist Post
                        $isExclusive=1;
                        $logString.="\n Native";
                        $postData_proc = "CALL Post_List(0, 0," . $userID . "," . $profileID . "," . $ComID . "," . $isExclusive . ",'" . self::S3BucketPath . "','" . self::Postvideosthumb . "','" . self::Postblurthumbvideos . "','','',@o_RecCount)";
                    }


                    $logString.="\n Post List : ".$postData_proc.'\n';

                    //echo $postData_proc; die;
                    $command4 = $connection->createCommand($postData_proc);

                    $postData = $connection->cache(function ($db) use ($command4) {
                        return $command4->queryAll();
                    });

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
                                $commandForImage = $connection->createCommand($postimageproc);;
                                $imageData = $connection->cache(function ($db) use ($commandForImage) {
                                    return $commandForImage->queryAll();
                                });
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

    public function actionArtisthomescreen() {
        Yii::$app->controller->layout = 'test';
        $logString  = "";
        try
        {
            $arrParams = Yii::$app->request->get();
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
                $postData = $command->queryAll();

                foreach ($postData as $key => $value)
                {
                    if (isset($value['PostID']) && $value['PostID'] != '')
                    {
                        $imageData = array();
                        $postID = $value['PostID'];
                        $postimageproc = "CALL Post_Image_List(1," . $postID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketPostImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $commandForImage = $connection->createCommand($postimageproc);
                        $imageData = $commandForImage->queryAll();

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
                /*echo json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "Result" => $postData], JSON_PRETTY_PRINT);*/
                $res = json_encode(["Status" => $status,
                    "Message" => $lngmsg,
                    "Result" => $postData], JSON_PRETTY_PRINT);
                return $this->render('test', [
                    'res' => $res,
                ]);
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
            echo $e->getMessage();
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

                    $bundleData = $modelSetting::getDb()->cache(function ($db) use ($modelSetting, $artistID){
                        return $modelSetting->find()->where(array(
                            'ArtistID' => $artistID))->asArray()->all();
                    });

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

                    $artist_profileData = $connection->cache(function ($db) use ($command) {
                        return $command->queryAll();
                    });

                    if (count($artist_profileData) > 0)
                    {
                        /*************** Get Artist Image List **************/
                        $postimagesprocedure = "CALL Artist_Image_List(2,0," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketArtistImages . "','" . self::S3BucketArtistThumb . "','" . self::S3BucketArtistMedium . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $commandForImage = $connection->createCommand($postimagesprocedure);

                        $artistimages = $connection->cache(function ($db) use ($commandForImage) {
                            return $commandForImage->queryAll();
                        });

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

                    $postData = $connection->cache(function ($db) use ($command) {
                        return $command->queryAll();
                    });

                    if (count($postData) > 0)
                    {
                        /*************** Get Image List **************/
                        $imageData = array();
                        /************* Get Post images **************/
                        $postimageproc = "CALL Post_Image_List(1," . $reqID . "," . $artistID . ",'" . self::S3BucketPath . "','" . self::S3BucketPostImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                        $logString.="\n Post Image List : ".$postimageproc.'\n';
                        //echo $postimageproc; die;
                        $commandForImage = $connection->createCommand($postimageproc);

                        $imageData = $connection->cache(function ($db) use ($commandForImage) {
                            return $commandForImage->queryAll();
                        });

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

                    $stickersData = $connection->cache(function ($db) use ($command) {
                        return $command->queryAll();
                    });

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
            $unreadQAData = $command->queryAll();
            return $unreadQAData[0]['total'];
        } catch (ErrorException $e) {
            $this->addLog($logString,$e);
        }
    }

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

}
