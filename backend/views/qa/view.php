<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Post */

$this->title = $model->PostID;
$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="post-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->PostID], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->PostID], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'PostID',
            'ArtistID',
            'PostTitle',
            'Description:ntext',
            'ThumbImage',
            'URL:url',
            'TotalComments',
            'TotalUpVote',
            'TotalShare',
            'TotalFlag',
            'IsExclusive',
            'IsShared',
            'Price',
            'IsDelete',
            'PostType',
            'CreatedBy',
            'UpdatedBy',
            'Created',
            'Updated',
        ],
    ]) ?>

</div>
