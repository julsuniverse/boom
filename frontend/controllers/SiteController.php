<?php
namespace frontend\controllers;

use Yii;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\ResetpasswordApp;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    
    public function actionPost($id,$appname = '') {
        if($appname == '') {
            $appname = 'Boom Fan App';
        }
        $model = new \backend\models\Post();
        $modelArtist = new \backend\models\Artist();
        $modelGallery = new \backend\models\Gallery();  
        $postData = $model->findOne(array('PostID'=>$id));
		
        $artistData = array();
        $galleryData = array();
        $image = '';
        $postContent = "To view this post click here";
        if(count($postData)>0){
                $artistID = $postData->ArtistID;
                $artistData = $modelArtist->findOne(array('ArtistID'=>$artistID));
                $conditionForGallery = array("RefTableID"=>$id);
                $galleryData = $modelGallery->findAll($conditionForGallery);

                if($postData->PostType == '1'){
                        $image = S3_BUCKETPATH.$artistID.PROFILE_IMAGES.$artistData->ProfileThumb;			
                        $postContent = $postData->Description;
                } else if($postData->PostType == '2' && isset($galleryData) && count($galleryData) > 0){
                        $image = S3_BUCKETPATH.$artistID.POST_IMAGES.$galleryData[0]['Image'];
                        $postContent = $postData->PostTitle.' - '.$postData->Description;
                } else if($postData->PostType == '3') {
                        $image = S3_BUCKETPATH.$artistID.POST_THUMBIMAGE_VIDEOS.$postData->VideoThumbImage;
                        $postContent = $postData->PostTitle.' - '.$postData->Description;
                }
        }
		
	\Yii::$app->view->registerMetaTag([
            'name' => 'og:title',
            'content' => $appname
        ]);
        \Yii::$app->view->registerMetaTag([
            'name' => 'og:description',
            'content' => $postContent
        ]);
	\Yii::$app->view->registerMetaTag([
            'name' => 'og:image',
            'content' => $image
        ]);
        \Yii::$app->view->registerMetaTag([
            'name' => 'description',
            'content' => $postContent
        ]);		
        \Yii::$app->view->registerMetaTag([
            'name' => 'twitter:card',
            'content' => 'summary_large_image'
        ]);
		\Yii::$app->view->registerMetaTag([
            'name' => 'twitter:title',
            'content' => $appname
        ]);
        \Yii::$app->view->registerMetaTag([
            'name' => 'twitter:description',
            'content' => 'To view this post click here'
        ]);
        \Yii::$app->view->registerMetaTag([
            'name' => 'twitter:image',
            'content' => $image
        ]);
		
		
        return $this->render('post', [
            'post' => $postData,
            'artist'=>$artistData,
            'gallery'=>$galleryData,
        ]);
    }
    
    public function actionMobilepost($id,$appname='') {
	
		if($appname == '') {
            $appname = 'Boom Fan App';
        }	
		
		$model = new \backend\models\Post();
        $modelArtist = new \backend\models\Artist();
        $modelGallery = new \backend\models\Gallery();  
        $postData = $model->findOne(array('PostID'=>$id));
        $image = '';
        $postContent = "To view this post click here";
				
        if(count($postData)>0){

                $artistID = $postData->ArtistID;
                $artistData = $modelArtist->findOne(array('ArtistID'=>$artistID));
                $conditionForGallery = array("RefTableID"=>$id);
                $galleryData = $modelGallery->findAll($conditionForGallery);

                if($postData->PostType == '1'){
                        $image = S3_BUCKETPATH.$artistID.PROFILE_IMAGES.$artistData->ProfileThumb;
                        $postContent = $postData->Description;
                } else if ($postData->PostType == '2' && isset($galleryData) && count($galleryData) > 0){
                        $image = S3_BUCKETPATH.$artistID.POST_IMAGES.$galleryData[0]['Image'];
                        $postContent = $postData->PostTitle.' - '.$postData->Description;
                } else if ($postData->PostType == '3'){
                        $image = S3_BUCKETPATH.$artistID.POST_THUMBIMAGE_VIDEOS.$postData->VideoThumbImage;
                        $postContent = $postData->PostTitle.' - '.$postData->Description;
                }
        }
				
		\Yii::$app->view->registerMetaTag([
            'name' => 'og:title',
            'content' => $appname
        ]);
        \Yii::$app->view->registerMetaTag([
            'name' => 'og:description',
            'content' => $postContent
        ]);
		\Yii::$app->view->registerMetaTag([
            'name' => 'og:image',
            'content' => $image
        ]);
		\Yii::$app->view->registerMetaTag([
            'name' => 'description',
            'content' => $postContent
        ]);        
        \Yii::$app->view->registerMetaTag([
            'name' => 'twitter:card',
            'content' => 'summary_large_image'
        ]);
		\Yii::$app->view->registerMetaTag([
            'name' => 'twitter:title',
            'content' => $appname
        ]);
        \Yii::$app->view->registerMetaTag([
            'name' => 'twitter:description',
            'content' => 'To view this post click here 1'
        ]);
        \Yii::$app->view->registerMetaTag([
            'name' => 'twitter:image',
            'content' => $image
        ]);				
		
        return $this->render('mobilepost');
    }

    function userAgent(){
        $iPod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");
        $iPhone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
        $iPad = strpos($_SERVER['HTTP_USER_AGENT'],"iPad");
        $android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
        file_put_contents('./public/upload/install_log/agent',$_SERVER['HTTP_USER_AGENT']);
        if($iPad||$iPhone||$iPod){
            return 'ios';
        }else if($android){
            return 'android';
        }else {
            return 'pc';
        }
    }



    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
    public function actionThanksignup($token)
    {
        if($token != "")
        {
            try 
            {
               $model = \backend\models\User::findOne(array("ActivationCode"=>$token));
               if(count($model) >0)
               {
                    if($model->Status == '1')
                    {
                        Yii::$app->session->setFlash('success', 'Your account has been already activated.');
                    }
                    else
                    {
                        $model->Status='1';
                        $model->ActivatedOn=date('Y-m-d H:i:s');
                        if($model->save(false) == 1)
                        {
                            Yii::$app->session->setFlash('success', 'Thank you for registering with us. Your account has been activated successfully.Please login with your username and password.');
                        }
                    }
               }
               else
               {
                   Yii::$app->session->setFlash('error', 'Invalid link.');
               }
               
            }
            catch (InvalidParamException $e) 
            {
                throw new BadRequestHttpException($e->getMessage());
            }
        }
        else
        {
            Yii::$app->session->setFlash('error', 'Invalid link.');
        }
        return $this->render('thanksignup', [
                                'model' => $model,
                            ]);
    }
    public function actionResetpasswordapp($token)
    {
        $model = new \frontend\models\ResetpasswordApp();
        if($token == "")
        {
             Yii::$app->session->setFlash('error', 'Invalid link');
        }
        if(isset($_POST['ResetPasswordApp']['password']) && $_POST['ResetPasswordApp']['password'] != "")
        {
            $password   =   $_POST['ResetPasswordApp']['password'];
            $reset  =   $model->resetPassword($password, $token);
            if($reset == 1)
            {
                Yii::$app->session->setFlash('success', 'Your password has been reset successfully');
            }
            else if($reset == -1)
            {
                Yii::$app->session->setFlash('error', 'Sorry, something went wrong. Please try again later.');
            }
            else
            {
                Yii::$app->session->setFlash('error', 'Invalid link');
            }
        }
        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
}
