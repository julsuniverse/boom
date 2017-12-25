<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Post */

$this->title = 'Update Post: ' . ' ' . $model->PostID;
$this->params['breadcrumbs'][] = ['label' => 'Posts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->PostID, 'url' => ['view', 'id' => $model->PostID]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="post-update">

<!--    <h1><?= Html::encode($this->title) ?></h1>-->

    <?= $this->render('_form', [
        'model' => $model,
        'galleryData' => $galleryData,
    ]) ?>

</div>
