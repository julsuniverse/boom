<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\SimilarappSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="similarapp-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'AppID') ?>

    <?= $form->field($model, 'ArtistID') ?>

    <?= $form->field($model, 'Name') ?>

    <?= $form->field($model, 'Url') ?>

    <?= $form->field($model, 'AppIcon') ?>

    <?php // echo $form->field($model, 'Type') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
