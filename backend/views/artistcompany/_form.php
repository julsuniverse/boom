<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\file\FileInput;


$readonly = array('maxlength' =>'100');
if(!$model->isNewRecord) {
    $readonly = array('readonly'=>'readonly','maxlength' => 100);
}
?>

<style>

</style>
 
<div class="artist-form">
    <?php $form = ActiveForm::begin([
                'options' => ['enctype'=>'multipart/form-data']
            ]); ?>
    
    
    <?= $form->field($model, 'Name')->textInput(['maxlength' => 100]) ?>
	<?= $form->field($model, 'AppDescription')->textarea(['rows' => 6])->label("App Description") ?>
    
	<?php
		echo $form->field($model, 'assignedArtists')->dropDownList(\backend\models\Artist_company::getArtistList($model->Id),['multiple'=>'multiple','class'=>'form-control choosen'])->label('Artists');
	?>
	
	<?php
    echo $form->field($model, 'Profile_Image')->widget(FileInput::classname(), [
        'options' => ['multiple' => false],
        'pluginOptions' => [
            'allowedFileExtensions'=>['jpg','png','jpeg'],
            'showPreview' => true,
            'showRemove' => false,
            'showUpload' => false,
        ]
    ])->label("Profile Image <span style='color:red;'>(Recommended image size : 300 x 300)</span>");
    ?>

    <?php
    if(!$model->isNewRecord && $model->Profile_Image!='') { 
        echo '<div class="form-group imageList">';    
        $imageName = $model->Profile_Image;
        $s3GalleryImage=$model->Profile_Image;
        ?>
        <div class="imageGallery">
            <a href="<?php echo Url::toRoute('artistcompany/removeprofileimage?id='.$model->Id); ?>" onclick="return confirm('Are you sure you want to delete this image?')"><i class="glyphicon glyphicon-remove"></i></a>
            <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" />
        </div>
        <?php
        echo '</div><div style="clear:both;"></div>';
    }
    ?>
	
	
	
	<?= $form->field($model, 'Website')->textInput(['maxlength' => 255]) ?>
	<?= $form->field($model, 'Error_Msg_Subscribe')->textarea(['rows' => 6])->label("Error Message on Subscribe") ?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<script>

function setSelectedArtists(){
  var select = document.getElementById("artist_company-assignedartists");
  var selectedvals = "<?php echo($model->assignedArtists);?>";
  var array = selectedvals.split(",");

  for(count=0; count<array.length; count++) {
    for(i=0; i<select.options.length; i++) {
      if(select.options[i].value == array[count]) {
        select.options[i].selected="selected";
      }
    }
  }
}
setSelectedArtists();

</script>