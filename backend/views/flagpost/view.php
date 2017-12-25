<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Members';
$this->params['breadcrumbs'][] = $this->title;
$id = $_GET['id'];
?>
<div class="post-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php \yii\widgets\Pjax::begin(
            ['id' => 'FlaggedPostList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->searchName($id),        
        'filterModel' => $model,
        'columns' => [
            [
                'label'=>'Name',
                'attribute'=>'Name',
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>

<script>
function confirmDelete() {
    if(confirm("Are you sure want to delete this comment ?")) {
        return true;
    } else {
        return false;
    }
}    
</script>    
