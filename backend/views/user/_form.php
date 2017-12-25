<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $form yii\widgets\ActiveForm */

$readOnly = array();
if(!$model->isNewRecord) {
    $readOnly = array('readonly'=>'readonly');
    $model->Password = '';
}

?>

<div class="user-form">
    <?php $form = ActiveForm::begin(); ?>
    
    <?php //echo $form->errorSummary($model); ?>
    
    <?= $form->field($model, 'UserName')->textInput($readOnly) ?>
    
    <?= $form->field($model, 'Email')->textInput() ?>
    
    <?= $form->field($model, 'Password')->passwordInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'ConfirmPassword')->passwordInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'Status')->dropDownList(\backend\models\User::getStatus()) //['prompt' => ''] ?>
	
	<?php
		if($model->UserType == 4){
			$companies = (array) \backend\models\Artist_company::getArtistCompanyList();
			echo $form->field($model, 'ArtistCompanyID')->dropDownList($companies,[])->label("Company");
		}
	?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<script>
    
</script>    
