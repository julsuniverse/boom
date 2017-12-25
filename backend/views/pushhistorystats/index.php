<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\PushhistorystatsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Push Notification';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pushhistorystats-index">

    <p>
        <?= Html::a('Add new', ['create'], ['class' => 'btn btn-success','style'=>'float:right;']) ?>
    </p>
    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'header'=>'Date Sent',
                'attribute' => 'Created',
                'filter' => false, // this line is optional
                'value' => function($data)
                {
                    $url = Url::toRoute('pushhistorystats/view?id='.$data['BatchID']);
                    return Html::a(getTimezone($data['Created']),$url,['data-pjax'=>"0"]);
                },
                'format'=>'raw',  
            ],
            [
                'attribute' => 'Message',
                'filter' => false, // this line is optional
            ],
            [
                'header'=>'Artist Name',
                'attribute' => 'ArtistName',
                'filter' => true, // this line is optional
            ],
            [
                'header'=>'# Recipients',
                'attribute' => 'TotalDevices',
                'filter' => false, // this line is optional
            ],
        ],
    ]); ?>

</div>
