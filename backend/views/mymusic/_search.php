<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\PostSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="post-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'PostID') ?>

    <?= $form->field($model, 'ArtistID') ?>

    <?= $form->field($model, 'PostTitle') ?>

    <?= $form->field($model, 'Description') ?>

    <?= $form->field($model, 'ThumbImage') ?>

    <?php // echo $form->field($model, 'URL') ?>

    <?php // echo $form->field($model, 'TotalComments') ?>

    <?php // echo $form->field($model, 'TotalUpVote') ?>

    <?php // echo $form->field($model, 'TotalShare') ?>

    <?php // echo $form->field($model, 'TotalFlag') ?>

    <?php // echo $form->field($model, 'IsExclusive') ?>

    <?php // echo $form->field($model, 'IsShared') ?>

    <?php // echo $form->field($model, 'Price') ?>

    <?php // echo $form->field($model, 'IsDelete') ?>

    <?php // echo $form->field($model, 'PostType') ?>

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
