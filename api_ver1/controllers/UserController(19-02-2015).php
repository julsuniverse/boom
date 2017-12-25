<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use api\models\User;
use backend\models\Artist;
use yii\filters\VerbFilter;
use yii\filters\auth\HttpBasicAuth;
use yii\db\Expression;

require_once '../../backend/models/Artist.php';
require_once '../../backend/models/Post.php';
require_once '../../backend/models/Pushqueue.php';	
/**
 * UserController implements the CRUD actions for User model.
 **/
 
class UserController extends Controller
{
   const STATUS_DELETED = 0;
   const STATUS_ACTIVE = 10;
   const EXPIRE_TIME = 5; // Token expire time in minute
   const S3Bucket               = 	S3BUCKET;
   const BoomFolder             = 	BOOM;
   const S3BucketAbsolutePath 	= 	S3_BUCKETABSOLUTE_PATH;
   const S3BucketPath 		= 	S3_BUCKETPATH;
   const S3BucketArtistImages 	= 	ARTIST_IMAGES;
   const S3BucketArtistThumb 	= 	ARTIST_THUMB_IMAGES;
   const S3BucketArtistMedium 	= 	ARTIST_MEDIUM_IMAGES;
   const S3BucketPostImages 	= 	POST_IMAGES;
   const S3BucketPostThumbImage =       POST_THUMB_IMAGES;
   const S3BucketPostMediumImage=       POST_MEDIUM_IMAGES;
   const S3BucketProfileImages          = 	PROFILE_IMAGES;
   const S3BucketProfileThumbImages 	= 	PROFILE_THUMB_IMAGES;
   const S3BucketProfileMediumImages 	= 	PROFILE_MEDIUM_IMAGES;
   const S3BucketAppIcons 	= 	SIMILAR_APP_MEDIUM;
   const S3BucketStickers 	= 	STICKER_IMAGES;
   const S3BucketStickersThumb  =       STICKER_THUMB_IMAGES;
   const S3BucketStickersSmall  =       STICKER_SMALL_IMAGES;
   const S3BucketStickersMedium =       STICKER_MEDIUM_IMAGES;
   const S3BucketPostVideos     = 	POST_VIDEOS;
   const BoomPath		=	BOOM;
   const EncryptKey 		= 	AESKEY;
   const Limit 			=	APILIMIT;
   const Thumbwidth 		= 	THUMB_WIDTH_SIZE;
   const Thumbheight 		=	THUMB_HEIGHT_SIZE;
   const PostThumbwidth 	= 	POST_THUMB_WIDTH_SIZE;
   const PostThumbheight 	=	POST_THUMB_HEIGHT_SIZE;
   const Mediumwidth 		= 	MEDIUM_WIDTH_SIZE;
   const Mediumheight 		=	MEDIUM_HEIGHT_SIZE;
   const Documentroot           =       DOCUMENT_ROOT;
   const Project                =       PROJECT;
   const Uploads                =       UPLOADS;
   const Postlarge              =       POSTLARGE;
   const Postthumb              =       POSTTHUMB;
   const Blurthumb              =       POSTBLURTHUMB;
   const Postsmall              =       POSTSMALL;
   const Postmedium             =       POSTMEDIUM;
   const Fromemail              =       FROM_EMAIL;
   const Activationpage         =       ACTIVATIONPAGE;
   const Resetpasswordpage      =       RESETPASSWORDPAGE;
   const Postvideosthumb        =       POST_THUMBIMAGE_VIDEOS;
   const Postblurthumb          =       POST_THUMB_BLUR_IMAGES;
   const Postblurthumbvideos    =       POST_BLURTHUMBIMAGE_VIDEOS;
   private $arrSkipAction = ['create', 'login'];
   
