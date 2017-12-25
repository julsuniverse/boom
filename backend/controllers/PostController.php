<?php
namespace backend\controllers;

require('Imagemagician.php');

use Yii;
use backend\models\Post;
use backend\models\ExclusivePrice;
use backend\models\PostSearch;
use backend\models\LikeSearch;
use backend\models\ShareSearch;
use backend\models\CommentSearch;
use backend\models\PostPages;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\data\ActiveDataProvider;

/**
 * PostController implements the CRUD actions for Post model.
 */
class PostController extends Controller
{
    public function behaviors()
    {
        if(Yii::$app->user->identity->UserType == 3) {
            return \Yii::$app->getResponse()->redirect(array('/site/logout',302));
        }
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post','get'],
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
        $searchModel = new PostSearch();
		if(Yii::$app->user->identity->UserType == 4) {
			$searchModel->CompanyID = Yii::$app->session->get('CompanyID');
		}
        if(isset($_GET['PostSearch'])) {
            $searchModel->attributes = $_GET['PostSearch'];
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
	
	
	    public function actionPages()
    {
        /***************** Get page list data *******************/
        $searchModel = new PostPages();
		$PostID = $_GET['id'];
        $dataProvider = new ActiveDataProvider([
				'query' => PostPages::find()->where('PostID='.$PostID)]);
        return $this->render('pages', [            
            'model' => $searchModel,
			'dataProvider' => $dataProvider,
        ]);
    }
	
	public function actionCreatepage()
    {
        $model = new PostPages();
		$PostID = $_GET['id'];
		$model->ID = 0;
		$model->PostID = $PostID;
		$post = Post::findOne($PostID);
		$model->PostTitle = $post->PostTitle;
        if($model->load(Yii::$app->request->post())) 
        {    
            if($model->validate()){
				$model->save(false);
				
				$pageID = $model->ID;
				/************ Upload image in s3 bucket **************/
                if(isset($_FILES['PostPages']['tmp_name']['ImageUrl']) && $_FILES['PostPages']['tmp_name']['ImageUrl'] != '') {
                    $fileExtension = "";
                    $Image = $_FILES['PostPages']['tmp_name']['ImageUrl'];
                    $filesNameArray = pathinfo($_FILES['PostPages']['name']['ImageUrl']);
                    $fileExtension = $filesNameArray['extension'];
                    if($fileExtension != "") {
                            $actualImageName = 'postpage_'.$pageID.'_'.time().'.'.$fileExtension;
                            $artistID = $post->ArtistID;

							$s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY);
                            if(\S3::putObjectFile($Image,S3BUCKET,BOOM.$artistID."/pageimages/".$actualImageName, \S3::ACL_PUBLIC_READ)) {
								$fullurl = S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$artistID."/pageimages/".$actualImageName;
                                $content = file_get_contents($fullurl);
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

                                \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID."/pageimages/thumbs/".$actualImageName, \S3::ACL_PUBLIC_READ);
                                \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$artistID."/pageimages/medium/".$actualImageName, \S3::ACL_PUBLIC_READ);
                                \S3::putObjectFile($createsmall,S3BUCKET,BOOM.$artistID."/pageimages/small/".$actualImageName, \S3::ACL_PUBLIC_READ);
                                
								$width; $height;
								list($width, $height) = getimagesize($Image);
								
                                $model = PostPages::findOne($pageID);
								$model->ImageUrl = $fullurl;
								$model->ImageWidth = $width;
								$model->ImageHeight = $height;
                                $model->save(false); 
                            }
                        }   
                }
				
				$tm = time();
				$artistID = $post->ArtistID;
				//Upload video thumbnail on s3 bucket
				if(isset($_FILES['PostPages']['name']['VideoThumbnailImage']) && $_FILES['PostPages']['name']['VideoThumbnailImage'] != "") {
					
					$filesArray = $_FILES['PostPages']['tmp_name']['VideoThumbnailImage'];
                    $filesNameArray = $_FILES['PostPages']['name']['VideoThumbnailImage'];    
                    $thumb = $filesArray;
                    if($thumb !='') {						
						$s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY);
						//$previousFileName = str_replace(' ','_',$filesNameArray);
                        //$actualImageName = $pageID.'_'.$tm.'_'.$previousFileName;
						$namespli = explode('.',$filesNameArray);
						$ext = ".".end($namespli);
						$actualImageName = $artistID.'_'.$pageID.'_'.$tm.$ext;
                        $originalPath = BOOM.$artistID."/pagevideos/thumb/".$actualImageName;
                        \S3::putObjectFile($thumb,S3BUCKET,$originalPath,\S3::ACL_PUBLIC_READ); 
                        $model = PostPages::findOne($pageID);
                        $model->VideoThumbnailImage = S3_BUCKETABSOLUTE_PATH."/".$originalPath;
                        $model->save(false); 
                    }    
                }
				
				
				//Upload video on s3 bucket
				if(isset($_FILES['PostPages']['name']['VideoUrl']) && $_FILES['PostPages']['name']['VideoUrl'] != "") {
					
					$filesArray = $_FILES['PostPages']['tmp_name']['VideoUrl'];
                    $filesNameArray = $_FILES['PostPages']['name']['VideoUrl'];    
                    $video = $filesArray;
                    if($video !='') {						
						$s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY);
						//$previousFileName = str_replace(' ','_',$filesNameArray);
                        //$actualImageName = $pageID.'_'.$tm.'_'.$previousFileName;
						$namespli = explode('.',$filesNameArray);
						$ext = ".".end($namespli);
						$actualImageName = $artistID.'_'.$pageID.'_'.$tm.$ext;
                        $originalPath = BOOM.$artistID."/pagevideos/".$actualImageName;
                        \S3::putObjectFile($video,S3BUCKET,$originalPath,\S3::ACL_PUBLIC_READ); 
                        $model = PostPages::findOne($pageID);
                        $model->VideoUrl = S3_BUCKETABSOLUTE_PATH."/".$originalPath;
                        $model->save(false); 
                    }    
                }
				
				
				Yii::$app->session->setFlash('success', 'Page created successfully');
                return $this->redirect('pages?id='.$PostID);
			}else{
				return $this->render('createpage', [
                    'model' => $model,
					'error' => 'yii\web\ErrorAction',
                ]);
			}
		}
		//calculates page number
		//$model->PageNumber = 1;
		$connection = Yii::$app->db;
		$command = $connection->createCommand('SELECT count(*) AS num FROM postpages WHERE PostID = '.$PostID);
        $res = $command->queryOne();
		$model->PageNumber = $res['num'] + 1;
		return $this->render('createpage', [
			'model' => $model,
        ]);
    }

	public function actionEditpage()
    {
		$PostID = $_GET['id'];
		$pageID = $_GET['pageID'];
		$model = PostPages::findOne($pageID);
		$post = Post::findOne($PostID);
		$model->PostTitle = $post->PostTitle;
		$oldVideo = $model->VideoUrl;
		$oldImage = $model->ImageUrl;
		$oldThumb = $model->VideoThumbnailImage;
        if($model->load(Yii::$app->request->post())) 
        {    
            if($model->validate()){
				$model->save(false);
				
				/************ Upload image in s3 bucket **************/
                if(isset($_FILES['PostPages']['tmp_name']['ImageUrl']) && $_FILES['PostPages']['tmp_name']['ImageUrl'] != '') {
					$pageID = $model->ID;
                    $fileExtension = "";
                    $Image = $_FILES['PostPages']['tmp_name']['ImageUrl'];
                    $filesNameArray = pathinfo($_FILES['PostPages']['name']['ImageUrl']);
                    $fileExtension = $filesNameArray['extension'];
                    if($fileExtension != "") {
                            $actualImageName = 'postpage_'.$pageID.'_'.time().'.'.$fileExtension;
                            $artistID = $post->ArtistID;

							$s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY);
                            if(\S3::putObjectFile($Image,S3BUCKET,BOOM.$artistID."/pageimages/".$actualImageName, \S3::ACL_PUBLIC_READ)) {
								$fullurl = S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$artistID."/pageimages/".$actualImageName;
                                $content = file_get_contents($fullurl);
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

                                \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID."/pageimages/thumbs/".$actualImageName, \S3::ACL_PUBLIC_READ);
                                \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$artistID."/pageimages/medium/".$actualImageName, \S3::ACL_PUBLIC_READ);
                                \S3::putObjectFile($createsmall,S3BUCKET,BOOM.$artistID."/pageimages/small/".$actualImageName, \S3::ACL_PUBLIC_READ);
								
								$width; $height;
								list($width, $height) = getimagesize($Image);
								
                                $model = PostPages::findOne($pageID);
								$model->ImageUrl = $fullurl;
								$model->ImageWidth = $width;
								$model->ImageHeight = $height;
                                $model->save(false); 
                            }
                        }   
                }else{
					$model = PostPages::findOne($pageID);
                    $model->ImageUrl = $oldImage;
                    $model->save(false);
				}
				
				$tm = time();
				$artistID = $post->ArtistID;
				//Upload video thumbnail on s3 bucket
				if(isset($_FILES['PostPages']['name']['VideoThumbnailImage']) && $_FILES['PostPages']['name']['VideoThumbnailImage'] != "") {
					
					$filesArray = $_FILES['PostPages']['tmp_name']['VideoThumbnailImage'];
                    $filesNameArray = $_FILES['PostPages']['name']['VideoThumbnailImage'];    
                    $thumb = $filesArray;
                    if($thumb !='') {						
						$s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY);
						//$previousFileName = str_replace(' ','_',$filesNameArray);
                        //$actualImageName = $pageID.'_'.$tm.'_'.$previousFileName;
						$namespli = explode('.',$filesNameArray);
						$ext = ".".end($namespli);
						$actualImageName = $artistID.'_'.$pageID.'_'.$tm.$ext;
                        $originalPath = BOOM.$artistID."/pagevideos/thumb/".$actualImageName;
                        \S3::putObjectFile($thumb,S3BUCKET,$originalPath,\S3::ACL_PUBLIC_READ); 
                        $model = PostPages::findOne($pageID);
                        $model->VideoThumbnailImage = S3_BUCKETABSOLUTE_PATH."/".$originalPath;
                        $model->save(false); 
                    }    
                }else{
					$model = PostPages::findOne($pageID);
                    $model->VideoThumbnailImage = $oldThumb;
                    $model->save(false);
				}
				
				
				//Upload video on s3 bucket
				if(isset($_FILES['PostPages']['name']['VideoUrl']) && $_FILES['PostPages']['name']['VideoUrl'] != "") {
					
					$filesArray = $_FILES['PostPages']['tmp_name']['VideoUrl'];
                    $filesNameArray = $_FILES['PostPages']['name']['VideoUrl'];    
                    $video = $filesArray;
                    if($video !='') {						
						$s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY);
						//$previousFileName = str_replace(' ','_',$filesNameArray);
                        //$actualImageName = $pageID.'_'.$tm.'_'.$previousFileName;
						$namespli = explode('.',$filesNameArray);
						$ext = ".".end($namespli);
						$actualImageName = $artistID.'_'.$pageID.'_'.$tm.$ext;
                        $originalPath = BOOM.$artistID."/pagevideos/".$actualImageName;
                        \S3::putObjectFile($video,S3BUCKET,$originalPath,\S3::ACL_PUBLIC_READ); 
                        $model = PostPages::findOne($pageID);
                        $model->VideoUrl = S3_BUCKETABSOLUTE_PATH."/".$originalPath;
                        $model->save(false); 
                    }    
                }else{
					$model = PostPages::findOne($pageID);
                    $model->VideoUrl = $oldVideo;
                    $model->save(false);
				}
				
				
				Yii::$app->session->setFlash('success', 'Page successfully updated');
                return $this->redirect('pages?id='.$PostID);
			}else{
				return $this->render('editpage', [
                    'model' => $model,
					'error' => 'yii\web\ErrorAction',
                ]);
			}
		}
		return $this->render('editpage', [
			'model' => $model,
        ]);
    }
	
	
	public function actionDeletepage($id, $pageID)
    {
		$PostID = $id;
		$pageID = $pageID;
        $model = PostPages::findOne($pageID);
		$pagenum = $model->PageNumber;
        $model->delete();
		//re-calculate page number for other pages
		$pages = PostPages::find()->where('PostID='.$PostID.' AND PageNumber > '.$pagenum)->orderBy('PageNumber ASC')->all();
		foreach($pages as $page){
			$page->PageNumber = $pagenum;
			$page->save(false);
			$pagenum++;
		}
        return $this->redirect('pages?id='.$PostID);
    }
	
	
	public function actionRemovepageimg($id) {
        /*************** Remove page image ****************/
        if((int)$id) {
            $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
            $model = PostPages::findOne($id);
			$splipath = explode('/', $model->ImageUrl);
			$splilen = count($splipath);
			$awspath = $splipath[$splilen-3].'/'.$splipath[$splilen-2].'/'.$splipath[$splilen-1];
            $deleteObject = \S3::deleteObject(S3BUCKET,$awspath);
            $model->ImageUrl = null;
            $model->save(false);
            Yii::$app->session->setFlash('success', 'Image has been deleted successfully');
            return $this->redirect('editpage?id='.$model->PostID.'&pageID='.$model->ID);
        }
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
        $model = new Post();
        $modelExclusivePrice = new ExclusivePrice();
        $model->scenario  = 'create';
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
                    $previousThumbFileName = pathinfo($previousthumbName,PATHINFO_EXTENSION);
                    $actualthumbImageName   =   'post_'.$postID.'_'.time().".".$previousThumbFileName;
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
                    if(isset($_FILES['Post']['tmp_name']['ThumbImage'])) {
                        $filesThumb = $_FILES['Post']['tmp_name']['ThumbImage'];
                        $filesThumbName = $_FILES['Post']['name']['ThumbImage'];
                        //$previousThumbFileName = str_replace(' ','_',$filesThumbName[0]);
                        $previousThumbFileName = pathinfo(str_replace(' ','_',$filesThumbName[0]),PATHINFO_EXTENSION);
                        $actualThumbImageName = 'post_'.$postID.'_'.time().'.'.$previousThumbFileName;

                        \S3::putObjectFile($filesThumb[0],S3BUCKET,BOOM.$artistID.POST_THUMB_IMAGES.$actualThumbImageName, \S3::ACL_PUBLIC_READ);
                        \S3::putObjectFile($filesThumb[0],S3BUCKET,BOOM.$artistID.POST_MEDIUM_IMAGES.$actualThumbImageName, \S3::ACL_PUBLIC_READ);

                        $connection = \Yii::$app->db;
                        $connection->createCommand()
                                   ->update('post', array("ThumbImage"=>$actualThumbImageName),'PostID = '.$postID)
                                   ->execute();
                    }    
                    /*********************************************************/
                    
                    for($n=0; $n<count($filesArray); $n++) 
                    {
                        if($filesArray[$n] != '') 
                        {
                            $profileImage = $filesArray[$n];
                            $previousFileName = pathinfo($filesNameArray[$n],PATHINFO_EXTENSION);
                            $previousFileExtension = $previousFileName;
                            if($previousFileName == 'png') {
                                $previousFileName = "jpg";
                            }
                            $actualImageName = 'post_'.$postID.'_'.time().'.'.$previousFileName;
                            if(\S3::putObjectFile($profileImage,S3BUCKET,BOOM.$artistID.POST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ)) 
                            {
                                move_uploaded_file($filesArray[$n], "uploads/postoriginal/".$actualImageName);
                                $s3GalleryImage = S3_BUCKETPATH.$artistID.POST_IMAGES.$actualImageName;
                                //$imageDetail = getimagesize($s3GalleryImage);
                                list($width, $height) = getimagesize("uploads/postoriginal/".$actualImageName);
                                if($previousFileExtension != 'png') {
                                    //$width = $imageDetail[0];
                                    //$height = $imageDetail[1];
                                    if($width >1280 || $height>1280) {
                                        //$content        = file_get_contents($s3GalleryImage);
                                        $commonthumb = "uploads/postlarge/".$actualImageName;
                                        $createthumb = "uploads/postthumb/".$actualImageName;
                                        $createtoriginalhumb = "uploads/postoriginal/".$actualImageName;
                                        //file_put_contents($commonthumb, $content);

                                        $magicianObj = new \imageLib($createtoriginalhumb);
                                        if($width > $height) {
                                            $magicianObj->resizeImage(1280,'',3);
                                        } else if($height>$width) {
                                            $magicianObj->resizeImage('',1280,3);
                                        }   
                                        $magicianObj->saveImage($createthumb, 50);
                                        $actualImageName = 'post_'.$postID.'_'.time().'.'.$previousFileName;
                                        \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.POST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                    } else {
                                        //$content        = file_get_contents($s3GalleryImage);
                                        $commonthumb = "uploads/postlarge/".$actualImageName;
                                        $createthumb = "uploads/postthumb/".$actualImageName;
                                        $createtoriginalhumb = "uploads/postoriginal/".$actualImageName;
                                        //file_put_contents($commonthumb, $content);

                                        $magicianObj = new \imageLib($createtoriginalhumb);
                                        $magicianObj->resizeImage($width,$height);
                                        $magicianObj->saveImage($createthumb, 50);
                                        $actualImageName = 'post_'.$postID.'_'.time().'.'.$previousFileName;
                                        \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.POST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);    
                                    }
                                }
                                $modelGallery = new \backend\models\Gallery();  
                                $modelGallery->Title =   $previousFileName;
                                $modelGallery->ArtistID  = $artistID;
                                $modelGallery->RefTableID = $postID;
                                $modelGallery->RefTableType = '1';
                                $modelGallery->Type = '1';
                                $modelGallery->Image = $actualImageName;
                                $modelGallery->Width = $width;
                                $modelGallery->Height = $height;
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
        //echo "<pre>";print_r($_POST);exit;
        $model = new Post();
        $modelExclusivePrice = new ExclusivePrice();
        $model->scenario  = 'create';
        if ($model->load(Yii::$app->request->post())) 
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
                    /*if($postType == '1') {
                        $model->Price = $statusPrice;
                    } else if($postType == '2') {
                        $model->Price = $imagePrice;
                    } else if($postType == '3') {
                        $model->Price = $videoPrice;
                    }*/
                    if(isset($_POST['Post']['Price']) && $_POST['Post']['Price'] != "")
                    {
                        $model->Price   =   $_POST['Post']['Price'];
                    }
                    if(isset($_POST['Post']['ProductSKUID']) && $_POST['Post']['ProductSKUID'] != "")
                    {
                        $model->ProductSKUID   =   $_POST['Post']['ProductSKUID'];
                    }
                } 
                else {
                    $model->IsExclusive = "0";
                }   
                if($postType != '1') {
                    $model->PostTitle = $_POST['Post']['PostTitle'];
                } else {
                    $model->PostTitle = '';
                }
                $artistID = $_POST['Post']['ArtistID'];
                $model->Created = date("Y-m-d H:i:s");
                $model->CreatedBy = Yii::$app->user->identity->UserID;
                $model->save();
                $postID = Yii::$app->db->getLastInsertID();
                Yii::$app->session->setFlash('success', 'Post has been added successfully');
                echo json_encode(array("status"=>"ok","id"=>$postID,"ArtistID"=>$artistID)); die;
            } 
            else
            {
                echo json_encode(array("status"=>"error")); die;
            }
        }
    }    
    
    public function actionUpdateajax() {
        $id = $_POST['id'];
        $model = $this->findModel($id);
        $modelExclusivePrice = new ExclusivePrice();
        $model->scenario = 'update';
        $modelGallery = new \backend\models\Gallery();  
        $conditionForGallery = array("RefTableID"=>$id);
        $galleryData = $modelGallery->findAll($conditionForGallery);
        $postType = $model->PostType;
        $artistID = $model->ArtistID;
        if ($model->load(Yii::$app->request->post())) {
            if($model->validate()) {
                $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                $pricelist = $modelExclusivePrice->findAll(array("ExclusivePriceID"=>"1"));
                $statusPrice = $pricelist[0]->StatusPrice;
                $imagePrice = $pricelist[0]->ImagePrice;
                $videoPrice = $pricelist[0]->VideoPrice;

                    /*if($postType == '1') {
                        $model->Price = $statusPrice;
                    } else if($postType == '2') {
                        $model->Price = $imagePrice;
                    } else if($postType == '3') {
                        $model->Price = $videoPrice;
                    }*/
                    if(isset($_POST['Post']['Price']) && $_POST['Post']['Price'] != "")
                    {
                        $model->Price   =   $_POST['Post']['Price'];
                    }
                    if(isset($_POST['Post']['ProductSKUID']) && $_POST['Post']['ProductSKUID'] != "")
                    {
                        $model->ProductSKUID   =   $_POST['Post']['ProductSKUID'];
                    }
                
                $model->PostTitle = $_POST['Post']['PostTitle'];
                //$model->ThumbImage = '';
                //$model->WebStreamUrl = '';
                //$model->MobileStreamUrl = '';
                $model->Updated = date("Y-m-d H:i:s");
                $model->UpdatedBy = Yii::$app->user->identity->UserID;
                $model->save();
                Yii::$app->session->setFlash('success', 'Post has been updated successfully');
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
        $modelUpdate = Post::findOne($postID);
       
        if($type == "video")
        {
            $modelUpdate->URL = $fileName;
            $modelUpdate->MobileStreamUrl = S3_BUCKETABSOLUTE_PATH."/".$fileName;
            $modelUpdate->WebStreamUrl = '';
            //$modelUpdate->MobileStreamUrl = '';
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

        if(isset($_POST['artistid']) && $_POST['artistid'] != "")
            $artistid_id = $_POST['artistid'];
        else
            $artistid_id = "";
        
        $postID = $_POST['id'];
        $modelUpdate = Post::findOne($postID);
        if($type == "image")
        {
            //$modelUpdate->ThumbImage = S3_BUCKETPATH.BOOM.$artistid_id."/thumb/".$thumbfileName;
            $modelUpdate->VideoThumbImage = $thumbfileName;
            $modelUpdate->ThumbImage =S3_BUCKETPATH.$artistid_id."/thumb/".$thumbfileName;
            //$modelUpdate->FullVideoLink =S3_BUCKETPATH.$artistid_id."/thumb/".$thumbfileName;
            
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
        //$model->ThumbImage = $thumbImage;
        $modelExclusivePrice = new ExclusivePrice();
        $model->scenario = 'update';
        $modelGallery = new \backend\models\Gallery();  
        $conditionForGallery = array("RefTableID"=>$id);
        $galleryData = $modelGallery->findAll($conditionForGallery);
        $postType = $model->PostType;
        $artistID = $model->ArtistID;
        if ($model->load(Yii::$app->request->post())) {
            if($model->validate()) {
                $s3 = new \S3(AWSACCESSKEY, AWSSECRETKEY); 
                $pricelist = $modelExclusivePrice->findAll(array("ExclusivePriceID"=>"1"));
                $statusPrice = $pricelist[0]->StatusPrice;
                $imagePrice = $pricelist[0]->ImagePrice;
                $videoPrice = $pricelist[0]->VideoPrice;
                   /* if($postType == '1') {
                        $model->Price = $statusPrice;
                    } else if($postType == '2') {
                        $model->Price = $imagePrice;
                    } else if($postType == '3') {
                        $model->Price = $videoPrice;
                    }*/
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
                            $previousFileName = pathinfo($filesNameArray[$n],PATHINFO_EXTENSION);
                            $previousFileExtension = $previousFileName;
                            if($previousFileName == 'png') {
                                $previousFileName = 'jpg';
                            }
                            $actualImageName = 'post_'.$id.'_'.time().'.'.$previousFileName;
                            if(\S3::putObjectFile($profileImage,S3BUCKET,BOOM.$artistID.POST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ)) 
                            {
                                move_uploaded_file($filesArray[$n], "uploads/postoriginal/".$actualImageName);
                                $s3GalleryImage = S3_BUCKETPATH.$artistID.POST_IMAGES.$actualImageName;
                                //$imageDetail = getimagesize($s3GalleryImage);
                                list($width, $height) = getimagesize("uploads/postoriginal/".$actualImageName);
                                if($previousFileExtension != 'png') {
                                    //$width = $imageDetail[0];
                                    //$height = $imageDetail[1];
                                    if($width >1280 || $height>1280) {
                                        //$content        = file_get_contents($s3GalleryImage);
                                        $commonthumb = "uploads/postlarge/".$actualImageName;
                                        $createthumb = "uploads/postthumb/".$actualImageName;
                                        $createtoriginalhumb = "uploads/postoriginal/".$actualImageName;
                                        //file_put_contents($commonthumb, $content);

                                        $magicianObj = new \imageLib($createtoriginalhumb);
                                        if($width > $height) {
                                            $magicianObj->resizeImage(1280,'',3);
                                        } else if($height>$width) {
                                            $magicianObj->resizeImage('',1280,3);
                                        }   
                                        $magicianObj->saveImage($createthumb, 50);
                                        $actualImageName = 'post_'.$id.'_'.time().'.'.$previousFileName;
                                        \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.POST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                    } else {
                                        //$content        = file_get_contents($s3GalleryImage);
                                        $commonthumb = "uploads/postlarge/".$actualImageName;
                                        $createthumb = "uploads/postthumb/".$actualImageName;
                                        $createtoriginalhumb = "uploads/postoriginal/".$actualImageName;
                                        //file_put_contents($commonthumb, $content);

                                        $magicianObj = new \imageLib($createtoriginalhumb);
                                        $magicianObj->resizeImage($width,$height);
                                        $magicianObj->saveImage($createthumb, 50);
                                        $actualImageName = 'post_'.$id.'_'.time().'.'.$previousFileName;
                                        \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.POST_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);    
                                    }
                                }
                                /*$content = file_get_contents(S3_BUCKETABSOLUTE_PATH."/".BOOM.$artistID.POST_IMAGES.$actualImageName);
                                $commonthumb = "uploads/postlarge/".$actualImageName;
                                $createthumb = "uploads/postthumb/".$actualImageName;
                                $createmedium = "uploads/postmedium/".$actualImageName;
                                file_put_contents($commonthumb, $content);

                                $magicianObj = new \imageLib($commonthumb);
                                $magicianObj->resizeImage(POST_THUMB_WIDTH_SIZE,POST_THUMB_HEIGHT_SIZE,1);
                                $magicianObj->saveImage($createthumb, 100);
                                
                                $magicianObj = new \imageLib($commonthumb);
                                $magicianObj->resizeImage(MEDIUM_WIDTH_SIZE,MEDIUM_HEIGHT_SIZE,1);
                                $magicianObj->saveImage($createmedium, 100);
                                
                                //$itemImage=Image::thumbnail( $commonthumb, 150, 150,\Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET)->save($createthumb, ['quality' => 100]);
                                //$itemImage=Image::thumbnail( $commonthumb, 300, 300,\Imagine\Image\ManipulatorInterface::THUMBNAIL_INSET)->save($createmedium, ['quality' => 100]);
                                \S3::putObjectFile($createthumb,S3BUCKET,BOOM.$artistID.POST_THUMB_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);
                                \S3::putObjectFile($createmedium,S3BUCKET,BOOM.$artistID.POST_MEDIUM_IMAGES.$actualImageName, \S3::ACL_PUBLIC_READ);*/
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
        $model = $this->findModel($id); //->delete()
        $model->IsDelete = "1";
        $model->save(false);
        Yii::$app->session->setFlash('success', 'Post has been deleted successfully');
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
        if (($model = Post::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
