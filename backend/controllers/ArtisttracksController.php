<?php

namespace backend\controllers;

use Yii;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use backend\models\Artisttracks;
use backend\models\ArtisttracksSearch;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\filters\AccessControl;
//require_once '../../common/config/autoload.php';
class ArtisttracksController extends \yii\web\Controller
{
    public function behaviors()
    {
        if(Yii::$app->user->identity->UserType == 1) {
            return \Yii::$app->getResponse()->redirect(array('/site/logout',302));
        }
        $session = Yii::$app->session;
        if(Yii::$app->user->identity->UserType == 1) {
            return [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index','delete','updateheader'],
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
                            'actions' => ['index','delete','updateheader'],
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
        $this->enableCsrfValidation = false;
        if (\Yii::$app->getUser()->isGuest &&
            \Yii::$app->getRequest()->url !== Url::to(\Yii::$app->getUser()->loginUrl)
        ) {
            \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        return parent::beforeAction($action);
    }
    
    public function actionIndex($code="")
    {
        $model      =   new ArtisttracksSearch();
        if(isset($_GET['ArtisttracksSearch'])) 
        {
            $model->attributes = $_GET['ArtisttracksSearch'];
        }
        if($model->load(Yii::$app->request->post())) 
        {
            $append             =   '~@@~';
            $playlistArr        =   explode($append,$_POST['ArtisttracksSearch']['Playlist']);
            $playlistid         =   $playlistArr[0];
            $playlistnme        =   $playlistArr[1];
            $totalTrack = $_POST['ArtisttracksSearch']['Track'];
            if(count($totalTrack)>0) {
                for($n=0;$n<count($totalTrack);$n++) {
                    $trackArr  =  explode($append,$_POST['ArtisttracksSearch']['Track'][$n]);
                    $trackData = \backend\models\Artisttracks::findAll(array("TrackID"=>$trackArr[0],"ArtistID"=>$_POST['ArtistID']));
                    if($trackData == null) {
                        $model      =   new ArtisttracksSearch();
                        $model->PlaylistID  =   $playlistid;
                        $model->PlaylistName=   $playlistnme;
                        $model->TrackID     =   $trackArr[0];
                        $model->TrackTitle  =   $trackArr[1];
                        $model->Duration    =   $trackArr[2];
                        $model->ThumbImage  =   $trackArr[3];
                        $model->URI         =   $trackArr[4];
                        $model->ArtistID    =   $_POST['ArtistID'];
                        $model->save();
                    }  
                }    
            }    
            Yii::$app->session->setFlash('success', 'Track added successfully');
            return $this->redirect('index');
        }
        else
        {
            return $this->render('index', [            
                'model' => $model,
            ]);
        }
    }
    
    public function actionDelete($id) {
        \backend\models\Artisttracks::findOne($id)->delete();
        Yii::$app->session->setFlash('success', 'Track deleted successfully');
        return $this->redirect('index');
    }
    
    public function actionUpdateheader() {
        $id = $_POST['artistID'];
        $model = \backend\models\Artist::find()->where('ArtistID ='.$id)->one();
        $model->Header = $_POST['headerString'];
        if(!$model->save(false)) {
            echo '<pre>';
            print_r($model->getErrors());
            die;
        }       
    }
}
