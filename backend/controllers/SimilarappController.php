<?php

namespace backend\controllers;
require('Imagemagician.php');
use Yii;
use backend\models\Applist;
use backend\models\Similarapp;
use backend\models\ApplistSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\filters\AccessControl;

/**
 * SimilarappController implements the CRUD actions for Similarapp model.
 */
class SimilarappController extends Controller
{
    public function behaviors()
    {
        /************** Restict action for artist and member  *******************/
        $session = Yii::$app->session;
        if(Yii::$app->user->identity->UserType == 1 || Yii::$app->user->identity->UserType == 4) {
            return [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index', 'create', 'update','delete','removeapp','removeimage'],
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
                            'actions' => [''],
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
        /************* Check if login or not ****************/
        if (\Yii::$app->getUser()->isGuest &&
            \Yii::$app->getRequest()->url !== Url::to(\Yii::$app->getUser()->loginUrl)
        ) {
            \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        return parent::beforeAction($action);
    }

    /**
     * Lists all Similarapp models.
     * @return mixed
     */
    public function actionIndex()
    {   
        /*********** Get App listing ************/
        $searchModel = new ApplistSearch();
		if(Yii::$app->user->identity->UserType == 4) {
			$searchModel->CompanyID = Yii::$app->session->get('CompanyID');
		}
        if(isset($_GET['ApplistSearch'])) {
            $searchModel->attributes = $_GET['ApplistSearch'];
        }
        return $this->render('index', [            
            'model' => $searchModel,
        ]);
    }

    /**
     * Displays a single Similarapp model.
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
     * Creates a new Similarapp model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /****************** Create new app ***************/
        $model = new Applist();
        if ($model->load(Yii::$app->request->post())) {
            if($model->validate()) {
                $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                $model->Created = date("Y-m-d H:i:s");
                $model->CreatedBy = Yii::$app->user->identity->UserID;
                $artistID = implode(',',$_POST['Applist']['ArtistID']);
                $model->ArtistID = $artistID;
                $model->Status = $_POST['Applist']['Status'];
                if(!$model->save()) {
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                $applistID = Yii::$app->db->getLastInsertID();
                $AppTitle = $_POST['AppTitle'];
                $IphoneURL = $_POST['IphoneURL'];
                $AndroidURL = $_POST['AndroidURL'];
                for($n=0;$n<count($AppTitle);$n++) {
                    $modelSimilarApp = new Similarapp();
                    $modelSimilarApp->ListID = $applistID;
                    $modelSimilarApp->Title = $AppTitle[$n];
                    $modelSimilarApp->AndroidUrl = $AndroidURL[$n];
                    $modelSimilarApp->IphoneUrl = $IphoneURL[$n];
                    $filesArray = $_FILES['AppLogo']['tmp_name'];
                    $filesNameArray = $_FILES['AppLogo']['name'];
                    
                    /*********** Upload app logo in s3 bucket **************/
                    if($filesArray[$n] != '') {
                        $appImage = $filesArray[$n];
                        $previousFileName = str_replace(' ','_',$filesNameArray[$n]);
                        $previousFileName = pathinfo($previousFileName,PATHINFO_EXTENSION);
                        $actualImageName = "similarapp_".$artistID."_".time().".".$previousFileName;
                        if(\S3::putObjectFile($appImage,S3BUCKET,SIMILAR_APP.$actualImageName, \S3::ACL_PUBLIC_READ)) 
                        {
                            $modelSimilarApp->AppIcon = $actualImageName;
                            $content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.SIMILAR_APP.$actualImageName);
                            $commonthumb = "uploads/postlarge/".$actualImageName;
                            $createthumb = "uploads/postthumb/".$actualImageName;                          
                            $createsmall  = "uploads/postsmall/".$actualImageName;
                            file_put_contents($commonthumb, $content);

                            $magicianObj = new \imageLib($commonthumb);
                            $magicianObj->resizeImage(THUMB_WIDTH_SIZE,THUMB_HEIGHT_SIZE,1);
                            $magicianObj->saveImage($createthumb, 100);


                            $magicianObj = new \imageLib($commonthumb);
                            $magicianObj->resizeImage(STICKER_SMALL_SIZE,STICKER_SMALL_SIZE,1);
                            $magicianObj->saveImage($createsmall, 100);

                            \S3::putObjectFile($createthumb,S3BUCKET,SIMILAR_APP_THUMB.$actualImageName, \S3::ACL_PUBLIC_READ);
                            \S3::putObjectFile($createsmall,S3BUCKET,SIMILAR_APP_MEDIUM.$actualImageName, \S3::ACL_PUBLIC_READ);                            \S3::putObjectFile($createsmall,S3BUCKET,STICKER_SMALL_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                        }
                    }    
                    $modelSimilarApp->save();
                }
            }
            Yii::$app->session->setFlash('success', 'Similar Applist has been added successfully');
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Similarapp model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        /************** Update app **************/
        $model = $this->findModel($id);
        $modelSimilarApp = new Similarapp();
        $condition = array('ListID'=>$id);
        $similarApp = $modelSimilarApp->findAll($condition);
        if ($model->load(Yii::$app->request->post())) {
            if($model->validate()) {
                $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                $model->Updated = date("Y-m-d H:i:s");
                $model->UpdatedBy = Yii::$app->user->identity->UserID;
                $artistID = implode(',',$_POST['Applist']['ArtistID']);
                $model->ArtistID = $artistID;
                $model->Status = $_POST['Applist']['Status'];
                if(!$model->save()) {
                    return $this->render('update', [
                        'model' => $model,
                    ]);
                }
                $AppTitle = "";
                $IphoneURL = "";
                $AndroidURL = "";
                $UpdateAppIcon = "";
                if(isset($_POST['AppTitle']) && $_POST['AppTitle']!="")
                    $AppTitle = $_POST['AppTitle'];
                if(isset($_POST['IphoneURL']) && $_POST['IphoneURL'] !="")
                    $IphoneURL = $_POST['IphoneURL'];
                if(isset($_POST['AndroidURL']) && $_POST['AndroidURL']!="")
                    $AndroidURL = $_POST['AndroidURL'];
                if(isset($_POST['UpdateAppIcon']) && $_POST['UpdateAppIcon']!="")
                    $UpdateAppIcon = $_POST['UpdateAppIcon'];
                $modelSimilarApp->deleteAll(array("ListID"=>$id));
                for($n=0;$n<count($AppTitle);$n++) {
                    $modelSimilarApp = new Similarapp();
                    $modelSimilarApp->ListID = $id;
                    if(isset($AppTitle[$n]) && $AppTitle[$n]!="")
                        $modelSimilarApp->Title = $AppTitle[$n];
                    if(isset($AndroidURL[$n]) && $AndroidURL[$n]!="")
                        $modelSimilarApp->AndroidUrl = $AndroidURL[$n];
                    if(isset($IphoneURL[$n]) && $IphoneURL[$n]!="")
                        $modelSimilarApp->IphoneUrl = $IphoneURL[$n];
                    $filesNameArray = array();
                    if(isset($_FILES['AppLogo']['tmp_name']) && $_FILES['AppLogo']['tmp_name']!="") {
                        $filesArray = $_FILES['AppLogo']['tmp_name'];
                        $filesNameArray = $_FILES['AppLogo']['name'];
                    }   
                    /*********** Upload app logo in s3 bucket **************/
                    if(isset($filesArray[$n]) && $filesArray[$n] != '') {
                        $appImage = $filesArray[$n];
                        $previousFileName = str_replace(' ','_',$filesNameArray[$n]);
                        $previousFileName = pathinfo($previousFileName,PATHINFO_EXTENSION);
                        $actualImageName = "similarapp_".$artistID."_".time().".".$previousFileName;
                        if(\S3::putObjectFile($appImage,S3BUCKET,SIMILAR_APP.$actualImageName, \S3::ACL_PUBLIC_READ))
                        {
                            $modelSimilarApp->AppIcon = $actualImageName;
                            $modelSimilarApp->AppIcon = $actualImageName;
                            $content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.SIMILAR_APP.$actualImageName);
                            $commonthumb = "uploads/postlarge/".$actualImageName;
                            $createthumb = "uploads/postthumb/".$actualImageName;                          
                            $createsmall  = "uploads/postsmall/".$actualImageName;
                            file_put_contents($commonthumb, $content);

                            $magicianObj = new \imageLib($commonthumb);
                            $magicianObj->resizeImage(THUMB_WIDTH_SIZE,THUMB_HEIGHT_SIZE,1);
                            $magicianObj->saveImage($createthumb, 100);


                            $magicianObj = new \imageLib($commonthumb);
                            $magicianObj->resizeImage(STICKER_SMALL_SIZE,STICKER_SMALL_SIZE,1);
                            $magicianObj->saveImage($createsmall, 100);

                            \S3::putObjectFile($createthumb,S3BUCKET,SIMILAR_APP_THUMB.$actualImageName, \S3::ACL_PUBLIC_READ);
                            \S3::putObjectFile($createsmall,S3BUCKET,SIMILAR_APP_MEDIUM.$actualImageName, \S3::ACL_PUBLIC_READ);
                        }
                    } else {
                        if(isset($UpdateAppIcon[$n]) && $UpdateAppIcon[$n]!='')
                            $modelSimilarApp->AppIcon = $UpdateAppIcon[$n];
                    } 
                    if(isset($AppTitle[$n]) && $AppTitle[$n]!="")
                        $modelSimilarApp->save();
                }
            }
            Yii::$app->session->setFlash('success', 'Similar Applist has been updated successfully');
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
                'similarApp' => $similarApp
            ]);
        }
    }
    
    public function actionRemoveimage($id,$appid) {
        /**************** Remove app image ***************/
        if((int)$id) {
            $model = \backend\models\Similarapp::findOne($id);
            $image = $model->AppIcon;
            $model->AppIcon = '';
            $model->save();
            $imageName = BOOM.SIMILAR_APP.$image; 
            $deleteObject = \S3::deleteObject(S3BUCKET,$imageName);
            
            Yii::$app->session->setFlash('success', 'Image has been deleted successfully');
            return $this->redirect('update?id='.$appid);
        }
    }

    /**
     * Deletes an existing Similarapp model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        
        return $this->redirect(['index']);
    }
    
    public function actionRemoveapp()
    {
        /************** Remove app **************/
        $id = $_GET['id'];
        $appID = $_GET['appid'];
        $modelSimilarApp = new Similarapp();
        $modelSimilarApp->deleteAll(array('AppID'=>$id));
        Yii::$app->session->setFlash('success', 'App has been removed successfully');
        return $this->redirect('update?id='.$appID);
    }

    /**
     * Finds the Similarapp model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Similarapp the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        /************** Get app data by app ID ************/
        if (($model = Applist::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
