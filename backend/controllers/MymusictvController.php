<?php

namespace backend\controllers;

require('Imagemagician.php');
use Yii;
use backend\models\Mymusictv;
use backend\models\MymusictvSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\db\Query;

/**
 * ArtistController implements the CRUD actions for Artist model.
 */
class MymusictvController extends Controller
{
    public function behaviors()
    {
        $session = Yii::$app->session;
        if(Yii::$app->user->identity->UserType == 2 || Yii::$app->user->identity->UserType == 1) {
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
        } else if(Yii::$app->user->identity->UserType == 2 || Yii::$app->user->identity->UserType == 1) {
            return [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['update','removeimage','removeprofileimage'],
                            'roles' => ['@'],
                        ],
                    ],
                ],
            ];
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
        $searchModel = new MymusictvSearch();
        if(isset($_GET['MymusictvSearch'])) {
            $searchModel->attributes = $_GET['MymusictvSearch'];
        }
        return $this->render('index', [            
            'model' => $searchModel, 
        ]);
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
        $model              =   new Mymusictv();
        $model->scenario    =   'create';
        $modelUser          =   new \backend\models\User();
        if(Yii::$app->user->identity->UserType == 1)
        {
            if(isset($_POST['Mymusictv']['ArtistID']))
            $artistID       =   $_POST['Mymusictv']['ArtistID'];
            else
              $artistID = "";  
        }
        else
        {
            $user               =   $modelUser->findAll(array("UserID"=>Yii::$app->user->identity->UserID));
            $artistID           =   $user[0]['ArtistID'];
        }

        if($model->load(Yii::$app->request->post())) 
        {
            if($model->validate()) 
            {
                $model->ArtistID    =   $artistID;
                $model->Created     =   date("Y-m-d H:i:s");
                $filesArray         =   $_FILES['Mymusictv']['tmp_name']['AlbumImage'];
                $filesNameArray     =   $_FILES['Mymusictv']['name']['AlbumImage'];    
                $albumImage         =   $filesArray;
                $previousFileName   =   str_replace(' ','_',$filesNameArray);
                $actualImageName    =   'album_'.time()."_".$previousFileName;
                $model->AlbumImage  =   $actualImageName;
                $model->Status      =   $_POST['Mymusictv']['Status'];
                if(!$model->save(false)) 
                {
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }

                $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                if(\S3::putObjectFile($albumImage,S3BUCKET,BOOM.$artistID.ALBUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ))
                {
                    $content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$artistID.ALBUM_IMAGES.$actualImageName);
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
                    
                    \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.ALBUM_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                    \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$artistID.ALBUM_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                }
                
                Yii::$app->session->setFlash('success', 'Album has been added successfully');
                return $this->redirect('index');
                //return $this->redirect('update?id='.$artistID);
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
        $model = $this->findModel($id);
        $model->scenario = 'update';
        $oldImage = $model->AlbumImage;   
        $artistID   =   $model->ArtistID;
        if ($model->load(Yii::$app->request->post()))
        {
            if($model->validate())
            {
                $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                $model->Updated = date("Y-m-d H:i:s");
                $model->Status  =   $_POST['Mymusictv']['Status'];
                
                if(isset($_FILES['Mymusictv']['tmp_name']['AlbumImage']) && $_FILES['Mymusictv']['tmp_name']['AlbumImage']!='') 
                {
                    $profileImage = $_FILES['Mymusictv']['tmp_name']['AlbumImage'];
                    $filesNameArray = pathinfo($_FILES['Mymusictv']['name']['AlbumImage']);
                    $fileExtension = $filesNameArray['extension'];
                    $model->save();
                    //$stickerID  = Yii::$app->db->getLastInsertID();
                    $actualImageName = 'album_'.$id.'_'.time().'.'.$fileExtension;
                    
                     
                    if(\S3::putObjectFile($profileImage,S3BUCKET,BOOM.$artistID.ALBUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ)) 
                    {
                       
                        $model->AlbumImage = $actualImageName;
                        $model->save();
                        $content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$artistID.ALBUM_IMAGES.$actualImageName);
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

                        \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.ALBUM_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                        \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$artistID.ALBUM_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                    }
                } 
                else 
                {
                    $model->AlbumImage = $oldImage;
                }    
                $model->save();
                Yii::$app->session->setFlash('success', 'Album has been updated successfully');
                return $this->redirect('index');
            }
            else
            {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        } 
        else
        {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    
    public function actionRemoveimage($id) {
        if((int)$id) {
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
            $modelGallery = new \backend\models\Gallery();  
            $condition = array("ID"=>$id);
            $galleryData = $modelGallery->findAll($condition);
            $artistID = $galleryData[0]['ArtistID'];
            $imageName = BOOM.$artistID.ALBUM_IMAGES.$galleryData[0]['Image']; 
            $deleteObject = \S3::deleteObject(S3BUCKET,$imageName);
            $modelGallery->deleteAll($condition);
            Yii::$app->session->setFlash('success', 'Image has been deleted successfully');
            return $this->redirect('update?id='.$artistID);
        }
    }
    
    public function actionRemoveprofileimage($id) {
        if((int)$id) {
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
            $model = Mymusictv::findOne($id);
            $imageName = BOOM.Yii::$app->session->get('ArtistID').ALBUM_IMAGES.$model->AlbumImage; 
            $imageName2 = BOOM.Yii::$app->session->get('ArtistID').ALBUM_THUMB_IMAGES.$model->AlbumImage; 
            $imageName3 = BOOM.Yii::$app->session->get('ArtistID').ALBUM_MEDIUM_IMAGES.$model->AlbumImage; 
            $deleteObject = \S3::deleteObject(S3BUCKET,$imageName);
            $deleteObject2 = \S3::deleteObject(S3BUCKET,$imageName2);
            $deleteObject3 = \S3::deleteObject(S3BUCKET,$imageName3);
            $model->AlbumImage = '';
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
        $model = Mymusictv::find()->select("*")
	    ->where('ID ='.$id)
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
}
