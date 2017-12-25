<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\file\FileInput;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\Member */
/* @var $form yii\widgets\ActiveForm */

$readonly = array();
$readOnlyForArtist = array('maxlength' => 100,'prompt'=>'--Select Artist--');
if(!$model->isNewRecord) {
    $readonly = array('readonly'=>'readonly','maxlength' => 100);
}
include 'tabs.php';

?>
<style>
.imageGallery {
    float:left;
    margin:5px;
}
.imageList {
    border: 1px solid grey;
    float: left;
    height: auto;
    margin: 0;
    width: auto;
}
</style>

<div class="member-form">
    <?php $form = ActiveForm::begin([
                'options' => ['enctype'=>'multipart/form-data']
            ]); ?>
    <?php //echo $form->errorSummary($model); ?>
    
    <?php
    if(!$model->isNewRecord)
    {
        $LastLoginDateTime = ((isset($modelUser->LastLoginDateTime) && $modelUser->LastLoginDateTime != '') ? date("d/m/Y h:i A", strtotime($modelUser->LastLoginDateTime)) : '');
        $Created = ((isset($modelUser->Created) && $modelUser->Created != '') ? date("d/m/Y h:i A", strtotime($modelUser->Created)) : '');
        
        echo "<label class='control-label'>Last login time: {$LastLoginDateTime}</label>";
        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        echo "<label class='control-label'>Date joined: {$Created}</label>";
    }
    ?>
    
    <?php
    if(Yii::$app->user->identity->UserType == 1)
    {
        echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Sticker::getArtistList(),$readOnlyForArtist);
    }else if(Yii::$app->user->identity->UserType == 4)
    {
        $companyID = Yii::$app->session->get('CompanyID');
		echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Artist_company::getCompanyArtistList($companyID),$readOnlyForArtist);
    }
    else
    {
        $model->ArtistID = Yii::$app->user->identity->ArtistID;
        echo $form->field($model, 'ArtistID')->hiddenInput()->label(false);
    }
    ?>
    
    <?= $form->field($model, 'MemberName')->textInput(['maxlength' => 100]) ?>
    
    <?= $form->field($model, 'UserName')->textInput($readonly) ?>
    
    <?= $form->field($model, 'Password')->passwordInput(['maxlength' => 20]) ?>
    
    <?= $form->field($model, 'ConfirmPassword')->passwordInput(['maxlength' => 20]) ?>
    
    <?= $form->field($model, 'Email')->textInput(['maxlength' => 100]) ?>
    
    <?= $form->field($model, 'Gender')->dropDownList([ 1 => 'Male', 2 => 'Female'], ['prompt' => '--Select Gender--']) ?>
    
    <?php
    if(!$model->isNewRecord) :
        echo $form->field($model, 'ProfileThumb')->widget(FileInput::classname(), [
            'options' => ['multiple' => false],
            'pluginOptions' => [
                'allowedFileExtensions'=>['jpg','png','jpeg'],
                'showPreview' => true,
                'showRemove' => false,
                'showUpload' => false,
            ]
        ])->label("Profile Thumb <span style='color:red;'>(Recommended image size : 300 x 300)</span>");

        if(!$model->isNewRecord && $model->ProfileThumb != '')
        {
            echo '<div class="form-group imageList">';    
            $imageName = $model->ProfileThumb;
            $s3GalleryImage=S3_BUCKETPATH.$model->ArtistID.PROFILE_IMAGES.$imageName;
            $defaultimage           =   S3_BUCKETABSOLUTE_PATH.'/noimg.jpg';
            $imagesize              = @getimagesize($s3GalleryImage);
            if(isset($imagesize['bits']) && $imagesize['bits'] > 0)
            {
                $s3GalleryImage = $s3GalleryImage;
            }
            else
            {
                $s3GalleryImage = $defaultimage;
            }
            ?>
            <div class="imageGallery">
                <a href="<?php echo Url::toRoute('member/removestickerimage?id='.$model->MemberID); ?>" onclick="return confirm('Are you sure you want to delete this image?')"><i class="glyphicon glyphicon-remove"></i></a>
                <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" />
            </div>
            <?php
            echo '</div><div style="clear:both;"></div>';
        }
    endif;    
    ?>
    
    <?= $form->field($model, 'ZipCode')->textInput(['maxlength' => 10]) ?>
    
    <?php
    echo $form->field($model, 'DOB')->widget(DatePicker::classname(), [
        'options' => ['placeholder' => 'Select date of birth'],
        'pluginOptions' => [
            'format' => 'M-dd-yyyy',
            'todayHighlight' => true,
            'endDate' => date("m-d-Y"),
            'autoclose'=>true,
        ]
    ])->label("Date of Birth");
    ?>
    <?php echo $form->field($model, 'IsPushEnabled')->checkbox(array("style"=>"cursor:pointer;","labelOptions"=>array('style'=>'cursor:pointer;'))); ?>
    <?php //echo $form->field($model, 'MobileNo')->textInput(['maxlength' => 20]); ?>
    
    <?= $form->field($model, 'Status')->dropDownList(\backend\models\User::getStatus()); ?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
<?php 
$this->registerJs('
    $("#w0").on("beforeSubmit", function (e) {    
        $("#spinnerLoader").show();
    });    
');
?>