<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\StickerSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sticker-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'StickerID') ?>

    <?= $form->field($model, 'Title') ?>

    <?= $form->field($model, 'Description') ?>

    <?= $form->field($model, 'PurchaseType') ?>

    <?= $form->field($model, 'Cost') ?>

    <?php // echo $form->field($model, 'ProductSKU') ?>

    <?php // echo $form->field($model, 'ArtistID') ?>

    <?php // echo $form->field($model, 'IsDelete') ?>

    <?php // echo $form->field($model, 'StickerImage') ?>

    <?php // echo $form->field($model, 'CreatedBy') ?>

    <?php // echo $form->field($model, 'UpdatedBy') ?>

    <?php // echo $form->field($model, 'Created') ?>

    <?php // echo $form->field($model, 'Updated') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
