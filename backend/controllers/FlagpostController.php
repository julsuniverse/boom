<?php
namespace backend\controllers;

use Yii;
use backend\models\Post;
use backend\models\ExclusivePrice;
use backend\models\PostSearch;
use backend\models\LikeSearch;
use backend\models\ShareSearch;
use backend\models\CommentSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\Url;

/**
 * PostController implements the CRUD actions for Post model.
 */
class FlagpostController extends Controller
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
                    'delete' => ['post','get'],
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
        /******** Get Flagged post List **************/
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
    
    public function actionView()
    {
        $searchModel = new PostSearch();
        if(isset($_GET['PostSearch'])) {
            $searchModel->attributes = $_GET['PostSearch'];
        }
        return $this->render('view', [            
            'model' => $searchModel,
        ]);
    }
    /**
     * Deletes an existing Post model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        /************ Delete Falgged Comment ****************/
        \backend\models\Memberactivity::findOne($id)->delete();
        \backend\models\Memberactivity::deleteAll("RefTable='2' AND ActivityTypeID='5' AND RefTableID = ".$id." ");
        Yii::$app->session->setFlash('success', 'Comment has been removed successfully');
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
        /************** Get post data post ID *************/
        if (($model = Post::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
