<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Flagged Comments';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php \yii\widgets\Pjax::begin(
            ['id' => 'FlaggedPostList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->searchflaggedpost(),        
        'filterModel' => $model,
        'columns' => [
            [
                'label'=>'Type',
                'attribute'=>'PostType',
                'filter'=>Html::activeDropDownList($model, 'PostType',array(""=>"All","1"=>"Status","2"=>"Images","3"=>"Video"),['options' => [ "" => ['selected ' => true]],'class'=>'form-control']),
            ],
            [
                'label'=>'Comment',
                'attribute'=>'ObjectMessage',
            ],
            [
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
		    //'placeholder' => 'Search'
		]
            ],        
            [
                //'header' => 'Artist',
                'attribute' => 'ArtistName',
		'visible' => ((Yii::$app->user->identity->UserType == 1) ? '1' : '0'),
            ],
            /*[
                //'header' => 'Date Posted',
                'attribute' => 'DateFlagged',
                'filter' => false,
                'value' => function($data) {
                    return getTimezone($data['DateFlagged']);
                },
                'contentOptions'=>['style'=>'width: 20%;']
            ],*/
            [
                //'header' => '# Likes',
		'label' => 'Total Flag',
                'attribute' => 'TotalFlag',
                'filter' => false,
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{delete} {view}',//{view}
                'buttons' => [
                    'delete' => function ($url, $model) {
                        $url = Url::toRoute('delete?id='.$model['ActivityID']);
                        return Html::a('<span onclick="return confirmDelete();" class="glyphicon glyphicon-trash"></span>',$url, [
                                    'title' => Yii::t('yii', 'Delete'),
                        ]);
                    },
                    'view' => function ($url, $model) {
                        $url = Url::toRoute('view?id='.$model['ActivityID']);
                        return Html::a('<span class="glyphicon glyphicon-eye-open"></span>',$url, [
                                    'title' => Yii::t('yii', 'View'),
                        ]);
                    }
                ],
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>

<script>
function confirmDelete() {
    if(confirm("Are you sure want to delete this comment ?")) {
        return true;
    } else {
        return false;
    }
}    
</script>    
