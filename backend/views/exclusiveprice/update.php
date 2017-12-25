<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ExclusivePrice */

$this->title = 'Update Exclusive Pricing';
$this->params['breadcrumbs'][] = ['label' => 'Exclusive Prices', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->ExclusivePriceID, 'url' => ['view', 'id' => $model->ExclusivePriceID]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="exclusive-price-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
