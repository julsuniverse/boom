<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Posts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Add Post', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php \yii\widgets\Pjax::begin(
            ['id' => 'PostList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            [
                'label'=>'Type',
                'attribute'=>'PostType',
                'filter'=>Html::activeDropDownList($model, 'PostType',array(""=>"All","1"=>"Status","2"=>"Images","3"=>"Video","5"=>"Article"),['options' => [ "" => ['selected ' => true]],'class'=>'form-control']),
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
                //'header' => 'Artist',
                'attribute' => 'ArtistName',
		'visible' => ((Yii::$app->user->identity->UserType == 1 || Yii::$app->user->identity->UserType == 4) ? '1' : '0'),
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
            [
                //'header' => 'Date Posted',
                'attribute' => 'DatePosted',
                'filter' => false,
                'value' => function($data) {
                    return getTimezone($data['DatePosted']);
                },
                'contentOptions'=>['style'=>'width: 20%;']
            ],
            [
                //'header' => '# Likes',
		'label' => 'Likes',
                'attribute' => 'TotalLikes',
                'filter' => false,
            ],
            [
                //'header' => '# Comments',
		'label' => 'Comments',
                'attribute' => 'TotalComments',
                'filter' => false,
            ],
	   /* [
                //'header' => '# Flag',
		'label' => 'Flag',
                'attribute' => 'TotalFlag',
                'filter' => false,
            ],*/
	    [
                //'header' => '# Share',
		'label' => 'Share',
                'attribute' => 'TotalShare',
                'filter' => false,
            ],
            [
                'label' => 'Exclusive',
                'attribute' => 'IsExclusive',
                'filter'=>Html::activeDropDownList($model, 'IsExclusive',array(""=>"All","1"=>"Normal","2"=>"Exclusive"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
            [
                //'header' => 'Status',
                'attribute' => 'Status',
                'filter'=>Html::activeDropDownList($model, 'Status',array(""=>"All","1"=>"Active","2"=>"Inactive"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{delete}',
                'buttons' => [
	                'delete' => function ($url,$model) {
                            $url = Url::toRoute('post/delete?id='.$model['PostID']);
	                    return Html::a('<span onclick="return confirmDelete();" class="glyphicon glyphicon-trash"></span>',$url,['data-pjax'=>"0"]);
	                },
	        ],
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>

<script>
/********** Delete confirmation ****************/    
function confirmDelete() {
    if(confirm("Are you sure want to delete this post?")) {
        return true;
    } else {
        return false;
    }
}    
</script>    
