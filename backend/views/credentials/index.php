<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use backend\models\Credentials;


$this->title = 'Credentials';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="artist-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Add Product', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php \yii\widgets\Pjax::begin(
            ['id' => 'CredentialsList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
		
    <?= GridView::widget([
        'dataProvider' => $dataProvider,        
        'columns' => [
            [
                'attribute'=>'ID',
                'value' => function($data) {
                    $url = Url::toRoute('credentials/update?id='.$data['ID']);
                    return Html::a($data['ID'],$url,['data-pjax'=>"0"]);
                },
                'format'=>'raw',
				'filterInputOptions' => [
					'class'       => 'form-control magnifying',
					'placeholder' => 'Search'
				]
            ],
			 [
                'attribute'=>'GUID',
            ],
			[
                'attribute'=>'AffiliateId',
            ],
   //          [
   //              'attribute'=>'artistID',
   //          ],
			// [
   //              'attribute'=>'StaticAdUnitId',
   //          ],
   //          [
   //              'attribute'=>'VideoAdUnitId',
   //          ],
   //          [
   //              'attribute'=>'BannerAdUnitId',
   //          ],
   //          [
   //              'attribute'=>'NativeAdUnitId',
   //          ],
   //          [
   //              'attribute'=>'AppId',
   //          ],
   //          [
   //              'attribute'=>'AppSecret',
   //          ],
   //          [
   //              'attribute'=>'InterstitialPlacementId',
   //          ],
   //          [
   //              'attribute'=>'BannerPlacementId',
   //          ],
   //          [
   //              'attribute'=>'NativeAdPlacementId',
   //          ],


        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>
