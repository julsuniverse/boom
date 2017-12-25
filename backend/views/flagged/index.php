<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Flagged Post';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?php \yii\widgets\Pjax::begin(
            ['id' => 'PostList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            [
		'label' => 'Type',
                'attribute'=>'PostType',
                'filter'=>Html::activeDropDownList($model, 'PostType',array(""=>"All","1"=>"Status","2"=>"Images","3"=>"Video"),['options' => [ "" => ['selected ' => true]],'class'=>'form-control']),
            ],
	    [
                //'header'=>'Name',
                'label'=>'Title',
                'attribute'=>'PostTitle',
                'value' => function($data) {
                    $stringDot = ''; 
                    $url = Url::toRoute('post/update?id='.$data['PostID']);
                    if($data['PostType'] == 'Status') {
                            if(strlen($data['Description'])>25) {
                                $stringDot = '....';
                            }
                            return Html::a(smiley(substr($data['Description'],0,25)).$stringDot,$url,['data-pjax'=>"0"]);
                    } else {
                        if(isset($data['PostTitle']) && trim($data['PostTitle'])!='') { 
                            return Html::a(smiley($data['PostTitle']),$url,['data-pjax'=>"0"]);
                        } else {
                            if(strlen($data['Description'])>25) {
                                $stringDot = '....';
                            }
                            if($data['Description'] != '') {
                                return Html::a(smiley(substr($data['Description'],0,25)).$stringDot,$url,['data-pjax'=>"0"]);
                            } else {
                                return Html::a('No Title',$url,['data-pjax'=>"0"]);
                            }    
                        }
                    }
                },
                'format'=>'raw',
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ], 
            [
                'header' => 'No Of Flagged',
                'attribute' => 'Total',
            ],
            /*[
                //'header' => 'Date Flagged',
                'attribute' => 'DateFlagged',
                'value' => function($data) {
                    return getTimezone($data['DateFlagged']);
                }
            ],*/
            [
                //'header' => 'Artist',
                'attribute' => 'ArtistName',
		'visible' => ((Yii::$app->user->identity->UserType == 1) ? '1' : '0'),
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
            [
		'header' => 'Action',
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update}',
                'buttons' => [
	                'update' => function ($url,$model) {
                            $urlForDelete = Url::toRoute('flagged/remove?id='.$model['PostID']);
                            $urlForFlagged = ""; //Url::toRoute('flagged/ignore?id='.$model['PostID'].'&actid='.$model['ActivityID']);
	                    //return Html::a('<span class="glyphicon glyphicon-remove" title="Remove"></span>',$urlForDelete,['data-pjax'=>"0",'data-confirm'=>'Are you sure you want to remove this post?']).Html::a('<span class="glyphicon glyphicon-flag" title="Ignore"></span>',$urlForFlagged,['data-pjax'=>"0",'data-confirm'=>'Are you sure you want to ignore this post?']);
                            return Html::a('<span class="glyphicon glyphicon-remove" title="Remove"></span>',$urlForDelete,['data-pjax'=>"0",'data-confirm'=>'Are you sure you want to remove this post?']);
	                },
	        ],
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>

</div>
