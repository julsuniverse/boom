<?php

namespace backend\controllers;

use Yii;
use backend\models\User;
use backend\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\filters\AccessControl;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    public function behaviors()
    {
        /************** Restict action for artist and member  *******************/
        $session = Yii::$app->session;
        if(Yii::$app->user->identity->UserType == 1) {
            return [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['update','create','index','index-company-admin','create-company-admin'],
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
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        /************ Get staff member list *************/
        $searchModel = new UserSearch();
		$searchModel->Role = 1;
        if(isset($_GET['UserSearch'])) {
            $searchModel->attributes = $_GET['UserSearch'];
        }
        return $this->render('index', [            
            'model' => $searchModel, 
        ]);
    }
	
	public function actionIndexCompanyAdmin()
    {
        /************ Get Company Admin list *************/
        $searchModel = new UserSearch();
		$searchModel->Role = 4;
        if(isset($_GET['UserSearch'])) {
            $searchModel->attributes = $_GET['UserSearch'];
        }
        return $this->render('index', [            
            'model' => $searchModel, 
        ]);
    }

    /**
     * Displays a single User model.
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
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($usertype)
    {
        /************* Crete new staff user ****************/
        $model = new User();
		$model->UserType = $usertype;
        $model->scenario = 'create';
        if($model->load(Yii::$app->request->post()) ) {
            if($model->validate()) {
                /**************** check if user exist or not in the user table ***************/
                
                $userData = $model->findAll(array("UserName"=>new  \yii\db\Expression("AES_ENCRYPT('".$_POST["User"]["UserName"]."',Password('".AESKEY."'))")));
                if(count($userData)>0) {
                    $model->addError('UserName', 'This username already exists ! Please try with a different username.');
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                $userName = $_POST["User"]["UserName"];
                $email = $_POST["User"]["Email"];
                $model->UserName = new  \yii\db\Expression("AES_ENCRYPT('".$userName."',Password('".AESKEY."'))");
                $model->Email = new  \yii\db\Expression("AES_ENCRYPT('".$email."',Password('".AESKEY."'))");
                $model->Password = md5($_POST['User']['Password']);
                $model->ConfirmPassword = md5($_POST['User']['Password']);
                $model->UserType = $usertype;
                $model->Created = date("Y-m-d H:i:s");
				if(isset($_POST["User"]["ArtistCompanyID"])){
					$model->ArtistCompanyID = $_POST["User"]["ArtistCompanyID"];
				}
                //$model->CreatedBy = Yii::$app->user->identity->UserID;
                if(!$model->save(false)) {
                    return $this->render('create', [
                        'model' => $model,
                    ]);
                }
                Yii::$app->session->setFlash('success', 'Admin has been added successfully');
                if($model->UserType == 1){return $this->redirect('index');}else{return $this->redirect('index-company-admin');}
            } else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }    
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
	

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        /************** Update staff member ******************/
        $model = $this->findModel($id);
        $model->scenario = 'update';
        $oldPassword = $model->Password;
        if ($model->load(Yii::$app->request->post())) {
            if( ($_POST['User']['Password']!='' && $_POST['User']['ConfirmPassword']!='') &&
                    ($_POST['User']['Password']==$_POST['User']['ConfirmPassword']) ) {
                    $model->Password = md5($_POST['User']['Password']);
                    $model->ConfirmPassword = md5($_POST['User']['Password']);
                } else {
                    $model->Password = $oldPassword;
                    $model->ConfirmPassword = $oldPassword;
                }
            if($model->validate()) {
                $email = $_POST["User"]["Email"];
                $model->Email = new  \yii\db\Expression("AES_ENCRYPT('".$email."',Password('".AESKEY."'))");
                $model->Updated = date("Y-m-d H:i:s");
                $model->UpdatedBy = Yii::$app->user->identity->UserID;
				if(isset($_POST["User"]["ArtistCompanyID"])){
					$model->ArtistCompanyID = $_POST["User"]["ArtistCompanyID"];
				}
                Yii::$app->session->setFlash('success', 'Admin has been updated successfully');
                $model->save(false);
                
				if($model->UserType == 1){return $this->redirect('index');}else{return $this->redirect('index-company-admin');}
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing User model.
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
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        /************** Get user data by user ID ******************/
        $model = User::find()->select("*,AES_DECRYPT(Email,Password('".AESKEY."')) AS Email,AES_DECRYPT(UserName,Password('".AESKEY."')) AS UserName")
	    ->where('UserID ='.$id)
	    ->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
