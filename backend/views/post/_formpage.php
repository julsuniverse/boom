<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\file\FileInput;


$readOnly = array('onchange'=>'getType();','prompt'=>'--Select Post Type--');
if($model->ID != 0) {
    $readOnly = array('disabled'=>'disabled','onchange'=>'getType();','prompt'=>'--Select Post Type--');
}

?>

 
<div class="post-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype'=>'multipart/form-data']]); ?>
    
	<?php echo $form->field($model, 'Type')->dropDownList([ 1 => 'Text', 2 => 'Image', 3 => 'Video'],$readOnly) ?>
	
	<?= $form->field($model, 'PageNumber')->textInput(['readonly'=>'readonly']) ?>
	
	<?= $form->field($model, 'Text')->textarea(['rows' => 8]) ?>
	
	<div id="pageImage" <?php if($model->Type != 2){ ?> style="display: none;" <?php } ?>>
	<?php
    echo $form->field($model, 'ImageUrl')->widget(FileInput::classname(), [
        'options' => ['multiple' => false],
        'pluginOptions' => [
            'allowedFileExtensions'=>['jpg','png','jpeg', 'gif'],
            'showPreview' => true,
            'showRemove' => false,
            'showUpload' => false,
        ]
    ])->label("Choose Image");
    
    if($model->ImageUrl != null && $model->ImageUrl != "")
    {
	?>
        <div class="image">
            <a href="<?php echo Url::toRoute('post/removepageimg?id='.$model->ID); ?>" onclick="return confirm('Are you sure you want to delete this image?')"><i class="glyphicon glyphicon-remove"></i></a>
            <img src="<?php echo $model->ImageUrl; ?>" class="img-responsive file-preview-image" />
        </div>
    <?php
	}?>
	</div>
	
	
	<div id="pageVideo" <?php if($model->Type != 3){ ?> style="display: none;" <?php } ?>>
        <?php
        echo $form->field($model, 'VideoUrl')->widget(FileInput::classname(), [
            'options' => ['multiple' => false],
            'pluginOptions' => [
                'allowedFileExtensions'=>['mp4'],
                'showPreview' => true,
                'showRemove' => false,
                'showUpload' => false,
            ]
        ])->label("Choose Video");
        
        if($model->VideoUrl != null && $model->VideoUrl != "")
        {
            echo xj\jplayer\VideoWidget::widget([
                'tagClass' => 'jp-video jp-video-360p',
                'skinAsset' => 'xj\jplayer\skins\PinkAssets', //OR xj\jplayer\skins\BlueAssets
                'mediaOptions' => [
                    'title' => $model->PostTitle,
                    //'poster' => $s3GalleryPoster,
                    'webmv' => $model->VideoUrl,
                ],
                'jsOptions' => [
                    'supplied' => "webmv, ogv, m4v",
                    'size' => [
                        'width' => "640px",
                        'height' => "360px",
                        'cssClass' => "jp-video-360p"
                    ],
                    'smoothPlayBar' => true,
                    'keyEnabled' => true,
                    'remainingDuration' => true,
                    'toggleDuration' => true
                ],
            ]);
        }
        ?>
		
		<?php
        echo $form->field($model, 'VideoThumbnailImage')->widget(FileInput::classname(), [
            'options' => ['multiple' => false],
            'pluginOptions' => [
                'allowedFileExtensions'=>['jpg','png','jpeg'],
                'showPreview' => true,
                'showRemove' => false,
                'showUpload' => false,
            ]
        ])->label("Thumb Image <span style='color:red;'>(Recommended image size : 600 x 400)</span>");
	
		if($model->VideoThumbnailImage != null && $model->VideoThumbnailImage != "")
		{
		?>
			<div class="Thumb">
				<img src="<?php echo $model->VideoThumbnailImage; ?>" class="img-responsive file-preview-image" />
			</div>
		<?php
		}?>
		
	</div>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<script>
function getType() {
    var postType  = $("#postpages-type").val();
    if(postType == '1') {
        $("#pageImage").hide();
        $("#pageVideo").hide();
    } else if(postType == '2') {
        $("#pageImage").show();
        $("#pageVideo").hide();
		$("#postDescription").show();
    } else if(postType == '3') {
        $("#pageImage").hide();
        $("#pageVideo").show();
    }
} 
</script>