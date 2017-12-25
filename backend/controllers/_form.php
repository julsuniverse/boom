<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\file\FileInput;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\Artist */
/* @var $form yii\widgets\ActiveForm */
$readonly = array('maxlength' =>'100');
if(!$model->isNewRecord) {
    $readonly = array('readonly'=>'readonly','maxlength' => 100);
}
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
    
<div class="artist-form">
    <?php $form = ActiveForm::begin([
                'options' => ['enctype'=>'multipart/form-data']
            ]); ?>
    
    <?php //echo $form->errorSummary($model); ?>
    
    <?= $form->field($model, 'ArtistName')->textInput(['maxlength' => 100]) ?>
    
    <?= $form->field($model, 'UserName')->textInput($readonly) ?>
    
    <?= $form->field($model, 'Email')->textInput($readonly) ?>
    
    <?= $form->field($model, 'Password')->passwordInput(['maxlength' => 20]) ?>
    
    <?= $form->field($model, 'ConfirmPassword')->passwordInput(['maxlength' => 20]) ?>    
    
    <?php
    echo $form->field($model, 'ProfileThumb')->widget(FileInput::classname(), [
        'options' => ['multiple' => false],
        'pluginOptions' => [
            'allowedFileExtensions'=>['jpg','png','jpeg'],
            'showPreview' => true,
            'showRemove' => false,
            'showUpload' => false,
        ]
    ])->label("Profile Picture(s) <span style='color:red;'>(Recommended image size : 300 x 300)</span>");
    ?>

    <?php
    if(!$model->isNewRecord) { 
        echo '<div class="form-group imageList">';    
        $imageName = $model->ProfileThumb;
        $s3GalleryImage=S3_BUCKETPATH.$model->ArtistID.PROFILE_IMAGES.'thumb/'.$imageName;
        ?>
        <div class="imageGallery">
            <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" />
        </div>
        <?php
        echo '</div><div style="clear:both;"></div>';
    }
    ?>
    
    <?php
    echo $form->field($model, 'ArtistImages[]')->widget(FileInput::classname(), [
        'options' => ['multiple' => true],
        'pluginOptions' => [
            //'uploadUrl' => Url::to(['/site/file-upload']),
            'allowedFileExtensions'=>['jpg','png','jpeg'],
            'showPreview' => true,
            'showRemove' => false,
            'showUpload' => false, 
            'maxFileCount' => 5,
        ],
    ])->label("Artist Images <span style='color:red;'>(Recommended image size : 600 x 400)</span>");
    ?>
        
    <?php if(!$model->isNewRecord) { 
        echo '<div class="form-group imageList">';    
            foreach($galleryData as $key => $value) {
                $imageName = $value['Image'];
                $galleryID = $value['ID'];
                $s3GalleryImage=S3_BUCKETPATH.$model->ArtistID.ARTIST_IMAGES.'thumb/'.$imageName;
    ?>
            <div class="imageGallery">
                <a href="<?php echo Url::toRoute('artist/removeimage?id='.$galleryID); ?>"><i class="glyphicon glyphicon-remove"></i></a>
                <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" />
            </div>
    <?php } echo '</div><div style="clear:both;"></div>'; } ?>
    
    
    <?php
    echo $form->field($model, 'DOB')->widget(DatePicker::classname(), [
            'name' => 'Artist[DOB]', 
            'value' => $model->DOB,
            'options' => ['placeholder' => 'Select date of birth'],
            'pluginOptions' => [
                    'format' => 'M-dd-yyyy',
                    'todayHighlight' => true,
                    'endDate' => date("m-d-Y"),
                    'autoclose'=>true,
            ]
    ])->label("Date of Birth");
    ?>
    
    <?= $form->field($model, 'Nationality')->textInput(['maxlength' => 25]) ?>
    <?= $form->field($model, 'Address')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'Website')->textInput(['maxlength' => 150]) ?>
    <?= $form->field($model, 'YearsActive')->textInput(['maxlength' => 100]) ?>
    <?= $form->field($model, 'Genre')->textInput(['maxlength' => 100]) ?>
    <?= $form->field($model, 'AboutMe')->textarea(['rows' => 6]) ?>
    
    <?php echo '<label class="control-label">Social media</label>'; ?>
    <?= $form->field($model, 'YouTubePageURL')->textInput(['maxlength' => 150])->label("<img height='30px' src='".LOCALIMG."youtube.png"."'>") ?>
    <?= $form->field($model, 'InstagramPageURL')->textInput(['maxlength' => 150])->label("<img height='30px' src='".LOCALIMG."instagram.png"."'>") ?>
    <?= $form->field($model, 'TwitterPageURL')->textInput(['maxlength' => 150])->label("<img height='30px' src='".LOCALIMG."twiter.png"."'>") ?>
    <?= $form->field($model, 'FacebookPageURL')->textInput(['maxlength' => 150])->label("<img height='30px' src='".LOCALIMG."fb.png"."'>") ?>
    
    <?= $form->field($model, 'Status')->dropDownList(\backend\models\User::getStatus()) //['prompt' => ''] ?>
    
    <?php
    $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
    $tzlist = array_combine($tzlist, $tzlist);
    ?>
    <?= $form->field($model, 'TimeZone')->dropDownList($tzlist)  ?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
