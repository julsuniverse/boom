<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Push Notification Statistics';
$this->params['breadcrumbs'][] = $this->title;
$id = $_GET['id'];
if(isset($id) && $id!='') {
    include 'tabs.php';
}
?>
<div class="post-index">
<p class="pull-right">
        <?= yii\helpers\Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
</p>
    <h1><?php //echo Html::encode($this->title) ?></h1>

    <?php \yii\widgets\Pjax::begin(
            ['id' => 'PushList', 'timeout' => false, 'enablePushState' => false, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search($id),        
        'filterModel' => $model,
        'columns' => [
            [
                'attribute' => 'Name',
                'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
            [
                'attribute' => 'Email',
                'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
            [
                'attribute' => 'AgeGroup',
                'filter'=>Html::activeDropDownList($model, 'AgeGroup',array(""=>"All","1"=>"18-30","2"=>"31-45","3"=>"46 & above"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
            [
                'attribute' => 'DateJoined',
                'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		],
                'value' => function($data) {
                    return getTimezone($data['DateJoined']);
                }
            ],
            [
                'attribute' => 'DeviceType',
                'filter'=>Html::activeDropDownList($model, 'DeviceType',array(""=>"All","1"=>"iOS","2"=>"Android"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>

</div>
