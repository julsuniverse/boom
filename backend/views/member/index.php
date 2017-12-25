<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\MemberSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="member-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php  echo $this->render('_search', ['model' => $model]); ?>

    <p class="pull-right">
    <?php echo Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>&nbsp;
    </p>
    
    <p class="pull-right">
        <?= Html::a('Export', ['export'], ['class' => 'btn btn-primary']) ?>
    </p>
    
    <?php \yii\widgets\Pjax::begin(
            ['id' => 'PostList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            [
                'label'=>'User Name',
                'attribute'=>'MemberName',
                'value' => function($data) {
                    $url = Url::toRoute('member/update?id='.$data['MemberID']);
                    return Html::a($data['MemberName'],$url,['data-pjax'=>"0"]);
                },
                'format'=>'raw',
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],            
	    [
                //'header' => 'Title',
                'attribute' => 'Email',
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
	    [
                //'header' => 'Title',
                'attribute' => 'ZipCode',
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
            /*[
                //'header' => 'Mobile Number',
                'attribute' => 'MobileNo',
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],*/
            [
                //'header' => 'Gender',
                'attribute' => 'Gender',
                'filter'=>Html::activeDropDownList($model, 'Gender',array(""=>"All","1"=>"Male","2"=>"Female"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
                'headerOptions' => ['width' => '150'],
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
                //'header' => 'Artist',
                'attribute' => 'DeviceType',
                'filter'=>Html::activeDropDownList($model, 'DeviceType',array(""=>"All","1"=>"iOS","2"=>"Android"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
            [
                //'header' => 'Status',
                'attribute' => 'Status',
                'filter'=>Html::activeDropDownList($model, 'Status',array(""=>"All","1"=>"Active","2"=>"Inactive","3"=>"Pending"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
            ['class' => 'yii\grid\ActionColumn',
                'template'=>'{delete}', //{view}
                'buttons' => [
                        'delete' => function ($url,$model) {
                                $blockedString = "Blocked";
                                if($model['IsBlocked'] == "1") {
                                    $blockedString = "Unblocked";
                                }
                                $url = Url::toRoute('member/block?id='.$model['MemberID'].'&blocked='.$model['IsBlocked']);
                                return Html::a($blockedString,$url,['data-pjax'=>"0"]);
	                },        
	        ],
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>
