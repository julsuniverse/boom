<div class="post-update">
<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Comment';
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
            ['id' => 'CommentList', 'timeout' => false, 'enablePushState' => false, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            [
                'attribute' => 'MemberName',
            ],
            [
                'attribute' => 'Email',
            ],
            [
                'attribute' => 'DateCommented',
                'value' => function($data) {
                    return getTimezone($data['DateCommented']);
                },
                'filter' => false,
            ],
            [
                'attribute' => 'Comment',
                'value' => function($data) {
                    return smiley($data['Comment']);
                },
                'format' => 'html',        
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>

</div>
</div>    
    
