<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\Setting */
/* @var $form yii\widgets\ActiveForm */
?>

<style>
#setting-qatype label {
    margin-right: 10px;
}   
</style>    

<?php 
$artistList = backend\models\Sticker::getAllArtistList();
$readonly = array('prompt'=>'--Select Artist--');

$textPrice = array('maxlength' => 255);
$videoPrice = array('maxlength' => 255);
$photoPrice = array('maxlength' => 255);
if($model->QaType == 2) {
    $textPrice = array('maxlength' => 255,'disabled'=>'disabled',"style"=>"opacity: 0.5 !important;");
	$photoPrice = array('maxlength' => 255,'disabled'=>'disabled',"style"=>"opacity: 0.5 !important;");
}
if($model->QaType == 1) {
    $videoPrice = array('maxlength' => 255,'disabled'=>'disabled',"style"=>"opacity: 0.5 !important;");
	$photoPrice = array('maxlength' => 255,'disabled'=>'disabled',"style"=>"opacity: 0.5 !important;");
}
if($model->QaType == 4) {
	$textPrice = array('maxlength' => 255,'disabled'=>'disabled',"style"=>"opacity: 0.5 !important;");
	$videoPrice = array('maxlength' => 255,'disabled'=>'disabled',"style"=>"opacity: 0.5 !important;");
}
if($model->QaType == 3) {
    $photoPrice = array('maxlength' => 255,'disabled'=>'disabled',"style"=>"opacity: 0.5 !important;");
}
if($model->QaType == 5) {
	$videoPrice = array('maxlength' => 255,'disabled'=>'disabled',"style"=>"opacity: 0.5 !important;");
}
if($model->QaType == 6) {
	$textPrice = array('maxlength' => 255,'disabled'=>'disabled',"style"=>"opacity: 0.5 !important;");
}
?>

<div class="setting-form">
    <?php $form = ActiveForm::begin(); ?>
    <?php if(Yii::$app->user->identity->UserType == 1) {
        echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Sticker::getArtistList(),$readonly,array('prompt'=>'--Select Artist--'))->label("Select Artist");
    }else if(Yii::$app->user->identity->UserType == 4) {
		$companyID = Yii::$app->session->get('CompanyID');
        echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Artist_company::getCompanyArtistList($companyID),$readonly,array('prompt'=>'--Select Artist--'))->label("Select Artist");
    }else {
        $model->ArtistID = Yii::$app->user->identity->ArtistID;
        echo $form->field($model, 'ArtistID')->hiddenInput()->label(false);
    } ?>
    <?= $form->field($model, 'QAModuleName')->textInput(['maxlength' => true])->label("Tab Name"); ?>
    <?= $form->field($model, 'QaType')->checkboxList(['1'=>'Text','2'=>'Video','4'=>'Photo'])->label("Type"); ?>
    <?= $form->field($model, 'TextPrice')->textInput($textPrice); ?>
    <?= $form->field($model, 'TextProductSKUID')->textInput($textPrice)->label('Text Prodcut SKUID'); ?>
    <?= $form->field($model, 'VideoPrice')->textInput($videoPrice); ?>
    <?= $form->field($model, 'VideoProductSKUID')->textInput($videoPrice)->label('Video Prodcut SKUID'); ?>
	<?= $form->field($model, 'PhotoPrice')->textInput($photoPrice); ?>
    <?= $form->field($model, 'PhotoProductSKUID')->textInput($photoPrice)->label('Photo Prodcut SKUID'); ?>
<!--    <h1><?php //echo "Product Configuration"; ?></h1>-->
    <div style="display: none;">
        <?= $form->field($model, 'ProductType')->radioList(['1'=>'Paid Post','2'=>'Subscription'])->label("Type"); ?>
        <div id="prodcuctSKU" <?php if($model->ProductType != "2") { ?> style="display:none;" <?php } ?>>
            <?= $form->field($model, 'ProductPrice')->textInput()->label('Product Price'); ?>
            <?= $form->field($model, 'ProductSKUID')->textInput()->label('Prodcut SKUID'); ?>
        </div>    
    </div>    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php 
/************ validate price ***************/
$this->registerJs('$(":checkbox").change(function(){
                if(this.checked) {
                    if(this.value == "1") {
                        $("#setting-textprice").removeAttr("disabled");
                        $("#setting-textprice").removeAttr("style");
                        $("#setting-textproductskuid").removeAttr("disabled");
                        $("#setting-textproductskuid").removeAttr("style");
                    }
					if(this.value == "2") {
                        $("#setting-videoprice").removeAttr("disabled");
                        $("#setting-videoprice").removeAttr("style"); 					
                        $("#setting-videoproductskuid").removeAttr("disabled");
                        $("#setting-videoproductskuid").removeAttr("style");    
                    }
					if(this.value == "4") {
						$("#setting-photoprice").removeAttr("disabled");
                        $("#setting-photoprice").removeAttr("style");    
                        $("#setting-photoproductskuid").removeAttr("disabled");
                        $("#setting-photoproductskuid").removeAttr("style");    
                    }
                } else {
                    if(this.value == "1") {
                        $("#setting-textprice").attr("disabled","disabled");
                        $("#setting-textprice").attr("style","opacity: 0.5 !important;");
                        $("#setting-textproductskuid").attr("disabled","disabled");
                        $("#setting-textproductskuid").attr("style","opacity: 0.5 !important;");
                    }
					if(this.value == "2") {    
                        $("#setting-videoprice").attr("disabled","disabled");
                        $("#setting-videoprice").attr("style","opacity: 0.5 !important;");
                        $("#setting-videoproductskuid").attr("disabled","disabled");
                        $("#setting-videoproductskuid").attr("style","opacity: 0.5 !important;");
                    }
					if(this.value == "4") {    
                        $("#setting-photoprice").attr("disabled","disabled");
                        $("#setting-photoprice").attr("style","opacity: 0.5 !important;");
                        $("#setting-photoproductskuid").attr("disabled","disabled");
                        $("#setting-photoproductskuid").attr("style","opacity: 0.5 !important;");
                    }   
                }
        });
        $(":radio").change(function(){
            if(this.value == "2") {
                $("#prodcuctSKU").show();
            } else {
                $("#prodcuctSKU").hide();
            }
        });
        $("#setting-artistid").change(function() {
            document.location.href="'.Url::toRoute('setting/update?id=').'"+this.value;
        });
        $("input[type=checkbox]").each(function () {
                if(this.value == "1") {
                    $(this).attr("id","qaText");
                }
                if(this.value == "2") {
                    $(this).attr("id","qaVideo");
                }
				if(this.value == "4") {
                    $(this).attr("id","qaPhoto");
                }
        });
        $("#w0").on(\'beforeSubmit\', function (e) { 
            if($("#qaText").val() == "1") {
               if($("#setting-textprice").val() == "") {
                   alert("Please enter text price");
                   return false;
               }
               if($("#setting-textproductskuid").val() == "") {
                   alert("Please enter text sku ID");
                   return false;
               }
            } 
            if($("#qaVideo").val() == "2") {
               if($("#setting-videoprice").val() == "") {
                   alert("Please enter video price");
                   return false;
               }
               if($("#setting-videoproductskuid").val() == "") {
                   alert("Please enter video sku ID");
                   return false;
               }
            }
			if($("#qaPhoto").val() == "4") {
               if($("#setting-photoprice").val() == "") {
                   alert("Please enter photo price");
                   return false;
               }
               if($("#setting-photoproductskuid").val() == "") {
                   alert("Please enter photo sku ID");
                   return false;
               }
            }
        });
');
?>
