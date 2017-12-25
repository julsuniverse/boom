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
$readOnly = array('onchange'=>'getPostType();','prompt'=>'--Select Post Type--');
$readOnlyForArtist = array('prompt'=>'--Select Artist--');
if(!$model->isNewRecord) {
    $readOnly = array('disabled'=>'disabled','onchange'=>'getPostType();','prompt'=>'--Select Post Type--');
    $readOnlyForArtist = array('disabled'=>'disabled','prompt'=>'--Select Artist--');
    $isExclusiveDisable = array('value'=>'1','onclick'=>'getIsExclusive();','disabled'=>'disabled');
} else {
    $isExclusiveDisable = array('value'=>'1','onclick'=>'getIsExclusive();');
    $isExclusiveDisable = array('value'=>'1','onclick'=>'getIsExclusive();');
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
    
    <?php //echo $form->errorSummary($model); 
    if(Yii::$app->user->identity->UserType == 1)
    {
        echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Sticker::getArtistList(),$readOnlyForArtist,array('prompt'=>'--Select Artist--'))->label("Select Artist");
    }
    else
    {
        $model->ArtistID = Yii::$app->user->identity->ArtistID;
        echo $form->field($model, 'ArtistID')->hiddenInput()->label(false);
    }
    ?>
    <?= $form->field($model, 'AlbumTitle')->textInput(['maxlength' => 100]) ?>
    
    <?= $form->field($model, 'AlbumLink')->textInput(['maxlength' => 100]) ?>
    
    <?php
    echo $form->field($model, 'AlbumImage')->widget(FileInput::classname(), [
        'options' => ['multiple' => false],
        'pluginOptions' => [
            'allowedFileExtensions'=>['jpg','png','jpeg'],
            'showPreview' => true,
            'showRemove' => false,
            'showUpload' => false,
        ]
    ])->label("Album Image <span style='color:red;'>(Recommended image size : 300 x 300)</span>");
    ?>

    <?php
    
    if(!$model->isNewRecord && $model->AlbumImage!='') { 
        echo '<div class="form-group imageList">';    
        $imageName = $model->AlbumImage;
        $s3GalleryImage=S3_BUCKETPATH.$model->ArtistID.ALBUM_IMAGES.'thumb/'.$imageName;
        ?>
        <div class="imageGallery">
            <a href="<?php echo Url::toRoute('mymusictv/removeprofileimage?id='.$model->ID); ?>" onclick="return confirm('Are you sure you want to delete this image?')"><i class="glyphicon glyphicon-remove"></i></a>
            <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" />
        </div>
        <?php
        echo '</div><div style="clear:both;"></div>';
    }
    ?>

    <?= $form->field($model, 'Status')->dropDownList(\backend\models\Mymusictv::getStatus()) //['prompt' => ''] ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary','onclick'=>'return validate()']) ?>
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
<script>
function validate() 
{
    var imagelength = $(".file-preview-image").length;
    var stickerImage = $("#mymusictv-albumimage").val();
    var ext = stickerImage.split('.').pop().toLowerCase();
    <?php if($model->isNewRecord) { ?>
            if(stickerImage == '') {
                alert("Please upload album image");
                return false;
            } else if($.inArray(ext, ['gif','png','jpg','jpeg']) == -1) {
                alert("Please upload valid album image");
                return false;
            } else {
                return true;    
            }
<?php 
    }
    else
    {
?>
        if(stickerImage!="" && $.inArray(ext, ['gif','png','jpg','jpeg']) == -1) {
                alert("Please upload valid album image");
                return false;
        }
        if(imagelength == 0)
        {
            alert("Please upload album image");
            return false;
        }
        else
        {
            return true;
        }
<?php
    }
?>
} 
</script>