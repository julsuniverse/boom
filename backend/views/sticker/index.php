<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\StickerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Stickers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="sticker-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Add Sticker', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php \yii\widgets\Pjax::begin(
            ['id' => 'StickerList', 'timeout' => false, 'enablePushState' => false, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            [
                'header' => 'Thumbnail',
                'attribute' => 'Stickerimage',
                'value' => function ($data) {
                        //$s3GalleryImage='http://'.S3BUCKET.'.s3.amazonaws.com/'.BOOM.$data['ArtistID'].STICKER_THUMB_IMAGES.$data['StickerImage'];
						$s3GalleryImage=$data['StickerUrl'];
                        return Html::a(
                        '<img src="'.$s3GalleryImage.'" width="80" class="img-responsive file-preview-image" />',                     //link text
                        $s3GalleryImage, //link url to some route
                        [                                 // link options
                          'title'=>'Click to view image!',
                          'target'=>'_blank'
                        ]
                  );

                },
                'format' => 'raw',    
            ],
            [
                //'header'=>'Name',
                'attribute'=>'Title',
                'value' => function($data) {
                    $url = Url::toRoute('sticker/update?id='.$data['StickerID']);
                    return Html::a($data['Title'],$url,['data-pjax'=>"0"]);
                },
                'format'=>'raw',  
                'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],            
            [
                'attribute' => 'Description',
                'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
                
            ],
			
            /*[
                    'attribute'=>'PurchaseType',
                    'value' => function ($data) {
                        if($data->PurchaseType == '1') {
                            return 'Free';
                        } else if($data->PurchaseType == '2') {
                            return 'Paid';
                        }
                    },
            ],*/
            [
                //'header' => 'Price',
                'attribute' => 'Cost',
                'value' => function($data) {
                    if($data['Cost'] == '$0.00') {
                        return "Free";
                    } else {
                        return $data['Cost'];
                    }
                },
                'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
                
            ],
            [
                'header' => 'Artist',
                'attribute' => 'ArtistName',
				'filterInputOptions' => [
					'class'       => 'form-control magnifying',
					'placeholder' => 'Search']
            ],
            /*['class' => 'yii\grid\ActionColumn',
                'template'=>'{update}',
                'buttons' => [
                        'update' => function ($url,$model) {
                                $url = Url::toRoute('sticker/update?id='.$model['StickerID']);
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>',$url,['data-pjax'=>"0"]);
	                },        
	        ],
            ],*/
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>

</div>
