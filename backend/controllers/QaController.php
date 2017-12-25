<?php
namespace backend\controllers;

require('Imagemagician.php');

use Yii;
use backend\models\Qapost;
use backend\models\ExclusivePrice;
use backend\models\QapostSearch;
use backend\models\LikeSearch;
use backend\models\ShareSearch;
use backend\models\CommentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;

/**
 * PostController implements the CRUD actions for Post model.
 */
class QaController extends Controller
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
        if ($this->action->id == 'updatevideo') {
            Yii::$app->controller->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    /**
     * Lists all Post models.
     * @return mixed
     */
    public function actionIndex()
    {
        /**************** Get QA List ****************/
        $searchModel = new QapostSearch();
		if(Yii::$app->user->identity->UserType == 4) {
			$searchModel->CompanyID = Yii::$app->session->get('CompanyID');
		}
        if(isset($_GET['QapostSearch'])) {
            $searchModel->attributes = $_GET['QapostSearch'];
        }
        return $this->render('index', [            
            'model' => $searchModel,
        ]);
    }
    
    public function actionLike()
    {
        $searchModel = new LikeSearch();
        if(isset($_GET['LikeSearch'])) {
            $searchModel->attributes = $_GET['LikeSearch'];
        }
        return $this->render('like', [            
            'model' => $searchModel,
        ]);
    }
    
    public function actionComment()
    {
        $searchModel = new CommentSearch();
        if(isset($_GET['CommentSearch'])) {
            $searchModel->attributes = $_GET['CommentSearch'];
        }
        return $this->render('comment', [            
            'model' => $searchModel,
        ]);
    }
    
    public function actionShare()
    {
        $searchModel = new ShareSearch();
        if(isset($_GET['ShareSearch'])) {
            $searchModel->attributes = $_GET['ShareSearch'];
        }
        return $this->render('share', [            
            'model' => $searchModel,
        ]);
    }
    
    public function actionTabs()
    {
        return $this->render('tabs');
    }

    /**
     * Displays a single Post model.
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
     * Creates a new Post model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Qapost();
        $modelExclusivePrice = new ExclusivePrice();
        $model->scenario  = 'qacreate';
        if($model->load(Yii::$app->request->post())) 
        {    
            if($model->validate()) 
            {
                $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                $pricelist = $modelExclusivePrice->findAll(array("ExclusivePriceID"=>"1"));
                $statusPrice = $pricelist[0]->StatusPrice;
                $imagePrice = $pricelist[0]->ImagePrice;
                $videoPrice = $pricelist[0]->VideoPrice;
                $postType = $_POST['Post']['PostType'];
                $isExclusive = $_POST['Post']['IsExclusive'];
                if($isExclusive == 1) 
                {
                    if(isset($_POST['Post']['Price']) && $_POST['Post']['Price'] != "")
                    {
                        $model->Price   =   $_POST['Post']['Price'];
                    }
                    if(isset($_POST['Post']['ProductSKUID']) && $_POST['Post']['ProductSKUID'] != "")
                    {
                        $model->ProductSKUID   =   $_POST['Post']['ProductSKUID'];
                    }
                    
                }
                else 
                {
                    $model->IsExclusive = "0";
                }   
                if($postType != '1')
                {
                    $model->PostTitle = $_POST['Post']['PostTitle'];
                }
                else 
                {
                    $model->PostTitle = '';
                }
                $artistID = $_POST['Post']['ArtistID'];
                $model->Created = date("Y-m-d H:i:s");
                $model->CreatedBy = Yii::$app->user->identity->UserID;
                if(!$model->save())
                {
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                $postID = Yii::$app->db->getLastInsertID();
                if($postType == '3') 
                {
                    $filesArray     = $_FILES['Post']['tmp_name']['PostVideo'];
                    $filesNameArray = $_FILES['Post']['name']['PostVideo'];   
                    $profileImage = $filesArray;
                    $previousFileName = str_replace(' ','_',$filesNameArray);
                    $actualImageName = $postID.'_'.time().'_'.$previousFileName;
                    $originalPath = BOOM.$artistID.POST_VIDEOS.$actualImageName;
                    \S3::putObjectFile($profileImage,S3BUCKET,$originalPath,\S3::ACL_PUBLIC_READ); 
                    
                    $modelUpdate = Post::findOne($postID);
                    $modelUpdate->URL = $originalPath;
                    
                    $thumbimgarray          =   $_FILES['Post']['tmp_name']['PostThumbImageVideo'];
                    $thumbnamearray         =   $_FILES['Post']['name']['PostThumbImageVideo'];
                    $thumbImage             =   $thumbimgarray;
                    $previousthumbName      =   str_replace(' ','_',$thumbnamearray);
                    $actualthumbImageName   =   'post_'.$postID.'_'.time().'_'.$previousthumbName;
                    $originalthumbPath      =   BOOM.$artistID.POST_THUMBIMAGE_VIDEOS.$actualthumbImageName;
                    \S3::putObjectFile($thumbImage,S3BUCKET,$originalthumbPath,\S3::ACL_PUBLIC_READ); 
                    $modelUpdate->VideoThumbImage=$actualthumbImageName;
                    $content        = file_get_contents(S3_BUCKETABSOLUTE_PATH."/".BOOM.$artistID.POST_THUMBIMAGE_VIDEOS.$actualthumbImageName);
                    $commonthumb = "uploads/postlarge/".$actualthumbImageName;
                    $createthumb = "uploads/postthumb/".$actualthumbImageName;
                    file_put_contents($commonthumb, $content);

                    $magicianObj = new \imageLib($commonthumb);
                    $magicianObj->resizeImage(200,100,1);
                    $magicianObj->saveImage($createthumb, 100);

                    \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.POST_BLURTHUMBIMAGE_VIDEOS.$actualthumbImageName, \S3::ACL_PUBLIC_READ);
                    $modelUpdate->save(); 
                }
                if($postType == '2') 
                {
                    $filesArray = $_FILES['Post']['tmp_name']['PostImages'];
                    $filesNameArray = $_FILES['Post']['name']['PostImages'];
                    
                    /********************** For Thumb Images *****************/
                    $filesThumb = $_FILES['Post']['tmp_name']['ThumbImage'];
                    $filesThumbName = $_FILES['Post']['name']['ThumbImage'];
                    //$previousThumbFileName = str_replace(' ','_',$filesThumbName[0]);
                    $previousThumbFileName = pathinfo($filesThumbName[0],PATHINFO_EXTENSION);
                    $actualThumbImageName = 'post_'.$postID.'_'.time().'.'.$previousThumbFileName;

                    \S3::putObjectFile($filesThumb[0],S3BUCKET,BOOM.$artistID.POST_THUMB_IMAGES.$actualThumbImageName, \S3::ACL_PUBLIC_READ);
                    \S3::putObjectFile($filesThumb[0],S3BUCKET,BOOM.$artistID.POST_MEDIUM_IMAGES.$actualThumbImageName, \S3::ACL_PUBLIC_READ);
                    
                    $connection = \Yii::$app->db;
                    $connection->createCommand()
                               ->update('post', array("ThumbImage"=>$actualThumbImageName),'PostID = '.$postID)
                               ->execute();
                    /*********************************************************/
                    
                    for($n=0; $n<count($filesArray); $n++) 
                    {
                        if($filesArray[$n] != '') 
                        {
                            $profileImage = $filesArray[$n];
                            //$previousFileName = str_replace(' ','_',$filesNameArray[$n]);
                            $previousFileName = pathinfo($filesNameArray[$n],PATHINFO_EXTENSION);
                            $actualImageName = 'post_'.$postID.'_'.time().'.'.$previousFileName;
                            if(\S3::putObjectFile($profileImage,S3BUCKET,BOOM.$artistID.POST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ)) 
                            {
                                $modelGallery = new \backend\models\Gallery();  
                                $modelGallery->Title =   $previousFileName;
                                $modelGallery->ArtistID  = $artistID;
                                $modelGallery->RefTableID = $postID;
                                $modelGallery->RefTableType = '1';
                                $modelGallery->Type = '1';
                                $modelGallery->Image = $actualImageName;
                                $modelGallery->Created = date("Y-m-d H:i:s");
                                $modelGallery->CreatedBy = Yii::$app->user->identity->UserID;
                                $modelGallery->save();  
                            }
                        }    
                    }
                }    
                
                Yii::$app->session->setFlash('success', 'Post has been added successfully');
                return $this->redirect('index');
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
    
    
    public function actionCreateajax()
    {
        $model = new Qapost();
        $model->scenario  = 'qacreate';
        if ($model->load(Yii::$app->request->post())) 
        {
            if($model->validate())
            {
                if(isset($_POST['Qapost']['Description']))
                    $model->Description = $_POST['Qapost']['Description'];
                if(isset($_POST['Qapost']['Reply']))
                    $model->Reply = $_POST['Qapost']['Reply'];
                $model->QAType = $_POST['Qapost']['PostType'];
                $model->PostType = "4";
                $artistID = $_POST['Qapost']['ArtistID'];
                $model->Created = date("Y-m-d H:i:s");
                $model->CreatedBy = Yii::$app->user->identity->UserID;
                $model->save();
                $postID = Yii::$app->db->getLastInsertID();
                Yii::$app->session->setFlash('success', 'Q&A has been added successfully');
                echo json_encode(array("status"=>"ok","id"=>$postID,"ArtistID"=>$artistID)); die;
            } 
            else
            {
                echo json_encode(array("status"=>"error")); die;
            }
        }
    }    
    
    public function actionUpdateajax() {
        /************** Answer the question by artist ***************/
		$id = $_POST['id'];
        $model = $this->findModel($id);
        $modelBlock = new \backend\models\Blockuser();
        $model->scenario = 'qaupdate';
        $postType = $model->PostType;
        $artistID = $model->ArtistID;
        $oldMemberID  = $model->MemberID;
        $oldArtistID = $model->ArtistID;
        if ($model->load(Yii::$app->request->post())) {
            if($model->validate()) {
                if(isset($_POST['Qapost']['Reply']))
                    $model->Reply = $_POST['Qapost']['Reply'];
                if(isset($_POST['Qapost']['QAIgnore']))
                    $model->QAIgnore = $_POST['Qapost']['QAIgnore'];
                if(isset($_POST['Qapost']['IsBlocked']))
                    $model->IsBlocked = $_POST['Qapost']['IsBlocked'];
                if(isset($_POST['Qapost']['BlockUser']) &&  $_POST['Qapost']['BlockUser'] == '1') :
                    $modelBlock->MemberID = $oldMemberID;
                    $modelBlock->ArtistID  = $oldArtistID;
                    $modelBlock->IsBlocked = '1';
                    $modelBlock->save();
                else :
                    if($oldMemberID != "")
                        $modelBlock->deleteAll(" MemberID = ".$oldMemberID." AND ArtistID = ".$oldArtistID." ");
                endif;
				if(isset($_POST['Qapost']['IsPublic']))
                    $model->IsPublic = $_POST['Qapost']['IsPublic'];	
				if(isset($_POST['Qapost']['RequestedPrivacy']))
                    $model->RequestedPrivacy = $_POST['Qapost']['RequestedPrivacy'];		
				if(isset($_POST['Qapost']['TextReply']))
                    $model->TextReply = $_POST['Qapost']['TextReply'];
                $model->Updated = date("Y-m-d H:i:s");
                $model->UpdatedBy = Yii::$app->user->identity->UserID;
                $model->save();				
				
				
                Yii::$app->session->setFlash('success', 'Q&A has been updated successfully');
                echo json_encode(array("status"=>"ok","id"=>$id,"ArtistID"=>$artistID)); die;
            } else {
                echo json_encode(array("status"=>"error")); die;
            }
        } 
    }
    
    public function actionUpdatevideo() 
    {
        $type   =   $_POST['type'];
        $fileName = $_POST['filename'];
        $postID = $_POST['id'];
        $modelUpdate = Qapost::findOne($postID);
        if($type == "video")
        {
            $modelUpdate->URL = $fileName;
            $modelUpdate->WebStreamUrl = '';
            $modelUpdate->MobileStreamUrl = '';
        }
        $modelUpdate->save(); 
        echo json_encode(array("status"=>"ok")); die;
    }
    
    public function actionUpdaterelplyvideo() {
        /*********** Once artist will reply the QA video URL will be stored in Reply **************/
        $type   =   $_POST['type'];
        $fileName = $_POST['filename'];
        $postID = $_POST['id'];
        $modelUpdate = Qapost::findOne($postID);
        if($type == "video")
        {
            $modelUpdate->Reply = $fileName;
            $modelUpdate->ReplyStreamURL = '';
        }
        $modelUpdate->save(); 
        echo json_encode(array("status"=>"ok")); die;
    }
    public function actionUpdateimage() 
    {
        $type   =   $_POST['type'];
        if(isset($_POST['thumbfilename']) && $_POST['thumbfilename'] != "")
            $thumbfileName = $_POST['thumbfilename'];
        else
            $thumbfileName = "";
        $postID = $_POST['id'];
        $modelUpdate = Qapost::findOne($postID);
        if($type == "image")
        {
            $modelUpdate->VideoThumbImage = $thumbfileName;
        }		
        $modelUpdate->save();
		
		
		if(isset($_POST['imgUrl']) && $_POST['imgUrl']!="") {
            $imgUrl = S3_BUCKETABSOLUTE_PATH."/".$_POST['imgUrl']; 
            $connection = \Yii::$app->db;
			$connection->createCommand()->update('post', array("ThumbImage"=>$imgUrl),'PostID = '.$postID)->execute();
        }
		
        echo json_encode(array("status"=>"ok")); die;
    }
    
    public function actionUpdatereplyimage() {	
        /************* Add Reply Thumb Image *****************/
		$type   =   $_POST['type'];
        if(isset($_POST['thumbfilename']) && $_POST['thumbfilename'] != "")
            $thumbfileName = $_POST['thumbfilename'];
        else
            $thumbfileName = "";
        $postID = $_POST['id'];
        $modelUpdate = Qapost::findOne($postID);
        if($type == "image")
        {
            $modelUpdate->ReplyThumbImage = $thumbfileName;
        }
        if(isset($_POST['width']) && $_POST['width']!="") {
            $width = $_POST['width']; 
            $modelUpdate->ReplyWidth = $width;
        }
        if(isset($_POST['height']) && $_POST['height']!="") {
            $height = $_POST['height']; 
            $modelUpdate->ReplyHeight = $height;
        }
		if(isset($_POST['imgUrl']) && $_POST['imgUrl']!="") {
            $imgUrl = S3_BUCKETABSOLUTE_PATH."/".$_POST['imgUrl']; 
            $modelUpdate->ReplyImage = $imgUrl;
        }
        $modelUpdate->save(); 
        echo json_encode(array("status"=>"ok")); die;
    }
	

    /**
     * Updates an existing Post model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $sql = 'SELECT * FROM post WHERE PostID ='.$id;
        $connection = \Yii::$app->db;
	$postData = $connection->createCommand($sql)->queryAll();
        $thumbImage = $postData[0]['ThumbImage'];
        $model->ThumbImage = $thumbImage;
        $modelExclusivePrice = new ExclusivePrice();
        $model->scenario = 'qaupdate';
        $modelGallery = new \backend\models\Gallery();  
        $conditionForGallery = array("RefTableID"=>$id);
        $galleryData = $modelGallery->findAll($conditionForGallery);
        $postType = $model->PostType;
        $artistID = $model->ArtistID;
        $model->PostType  = $model->QAType;
        $modelBlock = \backend\models\Blockuser::findOne(["MemberID"=>$model->MemberID,"ArtistID"=>$model->ArtistID]);
        if($modelBlock != null) {
            $model->BlockUser = "1";
        }
		
        if ($model->load(Yii::$app->request->post())) {
			
			if($model->validate()) {
				
				$s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                $pricelist = $modelExclusivePrice->findAll(array("ExclusivePriceID"=>"1"));
                $statusPrice = $pricelist[0]->StatusPrice;
                $imagePrice = $pricelist[0]->ImagePrice;
                $videoPrice = $pricelist[0]->VideoPrice;
                    if(isset($_POST['Post']['Price']) && $_POST['Post']['Price'] != "")
                    {
                        $model->Price   =   $_POST['Post']['Price'];
                    }
                    if(isset($_POST['Post']['ProductSKUID']) && $_POST['Post']['ProductSKUID'] != "")
                    {
                        $model->ProductSKUID   =   $_POST['Post']['ProductSKUID'];
                    }
                 
                if($postType != '1') {
                    $model->PostTitle = $_POST['Post']['PostTitle'];
                } else {
                    $model->PostTitle = '';
                }
                $model->Updated = date("Y-m-d H:i:s");
                $model->UpdatedBy = Yii::$app->user->identity->UserID;
                if(!$model->save()) {
                    return $this->render('update', [
                        'model' => $model,
                        'galleryData' => $galleryData,
                    ]);
                }
				
                /********************** For Thumb Images *****************/
                if($postType == '3') {
                    $filesArray = $_FILES['Post']['tmp_name']['PostVideo'];
                    $filesNameArray = $_FILES['Post']['name']['PostVideo'];    
                    $profileImage = $filesArray;
                    if($profileImage !='') {
                        $previousFileName = str_replace(' ','_',$filesNameArray);
                        $actualImageName = $id.'_'.time().'_'.$previousFileName;
                        $originalPath = BOOM.$artistID.POST_VIDEOS.$actualImageName;
                        \S3::putObjectFile($profileImage,S3BUCKET,$originalPath,\S3::ACL_PUBLIC_READ); 
                        $modelUpdate = Post::findOne($id);
                        $modelUpdate->URL = $originalPath;
                        $modelUpdate->save(); 
                    }    
                }
				
                if($postType == '2') 
                {
                    $filesArray = $_FILES['Post']['tmp_name']['PostImages'];
                    $filesNameArray = $_FILES['Post']['name']['PostImages'];
                    
                    /********************** For Thumb Images *****************/
                    if(isset($_FILES['Post']['tmp_name']['ThumbImage'][0]) && $_FILES['Post']['tmp_name']['ThumbImage'][0] !='') {
                        $filesThumb = $_FILES['Post']['tmp_name']['ThumbImage'];
                        $filesThumbName = $_FILES['Post']['name']['ThumbImage'];
                        //$previousThumbFileName = str_replace(' ','_',$filesThumbName[0]);
                        $previousThumbFileName = pathinfo($filesThumbName[0],PATHINFO_EXTENSION);
                        $actualThumbImageName = 'post_'.$id.'_'.time().'.'.$previousThumbFileName;

                        \S3::putObjectFile($filesThumb[0],S3BUCKET,BOOM.$artistID.POST_THUMB_IMAGES.$actualThumbImageName, \S3::ACL_PUBLIC_READ);
                        \S3::putObjectFile($filesThumb[0],S3BUCKET,BOOM.$artistID.POST_MEDIUM_IMAGES.$actualThumbImageName, \S3::ACL_PUBLIC_READ);

                        $connection = \Yii::$app->db;
                        $connection->createCommand()
                                   ->update('post', array("ThumbImage"=>$actualThumbImageName),'PostID = '.$id)
                                   ->execute();
                    }    
                    /*********************************************************/
                    
                    for($n=0; $n<count($filesArray); $n++) 
                    {
                        if($filesArray[$n] != '') 
                        {
                            $profileImage = $filesArray[$n];
                            //$previousFileName = str_replace(' ','_',$filesNameArray[$n]);
                            $previousFileName = pathinfo($filesNameArray[$n],PATHINFO_EXTENSION);
                            $actualImageName = 'post_'.$id.'_'.time().'.'.$previousFileName;
                            if(\S3::putObjectFile($profileImage,S3BUCKET,BOOM.$artistID.POST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ)) 
                            {
                                $modelGallery = new \backend\models\Gallery();  
                                $modelGallery->Title =   $previousFileName;
                                $modelGallery->ArtistID  = $artistID;
                                $modelGallery->RefTableID = $id;
                                $modelGallery->RefTableType = '1';
                                $modelGallery->Type = '1';
                                $modelGallery->Image = $actualImageName;
                                $modelGallery->Created = date("Y-m-d H:i:s");
                                $modelGallery->CreatedBy = Yii::$app->user->identity->UserID;
                                $modelGallery->save();  
                            }
                        }    
                    }
                } 
                
                Yii::$app->session->setFlash('success', 'Post has been updated successfully');
                return $this->redirect('index');
            } else {
				return $this->render('update', [
                    'model' => $model,
                    'galleryData' => $galleryData,
                ]);
            }
        } else {
            return $this->render('update', [
                'model' => $model,
                'galleryData' => $galleryData,
            ]);
        }
    }
    
    public function actionRemoveimage($id) {
        /**************** Remove Image ***********/
        if((int)$id) {
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
            $modelGallery = new \backend\models\Gallery();  
            $condition = array("ID"=>$id);
            $galleryData = $modelGallery->findAll($condition);
            $artistID = $galleryData[0]['ArtistID'];
            $refTableID = $galleryData[0]['RefTableID'];
            $imageName = BOOM.$artistID.POST_IMAGES.$galleryData[0]['Image']; 
            $deleteObject = \S3::deleteObject(S3BUCKET,$imageName);
            $modelGallery->deleteAll($condition);
            Yii::$app->session->setFlash('success', 'Image has been deleted successfully');
            return $this->redirect('update?id='.$refTableID);
        }
    }
    
    public function actionRemovethumbimage($id) {
        /************** Remove Thumb Image ***********/
        if((int)$id) {
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
            $model = Post::findOne($id);
            $artistID = $model->ArtistID;
            $imageName = BOOM.$artistID.POST_THUMB_IMAGES.$model->ThumbImage; 
            $deleteObject = \S3::deleteObject(S3BUCKET,$imageName);
            $connection = \Yii::$app->db;
            $connection->createCommand()
                       ->update('post', array("ThumbImage"=>''),'PostID = '.$id)
                       ->execute();
            Yii::$app->session->setFlash('success', 'Image has been deleted successfully');
            return $this->redirect('update?id='.$id);
        }
    }
    
    public function actionRemovevideothumbimage($id) {
        /********** Remove video thumb image ***************/
        if((int)$id) {
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
            $model = Post::findOne($id);
            $artistID = $model->ArtistID;
            $imageName = BOOM.$artistID.POST_THUMB_IMAGES.$model->VideoThumbImage; 
            $deleteObject = \S3::deleteObject(S3BUCKET,$imageName);
            $connection = \Yii::$app->db;
            $connection->createCommand()
                       ->update('post', array("VideoThumbImage"=>''),'PostID = '.$id)
                       ->execute();
            Yii::$app->session->setFlash('success', 'Image has been deleted successfully');
            return $this->redirect('update?id='.$id);
        }
    }

    /**
     * Deletes an existing Post model.
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
     * Finds the Post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        /************ Get QA data by Post ID **************/
        if (($model = Qapost::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
