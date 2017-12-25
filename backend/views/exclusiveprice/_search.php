<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ExclusivePriceSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="exclusive-price-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'ExclusivePriceID') ?>

    <?= $form->field($model, 'StatusPrice') ?>

    <?= $form->field($model, 'ImagePrice') ?>

    <?= $form->field($model, 'VideoPrice') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
