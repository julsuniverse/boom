<?php

namespace backend\controllers;

use Yii;
use backend\models\Nativeproducts;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\db\Query;
use yii\data\ActiveDataProvider;


class NativeproductsController extends Controller
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
                            'actions' => ['index', 'create', 'update', 'delete'],
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
     * Lists all Globalvar models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Nativeproducts();
		
		$dataProvider = new ActiveDataProvider([
		'query' => Nativeproducts::find(),
		'pagination' => [
			'pageSize' => 20,
		],]);
		
        return $this->render('index', [            
            'model' => $model,
			'dataProvider' => $dataProvider,
        ]);
    }
    

    /**
     * Displays a single Globalvar model.
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
     * Creates a new Globalvar model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Nativeproducts();
		$model->ID = 0;
        if($model->load(Yii::$app->request->post())) 
        {
            if($model->validate()) 
            {

				if(!$model->save(false)) 
                {
                    return $this->render('create', [
                        'model' => $model,
						'error' => 'yii\web\ErrorAction',
                    ]);
                }
                $ID = Yii::$app->db->getLastInsertID();	
				
				//updates the prices for all the other Native Products of same Type and belonging to same Comapny
				$other_prod = Nativeproducts::find()->where(['Type'=>$model->Type, 'ComID'=>$model->ComID])->all();
				foreach($other_prod as $prod){
					$prod->Price = $model->Price;
					$prod->save(false);
				}
			
                Yii::$app->session->setFlash('success', 'Value has been added successfully');
                //return $this->redirect('index');
                return $this->redirect('update?id='.$ID);
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
     * Updates an existing Globalvar model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if($model->load(Yii::$app->request->post())) {
            if($model->validate()) {
                
				if(!$model->save(false)) 
                {
                    return $this->render('update', [
                        'model' => $model,
						'error' => 'yii\web\ErrorAction',
                    ]);
                }
				
				//updates the prices for all the other Native Products of same Type and belonging to same Comapny
				$other_prod = Nativeproducts::find()->where(['Type'=>$model->Type, 'ComID'=>$model->ComID])->all();
				foreach($other_prod as $prod){
					$prod->Price = $model->Price;
					$prod->save(false);
				}
				
                Yii::$app->session->setFlash('success', 'Value has been updated successfully');
                return $this->redirect('update?id='.$id);
            }
        } 
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Globalvar model.
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
     * Finds the Globalvar model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Artist the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Nativeproducts::findOne($id);
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
}
