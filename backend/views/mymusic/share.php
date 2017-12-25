<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Likes';
$this->params['breadcrumbs'][] = $this->title;
$id = $_GET['id'];
if(isset($id) && $id!='') {
    include 'tabs.php';
}
?>
<div class="post-index">

    <h1><?php //echo Html::encode($this->title) ?></h1>

    <?php \yii\widgets\Pjax::begin(
            ['id' => 'ShareList', 'timeout' => false, 'enablePushState' => false, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            [
                //'header' => 'Name',
                'attribute' => 'MemberName',
            ],
            [
                //'header' => 'Email',
                'attribute' => 'Email',
            ],
            [
                //'header' => 'Website',
                'attribute' => 'Website',
                'filter'=>Html::activeDropDownList($model, 'Website',array(""=>"All","1"=>"Like","2"=>"Comment","3"=>"Facebook","4"=>"Twitter","5"=>"Flag"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
            [
                //'header' => 'Date Liked',
                'attribute' => 'DateLiked',
                'filter' => false,
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>

</div>
