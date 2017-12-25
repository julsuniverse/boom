<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ArtistSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="artist-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'ArtistID') ?>

    <?= $form->field($model, 'ArtistName') ?>

    <?= $form->field($model, 'ProfileThumb') ?>

    <?= $form->field($model, 'Email') ?>

    <?= $form->field($model, 'DOB') ?>

    <?php // echo $form->field($model, 'Nationality') ?>

    <?php // echo $form->field($model, 'Address') ?>

    <?php // echo $form->field($model, 'Website') ?>

    <?php // echo $form->field($model, 'YoutubeChannelName') ?>

    <?php // echo $form->field($model, 'YearsActive') ?>

    <?php // echo $form->field($model, 'Genre') ?>

    <?php // echo $form->field($model, 'AboutMe') ?>

    <?php // echo $form->field($model, 'YouTubePageURL') ?>

    <?php // echo $form->field($model, 'LinkedInPageURL') ?>

    <?php // echo $form->field($model, 'TwitterPageURL') ?>

    <?php // echo $form->field($model, 'FacebookPageURL') ?>

    <?php // echo $form->field($model, 'DeviceType') ?>

    <?php // echo $form->field($model, 'DeviceToken') ?>

    <?php // echo $form->field($model, 'UserID') ?>

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
