<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use backend\models\Nativeproducts;


$this->title = 'Native Products';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="artist-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Add Product', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php \yii\widgets\Pjax::begin(
            ['id' => 'NativeProductList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
		
    <?= GridView::widget([
        'dataProvider' => $dataProvider,        
        'columns' => [
            [
                'attribute'=>'ID',
                'value' => function($data) {
                    $url = Url::toRoute('nativeproducts/update?id='.$data['ID']);
                    return Html::a($data['ID'],$url,['data-pjax'=>"0"]);
                },
                'format'=>'raw',
				'filterInputOptions' => [
					'class'       => 'form-control magnifying',
					'placeholder' => 'Search'
				]
            ],
			 [
                'attribute'=>'Price',
            ],
			[
                'attribute'=>'ProductSKUIOS',
            ],
			[
                'attribute'=>'ProductSKUANdr',
            ],
			[
                'attribute'=>'Type',
				'value' => function($data) {
                    switch($data->Type){
						case 1: return 'Subscribe'; break;
						case 2: return 'text question'; break;
						case 3: return 'Video question'; break;
						case 4: return 'Photo question'; break;
					}
                },
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>
