<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use backend\models\Artist_company;


$this->title = 'Artist Companies';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="artist-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Add Company', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php \yii\widgets\Pjax::begin(
            ['id' => 'ArtistList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
		
    <?= GridView::widget([
        'dataProvider' => $dataProvider,        
        'columns' => [
            [
                'attribute'=>'Id',
            ],
            [
                'attribute'=>'Name',
                'value' => function($data) {
                    $url = Url::toRoute('artistcompany/update?id='.$data['Id']);
                    return Html::a($data['Name'],$url,['data-pjax'=>"0"]);
                },
                'format'=>'raw',
				'filterInputOptions' => [
					'class'       => 'form-control magnifying',
					'placeholder' => 'Search'
				]
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>
