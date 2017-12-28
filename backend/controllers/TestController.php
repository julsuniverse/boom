<?php
namespace backend\controllers;

use backend\models\Artist;
use backend\models\Member;
use backend\models\Post;
use ErrorException;
use Yii;
use yii\filters\auth\HttpBasicAuth;
use yii\web\Controller;
use yii\filters\VerbFilter;

/**
 * Site controller
 */
class TestController extends Controller
{

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


    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        Yii::$app->controller->layout = '1';
        //$query = Member::findOne(1);
        //$query = Member::find()->limit(50)->all();
        $query = Post::findOne(1);
        //print_r($query); die;
        return $this->render('index', [
            'query' => $query,
        ]);
    }


}
