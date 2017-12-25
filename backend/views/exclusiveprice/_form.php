<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\ExclusivePrice */
/* @var $form yii\widgets\ActiveForm */
$readOnlyForArtist = array('maxlength' => 100,'prompt'=>'--Select Artist--');
?>

<div class="exclusive-price-form">
    <?php $form = ActiveForm::begin(); ?>
    <?php
    if(Yii::$app->user->identity->UserType == 1)
    {
        echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Sticker::getArtistList(),$readOnlyForArtist);
    }
    ?>
    <?= $form->field($model, 'StatusPrice')->textInput(['maxlength' => 10]) ?>
    <?= $form->field($model, 'StatusSKUID')->textInput(['maxlength' => 100]) ?>
    <?= $form->field($model, 'ImagePrice')->textInput(['maxlength' => 10]) ?>
    <?= $form->field($model, 'ImageSKUID')->textInput(['maxlength' => 100]) ?>
    <?= $form->field($model, 'VideoPrice')->textInput(['maxlength' => 10]) ?>
    <?= $form->field($model, 'VideoSKUID')->textInput(['maxlength' => 100]) ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php 
$this->registerJs('
    $(document).ready(function() {
        $("#exclusiveprice-artistid").change(function() {
            var artistID = $("#exclusiveprice-artistid").val();
            document.location.href= "'.Url::to(['exclusiveprice/update']).'?id="+artistID;
        });
    });    
');
?>