   public function behaviors()
   {
      $behaviors = parent::behaviors();
      $behaviors['authenticator'] =  [
		      'class' => HttpBasicAuth::className(),
		      'except' => ['create', 'login','getprofile',
		      'forgotpassword','activity','addpost','signup',
		      'editprofile','commentlist','postlist','checkusername',
		      'likepost','addcomment','likecomment','flag',
		      'stickers','applist','purchasesticker','purchasepost',
		      'artisteditpost','artistdeletepost','likelist',
		      'sharelist','share','flaglist','artisteditprofile',
                      'artisthomescreen','mymusic','globaliospush','globalandroidpush','latestcommentlist'],								
	      ];
      $behaviors['verbs'] =  [
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
                            'addpost' => ['post'],
                            'signup'=> ['post'],
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
                            'artistdeletepost'=>['post'],
                            'likelist'=>['post'],
                            'sharelist'=>['post'],
                            'share'=>['post'],
                            'flaglist'=>['post'],
                            'artisteditprofile'=>['post'],
                            'artisthomescreen'=>['post'],
                            'mymusic'=>['post'],
                            'globaliospush'=>['get'],
                            'globalandroidpush'=>['get'],
                            'latestcommentlist'=>['post'],
		      ]
	      ];		
      return $behaviors;
   }
	
   public function beforeAction($event)
   {	
      $action = $event->id;
      if(isset($this->actions[$action]))
      {
	 $verbs = $this->actions[$action];
      }
      elseif(isset($this->actions['*']))
      {
	 $verbs = $this->actions['*'];
      }
      else
      {
	 return $event->isValid;
      }
      $verb 	= 	Yii::$app->getRequest()->getMethod();
      $allowed 	= 	array_map('strtoupper', $verbs);	
      if(!in_array($verb, $allowed))
      {
	 $this->setHeader(400);
	 echo json_encode(array('status' => 0, 'error_code' => 400, 'message' => 'Method not allowed'), JSON_PRETTY_PRINT);
	 exit;
      }
      return true;
   }
	
   private function setHeader($status)
   {
      $statusHeader = 'HTTP/1.1' . $status . ' ' . _getStatusCodeMessageForUser($status);
      $contentType  = "application/json; charset=utf-8";
      header($statusHeader);
     // header('Content-type: ' . $contentType);
    header('Content-Type: text/html; charset=utf-8');
   }
   public function udate($format, $utimestamp = null) {
  if (is_null($utimestamp))
    $utimestamp = microtime(true);

  $timestamp = floor($utimestamp);
  $milliseconds = round(($utimestamp - $timestamp) * 1000000);

  return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
}
   public function actionLogin()
   {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('Username','Password','DeviceType','DeviceToken',
                                    'UserType','ArtistID','LoginType','MemberName','Email','Language');
      $compareField 	= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $fbmembername	=	"";
	 $fbEmail	=	"";
	 $password	=	"";
	 $username 	= 	$data->Username;
	 if(isset($data->Password))
	 {
	    $password	=	$data->Password;
	 }
	 $devicetype 	= 	$data->DeviceType;
	 $devicetoken 	= 	$data->DeviceToken;
	 $usertype 	= 	$data->UserType;
	 $artistID 	= 	$data->ArtistID;
	 $loginType 	= 	$data->LoginType;
         $language 	= 	$data->Language;
	 if(isset($data->MemberName))
	 {
	    $fbmembername = $data->MemberName;
	 }
	 if(isset($data->Email))
	 {
	    $fbEmail = $data->Email;
	 }
	 $connection 	= 	Yii::$app->db;
	 $procedure	=	"CALL Member_Login('".$username."','".$fbmembername."','".$fbEmail."','".$password."','".$devicetype."','".$devicetoken."',".$usertype.",".$artistID.",".$loginType.",'".self::EncryptKey."','".self::S3BucketPath."','".self::S3BucketProfileImages."',@UserID,@ProfileID,@UserType,@ErrorCode)";
	 $command 	= 	$connection->createCommand($procedure);     
	 $loginData 	= 	$command->queryAll();

	 $resultCode 	= 	$loginData[0]['ErrorCode'];
	 //$resultMessage = 	$loginData[0]['Message'];
        $resultMessage  =   _getStatusCodeMessageForUser($resultCode);
        $userID        =   $loginData[0]['UserID'];
        $artistUserID  =   $loginData[0]['ArtistUserID'];
        $artistProfileID=  $artistID;
        $profileID     =   $loginData[0]['ProfileID'];

        $user_profile_proc	=	"CALL Member_GetProfile('".$userID."','3','".$profileID."','".self::EncryptKey."','".self::S3BucketPath."','".self::S3BucketProfileThumbImages."')";
        $artist_profile_proc	=	"CALL Member_GetProfile('".$artistUserID."','2','".$artistProfileID."','".self::EncryptKey."','".self::S3BucketPath."','".self::S3BucketProfileThumbImages."')";
        $command2               = 	$connection->createCommand($user_profile_proc);     
        $user_profileData 	= 	$command2->queryAll();
        $command3               = 	$connection->createCommand($artist_profile_proc);     
        $artist_profileData 	= 	$command3->queryAll();
        
        if(count($artist_profileData)>0)
        {

            $postimagesprocedure	=	"CALL Artist_Image_List(2,0,".$profileID.",'".self::S3BucketPath."','".self::S3BucketArtistImages."','".self::S3BucketArtistThumb."','".self::S3BucketArtistMedium."','".self::S3BucketPostThumbImage."','".self::S3BucketPostMediumImage."')";
          
            $commandForImage 		= 	$connection->createCommand($postimagesprocedure);     
            $artistimages 		= 	$commandForImage->queryAll();
            $artist_profileData[0]['ArtistImage'] = $artistimages;
        }
        
        if($usertype == "3")
        {
            $postData_proc               =	"CALL Post_List(".$artistID.",".$userID.",".$profileID.",0,'".self::S3BucketPath."','".self::Postvideosthumb."','".self::Postblurthumbvideos."',".self::Limit.",1,@o_RecCount)";
        }
        else
        {
            $postData_proc               =      "CALL Artist_Home_News_Feed(".$artistID.",'".self::S3BucketAbsolutePath."','".self::S3BucketPath."','".self::S3BucketProfileThumbImages."','".self::S3BucketStickers."',".self::Limit.",1,@o_RecCount)";
        }
        $command4               = 	$connection->createCommand($postData_proc);     
        $postData               = 	$command4->queryAll();
        
        $similarApp_proc        =       "CALL SimilarApp_List(".$artistID.",'".self::S3BucketAbsolutePath."','".self::S3BucketAppIcons."')";
        $command5               = 	$connection->createCommand($similarApp_proc);     
        $similarApp             = 	$command5->queryAll();
        
        if(count($postData) > 0)
        {
            foreach($postData as $key => $value)
            {
                if(isset($value['PostID']) && $value['PostID']!= '')
                {
                    $imageData		=	array();
                    $postID 		= 	$value['PostID'];
                    $postimageproc		=	"CALL Post_Image_List(1,".$postID.",".$artistID.",'".self::S3BucketPath."','".self::S3BucketPostImages."','".self::S3BucketPostThumbImage."','".self::S3BucketPostMediumImage."')";
                    $commandForImage 	= 	$connection->createCommand($postimageproc);     
                    $imageData 		= 	$commandForImage->queryAll();
                    
                    $latestcmntsproc 	=	"CALL Latest_Post_CommentList(".$postID.",".$artistID.",".$profileID.",".$usertype.",'".self::S3BucketAbsolutePath."','".self::S3BucketPath."','".self::S3BucketProfileThumbImages."','".self::S3BucketStickers."','".self::S3BucketStickersSmall."','".self::S3BucketStickersMedium."')";
                    $commandForCmnts 	= 	$connection->createCommand($latestcmntsproc);
                    $cmntsData 		= 	$commandForCmnts->queryAll();

                    if(count($imageData)>0)
                    {
                       $postData[$key]['PostImage'] = $imageData;
                    }	
                    else
                    {
                       $postData[$key]['PostImage'] = $imageData;
                    }
                    
                    if(count($cmntsData)>0)
                    {
                        $postData[$key]['LatestComments'] = $cmntsData;
                    }	
                    else
                    {
                        $postData[$key]['LatestComments'] = array();
                    }
                }   
            }
        }
        \Yii::$app->language    = $language;
        $lngmsg = \Yii::t('api',$resultMessage);
        $this->setHeader(400);
        $userData = array();
        if(!empty($user_profileData) &&isset($user_profileData[0])) {
            $userData = $user_profileData[0];
        }
        $artistData = array();
        if(!empty($artist_profileData) &&isset($artist_profileData[0])) {
            $artistData = $artist_profileData[0];
        }
        if($resultCode == "200")
        {
           echo json_encode(['Status'=>1,"Message"=>$lngmsg,'Result'=>$loginData,'MemberProfile'=>$userData,'ArtistProfile'=>$artistData,'PostList'=>$postData,'SimilarApp'=>$similarApp], JSON_PRETTY_PRINT);
        }
        else
        { 
           echo json_encode(['Status'=>0,"Message"=>$lngmsg,'Result'=>$loginData,'MemberProfile'=>$userData,'ArtistProfile'=>$artistData,'PostList'=>$postData,'SimilarApp'=>$similarApp], JSON_PRETTY_PRINT);
        }   
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage = 	_getStatusCodeMessageForUser(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
  }
  
  public function actionCheckusername()
  {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('UserName','ArtistID','UserType','Language');
      $compareField 	= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $userName 	= 	$data->UserName;
	 $artistID 	= 	$data->ArtistID;
	 $userType 	= 	$data->UserType;
         $language 	= 	$data->Language;
	 $connection 	= 	Yii::$app->db;
	 $command 	= 	$connection->createCommand("CALL Check_Username('".$userName."',".$artistID.",".$userType.",'".self::EncryptKey."')");     
	 $userData 	= 	$command->queryAll();
	 $resultCode 	= 	$userData[0]['ErrorCode'];
	 $resultMessage = 	_getStatusCodeMessageForUsername($resultCode);
	 //$this->setHeader($resultCode);
          \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
	 if($resultCode == 200)
	 {
	    echo json_encode(['Status'=>1,"Message"=>$lngmsg], JSON_PRETTY_PRINT);
	 }
	 else
	 {
	    echo json_encode(['Status'=>4,"Message"=>$lngmsg], JSON_PRETTY_PRINT);
	 }    
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage = 	_getStatusCodeMessageForUsername(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
  }
        
   public function actionGetprofile()
   {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('UserID','UserType','ProfileID','Language');
      $compareField 	= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $userID 	= 	$data->UserID;
	 $profileID 	= 	$data->ProfileID;
	 $userType	= 	$data->UserType;
         $language 	= 	$data->Language;
	 $connection 	= 	Yii::$app->db;
	 $procedure	=	"CALL Member_GetProfile('".$userID."','".$userType."','".$profileID."','".self::EncryptKey."','".self::S3BucketPath."','".self::S3BucketProfileThumbImages."')";
	 $command 	= 	$connection->createCommand($procedure);     
	 $profileData 	= 	$command->queryAll();
	 if(count($profileData)>0)
	 {
            if($userType == "2")
            {
                $postimagesprocedure	=	"CALL Artist_Image_List(2,0,".$profileID.",'".self::S3BucketPath."','".self::S3BucketArtistImages."','".self::S3BucketArtistThumb."','".self::S3BucketArtistMedium."','".self::S3BucketPostThumbImage."','".self::S3BucketPostMediumImage."')";
            }
            else
            {
                $postimagesprocedure	=	"CALL Post_Image_List(2,0,".$profileID.",'".self::S3BucketPath."','".self::S3BucketArtistImages."','".self::S3BucketPostThumbImage."','".self::S3BucketPostMediumImage."')";
            }
	    
	    $commandForImage 		= 	$connection->createCommand($postimagesprocedure);     
	    $imageData 			= 	$commandForImage->queryAll();
	    $profileData[0]['ArtistImage'] = $imageData;
	    $resultCode 		= 	200;
	    $status			=	"1";
	 }
	 else
	 {
	    $resultCode 		= 	404;
	    $status			=	"0";
	 }
	 $resultMessage 		= 	_getStatusCodeMessageGetProfile($resultCode);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 $this->setHeader(400);
	 echo json_encode(['Status'=>$status,"Message"=>$lngmsg,'Result'=>$profileData], JSON_PRETTY_PRINT);
	  
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 		= 	_getStatusCodeMessageGetProfile(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
        
   public function actionForgotpassword()
   {
        $arrParams          = 	Yii::$app->request->post();
        $data               = 	json_decode($arrParams['params']);
        $availableParams    = 	array('Username','UserType','ArtistID','Language');
        $compareField       = 	array_diff_key(array_keys($arrParams),$availableParams);

        if(count($compareField) == 0)
        {
           $username 	= 	$data->Username;
           $artistID 	= 	$data->ArtistID;
           $userType 	= 	$data->UserType;
           $language 	= 	$data->Language;
           $appname     =       "Boom Video - Reset password link";
           if(isset($data->MailSubject) && $data->MailSubject != "")
           {
               $appname =    $data->MailSubject;
           }
           $connection 	= 	Yii::$app->db;
           $procedure	=	"CALL Forgot_Password('".$userType."','".$username."','".$artistID."','".self::EncryptKey."')";
           $command 	= 	$connection->createCommand($procedure);     
           $profileData = 	$command->queryAll();
           if(count($profileData)>0)
           {
                $resultCode	=	$profileData[0]['ErrorCode'];
                $resultMessage  =       _getStatusCodeMessageForForgotPassword($resultCode);
                if($resultCode == "200")
                {
                    $email	=	$profileData[0]['Email'];
                    $token	=	$profileData[0]['Token'];
                    $name          =	$profileData[0]['Name'];
                    $o_ArtistID     =	$profileData[0]['ArtistID'];
                    $getArtistData  =   new \backend\models\Artist();
                    if($userType == 2)
                        $artistname     =   $getArtistData->getArtistName($o_ArtistID);
                    else if($userType == 3)
                        $artistname     =   $getArtistData->getArtistName($artistID);
                    $link           =   self::Resetpasswordpage.$token;
                    $getcontent     =   $getArtistData->getForgotemailtemplate($name,$artistname,$link);
                    if($email != "")
                    {
                         Yii::$app->mailer->compose()
                        ->setFrom(self::Fromemail)
                        ->setTo($email)
                        ->setSubject($appname)
                        ->setHtmlBody($getcontent)
                        ->send();
                    }
                    $status    	=   "1";
                }
                else
                {
                    $status         = "0";
                    /*$mailmodel      =    new Sendmail();
                    echo $sendmail  =    $mailmodel->sendSesEmail("rosemary@e2logy.com","Test","","");die;*/
                }
           }
            \Yii::$app->language = $language;
			$lngmsg = \Yii::t('api',$resultMessage);
                        $this->setHeader(400);
           echo json_encode(['Status'=>$status,"Message"=>$lngmsg,'Result'=>$profileData], JSON_PRETTY_PRINT);
        }
        else
        {
            $resultMessage 	= 	_getStatusCodeMessageForUser(502);
            \Yii::$app->language = $language;
			$lngmsg = \Yii::t('api',$resultMessage);
                        $this->setHeader(400);
            echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
        }
   }
   
   public function actionLikepost()
   {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('PostID','ArtistID','ActivityTypeID','ProfileID','RefTable','RefTableID','Comment','Language','ActivityID','UserType');
      $compareField 	= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $postID 	= 	$data->PostID;
	 $artistID 	= 	$data->ArtistID;
	 $activityTypeID= 	$data->ActivityTypeID;
	 $profileID 	= 	$data->ProfileID;
	 $refTable 	= 	$data->RefTable;
	 $refTableID 	= 	$data->RefTableID;
	 $comment 	= 	$data->Comment;
	 $activityID 	= 	$data->ActivityID;
	 $userType 	= 	$data->UserType;
         $language 	= 	$data->Language;
	 $connection 	= 	Yii::$app->db;
	 $procedure 	=	"CALL Member_Add_Activity('".$postID."','".$artistID."','".$profileID."','".$activityTypeID."','".$refTable."','".$refTableID."','".$comment."','".$activityID."',".$userType.",0,'".self::S3BucketPath."')";
         $command 	= 	$connection->createCommand($procedure);     
	 $activityData 	= 	$command->queryAll();
	 if(count($activityData)>0)
	 {
	    $resultCode 	= 	200;
	    $status		=	"1";
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageCommon($resultCode);
	 //$this->setHeader($resultCode);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
	 echo json_encode(["Status"=>$status,"Message"=>$lngmsg,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageCommon(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
	 echo json_encode(["Status"=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
   
   public function actionFlag()
   {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('PostID','ArtistID','ActivityTypeID','ProfileID','RefTable','RefTableID','Language','Comment','ActivityID','UserType');
      $compareField 	= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $postID 	      	= 	$data->PostID;
	 $artistID 		= 	$data->ArtistID;
	 $activityTypeID 	= 	$data->ActivityTypeID;
	 $userID 		= 	$data->ProfileID;
	 $refTable 		= 	$data->RefTable;
	 $refTableID 		= 	$data->RefTableID;
	 $comment 		= 	$data->Comment;
	 $activityID 		= 	$data->ActivityID;
	 $userType 		= 	$data->UserType;
         $language 		= 	$data->Language;
	 $connection 		= 	Yii::$app->db;
	 $procedure		=	"CALL Member_Add_Activity('".$postID."','".$artistID."','".$userID."','".$activityTypeID."','".$refTable."','".$refTableID."','".$comment."',".$activityID.",".$userType.",0,'".self::S3BucketPath."')";
	 $command 		= 	$connection->createCommand($procedure);     
	 $activityData 		= 	$command->queryAll();
	 if(count($activityData)>0)
	 {
	    $resultCode 	= 	200;
	    $status		=	"1";
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageCommon($resultCode);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 $this->setHeader(400);
	 echo json_encode(["Status"=>$status,"Message"=>$lngmsg,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageCommon(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
	 echo json_encode(["Status"=>0,"Message"=>$lngmsg,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
   }
   public function actionShare()
   {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('PostID','ArtistID','ActivityTypeID','ProfileID','Language','UserType');
      $compareField 	= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $postID 	      	= 	$data->PostID;
	 $artistID 		= 	$data->ArtistID;
	 $activityTypeID 	= 	$data->ActivityTypeID;
	 $profileID 		= 	$data->ProfileID;
	 $userType 		= 	$data->UserType;
         $language 		= 	$data->Language;
	 $refTable 		= 	1;
	 $refTableID 		= 	$postID;
	 $comment 		= 	"";
	 $activityID 		= 	0;
	 $connection 		= 	Yii::$app->db;
	 $procedure		=	"CALL Member_Add_Activity('".$postID."','".$artistID."','".$profileID."','".$activityTypeID."','".$refTable."','".$refTableID."','".$comment."',".$activityID.",".$userType.",0,'".self::S3BucketPath."')";
	 $command 		= 	$connection->createCommand($procedure);     
	 $activityData 		= 	$command->queryAll();
	 if(count($activityData)>0)
	 {
	    $resultCode 	= 	200;
	    $status		=	"1";
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageCommon($resultCode);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 $this->setHeader(400);
	 echo json_encode(["Status"=>1,"Message"=>$lngmsg,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
      else
      {
	 $this->setHeader(400);
	 $resultMessage 	= 	_getStatusCodeMessageCommon(502);
          \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(["Status"=>0,"Message"=>$lngmsg,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
   }
   public function actionLikecomment()
   {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('PostID','ArtistID','ActivityTypeID','ProfileID','RefTable','Language','RefTableID','Comment','ActivityID','UserType');
      $compareField 	= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
	 $artistID 		= 	$data->ArtistID;
	 $activityTypeID 	= 	$data->ActivityTypeID;
	 $userID 		= 	$data->ProfileID;
	 $refTable 		= 	$data->RefTable;
	 $refTableID 		= 	$data->RefTableID;
	 $comment 		= 	$data->Comment;
	 $activityID 		= 	$data->ActivityID;
	 $userType 		= 	$data->UserType;
         $language 		= 	$data->Language;
	 $connection 		= 	Yii::$app->db;
	 $procedure 		=	"CALL Member_Add_Activity('".$postID."','".$artistID."','".$userID."','".$activityTypeID."','".$refTable."','".$refTableID."','".$comment."',".$activityID.",".$userType.",0,'".self::S3BucketPath."')";
	 $command 		= 	$connection->createCommand($procedure);     
	 $activityData 		= 	$command->queryAll();
	 if(count($activityData)>0)
	 {
	    $resultCode 	= 	200;
	    $status		=	"1";
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageCommon($resultCode);
          \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 $this->setHeader(400);
	 echo json_encode(["Status"=>$status,"Message"=>$lngmsg,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage = _getStatusCodeMessageCommon(502);
           \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg,"Result"=>$activityData],JSON_PRETTY_PRINT);
      }
   }
   
   public function actionAddcomment()
   {
        $arrParams 		= 	Yii::$app->request->post();
        $data 			= 	json_decode($arrParams['params']);
        $availableParams 	= 	array('PostID','ArtistID','ActivityTypeID','ProfileID','RefTable','Language','RefTableID','Comment','ActivityID','UserType','StickerID');
        $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
        if(count($compareField) == 0)
        {
            $postID 		= 	$data->PostID;
            $artistID      	= 	$data->ArtistID;
            $activityTypeID 	= 	$data->ActivityTypeID;
            $userID 		= 	$data->ProfileID;
            $refTable 		= 	$data->RefTable;
            $refTableID 	= 	$data->RefTableID;
            $comment 		= 	$data->Comment;
            $activityID 	= 	$data->ActivityID;
            $userType 		= 	$data->UserType;
            $language 		= 	$data->Language;
            $stickerID 		= 	$data->StickerID;
            $time               =       date("d M 'y H:i A");
            $connection 	= 	Yii::$app->db;
            $procedure		=	"CALL Member_Add_Activity('".$postID."','".$artistID."','".$userID."','".$activityTypeID."','".$refTable."','".$refTableID."','".addslashes($comment)."',".$activityID.",".$userType.",".$stickerID.",'".self::S3BucketPath."')";
            $command 		= 	$connection->createCommand($procedure);     
            $activityData 	= 	$command->queryAll();

            if(count($activityData)>0)
            {
                $resultCode         =   200;
                $status             =	"1"; 
                $totalcomments      =   0;
                $artistobj          =   new \backend\models\Artist();
                $commentactivityid  =   $activityData[0]['PostCommentActivityID'];
                $totalcomments      =   $activityData[0]['TotalComments'];
                $gettokens          =   $artistobj->getAllDevicesForCommentPush($postID, $artistID,$commentactivityid);
                $artistdevicetype   =       $artistobj->getArtistDeviceType($artistID);
                $artistdevicetoken  =       $artistobj->getArtistDeviceToken($artistID);
                if($userType == "3")
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
                                $sendpushtoartist->sendToIphone($artistID);
                            }
                            catch(Exception $e)
                            {
                                echo "cat message".$e->getMessage()." \n";
                            }
                        }
                        if($artistdevicetype == 2)
                        {
                            $sendpushtoartist->sendToAndroid($artistID);
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
                    
                }
            }
            else
            {
               $resultCode 	=      404;
               $status		=	"0";
            }
            $resultMessage 	= 	_getStatusCodeMessageCommon($resultCode);
            \Yii::$app->language = $language;
            $lngmsg = \Yii::t('api',$resultMessage);
            $this->setHeader(400);
            echo json_encode(['Status'=>$status,"Message"=>$lngmsg,"Result"=>$activityData], JSON_PRETTY_PRINT);
        }
      else
      {
	$this->setHeader(400);
	 $resultMessage 	= 	_getStatusCodeMessageCommon(502);
          \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg,"Result"=>$activityData],JSON_PRETTY_PRINT);
      }
   }
   
   public function actionSignup()
   {
       /*$getArtistData  =   new \backend\models\Artist();
                    //$artistname     =   $getArtistData->getArtistName($artistID);
                    $link           =   self::Activationpage."dfsdf";
                    $getcontent     =   $getArtistData->getSignupemailtemplate("Rosemary","dfsd",$link,"korean");
                     Yii::$app->mailer->compose()
                    ->setFrom(self::Fromemail)
                    ->setTo("rosemary@e2logy.com")
                    ->setSubject("test mail")
                    ->setHtmlBody($getcontent)
                    ->send();die;*/
        $arrParams 		= 	Yii::$app->request->post();
        $data 			= 	json_decode($arrParams['params']);
        $availableParams 	= 	array('ArtistID','Name','Username','Email','Password','Language');
        $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
        if(count($compareField) == 0)
        {
            $artistID 		= 	$data->ArtistID;
            $name 		= 	$data->Name;
            $username 		= 	$data->Username;
            $email 		= 	$data->Email; 
            if(isset($data->Birthdate) && $data->Birthdate != "")
            {
                $birthDate 		= 	date("d-m-Y",strtotime($data->Birthdate)); 
            }
            else
            {
               $birthDate 		= 	"0000-00-00";
            }
            if(isset($data->Gender) && $data->Gender != "")
            {
                $gender 		= 	$data->Gender;
            }
            else
            {
                $gender 		= 	"3";
            }
            if(isset($data->DeviceType) && $data->DeviceType != "")
            {
                $devicetype 		= 	$data->DeviceType;
            }
            else
            {
                $devicetype 		= 	"";
            }
            if(isset($data->Zipcode) && $data->Zipcode != "")
            {
                $zipCode 		= 	$data->Zipcode;
            }
            else
            {
                $zipCode 		= 	"";
            }
            if(isset($data->Mobile) && $data->Mobile != "")
            {
                $mobile 		= 	$data->Mobile;
            }
            else
            {
                $mobile 		= 	"";
            }
            
            if(isset($data->Image) && $data->Image != "")
            {
                $image 		= 	$data->Image;
            }
            else
            {
                $image 		= 	"";
            }
            if(isset($data->MailSubject) && $data->MailSubject != "")
            {
                $subject    =   $data->MailSubject;
            }
            else
            {
                $subject    =   "Boom Fan App";
            }
            $password 		= 	$data->Password;
            $language 		= 	$data->Language;
            $connection 	= 	Yii::$app->db;
            $command 		= 	$connection->createCommand("CALL Member_Registration('".$name."','".$image."','".$email."','".$birthDate."','".$zipCode."','".$mobile."','".$gender."','".$artistID."','".$username."','".$devicetype."','".self::EncryptKey."','".$password."')");     
            $registrationData 	= 	$command->queryAll();
            $resultCode 	= 	$registrationData[0]['ErrorCode'];
            $resultMessage 	= 	$registrationData[0]['Message'];

            //$this->setHeader($resultCode);

            if($resultCode == 200)
            {
                $activationcode 	= 	$registrationData[0]['ActivationCode'];
                if($email != "")
                {
                    $getArtistData  =   new \backend\models\Artist();
                    $artistname     =   $getArtistData->getArtistName($artistID);
                    $link           =   self::Activationpage.$activationcode;
                    $getcontent     =   $getArtistData->getSignupemailtemplate($name,$artistname,$link,"english");
                     Yii::$app->mailer->compose()
                    ->setFrom(self::Fromemail)
                    ->setTo($email)
                    ->setSubject($subject)
                    ->setHtmlBody($getcontent)
                    ->send();
                }
                \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
               echo json_encode(['Status'=>1,"Message"=>$lngmsg,'Result'=>$registrationData], JSON_PRETTY_PRINT);
            }
            else
            {
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api',$resultMessage);
                $this->setHeader(400);
               echo json_encode(['Status'=>0,"Message"=>$lngmsg,'Result'=>$registrationData], JSON_PRETTY_PRINT);
            }  

        }
        else
        {
            $this->setHeader(400);
            $resultMessage = _getStatusCodeMessageForUserRegistration(502);
            \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api',$resultMessage);
            echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
        }
   }
        
   public function actionEditprofile()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('UserID','ProfileID','ArtistID','Name','Email','Password','ShowNotification','Language','Mobile','Password','Image');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
      if(count($compareField) == 0)
      {
	 $userID 		= 	$data->UserID;
	 $profileID 		= 	$data->ProfileID;
	 $artistID 		= 	$data->ArtistID;
	 $name 			= 	$data->Name;
	 $email 		= 	$data->Email; 
         if(isset($data->Birthdate) && $data->Birthdate != "")
            {
                $birthDate 		= 	date("d-m-Y",strtotime($data->Birthdate)); 
            }
            else
            {
               $birthDate 		= 	"0000-00-00";
            }
            if(isset($data->Gender) && $data->Gender != "")
            {
                $gender 		= 	$data->Gender;
            }
            else
            {
                $gender 		= 	"3";
            }
            if(isset($data->DeviceType) && $data->DeviceType != "")
            {
                $devicetype 		= 	$data->DeviceType;
            }
            else
            {
                $devicetype 		= 	"";
            }
            if(isset($data->Zipcode) && $data->Zipcode != "")
            {
                $zipCode 		= 	$data->Zipcode;
            }
            else
            {
                $zipCode 		= 	"";
            }
	 $mobile 		= 	$data->Mobile;
	 $password 		= 	$data->Password;
	 $image 		= 	$data->Image;
          $language 		= 	$data->Language;
          $showNotification     =       $data->ShowNotification;
	 $connection 		= 	Yii::$app->db;
	 $procedure     	=	"CALL Member_Edit_Profile_new('".$name."','".$image."','".$email."','".$password."','".$birthDate."','".$zipCode."','".$mobile."','".$gender."','".$artistID."','".$profileID."','".$userID."',".$showNotification.",'".self::EncryptKey."')";
	 $command       	= 	$connection->createCommand($procedure);     
	 $registrationData 	= 	$command->queryAll();
         if($image != "")
         {
            $content = file_get_contents(self::S3BucketAbsolutePath.'/'.self::BoomFolder.$artistID.self::S3BucketProfileImages.$image);
            $commonthumb = self::Documentroot.self::Project.self::Uploads.self::Postlarge.$image;
            $createthumb = self::Documentroot.self::Project.self::Uploads.self::Postthumb.$image;
            $createmedium = self::Documentroot.self::Project.self::Uploads.self::Postmedium.$image;
            file_put_contents($commonthumb, $content);

            $thumbimage=Yii::$app->image->load($commonthumb);
            $thumbimage->resize("",self::Thumbheight);    
            $thumbimage->save($createmedium,100);

            $mediumimage=Yii::$app->image->load($commonthumb);
            $mediumimage->resize("",self::Mediumheight);    
            $mediumimage->save($createthumb,100);

            \S3::putObjectFile($createthumb,self::S3Bucket,self::BoomFolder.$artistID.self::S3BucketProfileThumbImages.$image, \S3::ACL_PUBLIC_READ);
            \S3::putObjectFile($createmedium,self::S3Bucket,self::BoomFolder.$artistID.self::S3BucketProfileMediumImages.$image, \S3::ACL_PUBLIC_READ);   
         }
	 $resultCode 		= 	$registrationData[0]['ErrorCode'];
	 $resultMessage 	= 	_getStatusCodeMessageForEditprofile($resultCode);
         \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api',$resultMessage);
	 $this->setHeader(400);
	 if($resultCode == 200)
	 {
	    echo json_encode(['Status'=>1,"Message"=>$lngmsg,'Result'=>$registrationData], JSON_PRETTY_PRINT);
	 }
	 else
	 {
	    echo json_encode(['Status'=>0,"Message"=>$lngmsg,'Result'=>$registrationData], JSON_PRETTY_PRINT);
	 }    
      }
      else
      {
	 $this->setHeader(400);
	 $resultMessage 	=	 _getStatusCodeMessageForUserRegistration(502);
         \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
   
   public function actionArtisteditprofile()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('ArtistID','UserID','ArtistName','ProfileThumb','Email','Birthdate','Nationality',
					      'Residence','Website','YouTubeChannelName','YearsActive','Genre','AboutMe','YouTubePageUrl',
					     'InstagramPageUrl','TwitterPageUrl','FacebookPageUrl','Language','DeleteMediaIDs','ArtistImages','Password');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
      if(count($compareField) == 0)
      {
	 $artistID 		= 	$data->ArtistID;
	 $userID 		= 	$data->UserID;
	 $artistName 		= 	$data->ArtistName;
	 $profileThumb 		= 	$data->ProfileThumb;
	 $email 		= 	$data->Email; 
	 $birthDate 		= 	date("d-m-Y",strtotime($data->Birthdate));
	 $nationality 		= 	$data->Nationality; 
	 $residence 		= 	$data->Residence;
	 $website       	= 	$data->Website;
	 $youTubeChannelName	= 	$data->YouTubeChannelName;
	 $yearsActive 		= 	$data->YearsActive;
	 $genre			= 	$data->Genre;
	 $aboutMe		= 	$data->AboutMe;
	 $youTubePageUrl	=	$data->YouTubePageUrl;
	 $instagramPageUrl	=	$data->InstagramPageUrl;
	 $twitterPageUrl	=	$data->TwitterPageUrl;
	 $facebookPageUrl	=	$data->FacebookPageUrl;
	 $deleteMediaIDs      	=	$data->DeleteMediaIDs;
	 $artistImages 		=	$data->ArtistImages;
	 $password		=	$data->Password;
         $language		=	$data->Language;
	 $connection 		= 	Yii::$app->db;
	 $procedure     	=	"CALL Artist_Edit_Profile(".$artistID.",".$userID.",'".$artistName."','".$profileThumb."',
				       '".$email."','".$birthDate."','".$nationality."','".$residence."','".$website."','".$youTubeChannelName."',
				       '".$yearsActive."','".$genre."','".addslashes($aboutMe)."','".$youTubePageUrl."','".$instagramPageUrl."',
				       '".$twitterPageUrl."','".$facebookPageUrl."','".$password."','".self::EncryptKey."')";
	 $command       	= 	$connection->createCommand($procedure);     
	 $editData 		= 	$command->queryAll();
        if($profileThumb != "")
        {
            $content = file_get_contents(self::S3BucketAbsolutePath.'/'.self::BoomFolder.$artistID.self::S3BucketProfileImages.$profileThumb);
            $commonthumb = self::Documentroot.self::Project.self::Uploads.self::Postlarge.$profileThumb;
            $createthumb = self::Documentroot.self::Project.self::Uploads.self::Postthumb.$profileThumb;
            $createmedium = self::Documentroot.self::Project.self::Uploads.self::Postmedium.$profileThumb;
            file_put_contents($commonthumb, $content);

            $thumbimage=Yii::$app->image->load($commonthumb);
            $thumbimage->resize("",self::Thumbheight);    
            $thumbimage->save($createmedium,100);

            $mediumimage=Yii::$app->image->load($commonthumb);
            $mediumimage->resize("",self::Mediumheight);    
            $mediumimage->save($createthumb,100);

            \S3::putObjectFile($createthumb,self::S3Bucket,self::BoomFolder.$artistID.self::S3BucketProfileThumbImages.$profileThumb, \S3::ACL_PUBLIC_READ);
            \S3::putObjectFile($createmedium,self::S3Bucket,self::BoomFolder.$artistID.self::S3BucketProfileMediumImages.$profileThumb, \S3::ACL_PUBLIC_READ);   
        }
	 $resultCode 		= 	$editData[0]['ErrorCode'];
	 $resultMessage 	= 	_getStatusCodeMessageForEditprofile($resultCode);
	$this->setHeader(400);
         \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api',$resultMessage);
	 if($resultCode == 200)
	 {
	    if(isset($deleteMediaIDs) && count($deleteMediaIDs) > 0)
	    {
	       foreach($deleteMediaIDs as $val)
	       {
		  $delProc    	=	"CALL Artist_Delete_Images(".$val.",@o_ErrorCode)";
		  $delCmnd    	= 	$connection->createCommand($delProc)->execute();
	       }
	    }
	    if(isset($artistImages) && count($artistImages) > 0)
	    {
	       foreach($artistImages as $keyval)
	       {
                    /*$content = file_get_contents(self::S3BucketAbsolutePath.'/'.self::BoomFolder.$artistID.self::S3BucketArtistImages.$keyval);
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
                    \S3::putObjectFile($createmedium,self::S3Bucket,self::BoomFolder.$artistID.self::S3BucketArtistMedium.$keyval, \S3::ACL_PUBLIC_READ);  */ 
		  $addProc    	=	"CALL Artist_Edit_Images(".$artistID.",'".$keyval."')";
		  $addCmnd    	= 	$connection->createCommand($addProc)->execute();
	       }
	    }
	    echo json_encode(['Status'=>1,"Message"=>$lngmsg,'Result'=>$editData], JSON_PRETTY_PRINT);
	 }
	 else
	 {
	    echo json_encode(['Status'=>0,"Message"=>$lngmsg,'Result'=>$editData], JSON_PRETTY_PRINT);
	 }    
      }
      else
      {
	 $this->setHeader(400);
	 $resultMessage 	=	 _getStatusCodeMessageForUserRegistration(502);
         \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
   public function actionCommentlist()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostID','ArtistID','ProfileID','UserType','PageIndex','Language');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
	 $artistID 		= 	$data->ArtistID;
	 $profileID		=	$data->ProfileID;
	 $userType		=	$data->UserType;
	 $pageindex		=	$data->PageIndex;
         $language		=	$data->Language;
	 $connection 		= 	Yii::$app->db;
	 $procedure 		=	"CALL Post_CommentList(".$postID.",".$artistID.",".$profileID.",".$userType.",'".self::S3BucketAbsolutePath."','".self::S3BucketPath."','".self::S3BucketProfileThumbImages."','".self::S3BucketStickers."','".self::S3BucketStickersSmall."','".self::S3BucketStickersMedium."',".self::Limit.",".$pageindex.",@o_RecCount)";
	 $command 		= 	$connection->createCommand($procedure);     
	 $commentData 		= 	$command->queryAll();
	 $reccommand 		= 	$connection->createCommand('SELECT @o_RecCount')->queryOne();
	 $recordcnt 		= 	$reccommand['@o_RecCount'];
	 if(count($commentData)>0)
	 {
	    $resultCode 	= 	200;
	    $status		=	"1";
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageCommentList($resultCode);
         \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api',$resultMessage);
	 $this->setHeader(400);
	 echo json_encode(['Status'=>$status,"Message"=>$lngmsg,"RecordCount"=>$recordcnt,"Result"=>$commentData], JSON_PRETTY_PRINT);
      }
      else
      {
	$this->setHeader(400);
	 $resultMessage 	= 	_getStatusCodeMessageCommentList(502);
         \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
   public function actionLatestcommentlist()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostID','ArtistID','ProfileID','UserType');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
	 $artistID 		= 	$data->ArtistID;
	 $profileID		=	$data->ProfileID;
	 $userType		=	$data->UserType;
         $language		=	$data->Language;
	 $connection 		= 	Yii::$app->db;
	 $latestcmntsproc 	=	"CALL Latest_Post_CommentList(".$postID.",".$artistID.",".$profileID.",".$userType.",'".self::S3BucketAbsolutePath."','".self::S3BucketPath."','".self::S3BucketProfileThumbImages."','".self::S3BucketStickers."','".self::S3BucketStickersSmall."','".self::S3BucketStickersMedium."')";
	 $command 		= 	$connection->createCommand($latestcmntsproc);     
	 $commentData 		= 	$command->queryAll();

	 if(count($commentData)>0)
	 {
	    $resultCode 	= 	200;
	    $status		=	"1";
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageCommentList($resultCode);
         \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api',$resultMessage);
	 $this->setHeader(400);
	 echo json_encode(['Status'=>$status,"Message"=>$lngmsg,"Result"=>$commentData], JSON_PRETTY_PRINT);
      }
      else
      {
	$this->setHeader(400);
	 $resultMessage 	= 	_getStatusCodeMessageCommentList(502);
         \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
   public function actionPostlist()
   {
       $filename = 'log/API_log_'.date('Ymd').'.txt';
        $log_current = "\n *******API Call for ".Yii::$app->controller->action->id."******* \n";
        $log_current .= "\n Start time : ".$this->udate('Y-m-d H:i:s:u')." ******* \n";
        $log_current .= "Request Method: ".$_SERVER['REQUEST_METHOD']."\n";
        $log_current .= "Request: ".print_r($_REQUEST, true);

         if(file_exists($filename)) 
         {
            $log_current .= file_get_contents($filename);
         }
        
        $arrParams 		= 	Yii::$app->request->post();
        $data 			= 	json_decode($arrParams['params']);
        $availableParams 	= 	array('UserID','ArtistID','ProfileID','IsExclusive','PageIndex','UserType','Language');
        $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
        if(count($compareField) == 0)
        {
            $postData               =   array();  
            $UserID                 = 	$data->UserID;
            $profileID              = 	$data->ProfileID;
            $artistID               = 	$data->ArtistID;
            $isExclusive            = 	$data->IsExclusive;
            $pageindex              = 	$data->PageIndex;
            $usertype               = 	$data->UserType;
            $language               = 	$data->Language;
            $connection             = 	Yii::$app->db;
            $procedure              =	"CALL Post_List(".$artistID.",".$UserID.",".$profileID.",".$isExclusive.",'".self::S3BucketPath."','".self::Postvideosthumb."','".self::Postblurthumbvideos."',".self::Limit.",".$pageindex.",@o_RecCount)";
            $command                =	$connection->createCommand($procedure);     
            $postData               = 	$command->queryAll();
            //$usermodel              =   new User;
            //$xmlarray1              =   $usermodel->xmlparsing('http://boom.boomvideo.tv/alpha/test/vast/rss_feed.php?PGUID=37ff8226-25ad-4b55-814a-75316b9cdefc');
           /* $procedure2              =	"CALL AddQueryLog(0,0,'Post_List- Xml','".print_r($xmlarray1,true)."')";
            $command2                =	$connection->createCommand($procedure2);     
            $querylog               = 	$command2->execute();*/
            //$xmlarray2              =   $usermodel->xmlparsing('http://boom.boomvideo.tv/alpha/test/vast/rss_feed.php?PGUID=37ff8226-25ad-4b55-814a-75316b9cdefc');
            //$xmlarray3              =   $usermodel->xmlparsing('http://boom.boomvideo.tv/alpha/test/vast/rss_feed.php?PGUID=37ff8226-25ad-4b55-814a-75316b9cdefc');
            //$xmltitlearray          =   array($xmlarray1[0]['title'],$xmlarray1[0]['title'],$xmlarray1[0]['title']);
            //$xmldescarray           =   array($xmlarray1[0]['description'],$xmlarray1[0]['description'],$xmlarray1[0]['description']);
           // $xmlmediatitltarray     =   array($xmlarray1[0]['media:title'],$xmlarray1[0]['media:title'],$xmlarray1[0]['media:title']);
            //$xmlmediathumb          =   array($xmlarray1[0]['media:thumbnail']['url'],$xmlarray1[0]['media:thumbnail']['url'],$xmlarray1[0]['media:thumbnail']['url']);
            //$xmlcontenturl          =   array($xmlarray1[0]['media:content']['url'],$xmlarray1[0]['media:content']['url'],$xmlarray1[0]['media:content']['url']);
            $reccommand             = 	$connection->createCommand('SELECT @o_RecCount')->queryOne();
            $recordcnt              = 	$reccommand['@o_RecCount'];
            foreach($postData as $key => $value)
            {
                if(isset($value['PostID']) && $value['PostID']!= '')
                {
                    $imageData		=	array();
                    $postID 		= 	$value['PostID'];
                    $postimageproc	=	"CALL Post_Image_List(1,".$postID.",".$artistID.",'".self::S3BucketPath."','".self::S3BucketPostImages."','".self::S3BucketPostThumbImage."','".self::S3BucketPostMediumImage."')";
                    $commandForImage 	= 	$connection->createCommand($postimageproc);
                    $imageData 		= 	$commandForImage->queryAll();
                    
                    $latestcmntsproc 	=	"CALL Latest_Post_CommentList(".$postID.",".$artistID.",".$profileID.",".$usertype.",'".self::S3BucketAbsolutePath."','".self::S3BucketPath."','".self::S3BucketProfileThumbImages."','".self::S3BucketStickers."','".self::S3BucketStickersSmall."','".self::S3BucketStickersMedium."')";
                    $commandForCmnts 	= 	$connection->createCommand($latestcmntsproc);
                    $cmntsData 		= 	$commandForCmnts->queryAll();
                    

                    if(count($imageData)>0)
                    {
                        $postData[$key]['PostImage'] = $imageData;
                    }	
                    else
                    {
                        $postData[$key]['PostImage'] = $imageData;
                    }
                    if(count($cmntsData)>0)
                    {
                        $postData[$key]['LatestComments'] = $cmntsData;
                    }	
                    else
                    {
                        $postData[$key]['LatestComments'] = array();
                    }
                }
            }
            /*$procedure3              =	"CALL AddQueryLog(0,0,'Post_List- postData','".print_r($postData,true)."')";
            $command3                =	$connection->createCommand($procedure3);     
            $querylog3               = 	$command3->execute();*/
            if(count($postData)>0)
            {
                $resultCode = 200;
                $status	   =	"1";
            }
            else
            {
                $resultCode = 404;
                $status		=	"0";
            }
            $resultMessage = _getStatusCodeMessagePostList($resultCode);
            \Yii::$app->language = $language;
            $lngmsg = \Yii::t('api',$resultMessage);
            $rssaray = array();
            $i=0;
            $j=0;
            if($usertype == "3")
            {
                /*foreach($postData as $key =>$val)
                {
                    if($j%3==0 && $key != 0 && $key <= 3)// && $key != 0 && $key <= 3  || $j == 4 
                    {
                        $title                           =   $xmltitlearray[$i];
                        $description                     =   $xmldescarray[$i]; 
                        $mediatitle                      =   $xmlmediatitltarray[$i];
                        $mediathumb                      =   $xmlmediathumb[$i];
                        $contenturl                      =   $xmlcontenturl[$i];
                        $rssaray[$key]['PostTitle']      =   $title;
                        $rssaray[$key]['Description']    =   $description; 
                        $rssaray[$key]['MediaTitle']     =   $mediatitle; 
                        $rssaray[$key]['ThumbImage']     =   $mediathumb; 
                        $rssaray[$key]['ContentUrl']     =   $contenturl; 
                        $rssaray[$key]['IsAdvertisement']=   "1";
                        if($i == 2)
                        {
                            $i = 0;
                        }
                        else
                        {
                            $i++;
                        }
                        $j=-1;
                        $rssaray[$key+1] =   $val;
                    }
                    else
                    {
                        $rssaray[] =   $val;
                    }
                    $j++;
                }*/
		/*$procedure4              =	"CALL AddQueryLog(0,0,'Post_List- rssData','".print_r($rssaray,true)."')";
                $command4                =	$connection->createCommand($procedure4);     
                $querylog4               = 	$command4->execute();*/
               $this->setHeader(400);
               $recordcnt = count($postData);
		$encodedArray = array('Status'=>$status,"Message"=>$lngmsg,"RecordCount"=>$recordcnt,"Result"=>$postData);
                $log_current .= "Response ".print_r($encodedArray,true)."\n";
                $log_current .= "\n End time : ".$this->udate('Y-m-d H:i:s:u')." ******* \n";
                $log_current .= "\n *******End API CALL********* \n";
                file_put_contents($filename, $log_current);
                echo json_encode($encodedArray,JSON_PRETTY_PRINT); //JSON_PRETTY_PRINT
                $log_current .= "\n *******After Json*********". $this->udate('Y-m-d H:i:s:u')." ******* \n";
                 file_put_contents($filename, $log_current);
            }
            else
            {
                $log_current .= "Response ".print_r($postData,true)."\n";
                $log_current .= "\n *******End API CALL********* \n";
                file_put_contents($filename, $log_current);
                echo json_encode(['Status'=>$status,"Message"=>$lngmsg,"RecordCount"=>$recordcnt,"Result"=>$postData], JSON_PRETTY_PRINT);
            }
        }
        else
        {
         $this->setHeader(400);
          $resultMessage = _getStatusCodeMessagePostList(502);
          \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api',$resultMessage);
          echo json_encode(['Status'=>0,"Message"=>$lngmsg,"RecordCount"=>"0"],JSON_PRETTY_PRINT);
        }
    }
     
   public function actionStickers()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('ProfileID','StickerType','DeviceType','Language');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $memberID 		= 	$data->ProfileID;
	 $stickerType 		= 	$data->StickerType;
	 $deviceType 		= 	$data->DeviceType;
         $language 		= 	$data->Language;
	 $connection 		= 	Yii::$app->db;
	 $procedure		=	"CALL Member_Sticker_List(".$memberID.",".$stickerType.",".$deviceType.",'".self::S3BucketAbsolutePath."/','".self::S3BucketStickers."','".self::S3BucketStickersSmall."','".self::S3BucketStickersMedium."')";
	 $command 		= 	$connection->createCommand($procedure);     
	 $stickersData 		= 	$command->queryAll();
	  
	 if(count($stickersData)>0)
	 {
	    $resultCode = 200;
	    $status     = "1";
	 }
	 else
	 {
	    $resultCode = 404;
	    $status     = "0";
	 }
	 $resultMessage = _getStatusCodeMessageForStickers($resultCode);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 $this->setHeader(400);
	 echo json_encode(['Status'=>$status,"Message"=>$lngmsg,"Result"=>$stickersData], JSON_PRETTY_PRINT);
      }
      else
      {
	 $this->setHeader(400);
	 $resultMessage = _getStatusCodeMessageForStickers(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
     
   public function actionApplist()
   {
      $arrParams 		=	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('ArtistID','Language');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $artistID 		= 	$data->ArtistID;
          $language 		= 	$data->Language;
	 $connection 		= 	Yii::$app->db;
	 $command 		= 	$connection->createCommand("CALL SimilarApp_List(".$artistID.",'".self::S3BucketAbsolutePath."','".self::S3BucketAppIcons."')");     
	 $appData		= 	$command->queryAll();
	  
	 if(count($appData)>0)
	 {
	    $resultCode = 200;
	    $status     = "1";
	 }
	 else
	 {
	    $resultCode = 404;
	    $status     = "0";
	 }
	 $resultMessage = _getStatusCodeMessageApplist($resultCode);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	$this->setHeader(400);
	  echo json_encode(['Status'=>1,"Message"=>$lngmsg,"Result"=>$appData], JSON_PRETTY_PRINT);
      }
      else
      {
	 $this->setHeader(400);
	 $resultMessage = _getStatusCodeMessageApplist(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
     
   public function actionAddpost()
   {
      $arrParams 		= 	Yii::$app->request->post();
        $data 			= 	json_decode($arrParams['params']);
        $availableParams 		= 	array('PostTitle','PostType','Language','Description','ArtistID','IsExclusive','IsShared','Price','Media','VideoThumbImage');
        $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
        $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
        if(count($compareField) == 0)
        {
           $postTitle 		= 	$data->PostTitle;
           $postType 		= 	$data->PostType;
           $description 		= 	$data->Description;
           $artistID 		= 	$data->ArtistID;
           $isExclusive 		= 	$data->IsExclusive;
           $isShared 		= 	$data->IsShared;
           $price 		= 	$data->Price;
           $media			=	$data->Media;
           $videothumb		=	$data->VideoThumbImage;
           $language		=	$data->Language;
           $connection       	= 	Yii::$app->db;
           $proc			=	"CALL Post_Add('".addslashes($postTitle)."','".$postType."','".addslashes($description)."','".$artistID."','".$isExclusive."','".$isShared."','".$price."')";
           $command 		= 	$connection->createCommand($proc);     
           $postData 		= 	$command->queryAll();
           if(count($postData)>0)
           {
              $postID		=	$postData[0]['PostID'];
              if($postType == "2" || $postType == "3")//Images
              {
                 //In gallery table Type 1- Image,2-Video, so here type is passed one for image
                 foreach ($media as $key=>$value)
                 {
                     if($key == 0)
                     {
                         $thumbvalue = $value;
                     }
                     else
                     {
                         $thumbvalue = "";
                     }
                      $gallComm		=	"CALL Post_Add_Media(".$artistID.",".$postID.",".$postType.",'".$value."','".$thumbvalue."','".$videothumb."','".self::BoomPath."','".self::S3BucketPostVideos."')";
                      $gallcommand      	= 	$connection->createCommand($gallComm);     
                      $mediadata        	= 	$gallcommand->queryAll();
                      if($postType == "2")
                      {
                          $content        =   file_get_contents(self::S3BucketAbsolutePath.'/'.self::BoomFolder.$artistID.self::S3BucketPostImages.$value);
                          $thumbcontent   =   file_get_contents(self::S3BucketAbsolutePath.'/'.self::BoomFolder.$artistID.self::S3BucketPostThumbImage.$value);
                          $commonthumb    =   self::Documentroot.self::Project.self::Uploads.self::Postlarge.$value;
                          $createthumb    =   self::Documentroot.self::Project.self::Uploads.self::Postthumb.$value;
                          //$blurthumb      =   self::Documentroot.self::Project.self::Uploads.self::Blurthumb.$value;
                          $createmedium   =   self::Documentroot.self::Project.self::Uploads.self::Postmedium.$value;
                          file_put_contents($commonthumb, $content);
                          file_put_contents($createthumb, $thumbcontent);

                          //$thumbimage=Yii::$app->image->load($createthumb);
                          //$thumbimage->resize("",100);    
                          //$thumbimage->save($blurthumb,10);

                          $mediumimage=Yii::$app->image->load($commonthumb);
                          $mediumimage->resize(self::Mediumwidth,self::Mediumheight);    
                          $mediumimage->save($createmedium,100);

                          //\S3::putObjectFile($blurthumb,self::S3Bucket,self::BoomFolder.$artistID.self::Postblurthumb.$value, \S3::ACL_PUBLIC_READ);
                          \S3::putObjectFile($createmedium,self::S3Bucket,self::BoomFolder.$artistID.self::S3BucketPostMediumImage.$value, \S3::ACL_PUBLIC_READ);     
                      }
                 }
                 if($postType == "3")
                 {
                     if($videothumb != "")
                     {
                          $content        =   file_get_contents(self::S3BucketAbsolutePath.'/'.self::BoomFolder.$artistID.self::Postvideosthumb.$videothumb); 
                          $createthumb    =   self::Documentroot.self::Project.self::Uploads.self::Postthumb.$videothumb;
                          $blurthumb      =   self::Documentroot.self::Project.self::Uploads.self::Blurthumb.$videothumb;

                          file_put_contents($createthumb, $content);

                          $thumbimage=Yii::$app->image->load($createthumb);
                          //$thumbimage->resize("",self::PostThumbheight);
                          $thumbimage->resize("",100);
                          $thumbimage->save($blurthumb,10);


                          \S3::putObjectFile($blurthumb,self::S3Bucket,self::BoomFolder.$artistID.self::Postblurthumbvideos.$videothumb, \S3::ACL_PUBLIC_READ);

                     }
                 }

                 if(count($mediadata) > 0)
                 {
                    $resultCode 	= 	200;
                    $status     = "1";
                 }
                 else
                 {
                    $resultCode 	= 	404;
                    $status     = "0";
                 }
              }
              else
              {
                 $resultCode 	= 	200;
                 $status     = "1";
              }
           }
           else
           {
              $resultCode 	= 	404;
              $status     = "0";
           }
           $resultMessage 	= 	_getStatusCodeMessageCommon($resultCode);
           \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
          $this->setHeader(400);
           echo json_encode(['Status'=>$status,"Message"=>$lngmsg,"Result"=>$postData], JSON_PRETTY_PRINT);
        }
        else
        {
           $this->setHeader(400);
           $resultMessage 	= 	_getStatusCodeMessageCommon(502);
           \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
           echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
        }
   }
     
   public function actionPurchasesticker()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('ProfileID','StickerID','DeviceType','Language');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $memberID 		= 	$data->ProfileID;
	 $stickerID 		= 	$data->StickerID;
	 $deviceType 		= 	$data->DeviceType;
         $language 		= 	$data->Language;
	 $connection       	= 	Yii::$app->db;
	 $procedure		=	"CALL Sticker_Purchase(".$memberID.",".$stickerID.",".$deviceType.")";
	 $command 		= 	$connection->createCommand($procedure);     
	 $postData 		= 	$command->queryAll();
	 if(count($postData)>0)
	 {
	    $resultCode = 200;
	    $status     = "1";
	 }
	 else
	 {
	    $resultCode = 404;
	    $status     = "0";
	 }
	 $resultMessage = _getStatusCodeMessageForPurchase($resultCode);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 $this->setHeader(400);
	 echo json_encode(['Status'=>$status,"Message"=>$lngmsg,'Result'=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	 $this->setHeader(400);
	 $resultMessage = _getStatusCodeMessageForPurchase(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
     
   public function actionPurchasepost()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('ArtistID','ProfileID','PostID','Language');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $artistID 		= 	$data->ArtistID;
	 $memberID 		= 	$data->ProfileID;
	 $postID 		= 	$data->PostID;
         $language 		= 	$data->Language;
	 $connection       	= 	Yii::$app->db;
	 $procedure 		=	"CALL Post_Purchase(".$artistID.",".$memberID.",".$postID.")";
	 $command 		= 	$connection->createCommand($procedure);     
	 $postData 		= 	$command->queryAll();
	 if(count($postData)>0)
	 {
	    $resultCode 	= 	200;
	    $status		=	"1";
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageForPurchase($resultCode);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 $this->setHeader(400);
	 echo json_encode(['Status'=>$status,"Message"=>$lngmsg,'Result'=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	$this->setHeader(400);
	 $resultMessage 	= 	_getStatusCodeMessageForPurchase(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
   public function actionArtisteditpost()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('ArtistID','ProfileID','PostID','PostType',
					      'PostTitle','Description','FullVideoLink','IsExclusive',
					      'IsShared','Price','MediaDeleteID','Media');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $artistID 		= 	$data->ArtistID;
	 $profileID 		= 	$data->ProfileID;
	 $postID 		= 	$data->PostID;
	 $postType 		= 	$data->PostType;
	 $postTitle 		= 	$data->PostTitle;
	 $description 		= 	$data->Description;
	 $fullVideoLink       	= 	$data->FullVideoLink;
	 $isExclusive 		= 	$data->IsExclusive;
	 $isShared 		= 	$data->IsShared;
	 $price 		= 	$data->Price;
	 $mediaDeleteID       	= 	$data->MediaDeleteID;
	 $media 		=	$data->Media;
         $language 		=	$data->Language;
	 $connection       	= 	Yii::$app->db;
	 $procedure 		=	'CALL Artist_Edit_Post('.$artistID.','.$profileID.','.$postID.',
					  "'.$postType.'","'.addslashes($postTitle).'","'.addslashes($description).'","'.$fullVideoLink.'",
					  '.$isExclusive.','.$isShared.','.$price.',@o_ErrorCode)';
	 $command 		= 	$connection->createCommand($procedure);     
         
	 $postData 		= 	$command->queryAll();
	 if(count($postData)>0)
	 {
	    $resultCode 	= 	200;
	    $status		=	"1";
	    if(count($mediaDeleteID) > 0)
	    {
	       foreach($mediaDeleteID as $deleteval)
	       {
		  $delProc 	=	"CALL Artist_EditPost_DeleteMedia(".$postID.",".$artistID.",
							       '".$postType."',".$deleteval.")";
		  $connection->createCommand($delProc)->execute(); 
	       }
	    }
	    if(count($media) > 0)
	    {
	       foreach($media as $mediaval)
	       {
		  $mediaProc 	=	"CALL Post_Add_Media(".$artistID.",".$postID.",".$postType.",'".$mediaval."','','','".self::BoomPath."','".self::S3BucketPostVideos."')";
		  $connection->createCommand($mediaProc)->queryAll(); 
	       }
	    }
	    
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageForEditpost($resultCode);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 $this->setHeader(400);
	 echo json_encode(['Status'=>$status,"Message"=>$lngmsg,'Result'=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	 $this->setHeader(400);
	 $resultMessage 	= 	_getStatusCodeMessageForEditpost(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
   public function actionArtistdeletepost()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostID','Language');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
          $language 		= 	$data->Language;
	 
	 $connection       	= 	Yii::$app->db;
	 $procedure 		=	"CALL Artist_Delete_Post(".$postID.",@o_ErrorCode)";
	 $command 		= 	$connection->createCommand($procedure);     
	 $postData 		= 	$command->queryAll();
	 if(count($postData)>0)
	 {
	    $resultCode 	= 	200;
	    $status		=	"1";
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageCommon($resultCode);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 $this->setHeader(400);
	 echo json_encode(['Status'=>$status,"Message"=>$lngmsg], JSON_PRETTY_PRINT);
      }
      else
      {
	$this->setHeader(400);
	 $resultMessage 	= 	_getStatusCodeMessageCommon(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
   public function actionLikelist()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostID','ArtistID','RefTable','RefTableID','Language');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
	 $artistID 		=	$data->ArtistID;
	 $reftable 		=	$data->RefTable;
	 $reftableID 		=	$data->RefTableID;
         $language 		=	$data->Language;
	 $connection       	= 	Yii::$app->db;
	 $procedure 		=	"CALL Post_Like_Detail_List(".$postID.",".$artistID.",".$reftable.",".$reftableID.",'".self::S3BucketPath."','".self::S3BucketProfileThumbImages."')";
	 $command 		= 	$connection->createCommand($procedure);     
	 $postData 		= 	$command->queryAll();
	 if(count($postData)>0)
	 {
	    $resultCode 	= 	200;
	    $status		=	"1";
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageLikelist($resultCode);
	  \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
	 echo json_encode(["Status"=>$status,"Message"=>$lngmsg,"Result"=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	 $this->setHeader(400);
	 $resultMessage 	= 	_getStatusCodeMessageLikelist(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
   public function actionSharelist()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostID','ArtistID','Language');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
	 $artistID 		=	$data->ArtistID;
          $language 		=	$data->Language;
	 $connection       	= 	Yii::$app->db;
	 $procedure 		=	"CALL Post_Share_Detail_List(".$postID.",".$artistID.",'".self::S3BucketPath."','".self::S3BucketProfileThumbImages."')";
	 $command 		= 	$connection->createCommand($procedure);     
	 $postData 		= 	$command->queryAll();
	 if(count($postData)>0)
	 {
	    $resultCode 	= 	200;
	    $status		=	"1";
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageSharelist($resultCode);
	 \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
	 echo json_encode(["Status"=>$status,"Message"=>$lngmsg,"Result"=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageSharelist(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
   public function actionFlaglist()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostID','ArtistID','RefTable','RefTableID','Language');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
	 $artistID 		=	$data->ArtistID;
	 $reftable 		=	$data->RefTable;
	 $reftableID 		=	$data->RefTableID;
         $language 		=	$data->Language;
	 $connection       	= 	Yii::$app->db;
	 $procedure 		=	"CALL Post_Flag_Detail_List(".$postID.",".$artistID.",".$reftable.",".$reftableID.",'".self::S3BucketPath."','".self::S3BucketProfileThumbImages."')";
	 $command 		= 	$connection->createCommand($procedure);     
	 $postData 		= 	$command->queryAll();
	 if(count($postData)>0)
	 {
	    $resultCode 	= 	200;
	    $status		=	"1";
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageFlaglist($resultCode);
	 \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         
         $this->setHeader(400);
	 echo json_encode(["Status"=>$status,"Message"=>$lngmsg,"Result"=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageFlaglist(502);
         \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
	 echo json_encode(['Status'=>0,"Message"=>$lngmsg],JSON_PRETTY_PRINT);
      }
   }
   public function actionArtisthomescreen()
   {
        $arrParams 		= 	Yii::$app->request->post();
        $data 			= 	json_decode($arrParams['params']);
        $availableParams 		= 	array('ArtistID','UserType','PageIndex','Language');
        $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
        if(count($compareField) == 0)
        {
            $artistID 		=	$data->ArtistID;
            $pageindex 		=	$data->PageIndex;
            $language 		=	$data->Language;
            $connection       	= 	Yii::$app->db;
            $procedure 		=	"CALL Artist_Home_News_Feed(".$artistID.",'".self::S3BucketAbsolutePath."','".self::S3BucketPath."','".self::S3BucketProfileThumbImages."','".self::S3BucketStickers."',".self::Limit.",".$pageindex.",@o_RecCount)";
            $command 		= 	$connection->createCommand($procedure);     
            $postData 		= 	$command->queryAll();
            $reccommand 		= 	$connection->createCommand('SELECT @o_RecCount')->queryOne();
            $recordcnt 		= 	$reccommand['@o_RecCount'];
            foreach($postData as $key => $value)
            {
                if(isset($value['PostID']) && $value['PostID']!= '')
                {
                    $imageData		=	array();
                    $postID 			= 	$value['PostID'];
                    $postimageproc		=	"CALL Post_Image_List(1,".$postID.",".$artistID.",'".self::S3BucketPath."','".self::S3BucketPostImages."','".self::S3BucketPostThumbImage."','".self::S3BucketPostMediumImage."')";
                    $commandForImage 	= 	$connection->createCommand($postimageproc);     
                    $imageData 		= 	$commandForImage->queryAll();

                    $latestcmntsproc 	=	"CALL Latest_Post_CommentList(".$postID.",".$artistID.",".$artistID.",2,'".self::S3BucketAbsolutePath."','".self::S3BucketPath."','".self::S3BucketProfileThumbImages."','".self::S3BucketStickers."','".self::S3BucketStickersSmall."','".self::S3BucketStickersMedium."')";
                    $commandForCmnts 	= 	$connection->createCommand($latestcmntsproc);
                    $cmntsData 		= 	$commandForCmnts->queryAll();
                    if(count($cmntsData)>0)
                    {
                       $postData[$key]['LatestComments'] = $cmntsData;
                    }	
                    else
                    {
                       $postData[$key]['LatestComments'] = array();
                    }
                    if(count($imageData)>0)
                    {
                       $postData[$key]['PostImage'] = $imageData;
                    }	
                    else
                    {
                       $postData[$key]['PostImage'] = $imageData;
                    }
                }   
            }
	 
            if(count($postData)>0)
            {
               $resultCode 	= 	200;
               $status		=	"1";
            }
            else
            {
               $resultCode 	= 	404;
               $status		=	"0";
            }

            $resultMessage 	= 	_getStatusCodeMessageArtistHomescreen($resultCode);
            \Yii::$app->language = $language;
            $lngmsg = \Yii::t('api',$resultMessage);
            $this->setHeader(400);
            echo json_encode(["Status"=>$status,"Message"=>$lngmsg,"RecordCount"=>$recordcnt,"Result"=>$postData], JSON_PRETTY_PRINT);
        }
        else
        {
            //$this->setHeader(502);
            $resultMessage 	= 	_getStatusCodeMessageArtistHomescreen(502);
            \Yii::$app->language = $language;
         $lngmsg = \Yii::t('api',$resultMessage);
         $this->setHeader(400);
            echo json_encode(['Status'=>0,"Message"=>$lngmsg,"RecordCount"=>"0"],JSON_PRETTY_PRINT);
        }
   }
   public function actionMymusic()
   {
        $arrParams 		= 	Yii::$app->request->post();
        $data 			= 	json_decode($arrParams['params']);
        $availableParams 	= 	array('ArtistID','PageIndex','Language');
        $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
        if(count($compareField) == 0)
        {
            $artistID 		=	$data->ArtistID;
            $pageindex 		=	$data->PageIndex;
            $language 		=	$data->Language;
            $connection       	= 	Yii::$app->db;
            $procedure 		=	"CALL Mymusic_List(".$artistID.",".self::Limit.",".$pageindex.",@o_RecCount)";
            $command 		= 	$connection->createCommand($procedure);     
            $trackData 		= 	$command->queryAll();
            $reccommand 	= 	$connection->createCommand('SELECT @o_RecCount')->queryOne();
            $recordcnt 		= 	$reccommand['@o_RecCount'];
	 
            if(count($trackData)>0)
            {
               $resultCode 	= 	200;
               $status		=	"1";
            }
            else
            {
               $resultCode 	= 	404;
               $status		=	"0";
            }
            $resultMessage 	= 	_getStatusCodeMessageMymusic($resultCode);
            \Yii::$app->language = $language;
            $lngmsg = \Yii::t('api',$resultMessage);
            $this->setHeader(400);
            echo json_encode(["Status"=>$status,"Message"=>$lngmsg,"RecordCount"=>$recordcnt,"Result"=>$trackData], JSON_PRETTY_PRINT);
        }
        else
        {
            $resultMessage 	= 	_getStatusCodeMessageMymusic(502);
            \Yii::$app->language = $language;
            $lngmsg = \Yii::t('api',$resultMessage);
            $this->setHeader(400);
            echo json_encode(['Status'=>0,"Message"=>$lngmsg,"Result"=>array(),"RecordCount"=>"0"],JSON_PRETTY_PRINT);
        }
   }
   public function actionGlobaliospush()
   {
        $push_obj           =   new \backend\models\Pushqueue();
        $sendpush           =   new \api\models\Globalpush();
        $iostokens          =   $push_obj->getIosDevices();
        $batchID            =   array();
        $batchMessage       =   array();
        $batchMemberid      =   array();
                
        if(count($iostokens) > 0)
        {
            foreach($iostokens as $key=>$val)
            {
                if(!in_array($val['BatchID'], $batchID)) 
                {
                    $batchID[]          =   $val['BatchID'];
                    $batchMessage[]     =   htmlspecialchars_decode($val['Message'],ENT_QUOTES);
                    $batchMemberid[]    =   $val['MemberID'];
                }
            }

            $uniquebatch = array();
            foreach($batchID as $value)
            {
                $uniquebatch[] = $push_obj->searchForId($value,$iostokens); 
            }
            $batches = array();
            foreach($batchID as $value)
            {
                $batches[] = $value; 
            }
            $currenttime		=	date("d M 'y H:i A");

            for($j=0;$j<count($iostokens);$j++)
            {
                $memberiddb     =   $iostokens[$j]['MemberID'];
                $batchiddb      =   $iostokens[$j]['BatchID'];
                $dbdevicetoken  =   $iostokens[$j]['DeviceToken'];
                $push_obj->addPushHistory($memberiddb,1,$dbdevicetoken,$batchiddb);
            }
            for($i=0;$i<count($uniquebatch);$i++) 
            {
                $getartistID    = \backend\models\Pushhistorystats::findAll(array("BatchID"=>$batches[$i]));
                $sendpush->sendToIphone($uniquebatch[$i], $batchMessage[$i], $currenttime,$getartistID[0]['ArtistID']);
            }

            echo json_encode(["Status"=>"1","Message"=>"Success"], JSON_PRETTY_PRINT);
        }
        else
        {
            echo json_encode(["Status"=>"0","Message"=>"No notification"],JSON_PRETTY_PRINT);
        }
   }
   public function actionGlobalandroidpush()
   {
        $push_obj           =   new \backend\models\Pushqueue();
        $sendpush           =   new \api\models\Globalpush();
        $androidtokens      =   $push_obj->getAndroidDevices();
        $batchID            =   array();
        $batchMessage       =   array();
        $batchMemberid      =   array();
                
        if(count($androidtokens) > 0)
        {
            foreach($androidtokens as $key=>$val)
            {
                if(!in_array($val['BatchID'], $batchID)) 
                {
                    $batchID[]          =   $val['BatchID'];
                    $batchMessage[]     =   htmlspecialchars_decode($val['Message'],ENT_QUOTES);
                    $batchMemberid[]    =   $val['MemberID'];
                }
            }

            $uniquebatch = array();
            foreach($batchID as $value)
            {
                $uniquebatch[] = $push_obj->searchForId($value,$androidtokens); 
            }
            $batches = array();
            foreach($batchID as $value)
            {
                $batches[] = $value; 
            }
            $currenttime		=	date("d M 'y H:i A");

            for($j=0;$j<count($androidtokens);$j++)
            {
                $memberiddb     =   $androidtokens[$j]['MemberID'];
                $batchiddb      =   $androidtokens[$j]['BatchID'];
                $dbdevicetoken  =   $androidtokens[$j]['DeviceToken'];
                $push_obj->addPushHistory($memberiddb,2,$dbdevicetoken,$batchiddb);
            }
            for($i=0;$i<count($uniquebatch);$i++) 
            {
                $getartistID    = \backend\models\Pushhistorystats::findAll(array("BatchID"=>$batches[$i]));
                $sendpush->sendToAndroid($uniquebatch[$i], $batchMessage[$i], $currenttime,$getartistID[0]['ArtistID']);
            }

            echo json_encode(["Status"=>"1","Message"=>"Success"], JSON_PRETTY_PRINT);
        }
        else
        {
            echo json_encode(["Status"=>"0","Message"=>"No notification"],JSON_PRETTY_PRINT);
        }
   }
   public function loadModel($id)
   {
      $model = User::findOne($id);
      if(is_null($model))
      {
	 //$this->setHeader(400);
	 echo json_encode(['status'=>0, 'error_code'=>400, 'message'=>'Bed Request'], JSON_PRETTY_PRINT);
	 exit;
      }
      return $model;
   }
 }
?>