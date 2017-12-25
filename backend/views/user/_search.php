<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\UserSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'UserID') ?>

    <?= $form->field($model, 'UserName') ?>

    <?= $form->field($model, 'Password') ?>

    <?= $form->field($model, 'Status') ?>

    <?= $form->field($model, 'LastLoginDateTime') ?>

    <?php // echo $form->field($model, 'UserType') ?>

    <?php // echo $form->field($model, 'ActivationCode') ?>

    <?php // echo $form->field($model, 'ActivatedOn') ?>

    <?php // echo $form->field($model, 'ArtistID') ?>

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
