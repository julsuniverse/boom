<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ArtistSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'My Music TV';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="member-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Add Album', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?php \yii\widgets\Pjax::begin(
            ['id' => 'MymusictvList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            [
                'header' => 'Album Image',
                'attribute' => 'AlbumImage',
                'filter'=>false,
                'value' => function ($data) {
                        $s3GalleryImage='http://'.S3BUCKET.'.s3.amazonaws.com/'.BOOM.$data['ArtistID'].ALBUM_THUMB_IMAGES.$data['AlbumImage'];
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
                'attribute'=>'AlbumTitle',
                'value' => function($data) {
                    $url = Url::toRoute('mymusictv/update?id='.$data['ID']);
                    return Html::a($data['AlbumTitle'],$url,['data-pjax'=>"0"]);
                },
                'format'=>'raw',
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
            [
                'attribute' => 'ArtistName',
		'visible' => ((Yii::$app->user->identity->UserType == 1) ? '1' : '0'),
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
	    [
                'attribute'=>'AlbumLink',
		'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
            [
                'attribute'=>'Status',
                'filter'=>Html::activeDropDownList($model, 'Status',array(""=>"All","1"=>"Active","2"=>"Inactive"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
            /*[
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{update}',
                'buttons' => [
	                'update' => function ($url,$model) {
                            $url = Url::toRoute('artist/update?id='.$model['ArtistID']);
	                    return Html::a('<span class="glyphicon glyphicon-pencil"></span>',$url,['data-pjax'=>"0"]);
	                },
	        ],
            ],*/
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>
