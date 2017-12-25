<?php

namespace backend\controllers;

require('Imagemagician.php');
use Yii;
use backend\models\Artist;
use backend\models\ArtistSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\db\Query;

/**
 * ArtistController implements the CRUD actions for Artist model.
 */
class ArtistController extends Controller
{
    public function behaviors()
    {
        $session = Yii::$app->session;
        if(Yii::$app->user->identity->UserType == 1 || Yii::$app->user->identity->UserType == 4) {
            return [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index', 'create', 'update','removeimage','delete','export','removeprofileimage'],
                            'roles' => ['@'],
                        ],
                    ],
                ],
            ];
        } else if(Yii::$app->user->identity->UserType == 2) {
            return [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index','update','removeimage','removeprofileimage'],
                            'roles' => ['@'],
                        ],
                    ],
                ],
            ];
        } else {
            return \Yii::$app->getResponse()->redirect(array('/site/logout',302));
        }
    }
    
    public function beforeAction($action)
    {
        if (\Yii::$app->getUser()->isGuest &&
            \Yii::$app->getRequest()->url !== Url::to(\Yii::$app->getUser()->loginUrl)
        ) {
            \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        return parent::beforeAction($action);
    }

    /**
     * Lists all Artist models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ArtistSearch();
		if(Yii::$app->user->identity->UserType == 4) {
			$searchModel->CompanyID = Yii::$app->session->get('CompanyID');
		}
        if(isset($_GET['ArtistSearch'])) {
            $searchModel->attributes = $_GET['ArtistSearch'];
        }
        return $this->render('index', [            
            'model' => $searchModel, 
        ]);
    }
    
    function convertToCSV($input_array, $output_file_name, $delimiter)
    {
        $f = fopen('php://memory', 'w');
        foreach ($input_array as $line) {
            fputcsv($f, $line, $delimiter);
        }
        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
        fpassthru($f);
    }
    
    public function actionExport() {
        $connection = \Yii::$app->db;
        $artistData = $connection->createCommand("CALL Admin_Artist_GetList('','','','','".AESKEY."','','','')")->queryAll();
        $arrayToCSV[0] = array('Name','Email','Nationality','Date Joined','# Registered Users','Status');
        if(count($artistData)>0) {
            $n=1;
            foreach($artistData as $key => $value) {
                $arrayToCSV[$n][0] = $value['ArtistName'];
                $arrayToCSV[$n][1] = $value['Email'];
                $arrayToCSV[$n][2] = $value['Nationality'];
                $arrayToCSV[$n][3] = $value['JoinedDate'];
                $arrayToCSV[$n][4] = $value['TotalRegisteredUsers'];
                $arrayToCSV[$n][5] = $value['Status'];
                $n++;
            } 
        }
        $this->convertToCSV($arrayToCSV, 'report.csv', ',');
    }

    /**
     * Displays a single Artist model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Artist model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model              =   new Artist();
        $model->scenario    =   'create';
        $model->ProductType  = "2";
        $modelUser          =   new \backend\models\User();
        if($model->load(Yii::$app->request->post())) 
        {
            if($model->validate()) 
            {
                $userData = \backend\models\User::findAll(array("UserName"=>new  \yii\db\Expression("AES_ENCRYPT('".$_POST["Artist"]["UserName"]."',Password('".AESKEY."'))")));
                if(count($userData)>0) 
                {
                    $model->addError('UserName', 'This username already exists! Please try with a different username.');
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                $modelUser->UserName        =   new  \yii\db\Expression("AES_ENCRYPT('".$_POST["Artist"]["UserName"]."',Password('".AESKEY."'))");
                $modelUser->Email           =   new  \yii\db\Expression("AES_ENCRYPT('".$_POST["Artist"]["Email"]."',Password('".AESKEY."'))");
                $modelUser->Password        =   md5($_POST['Artist']['Password']);
                $modelUser->ConfirmPassword =   md5($_POST['Artist']['Password']);
                $modelUser->UserType        =   '2';
                $modelUser->Status          =   $_POST['Artist']['Status'];
                $modelUser->Created         =   date("Y-m-d H:i:s");
                $modelUser->CreatedBy       =   Yii::$app->user->identity->UserID;
				if($_POST['Artist']['SocialPostEnabled']!=null){ $model->SocialPostEnabled = $_POST['Artist']['SocialPostEnabled'];}
				if($_POST['Artist']['Display24h']!=null){ $model->Display24h = $_POST['Artist']['Display24h'];}
				if(isset($_POST['Artist']['CompanyID']) && $_POST['Artist']['CompanyID'] == '0'){$model->CompanyID = null;}	
                
                if(!$modelUser->save(false)) 
                {
                    return $this->render('create', [
                        'model' => $modelUser,
                    ]);
                }
                $userID             = Yii::$app->db->getLastInsertID();
                //$DOB                = date("d-m-Y",strtotime($_POST['Artist']['DOB']));
                //$model->DOB         = new  \yii\db\Expression("AES_ENCRYPT('".$DOB."',Password('".AESKEY."'))");
                $model->Email       = new  \yii\db\Expression("AES_ENCRYPT('".$_POST['Artist']['Email']."',Password('".AESKEY."'))");
                $model->DeviceType  = '3';
                $model->UserID      = $userID;
                $model->Created     = date("Y-m-d H:i:s");
                $model->CreatedBy   = Yii::$app->user->identity->UserID;
                $filesArray         = $_FILES['Artist']['tmp_name']['ProfileThumb'];
                $filesNameArray     = $_FILES['Artist']['name']['ProfileThumb'];    
                $profileImage       = $filesArray;
                $previousFileName   = str_replace(' ','_',$filesNameArray);
                $previousFileName = pathinfo($previousFileName,PATHINFO_EXTENSION);
                if($previousFileName == 'png') {
                    $previousFileName = "jpg";
                }
                $actualImageName    = 'profile_'.time().".".$previousFileName;
                $model->ProfileThumb = $actualImageName;

				//if UserType is Company Admin, automatically assign Artist to the Company
				if(Yii::$app->user->identity->UserType == 4) {					
					$model->CompanyID = Yii::$app->session->get('CompanyID');
				}
				
                if(!$model->save(false)) 
                {
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                $artistID = Yii::$app->db->getLastInsertID();
                
                /********************* Settings Data ********************/
                $modelSettings = \backend\models\Setting::findOne(['ArtistID'=>$artistID]);
                if($modelSettings == "") {
                    $modelSettings = new \backend\models\Setting();
                    $modelSettings->ArtistID = $artistID;
                }
                $productType = "";
                if(isset($_POST['Artist']['ProductType'])) :
                    $productType = $_POST['Artist']['ProductType'];    
                    $modelSettings->ProductType = $productType;
                endif;
                if($productType == "2") {
                    if(isset($_POST['Artist']['ProductPrice']))
                        $modelSettings->ProductPrice = $_POST['Artist']['ProductPrice'];
                    else 
                        $modelSettings->ProductPrice = 0;

                    if(isset($_POST['Artist']['ProductSKUID']))
                        $modelSettings->ProductSKUID = $_POST['Artist']['ProductSKUID'];
                    else
                        $modelSettings->ProductSKUID = "";
                } else {
                    $modelSettings->ProductPrice = 0;
                    $modelSettings->ProductSKUID = "";
                }
                //no ads subscription
                if (!empty(trim($model->NoAdsProdID)) && $model->NoAdsSubsEnabled) {
                    $modelSettings->NoAdsProdID=$model->NoAdsProdID;
                } else {
                    $modelSettings->NoAdsProdID="";
                }

                $modelSettings->save(false);
                /********************************************************/
                
                $updateUser = \backend\models\User::findOne($userID);
                $updateUser->updateAll(array('ArtistID'=>$artistID),array('UserID'=>$userID));
                
                $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                if(\S3::putObjectFile($profileImage,S3BUCKET,BOOM.$artistID.PROFILE_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ))
                {
                    $content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$artistID.PROFILE_IMAGES.$actualImageName);
                    $commonthumb = "uploads/postlarge/".$actualImageName;
                    $createthumb = "uploads/postthumb/".$actualImageName;
                    $createmedium = "uploads/postmedium/".$actualImageName;
                    file_put_contents($commonthumb, $content);

                    $magicianObj = new \imageLib($commonthumb);
                    $magicianObj->resizeImage(THUMB_WIDTH_SIZE,THUMB_HEIGHT_SIZE,1);
                    $magicianObj->saveImage($createthumb, 100);

                    $magicianObj = new \imageLib($commonthumb);
                    $magicianObj->resizeImage(MEDIUM_WIDTH_SIZE,MEDIUM_HEIGHT_SIZE,1);
                    $magicianObj->saveImage($createmedium, 100);
                    
                    \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.PROFILE_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                    \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$artistID.PROFILE_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                }
                $filesArray = $_FILES['Artist']['tmp_name']['ArtistImages'];
                $filesNameArray = $_FILES['Artist']['name']['ArtistImages'];
                for($n=0; $n<count($filesArray); $n++) 
                {
                    if($filesArray[$n] != '') 
                    {
                        $profileImage = $filesArray[$n];
                        $previousFileName = str_replace(' ','_',$filesNameArray[$n]);
                        $previousFileName = pathinfo($previousFileName,PATHINFO_EXTENSION);
                        $previousFileExtension = $previousFileName;
                        if($previousFileName == 'png') {
                            $previousFileName = "jpg";
                        }
                        $actualImageName = 'artist_'.$artistID.'_'.time().".".$previousFileName;
                        if(\S3::putObjectFile($profileImage,S3BUCKET,BOOM.$artistID.ARTIST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ)) 
                        {
                            $s3GalleryImage = S3_BUCKETPATH.$artistID.ARTIST_IMAGES.$actualImageName;
                            $imageDetail = getimagesize($s3GalleryImage);
                            $width = $imageDetail[0];
                            $height = $imageDetail[1];
                            if($previousFileExtension != 'png') {
                                /************* Resize Image *************/
                                if($width >=1280 || $height>=1280) {
                                    $content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$artistID.ARTIST_IMAGES.$actualImageName);
                                    $commonthumb = "uploads/postlarge/".$actualImageName;
                                    $createthumb = "uploads/postthumb/".$actualImageName;
                                    $createmedium = "uploads/postmedium/".$actualImageName;
                                    $createhd = "uploads/posthd/".$actualImageName;
                                    file_put_contents($commonthumb, $content);

                                    $magicianObj = new \imageLib($commonthumb);
                                    if($width > $height) {
                                        $magicianObj->resizeImage(1280,'',3);
                                    } else if($height>$width) {
                                        $magicianObj->resizeImage('',1280,3);
                                    }   
                                    $magicianObj->saveImage($createhd, 50);
                                    $actualImageName = 'artist_'.$artistID.'_'.time().".".$previousFileName;
                                    \S3::putObjectFile($createhd,S3BUCKET,BOOM.$artistID.ARTIST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                    
                                    $magicianObj = new \imageLib($commonthumb);
                                    $magicianObj->resizeImage(THUMB_WIDTH_SIZE,THUMB_HEIGHT_SIZE,1);
                                    $magicianObj->saveImage($createthumb, 100);

                                    $magicianObj = new \imageLib($commonthumb);
                                    $magicianObj->resizeImage(MEDIUM_WIDTH_SIZE,MEDIUM_HEIGHT_SIZE,1);
                                    $magicianObj->saveImage($createmedium, 100);

                                    \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.ARTIST_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                    \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$artistID.ARTIST_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                } else {
                                    $content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$artistID.ARTIST_IMAGES.$actualImageName);
                                    $commonthumb = "uploads/postlarge/".$actualImageName;
                                    $createthumb = "uploads/postthumb/".$actualImageName;
                                    $createmedium = "uploads/postmedium/".$actualImageName;
                                    $createhd = "uploads/posthd/".$actualImageName;
                                    file_put_contents($commonthumb, $content);

                                    $magicianObj = new \imageLib($commonthumb);
                                    $magicianObj->resizeImage($width,$height);  
                                    $magicianObj->saveImage($createhd, 50);
                                    $actualImageName = 'artist_'.$artistID.'_'.time().".".$previousFileName;
                                    \S3::putObjectFile($createhd,S3BUCKET,BOOM.$artistID.ARTIST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                    
                                    $magicianObj = new \imageLib($commonthumb);
                                    $magicianObj->resizeImage(THUMB_WIDTH_SIZE,THUMB_HEIGHT_SIZE,1);
                                    $magicianObj->saveImage($createthumb, 100);

                                    $magicianObj = new \imageLib($commonthumb);
                                    $magicianObj->resizeImage(MEDIUM_WIDTH_SIZE,MEDIUM_HEIGHT_SIZE,1);
                                    $magicianObj->saveImage($createmedium, 100);

                                    \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.ARTIST_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                    \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$artistID.ARTIST_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                }
                                /**************************************************/      
                            }                            
                            $modelGallery = new \backend\models\Gallery();  
                            $modelGallery->Title =   $previousFileName;
                            $modelGallery->ArtistID  = $artistID;
                            $modelGallery->RefTableID = '';
                            $modelGallery->RefTableType = '2';
                            $modelGallery->Type = '1';
                            $modelGallery->Image = $actualImageName;
                            $modelGallery->Created = date("Y-m-d H:i:s");
                            $modelGallery->CreatedBy = Yii::$app->user->identity->UserID;
                            $modelGallery->save(false);  
                        }
                    }    
                }
                Yii::$app->session->setFlash('success', 'Artist has been added successfully');
                //return $this->redirect('index');
                return $this->redirect('update?id='.$artistID);
            } 
            else 
            {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }    
        } 
        else 
        {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Artist model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        if(Yii::$app->user->identity->UserType == 2) {
            $session = Yii::$app->session;
            if($session->get('ArtistID') != $id) {
                return $this->redirect('update?id='.$session->get('ArtistID'));
            }
        }
        $model = $this->findModel($id);
        $modelSettings = \backend\models\Setting::findOne(['ArtistID'=>$id]);
        if($modelSettings != "") {
            $model->ProductType = $modelSettings->ProductType; 
            $model->ProductPrice = $modelSettings->ProductPrice; 
            $model->ProductSKUID = $modelSettings->ProductSKUID;
            $model->NoAdsProdID = $modelSettings->NoAdsProdID;
            if (!empty(trim($model->NoAdsProdID))) {
                $model->NoAdsSubsEnabled=1;
            }
        } else {
            $model->ProductType = "2";
        }
        $modelUser  = $this->findModelForUser($model->UserID);
        $modelGallery = new \backend\models\Gallery();  
        $conditionForGallery = array("ArtistID"=>$model->ArtistID,"RefTableType"=>"2","Type"=>"1");
        $galleryData = $modelGallery->findAll($conditionForGallery);
        $conditionForUser = array("UserID"=>$model->UserID);
        $userName = $modelUser->UserName;
        $email = $modelUser->Email; 
        $model->Status = $modelUser->Status;
        $model->UserName = $userName;
        $model->Email = $email;
        $model->scenario = 'update';
        //$model->DOB  = date("M-d-Y",strtotime($model->DOB));
        $oldPassword = $modelUser->Password;
        $oldProfileImage = $model->ProfileThumb;
        if($model->load(Yii::$app->request->post())) {
            if( ($_POST['Artist']['Password']!='' && $_POST['Artist']['ConfirmPassword']!='') &&
                ($_POST['Artist']['Password']==$_POST['Artist']['ConfirmPassword']) ) {
                $model->Password = md5($_POST['Artist']['Password']);
                $model->ConfirmPassword = md5($_POST['Artist']['Password']);
            } else {
                $model->Password = $oldPassword;
                $model->ConfirmPassword = $oldPassword;
            }
            if($model->validate()) {
                if( ($_POST['Artist']['Password']!='' && $_POST['Artist']['ConfirmPassword']!='') &&
                    ($_POST['Artist']['Password']==$_POST['Artist']['ConfirmPassword']) ) {
                    $modelUser->Password = md5($_POST['Artist']['Password']);
                    $modelUser->ConfirmPassword = md5($_POST['Artist']['Password']);
                } else {
                    $modelUser->Password = $oldPassword;
                    $modelUser->ConfirmPassword = $oldPassword;
                }
                $modelUser->Status = $_POST['Artist']['Status'];
                $modelUser->UserName = new  \yii\db\Expression("AES_ENCRYPT('".$_POST["Artist"]["UserName"]."',Password('".AESKEY."'))");
                $modelUser->Updated = date("Y-m-d H:i:s");
                $modelUser->UpdatedBy = Yii::$app->user->identity->UserID;
                if(!$modelUser->save(false)) {
                    return $this->render('update', [
                        'model' => $modelUser,
                        'galleryData'=>$galleryData,
                    ]);
                } 
                //$DOB  = date("d-m-Y",strtotime($_POST['Artist']['DOB']));
                //$model->DOB = new  \yii\db\Expression("AES_ENCRYPT('".$DOB."',Password('".AESKEY."'))");
                $model->Updated = date("Y-m-d H:i:s");
                $model->UpdatedBy = Yii::$app->user->identity->UserID;
				if($_POST['Artist']['SocialPostEnabled']!=null){ $model->SocialPostEnabled = $_POST['Artist']['SocialPostEnabled'];}
				if($_POST['Artist']['Display24h']!=null){ $model->Display24h = $_POST['Artist']['Display24h'];}
				if(isset($_POST['Artist']['CompanyID']) && $_POST['Artist']['CompanyID'] == '0'){$model->CompanyID = null;}	
                
                if(isset($_FILES['Artist']['name']['ProfileThumb']) && $_FILES['Artist']['name']['ProfileThumb']!='') {
                    $filesArray = $_FILES['Artist']['tmp_name']['ProfileThumb'];
                    $filesNameArray = $_FILES['Artist']['name']['ProfileThumb'];    
                    $profileImage = $filesArray;
                    $previousFileName = str_replace(' ','_',$filesNameArray);
                    $previousFileName = pathinfo($previousFileName,PATHINFO_EXTENSION);
                    if($previousFileName == 'png') {
                        $previousFileName = "jpg";
                    }
                    $actualImageName = 'profile_'.time().".".$previousFileName;
                    $model->ProfileThumb = $actualImageName;
                    $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                    if(\S3::putObjectFile($profileImage,S3BUCKET,BOOM.$id.PROFILE_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ))
                    {
                        $content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$id.PROFILE_IMAGES.$actualImageName);
                        $commonthumb = "uploads/postlarge/".$actualImageName;
                        $createthumb = "uploads/postthumb/".$actualImageName;
                        $createmedium = "uploads/postmedium/".$actualImageName;
                        file_put_contents($commonthumb, $content);

                        $magicianObj = new \imageLib($commonthumb);
                        $magicianObj->resizeImage(THUMB_WIDTH_SIZE,THUMB_HEIGHT_SIZE,1);
                        $magicianObj->saveImage($createthumb, 100);

                        $magicianObj = new \imageLib($commonthumb);
                        $magicianObj->resizeImage(MEDIUM_WIDTH_SIZE,MEDIUM_HEIGHT_SIZE,1);
                        $magicianObj->saveImage($createmedium, 100);
                    
                        \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$id.PROFILE_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                        \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$id.PROFILE_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                    }
                } else {
                    $model->ProfileThumb = $oldProfileImage;
                }    
                
                if(!$model->save(false)) {
                    return $this->render('update', [
                        'model' => $model,
                        'galleryData'=>$galleryData,
                    ]);
                }
                
                /********************* Settings Data ********************/
                $modelSettings = \backend\models\Setting::findOne(['ArtistID'=>$id]);
                if($modelSettings == "") {
                    $modelSettings = new \backend\models\Setting();
                    $modelSettings->ArtistID = $id;
                }
                $productType = "";
                if(isset($_POST['Artist']['ProductType'])) :
                    $productType = $_POST['Artist']['ProductType'];    
                    $modelSettings->ProductType = $productType;
                endif;
                if($productType == "2") {
                    if(isset($_POST['Artist']['ProductPrice']))
                        $modelSettings->ProductPrice = $_POST['Artist']['ProductPrice'];
                    else 
                        $modelSettings->ProductPrice = 0;

                    if(isset($_POST['Artist']['ProductSKUID']))
                        $modelSettings->ProductSKUID = $_POST['Artist']['ProductSKUID'];
                    else
                        $modelSettings->ProductSKUID = "";
                } else {
                    $modelSettings->ProductPrice = 0;
                    $modelSettings->ProductSKUID = "";
                }
                //no ads subscription IAP id if set
                if (!empty(trim($model->NoAdsProdID)) && $model->NoAdsSubsEnabled) {
                    $modelSettings->NoAdsProdID=$model->NoAdsProdID;
                } else {
                    $modelSettings->NoAdsProdID="";
                }

                $modelSettings->save(false);
                /********************************************************/
                
                $filesArray = $_FILES['Artist']['tmp_name']['ArtistImages'];
                $filesNameArray = $_FILES['Artist']['name']['ArtistImages'];
                for($n=0; $n<count($filesArray); $n++) {
                    if($filesArray[$n] != '') {
                        $profileImage = $filesArray[$n];
                        $previousFileName = str_replace(' ','_',$filesNameArray[$n]);
                        $previousFileName = pathinfo($previousFileName,PATHINFO_EXTENSION);
                        $previousFileExtension = $previousFileName;
                        if($previousFileName == 'png') {
                            $previousFileName = "jpg";
                        }
                        $actualImageName = 'artist_'.$id.'_'.time().".".$previousFileName;
                        $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                        if(\S3::putObjectFile($profileImage,S3BUCKET,BOOM.$id.ARTIST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ)) 
                        {    
                            $s3GalleryImage = S3_BUCKETPATH.$id.ARTIST_IMAGES.$actualImageName;
                            $imageDetail = getimagesize($s3GalleryImage);
                            $width = $imageDetail[0];
                            $height = $imageDetail[1];
                            if($previousFileExtension != 'png') {
                                /************* Resize Image *************/
                                if($width >=1280 || $height>=1280) {
                                    $content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$id.ARTIST_IMAGES.$actualImageName);
                                    $commonthumb = "uploads/postlarge/".$actualImageName;
                                    $createthumb = "uploads/postthumb/".$actualImageName;
                                    $createmedium = "uploads/postmedium/".$actualImageName;
                                    $createhd = "uploads/posthd/".$actualImageName;
                                    file_put_contents($commonthumb, $content);

                                    $magicianObj = new \imageLib($commonthumb);
                                    if($width > $height) {
                                        $magicianObj->resizeImage(1280,'',3);
                                    } else if($height>$width) {
                                        $magicianObj->resizeImage('',1280,3);
                                    }   
                                    $magicianObj->saveImage($createhd, 50);
                                    $actualImageName = 'artist_'.$id.'_'.time().".".$previousFileName;
                                    \S3::putObjectFile($createhd,S3BUCKET,BOOM.$id.ARTIST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                    
                                    $magicianObj = new \imageLib($commonthumb);
                                    $magicianObj->resizeImage(THUMB_WIDTH_SIZE,THUMB_HEIGHT_SIZE,1);
                                    $magicianObj->saveImage($createthumb, 100);

                                    $magicianObj = new \imageLib($commonthumb);
                                    $magicianObj->resizeImage(MEDIUM_WIDTH_SIZE,MEDIUM_HEIGHT_SIZE,1);
                                    $magicianObj->saveImage($createmedium, 100);
                                    
                                    \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$id.ARTIST_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                    \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$id.ARTIST_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                } else {
                                    $content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$id.ARTIST_IMAGES.$actualImageName);
                                    $commonthumb = "uploads/postlarge/".$actualImageName;
                                    $createthumb = "uploads/postthumb/".$actualImageName;
                                    $createmedium = "uploads/postmedium/".$actualImageName;
                                    $createhd = "uploads/posthd/".$actualImageName;
                                    file_put_contents($commonthumb, $content);

                                    $magicianObj = new \imageLib($commonthumb);
                                    $magicianObj->resizeImage($width,$height);
                                    $magicianObj->saveImage($createhd, 50);
                                    $actualImageName = 'artist_'.$id.'_'.time().".".$previousFileName;
                                    \S3::putObjectFile($createhd,S3BUCKET,BOOM.$id.ARTIST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                    
                                    $magicianObj = new \imageLib($commonthumb);
                                    $magicianObj->resizeImage(THUMB_WIDTH_SIZE,THUMB_HEIGHT_SIZE,1);
                                    $magicianObj->saveImage($createthumb, 100);

                                    $magicianObj = new \imageLib($commonthumb);
                                    $magicianObj->resizeImage(MEDIUM_WIDTH_SIZE,MEDIUM_HEIGHT_SIZE,1);
                                    $magicianObj->saveImage($createmedium, 100);
                                    
                                    \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$id.ARTIST_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                    \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$id.ARTIST_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                }
                            }    
                            /**************************************************/   
                            /*$content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$id.ARTIST_IMAGES.$actualImageName);
                            $commonthumb = "uploads/postlarge/".$actualImageName;
                            $createthumb = "uploads/postthumb/".$actualImageName;
                            $createmedium = "uploads/postmedium/".$actualImageName;
                            file_put_contents($commonthumb, $content);*/
                            
                            
                            $modelGallery = new \backend\models\Gallery();  
                            $modelGallery->Title =   $previousFileName;
                            $modelGallery->ArtistID  = $id;
                            $modelGallery->RefTableID = '';
                            $modelGallery->RefTableType = '2';
                            $modelGallery->Type = '1';
                            $modelGallery->Image = $actualImageName;
                            $modelGallery->Created = date("Y-m-d H:i:s");
                            $modelGallery->CreatedBy = Yii::$app->user->identity->UserID;
                            $modelGallery->save(false);  
                        }
                    }    
                }   
                Yii::$app->session->setFlash('success', 'Artist has been updated successfully');
                return $this->redirect('update?id='.$id);
                /*if(Yii::$app->user->identity->UserType == 2) {
                    return $this->redirect('update?id='.$id);
                } else {
                    return $this->redirect('index');
                }*/
            }
        } 
        return $this->render('update', [
            'model' => $model,'galleryData'=>$galleryData,
        ]);
    }
    
    public function actionRemoveimage($id) {
        if((int)$id) {
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
            $modelGallery = new \backend\models\Gallery();  
            $condition = array("ID"=>$id);
            $galleryData = $modelGallery->findAll($condition);
            $artistID = $galleryData[0]['ArtistID'];
            $imageName = BOOM.$artistID.ARTIST_IMAGES.$galleryData[0]['Image']; 
            $deleteObject = \S3::deleteObject(S3BUCKET,$imageName);
            $modelGallery->deleteAll($condition);
            Yii::$app->session->setFlash('success', 'Image has been deleted successfully');
            return $this->redirect('update?id='.$artistID);
        }
    }
    
    public function actionRemoveprofileimage($id) {
        if((int)$id) {
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
            $model = Artist::findOne($id);
            $imageName = BOOM.$id.ARTIST_IMAGES.$model->ProfileThumb; 
            $deleteObject = \S3::deleteObject(S3BUCKET,$imageName);
            $model->ProfileThumb = '';
            $model->save(false);
            Yii::$app->session->setFlash('success', 'Image has been deleted successfully');
            return $this->redirect('update?id='.$id);
        }
    }

    /**
     * Deletes an existing Artist model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Artist model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Artist the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        //$model = Artist::findOne($id);
        $model = Artist::find()->select("*,AES_DECRYPT(Email,Password('".AESKEY."')) AS Email,AES_DECRYPT(DOB,Password('".AESKEY."')) AS DOB")
	    ->where('ArtistID ='.$id)
	    ->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    protected function findModelForUser($id)
    {
        $model =  \backend\models\User::find()->select("*,AES_DECRYPT(UserName,Password('".AESKEY."')) AS UserName,AES_DECRYPT(Email,Password('".AESKEY."')) AS Email")
	    ->where('UserID ='.$id.'')
	    ->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
