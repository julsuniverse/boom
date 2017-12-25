<?php
namespace api\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use api\models\User;
use yii\filters\VerbFilter;
use yii\filters\auth\HttpBasicAuth;
use yii\db\Expression;
	
/**
 * UserController implements the CRUD actions for User model.
 **/
 
class UserController extends Controller {
   
   const STATUS_DELETED = 0;
   const STATUS_ACTIVE = 10;
   const EXPIRE_TIME = 5; // Token expire time in minute
   
   private $arrSkipAction = ['create', 'login'];
   
	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] =  [
				'class' => HttpBasicAuth::className(),
				'except' => ['create', 'login'],								
			];
		$behaviors['verbs'] =  [
				'class' => VerbFilter::className(),
				'actions' => [
					'index' => ['get'],
					'view' => ['get'],
					'create' => ['put'],
					'update' => ['put'],
					'delete' => ['delete'],
					'deleteall' => ['post'],
					'login' => ['post']
				]
			];		
		return $behaviors;
	}
	
	
	/**
	 * This will execute before action
	 **/
	public function beforeAction($event) {
		
		$action = $event->id;
		if(!in_array($action,$this->arrSkipAction)) 
			$this->checkAccessToken();
		
		if(isset($this->actions[$action])) {
			$verbs = $this->actions[$action];
		}
		elseif(isset($this->actions['*'])) {
			$verbs = $this->actions['*'];
		}
		else {
			return $event->isValid;
		}
		
		$verb = Yii::$app->getRequest()->getMethod();
		
		$allowed = array_map('strtoupper', $verbs);	
		
		if(!in_array($verb, $allowed)) {
			
			$this->setHeader(400);
			
			echo json_encode(array('status' => 0, 'error_code' => 400, 'message' => 'Method not allowed'), JSON_PRETTY_PRINT);
			exit;
		}
		
		return true;
	}
	
	private function checkAccessToken() {
		$strHeader = Yii::$app->request->getHeaders()['authorization'];
		
		$arrHeader = explode('=', $strHeader);
		$access_token = '';

		if(strtolower($arrHeader[0]) != 'access_token') {
			$this->setHeader(401);			
			echo json_encode(array('status' => 0, 'error_code' => 401, 'message' => 'Unauthorized to perform this action'), JSON_PRETTY_PRINT);	
			exit;
		}
		if(empty($arrHeader[1])) {
			$this->setHeader(401);			
			echo json_encode(array('status' => 0, 'error_code' => 401, 'message' => 'Unauthorized to perform this action'), JSON_PRETTY_PRINT);	
			exit;
		}		
		
		$access_token = $arrHeader[1];

		$model = User::find()->where(['access_token' => $access_token])->andWhere(['>', new Expression(' TIMESTAMPDIFF(MINUTE, NOW(), expire_time)'), 0])->one();
	
		if(!$model) {
			$this->setHeader(401);			
			echo json_encode(array('status' => 0, 'error_code' => 401, 'message' => 'Unauthorized or expired token'), JSON_PRETTY_PRINT);			
			exit;
		}	
		
		$this->updateExpTime($model);
	}
	
	private function setHeader($status) {
		$statusHeader = 'HTTP/1.1' . $status . ' ' . $this->_getStatusCodeMessage($status);
		$contentType  = "application/json; charset=utf-8";
		
		header($statusHeader);
		header('Content-type: ' . $contentType);
		header('X-Powered-By: ' . "E2Logy Solutions");
	}
	
	private function _getStatusCodeMessage($status) {
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
			200 => 'OK',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
		);
		return (isset($codes[$status])) ? $codes[$status] : '';
        }
	
	public function actionLogin() {
		$arrParams = Yii::$app->request->post();
		
		$model = User::findOne(['username'=>$arrParams['username']]);
		
		if($model && $model->validatePassword($arrParams['password'])) {
			$token = $this->getAccessToken();
			if($this->updateLogin($model, $token)) {
				$this->setHeader(200);			
				echo json_encode(['status'=>1, 'data'=>$model->attributes],JSON_PRETTY_PRINT);
			}
			else {
				$this->setHeader(400);
				echo json_encode(['status'=>0, 'error_code'=>400,'errors'=>'Login failed'], JSON_PRETTY_PRINT);			
			}
		}
		else {
			$this->setHeader(400);
			echo json_encode(['status'=>0, 'error_code'=>400,'errors'=>'Login failed'], JSON_PRETTY_PRINT);			
		}
		
	}
	
	public function actionIndex() {
		
		$model = User::find()->asArray()->all();
		
		$this->setHeader(200);
		echo json_encode(['status'=>1, 'data'=>$model, 'totalItems' => count($model)]);

	}
	
	public function actionView($id) {
		$model = $this->loadModel($id);
		
		$this->setHeader(200);
		echo json_encode(['status'=>1, 'data'=>$model->attributes, 'totalItems' => count($model)]);
	}
	
	/**
	 * For put method send data as x-www-form-urlencode
	 **/
	public function actionCreate() {
		
		$params = Yii::$app->request->bodyParams;

		$model = new User();
		$model->attributes = $params;

		$model->setPassword();
		$model->generateAuthKey();	
		$model->status = self::STATUS_ACTIVE;
		
		$model->created_at = date('Y-m-d H:i:s');
		$model->updated_at = date('Y-m-d H:i:s');

		if($model->save()) {
			$this->setHeader(200);
			echo json_encode(['status'=>1, 'data'=>array_filter($model->attributes)], JSON_PRETTY_PRINT);			
		}
		else {
			$this->setHeader(400);
			echo json_encode(['status'=>0, 'error_code'=>400,'errors'=>$model->errors], JSON_PRETTY_PRINT);			
		}
		
	}
	
	/**
	 * For put method send data as x-www-form-urlencode
	 **/
	public function actionUpdate() {
		
		$params = Yii::$app->request->bodyParams;
			
		$model = $this->loadModel($params['id']);
		$model->setIsNewRecord(false);
		$model->attributes = $params;		
		
		$model->updated_at = date('Y-m-d H:i:s');
		
		if($model->save()) {
			$this->setHeader(200);
			echo json_encode(['status'=>1, 'data'=>array_filter($model->attributes)], JSON_PRETTY_PRINT);			
		}
		else {
			$this->setHeader(400);
			echo json_encode(['status'=>0, 'error_code'=>400,'errors'=>$model->errors], JSON_PRETTY_PRINT);			
		}
		
	}
	
	/**
	 * For delete method send data as x-www-form-urlencode
	 **/
	public function actionDelete() {
		
		$id = Yii::$app->request->getBodyParam('id');
		
		$model = $this->loadModel($id);
		
		if($model->delete()) {
			$this->setHeader(200);
			echo json_encode(['status'=>1, 'data'=>array_filter($model->attributes)], JSON_PRETTY_PRINT);
		}
		else {
			 $this->setHeader(400);
			 echo json_encode(['status'=>0,'error_code'=>400,'errors'=>$model->errors],JSON_PRETTY_PRINT);
		}
	}
	
	/**
	 * Finds the User model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
	 **/
	 public function loadModel($id) {
		 $model = User::findOne($id);
		 
		 if(is_null($model)) {
			 $this->setHeader(400);
			 echo json_encode(['status'=>0, 'error_code'=>400, 'message'=>'Bed Request'], JSON_PRETTY_PRINT);
			 exit;
		 }
		 
		 return $model;
	 }

	/**
	 * Following code is used to update users accesstoekn and its time
	 **/
	public function updateLogin($model, $token) {
		$model->access_token = $token;
		$model->expire_time = new Expression('DATE_ADD(NOW(), INTERVAL '. self::EXPIRE_TIME .' MINUTE)');
		//$model->setIsNewRecord(false);		
		return $model->save();
	}
	
	public function updateExpTime($model) {
		$model->expire_time = new Expression('DATE_ADD(NOW(), INTERVAL '. self::EXPIRE_TIME .' MINUTE)');		
		return $model->save();
	}
	
	/**
	 * Following code is used to get unique access token which is not availablein database
	 **/
	 public function getAccessToken() {
		 $token = Yii::$app->security->generateRandomString();
		 
		 $model = User::findOne(['access_token'=>$token]);
		 
		 if($model) {
			 $token = $this->getAccessToken();
		 }
		 
		 return $token;
		 
	 }
 }
?>