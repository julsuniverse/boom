<div class="post-update">
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
    <p class="pull-right">
            <?= yii\helpers\Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
    </p>
    <h1><?php //echo Html::encode($this->title) ?></h1>

    <?php \yii\widgets\Pjax::begin(
            ['id' => 'LikesList', 'timeout' => false, 'enablePushState' => false, 'clientOptions' => ['method' => 'GET']]
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
                'attribute' => 'Email',
            ],
            [
                'attribute' => 'DateLiked',
                'value' => function($data) {
                    return getTimezone($data['DateLiked']);
                },
                'filter' => false,
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>

</div>
</div>