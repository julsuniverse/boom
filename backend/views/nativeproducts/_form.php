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
    
    
    <?= $form->field($model, 'Price')->textInput() ?>
    <?= $form->field($model, 'ProductSKUIOS')->textInput(['maxlength' => 100]) ?>
	<?= $form->field($model, 'ProductSKUANdr')->textInput(['maxlength' => 100]) ?>
	<?= $form->field($model, 'Type')->dropDownList([ 1 => 'Subscribe', 2 => 'text question', 3 => 'Video question', 4 => 'Photo question'],$readonly)->label("Type") ?>
	<?= $form->field($model, 'ComID')->dropDownList(\backend\models\Artist_company::getArtistCompanyList(),[],array('prompt'=>'--Select Company--'))->label("Company Name") ?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>