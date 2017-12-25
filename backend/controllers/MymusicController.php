<?php
namespace backend\controllers;


use Yii;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use backend\models\Artisttracks;
use backend\models\ArtisttracksSearch;
use yii\filters\VerbFilter;
use yii\helpers\Url;

/**
 * PostController implements the CRUD actions for Post model.
 */
class MymusicController extends Controller
{
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

    /**
     * Lists all Post models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model          =   new ArtisttracksSearch();
        /*$connection     =   \yii::$app->db;
        $artistData     =   \backend\models\Artist::getArtistData(Yii::$app->user->identity->UserID);
        if(isset($artistData[0]['ArtistID']) && $artistData[0]['ArtistID']!= '') 
        {
            $artistiddb = $artistData[0]['ArtistID'];
        }*/
        $model = new ArtisttracksSearch();
        if(isset($_GET['ArtisttracksSearch'])) {
            $model->attributes = $_GET['ArtisttracksSearch'];
        }
        return $this->render('index', [            
            'model' => $model,
        ]);
    }
    public function actionCreate()
    {
        $model = new Artisttracks();
        echo '<pre>';
        print_r($_POST);
        die;
        $model->scenario = 'create';
    }
    public function actionRemove($artistID,$playlistid,$trackid)
    {
        $connection     =   \yii::$app->db;
        $deletetracks   =   $connection ->createCommand()
            ->delete('artisttracks','PlaylistID="'.$playlistid.'" AND ArtistID='.$artistID.' AND TrackID="'.$trackid.'"')
            ->execute();
        if($deletetracks > 0)
        {
            echo "success";
        }
        else
        {
            echo "error";
        }
        return $this->render('index');
    }
}
