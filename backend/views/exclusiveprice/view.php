<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\ExclusivePrice */

$this->title = $model->ExclusivePriceID;
$this->params['breadcrumbs'][] = ['label' => 'Exclusive Prices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="exclusive-price-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->ExclusivePriceID], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->ExclusivePriceID], [
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
            'ExclusivePriceID',
            'StatusPrice',
            'ImagePrice',
            'VideoPrice',
        ],
    ]) ?>

</div>
