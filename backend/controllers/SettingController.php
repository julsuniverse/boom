<?php

namespace backend\controllers;

use Yii;
use backend\models\Setting;
use backend\models\SettingSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SettingController implements the CRUD actions for Setting model.
 */
class SettingController extends Controller
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
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Setting models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SettingSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Setting model.
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
     * Creates a new Setting model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Setting();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->SettingID]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Setting model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate()
    {
        /************* Update artist setting data ****************/
        $id = "";
        if(Yii::$app->user->identity->UserType == 1 || Yii::$app->user->identity->UserType == 4) {
            if(isset($_GET['id'])) {
                $id = $_GET['id'];
            }
        } else if(Yii::$app->user->identity->UserType == 2) {
            $id = Yii::$app->user->identity->ArtistID;
        }
        if($id !="") {
            $model = $this->findModel($id);
            $model->ArtistID = $id;
        } else {
            $model = new Setting();
            $model->ProductType = "1";
        }
        if($model->QaType == "3") {
           $model->QaType = array(1,2); 
        }
		if($model->QaType == "5") {
           $model->QaType = array(1,4); 
        }
		if($model->QaType == "6") {
           $model->QaType = array(2,4); 
        }
		if($model->QaType == "7") {
           $model->QaType = array(1,2,4); 
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            //QAType:1-Text,2-Video,3-Text+Video,4-Photo,5-text+photo,6-video+photo,7-all
			if(isset($_POST['Setting']['QaType'])){
                $QAType = $_POST['Setting']['QaType'];
                /*if(count($QAType) == 2) {
                    $model->QaType =  3;
                } else if(isset($QAType[0]) && $QAType[0] == 1) {
                    $model->QaType =  1;
                } else if(isset($QAType[0]) && $QAType[0] == 2) {
                    $model->QaType =  2;
                } else {
                    $model->QaType = 0;
                    $model->IsQAEnable = "0";
                }*/
				if(isset($QAType[0])){
					if($QAType[0] == 1) { $model->QaType =  1; }//Text
					if($QAType[0] == 2) { $model->QaType =  2; }//Video
					if($QAType[0] == 4) { $model->QaType =  4; }//Photo
				}
				if(isset($QAType[1])){
					if($QAType[1] == 2) { 
						if($model->QaType == 1){$model->QaType =  3; }//Text+Video
					}
					if($QAType[1] == 4) { 
						if($model->QaType == 1){$model->QaType =  5; }//Text+Photo
						else if($model->QaType == 2){$model->QaType =  6; }//Video+Photo
					}	
				}
				if(isset($QAType[2])){ $model->QaType = 7;}//All
				if(!isset($QAType[0]) && !isset($QAType[1]) && !isset($QAType[2])){$model->QaType = 0;}//None
            }else{$model->QaType = 0;}
			
			if($model->QaType != null && $model->QaType != 0){
				$model->IsQAEnable = 1;
			}else{
				$model->IsQAEnable = 0;
			}
            
            /*if(isset($_POST['Setting']['ProductType'])) :
                $productType = $_POST['Setting']['ProductType'];    
                $model->ProductType = $productType;
            endif;*/
            
            $model->ArtistID = $id;
            
            if(isset($_POST['Setting']['TextPrice']))
                $model->TextPrice = $_POST['Setting']['TextPrice'];
            else
                $model->TextPrice = 0;
            
            if(isset($_POST['Setting']['VideoPrice']))
                $model->VideoPrice = $_POST['Setting']['VideoPrice'];
            else 
                $model->VideoPrice = 0;
				
			if(isset($_POST['Setting']['PhotoPrice']))
                $model->PhotoPrice = $_POST['Setting']['PhotoPrice'];
            else 
                $model->PhotoPrice = 0;
            
            if(isset($_POST['Setting']['TextProductSKUID']))
                $model->TextProductSKUID = $_POST['Setting']['TextProductSKUID'];
            else
                $model->TextProductSKUID = 0;
            
            if(isset($_POST['Setting']['VideoProductSKUID']))
                $model->VideoProductSKUID = $_POST['Setting']['VideoProductSKUID'];
            else
                $model->VideoProductSKUID = 0;
			
			if(isset($_POST['Setting']['PhotoProductSKUID']))
                $model->PhotoProductSKUID = $_POST['Setting']['PhotoProductSKUID'];
            else
                $model->PhotoProductSKUID = 0;
            
            /*if($productType == "2") {
                if(isset($_POST['Setting']['ProductPrice']))
                    $model->ProductPrice = $_POST['Setting']['ProductPrice'];
                else 
                    $model->ProductPrice = 0;

                if(isset($_POST['Setting']['ProductSKUID']))
                    $model->ProductSKUID = $_POST['Setting']['ProductSKUID'];
                else
                    $model->ProductSKUID = "";
            } else {
                $model->ProductPrice = 0;
                $model->ProductSKUID = "";
            }*/   
            
            $model->Updated = date("Y-m-d H:i:s");
            $model->UpdatedBy = Yii::$app->user->identity->UserID;
            $model->save();
            Yii::$app->session->setFlash('success', 'Setting has been updated successfully');
            return $this->redirect('update?id='.$id);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Setting model.
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
     * Finds the Setting model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Setting the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        /********* Get Setting data by artist ID **************/
        if (($model = Setting::findOne(['ArtistID'=>$id])) !== null) {
            return $model;
        } else {
            //throw new NotFoundHttpException('The requested page does not exist.');
            $model = new Setting();
            $model->QAModuleName = 'Ask Me A Question';
            $model->ArtistID = Yii::$app->user->identity->ArtistID; 
            return $model;
        }
    }
}
