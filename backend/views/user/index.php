<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

if($model->Role == 1){$this->title = 'Staff';}else{$this->title = 'Company Admin';}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <p>
		<?php
			$method = 'create'; $label = 'Add Staff';
			if($model->Role == 1){ $method = 'create?usertype=1'; $label = 'Add Staff'; }else{ $method = 'create?usertype=4'; $label = 'Add Company Admin'; }
		?>
		<?= Html::a($label, [$method], ['class' => 'btn btn-success']) ?>
    </p>
    <?php \yii\widgets\Pjax::begin(
            ['id' => 'PostList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
                [
                    'label'=>'Username',
                    'attribute'=>'UserName',
                    'value' => function($data) {
                        $url = Url::toRoute('user/update?id='.$data['UserID']);
                        return Html::a($data['UserName'],$url,['data-pjax'=>"0"]);
                    },
                    'format'=>'raw',        
                ],
                'Email',
                [
                    //'header' => 'Status',
                    'attribute' => 'Status',
                    'filter'=>Html::activeDropDownList($model, 'Status',array(""=>"All","1"=>"Active","2"=>"Inactive","3"=>"Pending"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
                ],            
                /*[
                    'class' => 'yii\grid\ActionColumn',
                    'template'=>'{update}',
                    'buttons' => [
                            'update' => function ($url,$model) {
                                $url = Url::toRoute('user/update?id='.$model['UserID']);
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>',$url,['data-pjax'=>"0"]);
                            },
                    ],
                ],*/
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>
