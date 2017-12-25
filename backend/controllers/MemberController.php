<?php
namespace backend\controllers;
require('Imagemagician.php');
use Yii;
use backend\models\Member;
use backend\models\MemberSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\LikememberSearch;
use backend\models\SharememberSearch;
use backend\models\CommentmemberSearch;
use backend\models\ExclusivememberSearch;
use yii\helpers\Url;
use yii\db\QueryBuilder;
/**
 * MemberController implements the CRUD actions for Member model.
 */
class MemberController extends Controller
{
    public function behaviors()
    {
        /************** Restict action for artist and member  *******************/
        if(Yii::$app->user->identity->UserType == 3) {
            return \Yii::$app->getResponse()->redirect(array('/site/logout',302));
        }
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }
    
    public function beforeAction($action)
    {        
        /************* Check if login or not ****************/
        if (\Yii::$app->getUser()->isGuest &&
            \Yii::$app->getRequest()->url !== Url::to(\Yii::$app->getUser()->loginUrl)
        ) {
            \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        return parent::beforeAction($action);
    }

    /**
     * Lists all Member models.
     * @return mixed
     */
    public function actionIndex()
    {
        /******************** Get Member List *************/
        $searchModel = new MemberSearch();
		if(Yii::$app->user->identity->UserType == 4) {
			$searchModel->CompanyID = Yii::$app->session->get('CompanyID');
		}
        if(isset($_GET['MemberSearch'])) {
            $searchModel->attributes = $_GET['MemberSearch'];
        }
        if(isset($_POST['Member']['FromJoinDate']) && $_POST['Member']['FromJoinDate']!='') {
            $searchModel->FromJoinDate = $_POST['Member']['FromJoinDate'];
        }
        if(isset($_POST['Member']['ToJoinDate']) && $_POST['Member']['ToJoinDate']!='') {
            $searchModel->ToJoinDate = $_POST['Member']['ToJoinDate'];
        }
        return $this->render('index', [            
            'model' => $searchModel,
        ]);
    }
    
    public function actionLike()
    {
        /********** Get Member list for particular post like **********/
        $searchModel = new LikememberSearch();
        if(isset($_GET['LikememberSearch'])) {
            $searchModel->attributes = $_GET['LikememberSearch'];
        }
        return $this->render('like', [            
            'model' => $searchModel,
        ]);
    }
    
    public function actionShare()
    {
        /********** Get Member list for particular post share **********/
        $searchModel = new SharememberSearch();
        if(isset($_GET['SharememberSearch'])) {
            $searchModel->attributes = $_GET['SharememberSearch'];
        }
        return $this->render('share', [            
            'model' => $searchModel,
        ]);
    }
    
    public function actionFlag()
    {
        /********** Get Member list for particular post flag **********/
        $searchModel = new SharememberSearch();
        if(isset($_GET['SharememberSearch'])) {
            $searchModel->attributes = $_GET['SharememberSearch'];
        }
        return $this->render('share', [            
            'model' => $searchModel,
        ]);
    }
    
    public function actionStickers()
    {
        /********** Get Member list for particular stickers **********/
        $searchModel = new SharememberSearch();
        if(isset($_GET['SharememberSearch'])) {
            $searchModel->attributes = $_GET['SharememberSearch'];
        }
        return $this->render('stickers', [            
            'model' => $searchModel,
        ]);
    }
    
    public function actionComment()
    {
        /********** Get Member list for particular post comment **********/
        $searchModel = new CommentmemberSearch();
        if(isset($_GET['CommentmemberSearch'])) {
            $searchModel->attributes = $_GET['CommentmemberSearch'];
        }
        return $this->render('comment', [            
            'model' => $searchModel,
        ]);
    }
    
    public function actionExclusive()
    {
        /********* For Exclusive member search **************/
        $searchModel = new ExclusivememberSearch();
        if(isset($_GET['ExclusivememberSearch'])) {
            $searchModel->attributes = $_GET['ExclusivememberSearch'];
        }
        return $this->render('exclusive', [            
            'model' => $searchModel,
        ]);
    }

    /**
     * Displays a single Member model.
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
     * Creates a new Member model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /**************** Create Member **************/
        $model = new Member();
        $model->scenario = 'create';
        $modelUser  = new \backend\models\User();
        $model->Status = 2;
        if ($model->load(Yii::$app->request->post())) {
            if($model->validate()) {
                $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                $userData = \backend\models\User::findAll(array("UserName"=>new  \yii\db\Expression("AES_ENCRYPT('".$_POST["Member"]["UserName"]."',Password('".AESKEY."'))")));
                
                /***************** Check if username is exists or not **************/
                if(count($userData)>0) {
                    $model->addError('UserName', 'This username already exists! Please try with a different username.');
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                $query = Yii::$app->getDb();
                $command = $query->createCommand("SELECT GetUniqueCode()");
                $rows = $command->queryAll();
                $activationcode = $rows[0]['GetUniqueCode()'];
				
				$artistID = $_POST['Member']['ArtistID'];
				if($artistID == null || $artistID == ""){ $artistID = 0; $model->ArtistID = 0;}
				
                $modelUser->UserName = new  \yii\db\Expression("AES_ENCRYPT('".$_POST["Member"]["UserName"]."',Password('".AESKEY."'))");
                $modelUser->Email = new  \yii\db\Expression("AES_ENCRYPT('".$_POST["Member"]["Email"]."',Password('".AESKEY."'))");
                $modelUser->Password = md5($_POST['Member']['Password']);
                $modelUser->ConfirmPassword = md5($_POST['Member']['Password']);
                $modelUser->UserType = '1';
				$modelUser->ArtistID = $artistID;
                $modelUser->ActivationCode = $activationcode;
                $modelUser->Status = $_POST['Member']['Status'];
                $modelUser->Created = date("Y-m-d H:i:s");
                $modelUser->CreatedBy = Yii::$app->user->identity->UserID;
                if(!$modelUser->save(false)) {
                    return $this->render('create', [
                        'model' => $modelUser,
                    ]);
                } 
                $userID = Yii::$app->db->getLastInsertID();
                if(isset($_POST['Member']['DOB']) && $_POST['Member']['DOB']!='') :
                    $DOB  = date("d-m-Y",strtotime($_POST['Member']['DOB']));
                    $model->DOB = new  \yii\db\Expression("AES_ENCRYPT('".$DOB."',Password('".AESKEY."'))");
                endif;    
                $model->Email = new  \yii\db\Expression("AES_ENCRYPT('".$_POST['Member']['Email']."',Password('".AESKEY."'))");
                $model->DeviceType = '3';
                $model->UserID = $userID;
                $model->Created = date("Y-m-d H:i:s");
                $model->CreatedBy = Yii::$app->user->identity->UserID;
                
                
                /********************** Upload member profiile image in bucket *********************/
                if(isset($_FILES['Member']['tmp_name']['ProfileThumb']) && $_FILES['Member']['tmp_name']['ProfileThumb']!='') {
                $filesArray = $_FILES['Member']['tmp_name']['ProfileThumb'];
                    $filesNameArray = $_FILES['Member']['name']['ProfileThumb'];    
                    $profileImage = $filesArray;
                    $previousFileName = str_replace(' ','_',$filesNameArray);
                    $previousFileName = pathinfo($previousFileName,PATHINFO_EXTENSION);
                    $actualImageName = 'member_'.$artistID.'_'.time().".".$previousFileName;
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
                    $model->ProfileThumb = $actualImageName;
                } 
                /***************** Send Email ****************/
                $email = $_POST['Member']['Email'];
                if($email != "" && $artistID != 0)
                {
                    $name = $_POST['Member']['MemberName'];
                    $getArtistData  =   new \backend\models\Artist();
                    $artistname     =   $getArtistData->getArtistName($artistID);
                    $link           =   ACTIVATIONPAGE.$activationcode;
                    $getcontent     =   $getArtistData->getSignupemailtemplate($name,$artistname,$link,'english');
                    Yii::$app->mailer->compose()
                                     ->setFrom(FROM_EMAIL)
                                     ->setTo($email)
                                     ->setSubject("Boom Video - Activation link")
                                     ->setHtmlBody($getcontent)
                                     ->send();
                }
                if(!$model->save(false)) 
                {
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                Yii::$app->session->setFlash('success', 'Member has been added successfully');
                return $this->redirect('index');
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
     * Updates an existing Member model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        /************** Update member *******************/
        $model = $this->findModel($id);
        $modelUser  = $this->findModelForUser($model->UserID);
        $conditionForUser = array("UserID"=>$model->UserID);
        $userName = $modelUser->UserName; 
        $email = $model->Email; 
        $model->UserName = $userName;
        $model->Email = $email;
        $model->scenario = 'update';
        $model->Status = $modelUser->Status;
        if(isset($model->DOB) && $model->DOB!='')
            $model->DOB  = date("M-d-Y",strtotime($model->DOB));
        $oldPassword = $modelUser->Password;
        $oldProfileImage = $model->ProfileThumb;
        if ($model->load(Yii::$app->request->post())) {
            if( ($_POST['Member']['Password']!='' && $_POST['Member']['ConfirmPassword']!='') &&
                ($_POST['Member']['Password']==$_POST['Member']['ConfirmPassword']) ) {
                $model->Password = md5($_POST['Member']['Password']);
                $model->ConfirmPassword = md5($_POST['Member']['Password']);
            } else {
                $model->Password = $oldPassword;
                $model->ConfirmPassword = $oldPassword;
            }
            if($model->validate()) {
                $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                if( ($_POST['Member']['Password']!='' && $_POST['Member']['ConfirmPassword']!='') &&
                    ($_POST['Member']['Password']==$_POST['Member']['ConfirmPassword']) ) {
                    $modelUser->Password = md5($_POST['Member']['Password']);
                    $modelUser->ConfirmPassword = md5($_POST['Member']['Password']);
                } else {
                    $modelUser->Password = $oldPassword;
                    $modelUser->ConfirmPassword = $oldPassword;
                }
				
				$artistID = $_POST['Member']['ArtistID'];
				if($artistID == null || $artistID == ""){ $artistID = 0; $model->ArtistID = 0;}
				
				$modelUser->ArtistID = $artistID;
                $modelUser->Status = $_POST['Member']['Status'];
                $modelUser->UserName = new  \yii\db\Expression("AES_ENCRYPT('".$_POST["Member"]["UserName"]."',Password('".AESKEY."'))");
				$modelUser->Email = new  \yii\db\Expression("AES_ENCRYPT('".$_POST["Member"]["Email"]."',Password('".AESKEY."'))");
                $modelUser->Updated = date("Y-m-d H:i:s");
                $modelUser->UpdatedBy = Yii::$app->user->identity->UserID;
                if(!$modelUser->save(false)) {
                    return $this->render('update', [
                        'model' => $modelUser,
                        'modelUser' => $modelUser
                    ]);
                }
                if(isset($_POST['Member']['DOB']) && $_POST['Member']['DOB']!='') :
                    $DOB  = date("d-m-Y",strtotime($_POST['Member']['DOB']));
                    // $MobileNo = $_POST['Member']['MobileNo'];
                    $model->DOB = new  \yii\db\Expression("AES_ENCRYPT('".$DOB."',Password('".AESKEY."'))");
                endif;    
                $model->IsPushEnabled = $_POST['Member']['IsPushEnabled'];
                //$model->MobileNo = new  \yii\db\Expression("AES_ENCRYPT('".$MobileNo."',Password('".AESKEY."'))");
				$model->Email = new  \yii\db\Expression("AES_ENCRYPT('".$_POST['Member']['Email']."',Password('".AESKEY."'))");
                $model->Updated = date("Y-m-d H:i:s");
                $model->UpdatedBy = Yii::$app->user->identity->UserID; 

                
                /******************* Upload member profile image in bucket *******************/
                if(isset($_FILES['Member']['name']['ProfileThumb']) && $_FILES['Member']['name']['ProfileThumb']!='') {
                    $filesArray = $_FILES['Member']['tmp_name']['ProfileThumb'];
                    $filesNameArray = $_FILES['Member']['name']['ProfileThumb'];    
                    $profileImage = $filesArray;
                    $previousFileName = str_replace(' ','_',$filesNameArray);
                    $previousFileName = pathinfo($previousFileName,PATHINFO_EXTENSION);
                    $actualImageName = 'member_'.$artistID.'_'.time().".".$previousFileName;
                    $model->ProfileThumb = $actualImageName;
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
                }
                if(!$model->save(false)) {
                    return $this->render('update', [
                        'model' => $model,
                        'modelUser' => $modelUser
                    ]);
                }
                    
                Yii::$app->session->setFlash('success', 'Member has been updated successfully');
                return $this->redirect('index');
            }
        } else {
            return $this->render('update', [
                'model' => $model,
                'modelUser' => $modelUser
            ]);
        }
    }

    /**
     * Deletes an existing Member model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    public function actionBlock($id,$blocked)
    {
        /************** Block user *****************/
        $blockString = "";
        if($blocked == "1") {
            $blocked = "0"; 
            $blockString = "unblocked";
        } else {
            $blocked = "1"; 
            $blockString = "blocked";
        }
        $model = $this->findModel($id);
        $model->IsBlocked = $blocked;
        $model->save();
        Yii::$app->session->setFlash('success', 'Member has been '.$blockString.' successfully');
        return $this->redirect(['index']);
    }

    /**
     * Finds the Member model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Member the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    
    public function actionRemovestickerimage($id) {
        /******************* Remove profile image ****************/
        if((int)$id) {
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
            $model = Member::findOne($id);
            $artistID = $model->ArtistID;
            $imageName = BOOM.$artistID.PROFILE_IMAGES.$model->ProfileThumb; 
            $deleteObject = \S3::deleteObject(S3BUCKET,$imageName);
            $connection = \Yii::$app->db;
            $connection->createCommand()
                       ->update('member', array("ProfileThumb"=>''),'MemberID = '.$id)
                       ->execute();
            Yii::$app->session->setFlash('success', 'Image has been deleted successfully');
            return $this->redirect('update?id='.$id);
        }
    }
    
    protected function findModel($id)
    {
        /****************** Get member data my member ID ****************/
        //$model = Artist::findOne($id);
        $model = Member::find()->select("*,AES_DECRYPT(Email,Password('".AESKEY."')) AS Email,AES_DECRYPT(DOB,Password('".AESKEY."')) AS DOB,AES_DECRYPT(MobileNo,Password('".AESKEY."')) AS MobileNo")
	    ->where('MemberID ='.$id)
	    ->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    protected function findModelForUser($id)
    {
        /********************** Get user data by user ID ******************/
        $model =  \backend\models\User::find()->select("*,AES_DECRYPT(UserName,Password('".AESKEY."')) AS UserName,AES_DECRYPT(Email,Password('".AESKEY."')) AS Email")
	    ->where('UserID ='.$id.'')
	    ->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
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
        /************ Export member data **************/
        $connection = \Yii::$app->db;
        $artistData = $connection->createCommand("CALL Admin_Users_GetList('', '', '', '', '', '', '', '', '', '', '', '1', '".AESKEY."', '', '', '')")->queryAll();
        //echo '<pre>';
        //print_r($artistData);die;
        $arrayToCSV[0] = array('Member Name', 'Email', 'Zip Code', 'Mobile No', 'Gender', 'Artist Name', 'Status');
        if(count($artistData)>0) {
            $n=1;
            foreach($artistData as $key => $value) {
                $arrayToCSV[$n][0] = $value['MemberName'];
                $arrayToCSV[$n][1] = $value['Email'];
                $arrayToCSV[$n][2] = $value['ZipCode'];
                $arrayToCSV[$n][3] = $value['MobileNo'];
                $arrayToCSV[$n][4] = $value['Gender'];
                $arrayToCSV[$n][5] = $value['ArtistName'];
                $arrayToCSV[$n][6] = $value['Status'];
                $n++;
            } 
        }
        $this->convertToCSV($arrayToCSV, 'report.csv', ',');
    }
}
