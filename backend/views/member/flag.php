<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Flag';
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
            ['id' => 'FlagList', 'timeout' => false, 'enablePushState' => false, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute'=>'PostType',
                'filter'=>Html::activeDropDownList($model, 'PostType',array(""=>"All","1"=>"Status","2"=>"Images","3"=>"Video"),['options' => [ "" => ['selected ' => true]],'class'=>'form-control']),
            ],
            [
                'header' => 'Title',
                'attribute' => 'PostTitle',
            ],
            [
                'header' => 'Artist Name',
                'attribute' => 'ArtistName',
            ],
            [
                'header' => 'Date Liked',
                'attribute' => 'DateLiked',
                'filter' => false,
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>

</div>
