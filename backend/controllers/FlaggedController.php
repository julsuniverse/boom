<?php

namespace backend\controllers;

use Yii;
use backend\models\Flagged;
use backend\models\FlaggedSearch;
use backend\models\Ignore;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;

/**
 * PostController implements the CRUD actions for Post model.
 */
class FlaggedController extends Controller
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
        return parent::beforeAction($action);
    }

    /**
     * Lists all Post models.
     * @return mixed
     */
    public function actionIndex()
    {
        /************ Get list of the Flagged Post **************/
        $searchModel = new FlaggedSearch();
		if(Yii::$app->user->identity->UserType == 4) {
			$searchModel->CompanyID = Yii::$app->session->get('CompanyID');
		}
        if(isset($_GET['FlaggedSearch'])) {
            $searchModel->attributes = $_GET['FlaggedSearch'];
        }
        return $this->render('index', [            
            'model' => $searchModel,
        ]);
    }
    
    
    /**
     * Deletes an existing Post model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionRemove($id)
    {
        /*********** Remove flagged post *************/
        $model = $this->findModel($id);
        $model->IsDelete = '1';
        $model->save();
        Yii::$app->session->setFlash('success', 'Post has been deleted successfully');
        return $this->redirect(['index']);
    }
    
    public function actionIgnore($id) 
    {
        /************** Ignore flagged post *****************/
        $activityID = $_GET['actid'];
        $modelIgnore = new Ignore();
        $modelIgnore->PostID = $id;
        $modelIgnore->ActivityID = $activityID;
        $modelIgnore->Created = date("Y-m-d H:i:s");
        $modelIgnore->CreatedBy = Yii::$app->user->identity->UserID;
        $modelIgnore->save();
        Yii::$app->session->setFlash('success', 'Post has been ignored successfully');
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
        /****************** Get post data by post ID **************/
        if (($model = \backend\models\Post::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
