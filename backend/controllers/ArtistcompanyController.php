<?php

namespace backend\controllers;
require('Imagemagician.php');
use Yii;
use backend\models\Artist_company;
use backend\models\Artist;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\db\Query;
use yii\data\ActiveDataProvider;

/**
 * ArtistCompanyController implements the CRUD actions for ArtistCompany model.
 */
class ArtistcompanyController extends Controller
{
    public function behaviors()
    {
        $session = Yii::$app->session;
        if(Yii::$app->user->identity->UserType == 1) {
            return [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index', 'create', 'update', 'delete', 'removeprofileimage'],
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
     * Lists all ArtistCompany models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Artist_company();
		
		$dataProvider = new ActiveDataProvider([
		'query' => Artist_company::find(),
		'pagination' => [
			'pageSize' => 20,
		],]);
		
        return $this->render('index', [            
            'model' => $model,
			'dataProvider' => $dataProvider,
        ]);
    }
    

    /**
     * Displays a single Artist_company model.
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
     * Creates a new Artist_comapny model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Artist_company();
		$model->Id = 0;
		$model->getassignedArtists(0);
        if($model->load(Yii::$app->request->post())) 
        {
            if($model->validate()) 
            {
				$model->AppDescription = $_POST['Artist_company']['AppDescription'];
				if(!$model->save(false)) 
                {
                    return $this->render('create', [
                        'model' => $model,
						'error' => 'yii\web\ErrorAction',
                    ]);
                }
                $id = Yii::$app->db->getLastInsertID();
				
				$selectedArtists= $_POST['Artist_company']['assignedArtists'];
				if($selectedArtists != null){

					//set all CompanyIDs for the group
					foreach($selectedArtists as $artID){
						$art = Artist::findOne($artID);
						$art->CompanyID = $id;
						$art->save(false);
					}
				}
				
				//Upload Profile image
				if(isset($_FILES['Artist_company']['name']['Profile_Image']) && $_FILES['Artist_company']['name']['Profile_Image'] != ""){
					$filesArray = $_FILES['Artist_company']['tmp_name']['Profile_Image'];
					$filesNameArray     = $_FILES['Artist_company']['name']['Profile_Image'];    
					$profileImage       = $filesArray;
					$previousFileName   = str_replace(' ','_',$filesNameArray);
					$previousFileName = pathinfo($previousFileName,PATHINFO_EXTENSION);
					$actualImageName = 'profile_'.time().".".str_replace(' ','_',$previousFileName);
					$s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
					if(\S3::putObjectFile($profileImage,S3BUCKET,"Companies/".BOOM.$id.PROFILE_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ))
					{
						$content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/Companies/'.BOOM.$id.PROFILE_IMAGES.$actualImageName);
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
						
						\S3::putObjectFile($createthumb,S3BUCKET,"Companies/".BOOM.$id.PROFILE_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
						\S3::putObjectFile($createmedium,S3BUCKET,"Companies/".BOOM.$id.PROFILE_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
						
						//save full path on db
						$model->Profile_Image = S3_BUCKETABSOLUTE_PATH.'/Companies/'.BOOM.$id.PROFILE_IMAGES.$actualImageName;
						$model->save(false);
					}
				}
				
			
                Yii::$app->session->setFlash('success', 'Artist Company has been added successfully');
                //return $this->redirect('index');
                return $this->redirect('update?id='.$id);
            } 
            else 
            {
                return $this->render('create', [
                    'model' => $model,
					'error' => 'yii\web\ErrorAction',
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
     * Updates an existing Artist_company model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
		$model->getassignedArtists($id);
		$oldProfileImage = $model->Profile_Image;

        if($model->load(Yii::$app->request->post())) {
            if($model->validate()) {
                $model->AppDescription = $_POST['Artist_company']['AppDescription'];
				if(!$model->save(false)) 
                {
                    return $this->render('update', [
                        'model' => $model,
						'error' => 'yii\web\ErrorAction',
                    ]);
                }
				
				$selectedArtists= $_POST['Artist_company']['assignedArtists'];
				if($selectedArtists != null){
					//reset to null all the CompanyIDs for the group
					$oldArtists = Artist::find()->where(['CompanyID' => $id])->all();
					foreach($oldArtists as $art){
						$art->CompanyID = null;
						$art->save(false);
					}

					//re-set all CompanyIDs for the group
					foreach($selectedArtists as $artID){
						$art = Artist::findOne($artID);
						$art->CompanyID = $id;
						$art->save(false);
					}
				}
				
				//Upload Profile image
				if(isset($_FILES['Artist_company']['name']['Profile_Image']) && $_FILES['Artist_company']['name']['Profile_Image'] != ""){
					$filesArray = $_FILES['Artist_company']['tmp_name']['Profile_Image'];
					$filesNameArray     = $_FILES['Artist_company']['name']['Profile_Image'];    
					$profileImage       = $filesArray;
					$previousFileName   = str_replace(' ','_',$filesNameArray);
					$previousFileName = pathinfo($previousFileName,PATHINFO_EXTENSION);
					$actualImageName = 'profile_'.time().".".str_replace(' ','_',$previousFileName);
					$s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
					if(\S3::putObjectFile($profileImage,S3BUCKET,"Companies/".BOOM.$id.PROFILE_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ))
					{
						$content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/Companies/'.BOOM.$id.PROFILE_IMAGES.$actualImageName);
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
						
						\S3::putObjectFile($createthumb,S3BUCKET,"Companies/".BOOM.$id.PROFILE_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
						\S3::putObjectFile($createmedium,S3BUCKET,"Companies/".BOOM.$id.PROFILE_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
						
						//save full path on db
						$model->Profile_Image = S3_BUCKETABSOLUTE_PATH.'/Companies/'.BOOM.$id.PROFILE_IMAGES.$actualImageName;
					}
				}else{
					$model->Profile_Image = $oldProfileImage;
				}
				$model->save(false);
				
                Yii::$app->session->setFlash('success', 'Artist Company has been updated successfully');
                return $this->redirect('update?id='.$id);
            }
        } 
        return $this->render('update', [
            'model' => $model,
        ]);
    }
	
	
	
	public function actionRemoveprofileimage($id) {
        /******************** Remove profile image ******************/
        if((int)$id) {
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
            $model = Artist_company::findOne($id);
			$strsplit = explode("/",$model->Profile_Image);
            $imageName = end($strsplit); 
            $deleteObject = \S3::deleteObject(S3BUCKET,"Companies/".BOOM.$id.PROFILE_IMAGES.$imageName);
            $model->Profile_Image = '';
            $model->save(false);
            Yii::$app->session->setFlash('success', 'Image has been deleted successfully');
            return $this->redirect('update?id='.$id);
        }
    }
	

    /**
     * Deletes an existing Artist_company model.
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
     * Finds the Artist_company model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Artist the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Artist_company::findOne($id);
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
}
