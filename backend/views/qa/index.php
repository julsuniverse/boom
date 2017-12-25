<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Q&A';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?php //echo  Html::a('Add Q&A', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php \yii\widgets\Pjax::begin(
            ['id' => 'QAList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            [
                'label'=>'Description',
                'attribute'=>'Description',
                'value' => function($data) {
                    $stringDot = ''; 
                    $url = Url::toRoute('qa/update?id='.$data['PostID']);
                    if($data['QAType'] == "1" || $data['QAType'] == "4") {
                        if(isset($data['Description']) && trim($data['Description'])!='') { 
                            return Html::a(smiley($data['Description']),$url,['data-pjax'=>"0"]);
                        } else {
                            if(strlen($data['Description'])>25) {
                                $stringDot = '....';
                            }
                            return Html::a(smiley(substr($data['Description'],0,25)).$stringDot,$url,['data-pjax'=>"0"]);
                        }
                    } else {
                        return Html::a(("Video"),$url,['data-pjax'=>"0"]);
                    }    
                },
                'format'=>'raw',
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],        
            [
                //'header' => 'Artist',
                'attribute' => 'ArtistName',
		'visible' => ((Yii::$app->user->identity->UserType == 1 || Yii::$app->user->identity->UserType == 4) ? '1' : '0'),
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
            [
                'attribute' => 'DatePosted',
                'filter' => false,
                'value' => function($data) {
                    return getTimezone($data['DatePosted']);
                },
                'contentOptions'=>['style'=>'width: 20%;']
            ],
            [
		'label' => 'Likes',
                'attribute' => 'TotalLikes',
                'filter' => false,
            ],
            [
		'label' => 'Comments',
                'attribute' => 'TotalComments',
                'filter' => false,
            ],
            [
                'attribute' => 'Status',
                'filter'=>Html::activeDropDownList($model, 'Status',array(""=>"All","1"=>"Active","2"=>"Inactive"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>

</div>
