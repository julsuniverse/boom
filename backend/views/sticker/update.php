<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Sticker */

$this->title = 'Update Sticker';
$this->params['breadcrumbs'][] = ['label' => 'Stickers', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->Title, 'url' => ['view', 'id' => $model->StickerID]];
$this->params['breadcrumbs'][] = 'Update';

?>

<div class="sticker-update">
<p class="pull-right">
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
</p>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'mappedData' => $mappedData,
        'artistArray' => $artistArray,
        'IOSSKUArray'=>$IOSSKUArray,
        'androidSKUArray'=>$androidSKUArray,
        'stickerImages'=>$stickerImages,
    ]); ?>

</div>
