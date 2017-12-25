<?php
namespace backend\controllers;

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
/**
 * MemberController implements the CRUD actions for Member model.
 */
class MemberController extends Controller
{
    public function behaviors()
    {
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
        /*$searchModel = new MemberSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);*/
        
        $searchModel = new MemberSearch();
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
        $model = new Member();
        $model->scenario = 'create';
        $modelUser  = new \backend\models\User();
        if ($model->load(Yii::$app->request->post())) {
            if($model->validate()) {
                $userData = \backend\models\User::findAll(array("UserName"=>new  \yii\db\Expression("AES_ENCRYPT('".$_POST["Member"]["UserName"]."',Password('".AESKEY."'))")));
                if(count($userData)>0) {
                    $model->addError('UserName', 'This username is already exists please try with different username');
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                $modelUser->UserName = new  \yii\db\Expression("AES_ENCRYPT('".$_POST["Member"]["UserName"]."',Password('".AESKEY."'))");
                $modelUser->Password = md5($_POST['Member']['Password']);
                $modelUser->ConfirmPassword = md5($_POST['Member']['Password']);
                $modelUser->UserType = '1';
                $modelUser->Status = $_POST['Member']['Status'];
                $modelUser->Created = date("Y-m-d H:i:s");
                $modelUser->CreatedBy = Yii::$app->user->identity->UserID;
                if(!$modelUser->save(false)) {
                    return $this->render('create', [
                        'model' => $modelUser,
                    ]);
                } 
                $userID = Yii::$app->db->getLastInsertID();
                $DOB  = date("d-m-Y",strtotime($_POST['Member']['DOB']));
                $MobileNo = $_POST['Member']['MobileNo'];
                $model->DOB = new  \yii\db\Expression("AES_ENCRYPT('".$DOB."',Password('".AESKEY."'))");
                $model->MobileNo = new  \yii\db\Expression("AES_ENCRYPT('".$MobileNo."',Password('".AESKEY."'))");
                $model->Email = new  \yii\db\Expression("AES_ENCRYPT('".$_POST['Member']['Email']."',Password('".AESKEY."'))");
                $model->DeviceType = '3';
                $model->UserID = $userID;
                $model->Created = date("Y-m-d H:i:s");
                $model->CreatedBy = Yii::$app->user->identity->UserID;
                $artistID = $_POST['Member']['ArtistID'];
                $filesArray = $_FILES['Member']['tmp_name']['ProfileThumb'];
                $filesNameArray = $_FILES['Member']['name']['ProfileThumb'];    
                $profileImage = $filesArray;
                $previousFileName = str_replace(' ','_',$filesNameArray);
                $actualImageName = time()."_".$previousFileName;
                \S3::putObjectFile($profileImage,S3BUCKET,BOOM.$artistID.PROFILE_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                $model->ProfileThumb = $actualImageName;
                if(!$model->save(false)) {
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                Yii::$app->session->setFlash('success', 'Member has been added successfully');
                return $this->redirect('index');
            }    
        } else {
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
        $model = $this->findModel($id);
        $modelUser  = $this->findModelForUser($model->UserID);
        $conditionForUser = array("UserID"=>$model->UserID);
        $userName = $modelUser->UserName; 
        $model->UserName = $userName;
        $model->scenario = 'update';
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
                if( ($_POST['Member']['Password']!='' && $_POST['Member']['ConfirmPassword']!='') &&
                    ($_POST['Member']['Password']==$_POST['Member']['ConfirmPassword']) ) {
                    $modelUser->Password = md5($_POST['Member']['Password']);
                    $modelUser->ConfirmPassword = md5($_POST['Member']['Password']);
                } else {
                    $modelUser->Password = $oldPassword;
                    $modelUser->ConfirmPassword = $oldPassword;
                }
                $modelUser->Status = $_POST['Member']['Status'];
                $modelUser->UserName = new  \yii\db\Expression("AES_ENCRYPT('".$_POST["Member"]["UserName"]."',Password('".AESKEY."'))");
                $modelUser->Updated = date("Y-m-d H:i:s");
                $modelUser->UpdatedBy = Yii::$app->user->identity->UserID;
                if(!$modelUser->save(false)) {
                    return $this->render('update', [
                        'model' => $modelUser,
                        'modelUser' => $modelUser
                    ]);
                }
                $DOB  = date("d-m-Y",strtotime($_POST['Member']['DOB']));
                $MobileNo = $_POST['Member']['MobileNo'];
                $model->DOB = new  \yii\db\Expression("AES_ENCRYPT('".$DOB."',Password('".AESKEY."'))");
                $model->MobileNo = new  \yii\db\Expression("AES_ENCRYPT('".$MobileNo."',Password('".AESKEY."'))");
                $model->Updated = date("Y-m-d H:i:s");
                $model->UpdatedBy = Yii::$app->user->identity->UserID; 
                $artistID = $_POST['Member']['ArtistID'];
                if(isset($_FILES['Member']['name']['ProfileThumb']) && $_FILES['Member']['name']['ProfileThumb']!='') {
                    $filesArray = $_FILES['Member']['tmp_name']['ProfileThumb'];
                    $filesNameArray = $_FILES['Member']['name']['ProfileThumb'];    
                    $profileImage = $filesArray;
                    $previousFileName = str_replace(' ','_',$filesNameArray);
                    $actualImageName = time()."_".$previousFileName;
                    $model->ProfileThumb = $actualImageName;
                    \S3::putObjectFile($profileImage,S3BUCKET,BOOM.$artistID.PROFILE_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
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

    /**
     * Finds the Member model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Member the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    
    protected function findModel($id)
    {
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
        $model =  \backend\models\User::find()->select("*,AES_DECRYPT(UserName,Password('".AESKEY."')) AS UserName")
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
