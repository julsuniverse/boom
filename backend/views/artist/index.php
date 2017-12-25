<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ArtistSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Artists';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="artist-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Add Artist', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <p class="pull-right">
        <?= Html::a('Export', ['export'], ['class' => 'btn btn-primary']) ?>
    </p>
    <?php \yii\widgets\Pjax::begin(
            ['id' => 'ArtistList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            [
                //'header'=>'Name',
                'attribute'=>'ArtistID',
				'visible' => ((Yii::$app->user->identity->UserType == 1) ? '1' : '0'),
            ],
            [
                //'header'=>'Name',
                'attribute'=>'ArtistName',
                'value' => function($data) {
                    $url = Url::toRoute('artist/update?id='.$data['ArtistID']);
                    return Html::a($data['ArtistName'],$url,['data-pjax'=>"0"]);
                },
                'format'=>'raw',
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
	    [
                'attribute'=>'Email',
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
	    [
                'attribute'=>'Nationality',
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
            [
                //'header'=>'Date Joined',
                'attribute'=>'JoinedDate',
                'value' => function($data) {
                    return getTimezone($data['JoinedDate']);
                }
            ],
            [
                //'header'=>'# Registered Users',
                'attribute'=>'TotalRegisteredUsers',
            ],
            [
                'attribute'=>'Status',
                'filter'=>Html::activeDropDownList($model, 'Status',array(""=>"All","1"=>"Active","2"=>"Inactive"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
            /*[
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update}',
                'buttons' => [
	                'update' => function ($url,$model) {
                            $url = Url::toRoute('artist/update?id='.$model['ArtistID']);
	                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>',$url,['data-pjax'=>"0"]);
	                },
	        ],
            ],*/
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>
