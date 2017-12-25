<?php

namespace backend\controllers;

require('Imagemagician.php');

use Yii;
use backend\models\Sticker;
use backend\models\StickerSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use backend\models\Artist;
use backend\models\ArtistSearch;
use yii\helpers\Url;
use yii\filters\AccessControl;
use backend\models\Stickers_artist;

/**
 * StickerController implements the CRUD actions for Sticker model.
 */
class StickerController extends Controller
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
     * Lists all Sticker models.
     * @return mixed
     */
    public function actionIndex()
    {
        /************** Get sticker List *************/
        /*$searchModel = new StickerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);*/
        $searchModel = new StickerSearch();
		if(Yii::$app->user->identity->UserType == 4) {
			$searchModel->CompanyID = Yii::$app->session->get('CompanyID');
		}
        if(isset($_GET['StickerSearch'])) {
            $searchModel->attributes = $_GET['StickerSearch'];
        }
        return $this->render('index', [            
            'model' => $searchModel,
            
        ]);
    }

    /**
     * Displays a single Sticker model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    
    /*public function cutNum( $number, $separator = '.', $format = 2 ){
        $response = '';
        $brokenNumber = explode( $separator, $number );
        $response = $brokenNumber[0] . $separator;
        if(isset($brokenNumber[1]) && $brokenNumber[1] !='') { 
            $brokenBackNumber = str_split($brokenNumber[1]);
            if( $format < count($brokenBackNumber) ){
                for( $i = 1; $i <= $format; $i++ )
                    $response .= $brokenBackNumber[$i];
            }
            return $response;
        } else {
            return $number;
        }   
    }*/

    /**
     * Creates a new Sticker model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /************** Create new sticekr *************/
        $model = new Sticker();
        $model->scenario = 'create';
        $model->Cost = "0";
		
		if(Yii::$app->user->identity->UserType == 2) {
			$userData = \backend\models\User::findAll(array("UserID"=>Yii::$app->user->identity->UserID));
            $artistID = $userData[0]['ArtistID'];
			$model->assignedArtists = $artistID;
        }
		
		
        if ($model->load(Yii::$app->request->post())) 
        {
            $model->StickerImage = "NULL";
            if($model->validate())
            {
                $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                $model->Created = date("Y-m-d H:i:s");
                $model->CreatedBy = Yii::$app->user->identity->UserID;
                if(Yii::$app->user->identity->UserType == 2) {
                    $userData = \backend\models\User::findAll(array("UserID"=>Yii::$app->user->identity->UserID));
                    $model->ArtistID    =   $userData[0]['ArtistID'];
                } else {
                    $model->ArtistID = $_POST['Sticker']['ArtistID'];
                }
                if(isset($_POST['Sticker']['IOSSKUID']) && $_POST['Sticker']['IOSSKUID'] != "")
                {
                    $model->IOSSKUID    =   $_POST['Sticker']['IOSSKUID'];
                }
                if(isset($_POST['Sticker']['AndroidSKUID']) && $_POST['Sticker']['AndroidSKUID'] != "")
                {
                    $model->AndroidSKUID    =   $_POST['Sticker']['AndroidSKUID'];
                }
                
                if(isset($_POST['Sticker']['Cost']) && $_POST['Sticker']['Cost'] != '0.00') {
                    $model->Cost = $_POST['Sticker']['Cost']; //$this->cutNum($_POST['Sticker']['Cost'],'.','2');
                } else {
                    $model->Cost = $_POST['Sticker']['Cost'];
                }
                if(!$model->save()) {
                    return $this->render('create', [
                        'model' => $model,
                        'stickerImages'=>array(),
                    ]); 
                }
                $totalImage = count($_FILES['Sticker']['name']['StickerImage']);
                $stickerID  = Yii::$app->db->getLastInsertID();
                $updateStickerImage = "";
                /************ Upload sticker image in s3 bucket **************/
                for($n=0;$n<$totalImage;$n++) {
                    if(isset($_FILES['Sticker']['tmp_name']['StickerImage'][$n]) && $_FILES['Sticker']['tmp_name']['StickerImage'][$n]!='') {
                        $profileImage = $_FILES['Sticker']['tmp_name']['StickerImage'][$n];
                        $filesNameArray = pathinfo($_FILES['Sticker']['name']['StickerImage'][$n]);
                        $fileExtension = $filesNameArray['extension'];
                    }
                    $actualImageName = 'sticker_'.$stickerID.'_'.time().'.'.$fileExtension;
                    if($n==0) {
                        $updateStickerImage = $actualImageName;
                    }
                    $artistID = $_POST['Sticker']['ArtistID'];

                    if(\S3::putObjectFile($profileImage,S3BUCKET,BOOM.$artistID.STICKER_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ)) {
                        $content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$artistID.STICKER_IMAGES.$actualImageName);
                        $commonthumb = "uploads/postlarge/".$actualImageName;
                        $createthumb = "uploads/postthumb/".$actualImageName;
                        $createmedium = "uploads/postmedium/".$actualImageName;
                        $createsmall  = "uploads/postsmall/".$actualImageName;
                        file_put_contents($commonthumb, $content);

                        $magicianObj = new \imageLib($commonthumb);
                        $magicianObj->resizeImage(THUMB_WIDTH_SIZE,THUMB_HEIGHT_SIZE,1);
                        $magicianObj->saveImage($createthumb, 100);

                        $magicianObj = new \imageLib($commonthumb);
                        $magicianObj->resizeImage(MEDIUM_WIDTH_SIZE,MEDIUM_HEIGHT_SIZE,1);
                        $magicianObj->saveImage($createmedium, 100);

                        $magicianObj = new \imageLib($commonthumb);
                        $magicianObj->resizeImage(STICKER_SMALL_SIZE,STICKER_SMALL_SIZE,1);
                        $magicianObj->saveImage($createsmall, 100);

                        \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.STICKER_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                        \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$artistID.STICKER_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                        \S3::putObjectFile($createsmall,S3BUCKET,BOOM.$artistID.STICKER_SMALL_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                        $modelStickerImage = new \backend\models\Stickerimage();
                        $modelStickerImage->Image = $actualImageName;
                        $modelStickerImage->StickerID = $stickerID;
						$modelStickerImage->Url = S3_BUCKETPATH.$artistID.STICKER_IMAGES.$actualImageName;
                        $modelStickerImage->save(false); 
                    }
                }
                if($updateStickerImage !="") {
                    $model = $this->findModel($stickerID);
                    $model->StickerImage = $updateStickerImage; 
                    $model->save(false);
                }   
				
				
				$selectedArtists= $_POST['Sticker']['assignedArtists'];
				if($selectedArtists != null){
					//insert new values
					if(Yii::$app->user->identity->UserType == 2) {
						$stickerArtist = new Stickers_artist();
						$stickerArtist->ArtistID = $selectedArtists;
						$stickerArtist->StickerID = $model->StickerID;
						$stickerArtist->save(false);
					}else{
						foreach($selectedArtists as $art){
							$stickerArtist = new Stickers_artist();
							$stickerArtist->ArtistID = $art;
							$stickerArtist->StickerID = $model->StickerID;
							$stickerArtist->save(false);
						}
					}
				}


				//Upload Video -- Daniele
				if($_FILES['Sticker']['name']['Call_Video_Url'] != null) 
                {
                    $filesArray     = $_FILES['Sticker']['tmp_name']['Call_Video_Url'];
                    $filesNameArray = $_FILES['Sticker']['name']['Call_Video_Url'];   
                    $profileImage = $filesArray;
                    $previousFileName = str_replace(' ','_',$filesNameArray);
                    $actualImageName = $stickerID.'_'.time().'_'.$previousFileName;
                    $originalPath = BOOM.$model->ArtistID.POST_VIDEOS.$actualImageName;
                    \S3::putObjectFile($profileImage,S3BUCKET,$originalPath,\S3::ACL_PUBLIC_READ); 
                    
                    $modelUpdate = Sticker::findOne($stickerID);
                    $modelUpdate->Call_Video_Url = S3_BUCKETABSOLUTE_PATH."/".$originalPath;
                    $modelUpdate->save(false); 
                }
				//-- Daniele


				
                Yii::$app->session->setFlash('success', 'Sticker has been added successfully');
                return $this->redirect('index');
            }
            else
            {
                return $this->render('create', [
                    'model' => $model,
                    'stickerImages'=>array(),
                ]);
            }    
        }
        else
        {
            return $this->render('create', [
                'model' => $model,
                'stickerImages'=>array(),
            ]);
        }
    }

    /**
     * Updates an existing Sticker model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        /************ Update stickr ***********/
        $model = $this->findModel($id);
        $model->scenario = 'update';
        $oldImage = $model->StickerImage; 
		$oldVideo = $model->Call_Video_Url; //Daniele	
		$model->getassignedArtists($id);//Daniele
		
		/*$myfile = fopen("C:/Program Files (x86)/EasyPHP-Webserver-14.1b2/www/api_ver2/runtime/logs/danieLog.log", "a+") or die("Unable to open file!");
		fwrite($myfile, "Selected Artists: ".$model->assignedArtists."\n");
		fclose($myfile);*/
		
		
        $artistArray = array();
        $IOSSKUArray = array();
        $androidSKUArray = array();
        $mappedData = array();
        $artistID = $model->ArtistID;
        $modelStickerImage = new \backend\models\Stickerimage();
        $stickerImages = $modelStickerImage->find()->where("StickerID=".$id." AND IsDelete !=1")->asArray()->all();	
        if ($model->load(Yii::$app->request->post()))
        {		
            if($model->validate())
            {
                $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                $model->Updated = date("Y-m-d H:i:s");
                $model->UpdatedBy = Yii::$app->user->identity->UserID;
                if(isset($_POST['Sticker']['IOSSKUID']) && $_POST['Sticker']['IOSSKUID'] != "")
                {
                    $model->IOSSKUID    =   $_POST['Sticker']['IOSSKUID'];
                }
                if(isset($_POST['Sticker']['AndroidSKUID']) && $_POST['Sticker']['AndroidSKUID'] != "")
                {
                    $model->AndroidSKUID    =   $_POST['Sticker']['AndroidSKUID'];
                }
                
                if(isset($_POST['Sticker']['Cost']) && $_POST['Sticker']['Cost'] != '0.00') {
                    $model->Cost = $_POST['Sticker']['Cost']; //$this->cutNum(,'.','2');
                } else {
                    $model->Cost = $_POST['Sticker']['Cost'];
                } 
				
				if(isset($_POST['Sticker']['Type'])) {
                    $model->Type = $_POST['Sticker']['Type']; //Daniele
                } 
                $model->save();
                $totalImage = count($_FILES['Sticker']['name']['StickerImage']);
                $updateStickerImage = "";
                
                /************ Upload sticker image in s3 bucket **************/
                
                if(isset($_FILES['Sticker']['name']['StickerImage']) && !empty($totalImage)) {
                    $stickerID  = Yii::$app->db->getLastInsertID();
                    for($n=0;$n<$totalImage;$n++) {
                        $fileExtension = "";
                        if(isset($_FILES['Sticker']['tmp_name']['StickerImage'][$n]) && $_FILES['Sticker']['tmp_name']['StickerImage'][$n]!='') {
                            $profileImage = $_FILES['Sticker']['tmp_name']['StickerImage'][$n];
                            $filesNameArray = pathinfo($_FILES['Sticker']['name']['StickerImage'][$n]);
                            $fileExtension = $filesNameArray['extension'];
                        }
                        if($fileExtension != "") {
                            $actualImageName = 'sticker_'.$stickerID.'_'.time().'.'.$fileExtension;
                            if($n == 0) {
                                $updateStickerImage = $actualImageName;
                            }
                            $artistID = $_POST['Sticker']['ArtistID'];

                            if(\S3::putObjectFile($profileImage,S3BUCKET,BOOM.$artistID.STICKER_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ)) {
                                $content = file_get_contents(S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$artistID.STICKER_IMAGES.$actualImageName);
                                $commonthumb = "uploads/postlarge/".$actualImageName;
                                $createthumb = "uploads/postthumb/".$actualImageName;
                                $createmedium = "uploads/postmedium/".$actualImageName;
                                $createsmall  = "uploads/postsmall/".$actualImageName;
                                file_put_contents($commonthumb, $content);

                                $magicianObj = new \imageLib($commonthumb);
                                $magicianObj->resizeImage(THUMB_WIDTH_SIZE,THUMB_HEIGHT_SIZE,1);
                                $magicianObj->saveImage($createthumb, 100);

                                $magicianObj = new \imageLib($commonthumb);
                                $magicianObj->resizeImage(MEDIUM_WIDTH_SIZE,MEDIUM_HEIGHT_SIZE,1);
                                $magicianObj->saveImage($createmedium, 100);

                                $magicianObj = new \imageLib($commonthumb);
                                $magicianObj->resizeImage(STICKER_SMALL_SIZE,STICKER_SMALL_SIZE,1);
                                $magicianObj->saveImage($createsmall, 100);

                                \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.STICKER_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$artistID.STICKER_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                \S3::putObjectFile($createsmall,S3BUCKET,BOOM.$artistID.STICKER_SMALL_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                $modelStickerImage = new \backend\models\Stickerimage();
                                $modelStickerImage->Image = $actualImageName;
                                $modelStickerImage->StickerID = $id;
								$modelStickerImage->Url = S3_BUCKETPATH.$artistID.STICKER_IMAGES.$actualImageName;
                                $modelStickerImage->save(false); 
                            }
                        }    
                    }    
                }   
                if($updateStickerImage !="") {
                    $model = $this->findModel($id);
                    $model->StickerImage = $updateStickerImage; 
                    $model->save(false);
                }
				
				$selectedArtists= $_POST['Sticker']['assignedArtists'];
				if($selectedArtists != null && Yii::$app->user->identity->UserType != 2){
					//Delete olds
					$stickerArtist = Stickers_artist::deleteAll(['StickerID' => $model->StickerID]);

					//re-insert new values
					foreach($selectedArtists as $art){
						$stickerArtist = new Stickers_artist();
						$stickerArtist->ArtistID = $art;
						$stickerArtist->StickerID = $model->StickerID;
						$stickerArtist->save(false);
					}
				}

				//Update uploaded video -- Daniele
				if($_FILES['Sticker']['name']['Call_Video_Url'] != null && $_FILES['Sticker']['name']['Call_Video_Url'] != "") {
                    $filesArray = $_FILES['Sticker']['tmp_name']['Call_Video_Url'];
                    $filesNameArray = $_FILES['Sticker']['name']['Call_Video_Url'];    
                    $profileImage = $filesArray;
                    if($profileImage !='') {
                        $previousFileName = str_replace(' ','_',$filesNameArray);
                        $actualImageName = $id.'_'.time().'_'.$previousFileName;
                        $originalPath = BOOM.$artistID.POST_VIDEOS.$actualImageName;
                        \S3::putObjectFile($profileImage,S3BUCKET,$originalPath,\S3::ACL_PUBLIC_READ); 
                        $modelUpdate = Sticker::findOne($id);
                        $modelUpdate->Call_Video_Url = S3_BUCKETABSOLUTE_PATH."/".$originalPath;
                        $modelUpdate->save(false); 
                    }    
                }else{
					$modelUpdate = Sticker::findOne($id);
                    $modelUpdate->Call_Video_Url = $oldVideo;
                    $modelUpdate->save(false);
				}

				//-- Daniele


				
                Yii::$app->session->setFlash('success', 'Sticker has been updated successfully');
                return $this->redirect('index');
            }
            else
            {
                return $this->render('update', [
                    'model' => $model,
                    'mappedData' => $mappedData,
                ]);
            }
        } 
        else
        {
            return $this->render('update', [
                'model' => $model,
                'mappedData' => $mappedData,
                'artistArray' => $artistArray,
                'IOSSKUArray'=>$IOSSKUArray,
                'androidSKUArray'=>$androidSKUArray,
                'stickerImages'=>$stickerImages,
            ]);
        }
    }
    
    public function actionRemovestickerimage($id) {
        /*************** Remove sticker image ****************/
        if((int)$id) {
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
            $model = \backend\models\Stickerimage::findOne($id);
            $imageName = BOOM.STICKER_THUMB_IMAGES.$model->Image; 
            $deleteObject = \S3::deleteObject(S3BUCKET,$imageName);
            $model->IsDelete = 1;
            $model->save(false);
            Yii::$app->session->setFlash('success', 'Image has been deleted successfully');
            return $this->redirect('update?id='.$model->StickerID);
        }
    }

    /**
     * Deletes an existing Sticker model.
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
     * Finds the Sticker model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Sticker the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        /**************** Get sticker data by sticker id ******************/
        if (($model = Sticker::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
