<?php
namespace backend\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use common\models\LoginForm;
use yii\filters\VerbFilter;
use backend\models\User;
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
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
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
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }
    
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new User();
        $model->scenario = 'login';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $userName = $_POST['User']['UserName'];
            $password = $_POST['User']['Password'];
            $rememberMe = $_POST['User']['rememberMe'];
            $condition = "UserName = AES_ENCRYPT('".$userName."',Password('".AESKEY."')) AND Password = '".md5($password)."' AND Status = '1' AND UserType IN ('1','2') ";
            //$loginData = User::find()->where($condition)->all();
            $loginData = User::find()->select("*,AES_DECRYPT(Email,Password('".AESKEY."')) AS Email,AES_DECRYPT(UserName,Password('".AESKEY."')) AS UserName")
	    ->where($condition)->all();
            
            if(count($loginData)>0) {
                $username = $loginData[0]->UserName;
                $userID = $loginData[0]->UserID;
                $userType = $loginData[0]->UserType;
                $session = Yii::$app->session;
                $session->set('userName',$username);
                $session->set('userID',$userID);
                $session->set('userType',$userType);    
                User::loginForAdmin($username,$rememberMe);
		if($userType == '2')
		{
		    $url = Url::toRoute('post/index');
		    return $this->redirect($url);
		}
		else
		{
		    return $this->goHome();
		}
            } else {
                Yii::$app->session->setFlash('error', 'Username or password is incorrect');
            }
        }
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
}
