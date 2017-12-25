<?php

namespace backend\controllers;

use Yii;
use backend\models\ExclusivePrice;
use backend\models\ExclusivePriceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\filters\AccessControl;

/**
 * ExclusivePriceController implements the CRUD actions for ExclusivePrice model.
 */
class ExclusivepriceController extends Controller
{
    /*public function behaviors()
    {
        $session = Yii::$app->session;
        if(Yii::$app->user->identity->UserType == 1) {
            return [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['update'],
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
        }
    }*/
    public function behaviors()
    {
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
        if (\Yii::$app->getUser()->isGuest &&
            \Yii::$app->getRequest()->url !== Url::to(\Yii::$app->getUser()->loginUrl)
        ) {
            \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        return parent::beforeAction($action);
    }
    
    /*public function cutNum( $number, $separator = '.', $format = 2 ){
        $response = '';
        $brokenNumber = explode( $separator, $number );
        $response = $brokenNumber[0] . $separator;
        if(isset($brokenNumber[1]) && $brokenNumber[1] !='') { 
            $brokenBackNumber = str_split($brokenNumber[1]);
            echo '<pre>';
            print_r($brokenBackNumber);
            die;
            if( $format < count($brokenBackNumber) ){
                for( $i = 1; $i <= $format; $i++ )
                    $response .= $brokenBackNumber[$i];
            }
            return $response;
        } else {
            return $number;
        }
    }*/


    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if(Yii::$app->user->identity->UserType == 1)
        {
            $model->ArtistID = $id;
        }
        if(isset($model->ExclusivePriceID))
        {
            $model->scenario = 'update';
        }
        else
        {
            $model->scenario = 'create';
        }
        
        if ($model->load(Yii::$app->request->post())) 
        {
            if($model->validate()) 
            {
                /*$model->StatusPrice  = $this->cutNum($_POST['ExclusivePrice']['StatusPrice'],'.',2);
                $model->ImagePrice  = $this->cutNum($_POST['ExclusivePrice']['ImagePrice'],'.',2);
                $model->VideoPrice  = $this->cutNum($_POST['ExclusivePrice']['VideoPrice'],'.',2);*/
                
                $model->StatusPrice  = $_POST['ExclusivePrice']['StatusPrice'];
                $model->ImagePrice  = $_POST['ExclusivePrice']['ImagePrice'];
                $model->VideoPrice  = $_POST['ExclusivePrice']['VideoPrice'];
                if(Yii::$app->user->identity->UserType == 1) {
                    $model->ArtistID = $_POST['ExclusivePrice']['ArtistID'];
                } else {
                    $model->ArtistID  = $id;
                }
                
                $model->save(false);
                Yii::$app->session->setFlash('success', 'Exclusive Price has been updated successfully');
                return $this->redirect(['update', 'id' => $model->ArtistID]);
            }    
            else 
            {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        } 
        else 
        {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    protected function findModel($id)
    {
        if (($model = ExclusivePrice::findOne(["ArtistID"=>$id])) !== null) {
            return $model;
        } else {
            return $model = new ExclusivePrice();
            //throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
