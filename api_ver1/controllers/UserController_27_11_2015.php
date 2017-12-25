<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use api\models\User;
use yii\filters\VerbFilter;
use yii\filters\auth\HttpBasicAuth;
use yii\db\Expression;

	
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
   const S3BucketProfileImages 	= 	PROFILE_IMAGES;
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
   const PostThumbwidth 		= 	POST_THUMB_WIDTH_SIZE;
   const PostThumbheight 		=	POST_THUMB_HEIGHT_SIZE;
   const Mediumwidth 		= 	MEDIUM_WIDTH_SIZE;
   const Mediumheight 		=	MEDIUM_HEIGHT_SIZE;
   const Documentroot           =       DOCUMENT_ROOT;
   const Project                =       PROJECT;
   const Uploads                =       UPLOADS;
   const Postlarge              =       POSTLARGE;
   const Postthumb              =       POSTTHUMB;
   const Postsmall              =       POSTSMALL;
   const Postmedium             =       POSTMEDIUM;
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
		      'sharelist','share','flaglist','artisteditprofile','artisthomescreen'],								
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
      header('Content-type: ' . $contentType);
   }
	
   public function actionLogin()
   {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('Username','Password','DeviceType','DeviceToken','UserType','ArtistID','LoginType','MemberName','Email');
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
	 //$this->setHeader($resultCode);
	 if($resultCode == "200")
	 {
	    echo json_encode(['Status'=>1,"Message"=>$resultMessage,'Result'=>$loginData], JSON_PRETTY_PRINT);
	 }
	 else
	 { 
	    echo json_encode(['Status'=>0,"Message"=>$resultMessage,'Result'=>$loginData], JSON_PRETTY_PRINT);
	 }   
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage = 	_getStatusCodeMessageForUser(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
  }
  
  public function actionCheckusername()
  {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('UserName','ArtistID','UserType');
      $compareField 	= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $userName 	= 	$data->UserName;
	 $artistID 	= 	$data->ArtistID;
	 $userType 	= 	$data->UserType;
	 $connection 	= 	Yii::$app->db;
	 $command 	= 	$connection->createCommand("CALL Check_Username('".$userName."',".$artistID.",".$userType.",'".self::EncryptKey."')");     
	 $userData 	= 	$command->queryAll();
	 $resultCode 	= 	$userData[0]['ErrorCode'];
	 $resultMessage = 	_getStatusCodeMessageForUsername($resultCode);
	 //$this->setHeader($resultCode);
	 if($resultCode == 200)
	 {
	    echo json_encode(['Status'=>1,"Message"=>$resultMessage], JSON_PRETTY_PRINT);
	 }
	 else
	 {
	    echo json_encode(['Status'=>4,"Message"=>$resultMessage], JSON_PRETTY_PRINT);
	 }    
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage = 	_getStatusCodeMessageForUsername(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
  }
        
   public function actionGetprofile()
   {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('UserID','UserType','ProfileID');
      $compareField 	= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $userID 	= 	$data->UserID;
	 $profileID 	= 	$data->ProfileID;
	 $userType	= 	$data->UserType;
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
	 //$this->setHeader($resultCode);
	 echo json_encode(['Status'=>$status,"Message"=>$resultMessage,'Result'=>$profileData], JSON_PRETTY_PRINT);
	  
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 		= 	_getStatusCodeMessageGetProfile(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
        
   public function actionForgotpassword()
   {
        $arrParams          = 	Yii::$app->request->post();
        $data               = 	json_decode($arrParams['params']);
        $availableParams    = 	array('Username','UserType','ArtistID');
        $compareField       = 	array_diff_key(array_keys($arrParams),$availableParams);

        if(count($compareField) == 0)
        {
           $username 	= 	$data->Username;
           $artistID 	= 	$data->ArtistID;
           $userType 	= 	$data->UserType;
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
                    $status    	=   "1";

                }
                else
                {
                    $status         = "0";
                    /*$mailmodel      =    new Sendmail();
                    echo $sendmail  =    $mailmodel->sendSesEmail("rosemary@e2logy.com","Test","","");die;*/
                }
           }
           echo json_encode(['Status'=>$status,"Message"=>$resultMessage,'Result'=>$profileData], JSON_PRETTY_PRINT);
        }
        else
        {
            $resultMessage 	= 	_getStatusCodeMessageForUser(502);
            echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
        }
   }
   
   public function actionLikepost()
   {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('PostID','ArtistID','ActivityTypeID','ProfileID','RefTable','RefTableID','Comment','ActivityID','UserType');
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
	 echo json_encode(["Status"=>$status,"Message"=>$resultMessage,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageCommon(502);
	 echo json_encode(["Status"=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
   
   public function actionFlag()
   {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('PostID','ArtistID','ActivityTypeID','ProfileID','RefTable','RefTableID','Comment','ActivityID','UserType');
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
	 //$this->setHeader($resultCode);
	 echo json_encode(["Status"=>$status,"Message"=>$resultMessage,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageCommon(502);
	 echo json_encode(["Status"=>0,"Message"=>$resultMessage,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
   }
   public function actionShare()
   {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('PostID','ArtistID','ActivityTypeID','ProfileID','UserType');
      $compareField 	= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $postID 	      	= 	$data->PostID;
	 $artistID 		= 	$data->ArtistID;
	 $activityTypeID 	= 	$data->ActivityTypeID;
	 $profileID 		= 	$data->ProfileID;
	 $userType 		= 	$data->UserType;
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
	 //$this->setHeader($resultCode);
	 echo json_encode(["Status"=>1,"Message"=>$resultMessage,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageCommon(502);
	 echo json_encode(["Status"=>0,"Message"=>$resultMessage,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
   }
   public function actionLikecomment()
   {
      $arrParams 	= 	Yii::$app->request->post();
      $data 		= 	json_decode($arrParams['params']);
      $availableParams 	= 	array('PostID','ArtistID','ActivityTypeID','ProfileID','RefTable','RefTableID','Comment','ActivityID','UserType');
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
	 //$this->setHeader($resultCode);
	 echo json_encode(["Status"=>$status,"Message"=>$resultMessage,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage = _getStatusCodeMessageCommon(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage,"Result"=>$activityData],JSON_PRETTY_PRINT);
      }
   }
   
   public function actionAddcomment()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostID','ArtistID','ActivityTypeID','ProfileID','RefTable','RefTableID','Comment','ActivityID','UserType','StickerID');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
	 $artistID      	= 	$data->ArtistID;
	 $activityTypeID 	= 	$data->ActivityTypeID;
	 $userID 		= 	$data->ProfileID;
	 $refTable 		= 	$data->RefTable;
	 $refTableID 		= 	$data->RefTableID;
	 $comment 		= 	$data->Comment;
	 $activityID 		= 	$data->ActivityID;
	 $userType 		= 	$data->UserType;
	 $stickerID 		= 	$data->StickerID;
	 $connection 		= 	Yii::$app->db;
	 $procedure		=	"CALL Member_Add_Activity('".$postID."','".$artistID."','".$userID."','".$activityTypeID."','".$refTable."','".$refTableID."','".$comment."',".$activityID.",".$userType.",".$stickerID.",'".self::S3BucketPath."')";
	 $command 		= 	$connection->createCommand($procedure);     
	 $activityData 		= 	$command->queryAll();
	 if(count($activityData)>0)
	 {
	    $resultCode 	=      200;
	    $status		=	"1";
	 }
	 else
	 {
	    $resultCode 	=      404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageCommon($resultCode);
	 //$this->setHeader($resultCode);
	 echo json_encode(['Status'=>$status,"Message"=>$resultMessage,"Result"=>$activityData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageCommon(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage,"Result"=>$activityData],JSON_PRETTY_PRINT);
      }
   }
   
   public function actionSignup()
   {
       $arrParams 		= 	Yii::$app->request->post();
       $data 			= 	json_decode($arrParams['params']);
       $availableParams 	= 	array('ArtistID','Name','Username','Email','Birthdate','Gender','Zipcode','Mobile','Password','Image');
       $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
       if(count($compareField) == 0)
       {
	 $artistID 		= 	$data->ArtistID;
	 $name 			= 	$data->Name;
	 $username 		= 	$data->Username;
	 $email 		= 	$data->Email; 
	 $birthDate 		= 	date("d-m-Y",strtotime($data->Birthdate)); 
	 $gender 		= 	$data->Gender;
	 $zipCode 		= 	$data->Zipcode;
	 $mobile 		= 	$data->Mobile;
	 $password 		= 	$data->Password;
	 $image 		= 	$data->Image;
	 $connection 		= 	Yii::$app->db;
	 $command 		= 	$connection->createCommand("CALL Member_Registration('".$name."','".$image."','".$email."','".$birthDate."','".$zipCode."','".$mobile."','".$gender."','".$artistID."','".$username."','".self::EncryptKey."','".$password."')");     
	 $registrationData 	= 	$command->queryAll();
	 $resultCode 		= 	$registrationData[0]['ErrorCode'];
	 $resultMessage 	= 	$registrationData[0]['Message'];
	 //$this->setHeader($resultCode);
	 if($resultCode == 200)
	 {
	    echo json_encode(['Status'=>1,"Message"=>$resultMessage,'Result'=>$registrationData], JSON_PRETTY_PRINT);
	 }
	 else
	 {
	    echo json_encode(['Status'=>0,"Message"=>$resultMessage,'Result'=>$registrationData], JSON_PRETTY_PRINT);
	 }    
       }
       else
       {
	 //$this->setHeader(502);
	 $resultMessage = _getStatusCodeMessageForUserRegistration(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
       }
   }
        
   public function actionEditprofile()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('UserID','ProfileID','ArtistID','Name','Email','Password','Birthdate','Gender','Zipcode','Mobile','Password','Image');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $userID 		= 	$data->UserID;
	 $profileID 		= 	$data->ProfileID;
	 $artistID 		= 	$data->ArtistID;
	 $name 			= 	$data->Name;
	 $email 		= 	$data->Email; 
	 $birthDate 		= 	date("d-m-Y",strtotime($data->Birthdate)); 
	 $gender 		= 	$data->Gender;
	 $zipCode       	= 	$data->Zipcode;
	 $mobile 		= 	$data->Mobile;
	 $password 		= 	$data->Password;
	 $image 		= 	$data->Image;
	 $connection 		= 	Yii::$app->db;
	 $procedure     	=	"CALL Member_Edit_Profile('".$name."','".$image."','".$email."','".$password."','".$birthDate."','".$zipCode."','".$mobile."','".$gender."','".$artistID."','".$profileID."','".$userID."','".self::EncryptKey."')";
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
	 $resultMessage 	= 	_getStatusCodeMessageForUserRegistration($resultCode);
	 //$this->setHeader($resultCode);
	 if($resultCode == 200)
	 {
	    echo json_encode(['Status'=>1,"Message"=>$resultMessage,'Result'=>$registrationData], JSON_PRETTY_PRINT);
	 }
	 else
	 {
	    echo json_encode(['Status'=>0,"Message"=>$resultMessage,'Result'=>$registrationData], JSON_PRETTY_PRINT);
	 }    
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	=	 _getStatusCodeMessageForUserRegistration(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
   
   public function actionArtisteditprofile()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('ArtistID','UserID','ArtistName','ProfileThumb','Email','Birthdate','Nationality',
					      'Residence','Website','YouTubeChannelName','YearsActive','Genre','AboutMe','YouTubePageUrl',
					      'LinkedInPageUrl','TwitterPageUrl','FacebookPageUrl','DeleteMediaIDs','ArtistImages','Password');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
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
	 $linkedInPageUrl	=	$data->LinkedInPageUrl;
	 $twitterPageUrl	=	$data->TwitterPageUrl;
	 $facebookPageUrl	=	$data->FacebookPageUrl;
	 $deleteMediaIDs      	=	$data->DeleteMediaIDs;
	 $artistImages 		=	$data->ArtistImages;
	 $password		=	$data->Password;
	 $connection 		= 	Yii::$app->db;
	 $procedure     	=	"CALL Artist_Edit_Profile(".$artistID.",".$userID.",'".$artistName."','".$profileThumb."',
				       '".$email."','".$birthDate."','".$nationality."','".$residence."','".$website."','".$youTubeChannelName."',
				       '".$yearsActive."','".$genre."','".$aboutMe."','".$youTubePageUrl."','".$linkedInPageUrl."',
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
	 $resultMessage 	= 	_getStatusCodeMessageForUserRegistration($resultCode);
	 //$this->setHeader($resultCode);
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
                    $content = file_get_contents(self::S3BucketAbsolutePath.'/'.self::BoomFolder.$artistID.self::S3BucketArtistImages.$keyval);
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
                    \S3::putObjectFile($createmedium,self::S3Bucket,self::BoomFolder.$artistID.self::S3BucketArtistMedium.$keyval, \S3::ACL_PUBLIC_READ);
                    $addProc    	=	"CALL Artist_Edit_Images(".$artistID.",'".$keyval."')";
                    $addCmnd    	= 	$connection->createCommand($addProc)->execute();
	       }
	    }
	    echo json_encode(['Status'=>1,"Message"=>$resultMessage,'Result'=>$editData], JSON_PRETTY_PRINT);
	 }
	 else
	 {
	    echo json_encode(['Status'=>0,"Message"=>$resultMessage,'Result'=>$editData], JSON_PRETTY_PRINT);
	 }    
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	=	 _getStatusCodeMessageForUserRegistration(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
   public function actionCommentlist()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostID','ArtistID','ProfileID','UserType','PageIndex');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
	 $artistID 		= 	$data->ArtistID;
	 $profileID		=	$data->ProfileID;
	 $userType		=	$data->UserType;
	 $pageindex		=	$data->PageIndex;
	 $connection 		= 	Yii::$app->db;
	 $procedure 		=	"CALL Post_CommentList(".$postID.",".$artistID.",".$profileID.",".$userType.",'".self::S3BucketAbsolutePath."','".self::S3BucketPath."','".self::S3BucketProfileImages."','".self::S3BucketStickers."','".self::S3BucketStickersSmall."','".self::S3BucketStickersMedium."',".self::Limit.",".$pageindex.",@o_RecCount)";
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
	 //$this->setHeader($resultCode);
	 echo json_encode(['Status'=>$status,"Message"=>$resultMessage,"RecordCount"=>$recordcnt,"Result"=>$commentData], JSON_PRETTY_PRINT);
      }
      else
      {
	// $this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageCommentList(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
   
   public function actionPostlist()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('UserID','ArtistID','ProfileID','IsExclusive','PageIndex');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
        $postData        	=       array();  
        $UserID 		= 	$data->UserID;
        $profileID 		= 	$data->ProfileID;
        $artistID 		= 	$data->ArtistID;
        $isExclusive 		= 	$data->IsExclusive;
        $pageindex 		= 	$data->PageIndex;
        $connection 		= 	Yii::$app->db;
        $procedure		=	"CALL Post_List(".$artistID.",".$UserID.",".$profileID.",".$isExclusive.",".self::Limit.",".$pageindex.",@o_RecCount)";
        $command                =	$connection->createCommand($procedure);     
        $postData 		= 	$command->queryAll();
        $usermodel              =       new User;
        $xmlarray1              =       $usermodel->xmlparsing('http://boom.boomvideo.tv/alpha/test/vast/rss_feed.php?PGUID=37ff8226-25ad-4b55-814a-75316b9cdefc');
        $xmlarray2              =       $usermodel->xmlparsing('http://boom.boomvideo.tv/alpha/test/vast/rss_feed.php?PGUID=37ff8226-25ad-4b55-814a-75316b9cdefc');
        $xmlarray3              =       $usermodel->xmlparsing('http://boom.boomvideo.tv/alpha/test/vast/rss_feed.php?PGUID=37ff8226-25ad-4b55-814a-75316b9cdefc');
        $xmltitlearray          =       array($xmlarray1[0]['title'],$xmlarray2[0]['title'],$xmlarray3[0]['title']);
        $xmldescarray           =       array($xmlarray1[0]['description'],$xmlarray2[0]['description'],$xmlarray3[0]['description']);
        $xmlmediatitltarray     =       array($xmlarray1[0]['media:title'],$xmlarray2[0]['media:title'],$xmlarray3[0]['media:title']);
        $xmlmediathumb          =       array($xmlarray1[0]['media:thumbnail']['url'],$xmlarray2[0]['media:thumbnail']['url'],$xmlarray3[0]['media:thumbnail']['url']);
        $xmlcontenturl          =       array($xmlarray1[0]['media:content']['url'],$xmlarray2[0]['media:content']['url'],$xmlarray3[0]['media:content']['url']);
        $reccommand             = 	$connection->createCommand('SELECT @o_RecCount')->queryOne();
        $recordcnt 		= 	$reccommand['@o_RecCount'];
        foreach($postData as $key => $value)
           {
                if(isset($value['PostID']) && $value['PostID']!= '')
                {
                    $imageData		=	array();
                    $postID 		= 	$value['PostID'];
                    $postimageproc	=	"CALL Post_Image_List(1,".$postID.",".$artistID.",'".self::S3BucketPath."','".self::S3BucketPostImages."')";
                    $commandForImage 	= 	$connection->createCommand($postimageproc);     
                    $imageData 		= 	$commandForImage->queryAll();

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
	    $resultCode = 200;
	    $status	   =	"1";
	 }
	 else
	 {
	    $resultCode = 404;
	    $status		=	"0";
	 }
        $resultMessage = _getStatusCodeMessagePostList($resultCode);
        $rssaray = array();
        $i=0;
        $j=0;
        foreach($postData as $key =>$val)
        {
             if(($key % 3==0 && $key != 0 && $key <= 3 )|| $j == 4 )
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
                 $j=0;
             }
             else
             {
                  $rssaray[] =   $val;
             }
            $j++;
        }
	 //$this->setHeader($resultCode);
	 echo json_encode(['Status'=>$status,"Message"=>$resultMessage,"RecordCount"=>$recordcnt,"Result"=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	//$this->setHeader(502);
	$resultMessage = _getStatusCodeMessagePostList(502);
	echo json_encode(['Status'=>0,"Message"=>$resultMessage,"RecordCount"=>"0"],JSON_PRETTY_PRINT);
      }
   }
     
   public function actionStickers()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('ProfileID','StickerType','DeviceType');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $memberID 		= 	$data->ProfileID;
	 $stickerType 		= 	$data->StickerType;
	 $deviceType 		= 	$data->DeviceType;
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
	 //$this->setHeader($resultCode);
	 echo json_encode(['Status'=>$status,"Message"=>$resultMessage,"Result"=>$stickersData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage = _getStatusCodeMessageForStickers(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
     
   public function actionApplist()
   {
      $arrParams 		=	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('ArtistID');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $artistID 		= 	$data->ArtistID;
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
	 //$this->setHeader($resultCode);
	  echo json_encode(['Status'=>1,"Message"=>$resultMessage,"Result"=>$appData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage = _getStatusCodeMessageApplist(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
     
   public function actionAddpost()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostTitle','PostType','Description','ArtistID','IsExclusive','IsShared','Price','Media');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
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
	       foreach ($media as $value)
	       {
                    $gallComm		=	"CALL Post_Add_Media(".$artistID.",".$postID.",".$postType.",'".$value."','".self::BoomPath."','".self::S3BucketPostVideos."')";
                    $gallcommand      	= 	$connection->createCommand($gallComm);     
                    $mediadata        	= 	$gallcommand->queryAll();
                    if($postType == "2")
                    {
                        $content = file_get_contents(self::S3BucketAbsolutePath.'/'.self::BoomFolder.$artistID.self::S3BucketPostImages.$value);
                        $commonthumb = self::Documentroot.self::Project.self::Uploads.self::Postlarge.$value;
                        $createthumb = self::Documentroot.self::Project.self::Uploads.self::Postthumb.$value;
                        $createmedium = self::Documentroot.self::Project.self::Uploads.self::Postmedium.$value;
                        file_put_contents($commonthumb, $content);

                        $thumbimage=Yii::$app->image->load($commonthumb);
                        $thumbimage->resize(self::PostThumbwidth,self::PostThumbheight);      
                        $thumbimage->save($createmedium,100);

                        $mediumimage=Yii::$app->image->load($commonthumb);
                        $mediumimage->resize(self::Mediumwidth,self::Mediumheight);     
                        $mediumimage->save($createthumb,100);
                        
                        \S3::putObjectFile($createthumb,self::S3Bucket,self::BoomFolder.$artistID.self::S3BucketPostThumbImage.$value, \S3::ACL_PUBLIC_READ);
                        \S3::putObjectFile($createmedium,self::S3Bucket,self::BoomFolder.$artistID.self::S3BucketPostMediumImage.$value, \S3::ACL_PUBLIC_READ);   
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
	 //$this->setHeader($resultCode);
	 echo json_encode(['Status'=>$status,"Message"=>$resultMessage], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageCommon(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
     
   public function actionPurchasesticker()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('ProfileID','StickerID','DeviceType');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      if(count($compareField) == 0)
      {
	 $memberID 		= 	$data->ProfileID;
	 $stickerID 		= 	$data->StickerID;
	 $deviceType 		= 	$data->DeviceType;
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
	 //$this->setHeader($resultCode);
	 echo json_encode(['Status'=>$status,"Message"=>$resultMessage,'Result'=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage = _getStatusCodeMessageForPurchase(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
     
   public function actionPurchasepost()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('ArtistID','ProfileID','PostID');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $artistID 		= 	$data->ArtistID;
	 $memberID 		= 	$data->ProfileID;
	 $postID 		= 	$data->PostID;
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
	 //$this->setHeader($resultCode);
	 echo json_encode(['Status'=>$status,"Message"=>$resultMessage,'Result'=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageForPurchase(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
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
		  $mediaProc 	=	"CALL Post_Add_Media(".$artistID.",".$postID.",".$postType.",'".$mediaval."','".self::BoomPath."','".self::S3BucketPostVideos."')";
		  $connection->createCommand($mediaProc)->queryAll(); 
	       }
	    }
	    
	 }
	 else
	 {
	    $resultCode 	= 	404;
	    $status		=	"0";
	 }
	 $resultMessage 	= 	_getStatusCodeMessageCommon($resultCode);
	 //$this->setHeader($resultCode);
	 echo json_encode(['Status'=>$status,"Message"=>$resultMessage,'Result'=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageCommon(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
   public function actionArtistdeletepost()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostID');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
	 
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
	 //$this->setHeader($resultCode);
	 echo json_encode(['Status'=>$status,"Message"=>$resultMessage], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageCommon(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
   public function actionLikelist()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostID','ArtistID','RefTable','RefTableID');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
	 $artistID 		=	$data->ArtistID;
	 $reftable 		=	$data->RefTable;
	 $reftableID 		=	$data->RefTableID;
	 $connection       	= 	Yii::$app->db;
	 $procedure 		=	"CALL Post_Like_Detail_List(".$postID.",".$artistID.",".$reftable.",".$reftableID.",'".self::S3BucketPath."','".self::S3BucketProfileImages."')";
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
	 //$this->setHeader($resultCode);
	 echo json_encode(["Status"=>$status,"Message"=>$resultMessage,"Result"=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageLikelist(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
   public function actionSharelist()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostID','ArtistID');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
	 $artistID 		=	$data->ArtistID;
	 $connection       	= 	Yii::$app->db;
	 $procedure 		=	"CALL Post_Share_Detail_List(".$postID.",".$artistID.",'".self::S3BucketPath."','".self::S3BucketProfileImages."')";
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
	 //$this->setHeader($resultCode);
	 echo json_encode(["Status"=>$status,"Message"=>$resultMessage,"Result"=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageSharelist(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
   public function actionFlaglist()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('PostID','ArtistID','RefTable','RefTableID');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $postID 		= 	$data->PostID;
	 $artistID 		=	$data->ArtistID;
	 $reftable 		=	$data->RefTable;
	 $reftableID 		=	$data->RefTableID;
	 $connection       	= 	Yii::$app->db;
	 $procedure 		=	"CALL Post_Flag_Detail_List(".$postID.",".$artistID.",".$reftable.",".$reftableID.",'".self::S3BucketPath."','".self::S3BucketProfileImages."')";
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
	 //$this->setHeader($resultCode);
	 echo json_encode(["Status"=>$status,"Message"=>$resultMessage,"Result"=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageFlaglist(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage],JSON_PRETTY_PRINT);
      }
   }
   public function actionArtisthomescreen()
   {
      $arrParams 		= 	Yii::$app->request->post();
      $data 			= 	json_decode($arrParams['params']);
      $availableParams 		= 	array('ArtistID','UserType','PageIndex');
      $compareField 		= 	array_diff_key(array_keys($arrParams),$availableParams);
      
      if(count($compareField) == 0)
      {
	 $artistID 		=	$data->ArtistID;
	 $pageindex 		=	$data->PageIndex;
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
	 //$this->setHeader($resultCode);
	 echo json_encode(["Status"=>$status,"Message"=>$resultMessage,"RecordCount"=>$recordcnt,"Result"=>$postData], JSON_PRETTY_PRINT);
      }
      else
      {
	 //$this->setHeader(502);
	 $resultMessage 	= 	_getStatusCodeMessageArtistHomescreen(502);
	 echo json_encode(['Status'=>0,"Message"=>$resultMessage,"RecordCount"=>"0"],JSON_PRETTY_PRINT);
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