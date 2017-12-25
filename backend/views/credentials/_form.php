<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;


$readonly = array('maxlength' =>'100');
if(!$model->isNewRecord) {
    $readonly = array('disabled'=>'disabled','maxlength' => 100);
}
?>

<style>

</style>
 
<div class="artist-form">
    <?php $form = ActiveForm::begin([
                'options' => []
            ]); ?>
    
    <?= $form->field($model, 'artistID')->dropDownList(\backend\models\Sticker::getArtistList(),array('prompt'=>'--Select Artist--'),array('prompt'=>'--Select Artist--'))->label("Artist") ?>  
    <?= $form->field($model, 'GUID')->textInput() ?>
    <?= $form->field($model, 'AffiliateId')->textInput(['maxlength' => 100]) ?>

    <h2>Admob</h2>

    <?= $form->field($model, 'StaticAdUnitId')->textInput(['maxlength' => 100]) ?> 
    <?= $form->field($model, 'VideoAdUnitId')->textInput(['maxlength' => 100]) ?> 
    <?= $form->field($model, 'BannerAdUnitId')->textInput(['maxlength' => 100]) ?> 
    <?= $form->field($model, 'NativeAdUnitId')->textInput(['maxlength' => 100]) ?> 

    <h2>Facebook</h2>

    <?= $form->field($model, 'AppId')->textInput(['maxlength' => 100]) ?> 
    <?= $form->field($model, 'AppSecret')->textInput(['maxlength' => 100]) ?> 
    <?= $form->field($model, 'InterstitialPlacementId')->textInput(['maxlength' => 100]) ?> 
    <?= $form->field($model, 'BannerPlacementId')->textInput(['maxlength' => 100]) ?> 
    <?= $form->field($model, 'NativeAdPlacementId')->textInput(['maxlength' => 100]) ?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>


<?php 
/************ validate price ***************/
$this->registerJs('
        $("#credentials-artistid").change(function() {
            document.location.href="'.Url::toRoute('credentials/update?id=').'"+this.value;
        });
        
');
?>
