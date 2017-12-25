<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SimilarappSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Similar Apps';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="similarapp-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Add Similar App', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php \yii\widgets\Pjax::begin(
            ['id' => 'StickerList', 'timeout' => false, 'enablePushState' => false, 'clientOptions' => ['method' => 'GET']]
        ); ?>

    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            [
                //'header'=>'Name',
                'attribute'=>'ListTitle',
                'value' => function($data) {
                    $url = Url::toRoute('similarapp/update?id='.$data['ListID']);
                    return Html::a($data['ListTitle'],$url,['data-pjax'=>"0"]);
                },
                'format'=>'raw',
                'format'=>'raw',
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],    
            [
                //'header' => 'Date Added',
                'attribute' => 'DateAdded',
                'value' => function($data) {
                    return getTimezone($data['DateAdded']);
                }
            ],
            [
                //'header' => 'Status',
                'attribute' => 'Status',
                'filter'=>Html::activeDropDownList($model, 'Status',array(""=>"All","1"=>"Active","2"=>"Inactive"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
//            [   'class' => 'yii\grid\ActionColumn',
//                'template'=>'{update}',
//                'buttons' => [
//	                'update' => function ($url,$model) {
//                            $url = Url::toRoute('similarapp/update?id='.$model['ListID']);
//	                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>',$url,['data-pjax'=>"0"]);
//	                },
//	        ],
//            ]                    
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>

</div>
