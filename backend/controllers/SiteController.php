<?php
namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use common\models\LoginForm;
use yii\filters\VerbFilter;
use backend\models\User;
use backend\models\Forgotpassword;
use yii\helpers\Url;
//use yii\rbac\PhpManager;
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
        /************** Restict action for artist and member  *******************/
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login','forgotpassword', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
           /* 'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],*/
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
        ];
    }

    public function actionIndex()
    {
        /****************** Check if already login then redirect it ************/
        if(Yii::$app->user->identity->UserType == 1) {
            $url = Url::toRoute('artist/index');
        } else if(Yii::$app->user->identity->UserType == 2) {
            $url = Url::toRoute('post/index');
        } else {
            $url = Url::toRoute('site/logout');
        }
        return $this->redirect($url);
        return $this->render('index');
    }
    
    
    public function actionLogin()
    {
        /************ Check member and artist login *********/
        if (!\Yii::$app->user->isGuest) {
            //return $this->goHome();
            if(Yii::$app->user->identity->UserType == 1) {
                $url = Url::toRoute('artist/index');
            } else if(Yii::$app->user->identity->UserType == 2) {
                $url = Url::toRoute('post/index');
            } else {
                $url = Url::toRoute('artist/index');
            }
            return $this->redirect($url);
        }
        $model = new User();
        $model->scenario = 'login';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $userName = $_POST['User']['UserName'];
            $password = $_POST['User']['Password'];
            $rememberMe = $_POST['User']['rememberMe'];
            
            /************* Check if username is exist in the database ***************/
            $condition = "UserName = AES_ENCRYPT('".$userName."',Password('".AESKEY."')) AND Password = '".md5($password)."' AND Status = '1' AND UserType IN ('1','2','4') ";
            //$loginData = User::find()->where($condition)->all();
            $loginData = User::find()->select("*,AES_DECRYPT(Email,Password('".AESKEY."')) AS Email,AES_DECRYPT(UserName,Password('".AESKEY."')) AS UserName")
	    ->where($condition)->all();
            
            if(count($loginData)>0) {
                $session = Yii::$app->session;
                $username = $loginData[0]->UserName;
                $userID = $loginData[0]->UserID;
                $userType = $loginData[0]->UserType;
                if($userType == 2) {
                    if(isset($loginData[0]->ArtistID) && $loginData[0]->ArtistID!='') {
                        $artistID = $loginData[0]->ArtistID;
                        $artistData = \backend\models\Artist::find()->select("*")->where("ArtistID = ".$artistID." ")->all();
                        $timeZone = $artistData[0]['TimeZone'];
                        if($timeZone != '') {
                            $session->set('timezone',$timeZone);
                        } else {
                            $session->set('timezone','Australia/Sydney');
                        }
                    }
                }
                $session->set('userName',$username);
                $session->set('userID',$userID);
                $session->set('userType',$userType);  
                User::loginForAdmin($username,$rememberMe);
		if($userType == '4'){
			$useradmin = \backend\models\User::find()->where('UserID ='.$userID)->one();	
			if($useradmin != null && $useradmin->ArtistCompanyID != null){						
				$session->set('CompanyID',$useradmin->ArtistCompanyID); 
			}
		}
		if($userType == '2')
		{
		    $url = Url::toRoute('post/index');
		    return $this->redirect($url);
		}
		else 
		{
		    $url = Url::toRoute('artist/index');
		    return $this->redirect($url);
		    //return $this->goHome();
		}
            } else {
                Yii::$app->session->setFlash('error', 'Either Username or Password is invalid. Please enter valid Username and Password.');
            }
        }
        $this->layout= 'login';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        /********** If logout redirect to login page ****************/
        Yii::$app->user->logout();
        return $this->goHome();
    }
    public function actionForgotpassword()
    {
        /*************** Get reset password link **************/
        /*if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }*/
        $model = new Forgotpassword();
        $modellogin = new User();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) 
        {
            $username = $_POST['Forgotpassword']['Username'];
            $password = $_POST['Forgotpassword']['password'];
            $modeluser = User::findOne(array("Username"=>new  \yii\db\Expression("AES_ENCRYPT('".$username."',PASSWORD('".AESKEY."'))"),"UserType"=>"1"));
            if(count($modeluser) > 0)
            {
                $modeluser->Password = md5($password);
                if($modeluser->save(false))
                {
                    Yii::$app->session->setFlash('success', 'Your password has been reset successfully');
                    return $this->goHome();
                }
                else
                {
                    Yii::$app->session->setFlash('error', 'Please try again.');
                }
            }
            else
            {
                Yii::$app->session->setFlash('error', 'Username is invalid.Please try again');
            }
        }
        $model = new Forgotpassword();
        return $this->render('forgotpassword', [
            'model' => $model,
        ]);
    }
}
