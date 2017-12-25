<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\Post */
/* @var $form yii\widgets\ActiveForm */

if(isset($_GET['id']) && $_GET['id']!='') {
    $id = $_GET['id'];
    include 'tabs.php';
}

$readOnly = array('onchange'=>'getPostType();','prompt'=>'--Select Post Type--');
$readOnlyForArtist = array('prompt'=>'--Select Artist--');
if(!$model->isNewRecord) {
    $readOnly = array('disabled'=>'disabled','onchange'=>'getPostType();','prompt'=>'--Select Post Type--');
    $readOnlyForArtist = array('disabled'=>'disabled','prompt'=>'--Select Artist--');
    $isExclusiveDisable = array('value'=>'1','onclick'=>'getIsExclusive();'); //,'disabled'=>'disabled'
} else {
    $isExclusiveDisable = array('value'=>'1','onclick'=>'getIsExclusive();');
    $isExclusiveDisable = array('value'=>'1','onclick'=>'getIsExclusive();');
} 

?>

<script src="https://sdk.amazonaws.com/js/aws-sdk-2.1.24.min.js"></script>
<script type="text/javascript">
    AWS.config.update({
        accessKeyId : '<?php echo AWSACCESSKEY; ?>',
        secretAccessKey : '<?php echo AWSSECRETKEY; ?>'
    });
    AWS.config.region = '<?php echo AWSREGION; ?>';
</script>

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
.file-caption-name .text-danger {
    display:none;
}
<?php if(!$model->isNewRecord) { ?> 
.field-post-isexclusive {
    color: #fff !important;
}    
<?php } ?>
</style>

<?php 
$this->registerJs("
    $('#post-postvideo').change(function() {
        var videoName = $('#post-postvideo').val();
        var extension = videoName.split('.').pop();
        //console.log(extension);
        if(extension != 'mp4') {
            $('.field-post-postvideo').after('<div class=\"text-danger\" id=\"mp4Error\">Please upload video in mp4 format</div>');
            console.log(extension);
            return false;
        } else {
            $('#mp4Error').remove();
        }
    });
");
?>

<div class="post-form">

    <?php 
    $form = ActiveForm::begin([
                'options' => ['enctype'=>'multipart/form-data','id'=>'ajax-post-form'
                ]
            ]); ?>
            
    <?php //echo $form->errorSummary($model); ?>
    
    <?php
    if(!$model->isNewRecord)
    {
        echo "<label class='control-label'>Date created: ".getTimezone($model->Created)."</label>";
    }
    ?>
    
    <?php echo $form->field($model, 'PostType')->dropDownList([ 1 => 'Status', 2 => 'Images', 3 => 'Video', 5 => 'Article'],$readOnly)->label("Type") ?>
    
    <?php
    if(Yii::$app->user->identity->UserType == 1)
    {
        echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Sticker::getArtistList(),$readOnlyForArtist,array('prompt'=>'--Select Artist--'))->label("Select Artist");
    }else if(Yii::$app->user->identity->UserType == 4)
    {
        $companyID = Yii::$app->session->get('CompanyID');
		if($companyID != null && $companyID != ""){
			echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Artist_company::getCompanyArtistList($companyID),$readOnlyForArtist,array('prompt'=>'--Select Artist--'))->label("Select Artist");
		}
    }
    else
    {
        $model->ArtistID = Yii::$app->user->identity->ArtistID;
        echo $form->field($model, 'ArtistID')->hiddenInput()->label(false);
    }
    ?>
    
    <div id="postTitle" <?php if((!$model->isNewRecord && $model->PostType == '1') || $model->isNewRecord) { ?> style="display: none;" <?php } ?>>
        <?php echo $form->field($model, 'PostTitle')->textInput(['maxlength' => 100]) ?>
    </div>    
    <div id="postDescription" <?php if(!$model->isNewRecord && $model->PostType == '5') { ?> style="display: none;" <?php } ?>>
        <?php echo $form->field($model, 'Description')->textarea(['rows' => 6]) ?>
    </div>    
    
    <div id="postImage" <?php if((!$model->isNewRecord && $model->PostType != '2') || $model->isNewRecord) { ?> style="display: none;" <?php } ?>>
        <?php
        
        /********************************* Post Thumb Image ******************************/
        /*echo $form->field($model, 'ThumbImage[]')->widget(FileInput::classname(), [
            'options' => ['multiple' => false],
            'pluginOptions' => [
                'allowedFileExtensions'=>['jpg','png','jpeg'],
                'showPreview' => true,
                'showRemove' => false,
                'showUpload' => false,
            ]
        ])->label("Thumb Image <span style='color:red;'>(Recommended image size : 600 x 400)</span>");
        
        
        if(!$model->isNewRecord && $model->ThumbImage != '')
        {
            $imageName = $model->ThumbImage;
            $s3GalleryImage=S3_BUCKETPATH.$model->ArtistID.POST_THUMB_IMAGES.$imageName;
            echo '<div class="form-group imageList">';   ?>
                <div class="imageGallery">
                    <a href="<?php echo Url::toRoute('post/removethumbimage?id='.$model->PostID); ?>" onclick="return confirm('Are you sure you want to delete this image?')"><i class="glyphicon glyphicon-remove"></i></a>
                    <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" />
                </div>
            <?php echo '</div><div style="clear:both;"></div>';
        }*/ ?>
        <input type="hidden" value="<?php if($model->ThumbImage != '') { echo '1'; } else { echo '0'; } ?>" name="postthumbimages_cnt" id="postthumbimages_cnt" />
        <?php
        /*********************************************************************************/
        echo $form->field($model, 'PostImages[]')->widget(FileInput::classname(), [
            'options' => ['multiple' => true],
            'pluginOptions' => [
                'allowedFileExtensions'=>['jpg','png','jpeg'],
                'showPreview' => true,
                'showRemove' => false,
                'showUpload' => false,
            ]
        ])->label("Images");
        
        if(!$model->isNewRecord && isset($galleryData))
        {
            echo '<div class="form-group imageList">';    
            foreach($galleryData as $key => $value)
            {
                $imageName = $value['Image'];
                $galleryID = $value['ID'];
                $s3GalleryImage=S3_BUCKETPATH.$model->ArtistID.POST_IMAGES.$imageName;
                ?>
                <div class="imageGallery">
                    <a href="<?php echo Url::toRoute('post/removeimage?id='.$galleryID); ?>" onclick="return confirm('Are you sure you want to delete this image?')"><i class="glyphicon glyphicon-remove"></i></a>
                    <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" />
                </div>
                <?php
            }
?>
            <input type="hidden" value="<?php echo count($galleryData); ?>" name="postimages_cnt" id="postimages_cnt" />
<?php
            echo '</div><div style="clear:both;"></div>';
        }
        ?>
    </div>
    
    <div id="postVideo" <?php if((!$model->isNewRecord && $model->PostType != '3') || $model->isNewRecord) { ?> style="display: none;" <?php } ?>>
        <?php
        echo $form->field($model, 'PostVideo')->widget(FileInput::classname(), [
            'options' => ['multiple' => false],
            'pluginOptions' => [
                'allowedFileExtensions'=>['mp4'],
                'showPreview' => false,
                'showRemove' => false,
                'showUpload' => false,
            ]
        ])->label("Video");
        
        if(!$model->isNewRecord && $model->MobileStreamUrl!='')
        {
            $s3GalleryVideo= $model->MobileStreamUrl;
            $s3GalleryPoster= $model->ThumbImage;
            echo xj\jplayer\VideoWidget::widget([
                'tagClass' => 'jp-video jp-video-360p',
                'skinAsset' => 'xj\jplayer\skins\PinkAssets', //OR xj\jplayer\skins\BlueAssets
                'mediaOptions' => [
                    'title' => $model->PostTitle,
                    'poster' => $s3GalleryPoster,
                    //'m4v' => yii\helpers\Url::base() . '/upload/jplayer-example/Big_Buck_Bunny_Trailer.m4v',
                    //'ogv' => yii\helpers\Url::base() . '/upload/jplayer-example/Big_Buck_Bunny_Trailer.ogv',
                    'webmv' => $s3GalleryVideo,
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
        else if(!$model->isNewRecord)
        {
            echo "<span style='color:white;'><b>Video streaming is in progress...</b></span>";
        }
        ?>
         <div class="progress" style="display: none;">
            <div class="progress-bar progress-bar-striped active" id="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
            progress    
            </div>
        </div>
        <div>
        <?php
        echo $form->field($model, 'VideoThumbImage')->widget(FileInput::classname(), [
            'options' => ['multiple' => false],
            'pluginOptions' => [
                'allowedFileExtensions'=>['jpg','png','jpeg'],
                'showPreview' => true,
                'showRemove' => false,
                'showUpload' => false,
            ]
        ])->label("Thumb Image <span style='color:red;'>(Recommended image size : 600 x 400)</span>"); ?>
        </div>
        
        <?php
        if(!$model->isNewRecord && isset($model->VideoThumbImage) && $model->VideoThumbImage != "")
        {
            echo '<div class="form-group thumbimageList">';    
                $s3GalleryImage=S3_BUCKETPATH.$model->ArtistID.POST_THUMBIMAGE_VIDEOS.$model->VideoThumbImage;
                ?>
                <div class="imageGallery">
                    <a href="<?php echo Url::toRoute('post/removevideothumbimage?id='.$model->PostID); ?>" onclick="return confirm('Are you sure you want to delete this image?')"><i class="glyphicon glyphicon-remove"></i></a>
<!--                    <a href="<?php //echo Url::toRoute('post/removeimage?id='.$galleryID); ?>" onclick="return confirm('Are you sure you want to delete this image?')"><i class="glyphicon glyphicon-remove"></i></a>-->
                    <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" />
                </div>
                <?php
            echo '</div><div style="clear:both;"></div>';
        }
        ?>
    </div>

    <div class="progress" id="progressForThumb" style="display: none;">
        <div class="progress-bar progress-bar-striped active" id="progress-barForThumb" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
        progress
        </div>
    </div>
    <?php //if($model->isNewRecord) { ?>
    <div>
        <?php echo $form->field($model, 'IsExclusive')->checkbox($isExclusiveDisable); ?>
    </div>    
    <?php //} ?>
    <div id="isShared" <?php if(($model->IsExclusive != '1') || $model->isNewRecord) { ?> style="display: none;" <?php } ?>>
        <br />
        <?php echo $form->field($model, 'Price')->textInput() ?>
        <?php echo $form->field($model, 'ProductSKUID')->textInput(['maxlength' => 255]) ?>
        <span style="color:white"><?php echo $form->field($model, 'IsShared')->checkbox() ?></span>
    </div>
    <?= $form->field($model, 'Status')->dropDownList($model->getStatus()); ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php
$artist = \backend\models\Artist::find()->where(['ArtistID'=>$model->ArtistID])->one();
if($artist != null &&
	$artist->AppName != null && $artist->AppName != "" &&
	$artist->AppID != null && $artist->AppID != "" &&
	$artist->Scheme != null && $artist->Scheme != ""){
	$applink = 'http://artist.boomcollective.net/dp/post?id='.$model->PostID.'&artistid='.$model->ArtistID.'&name='.$artist->AppName.'&scheme='.$artist->Scheme.'&appid='.$artist->AppID;
	echo '<div>
		<a href="'.$applink.'" >'.$applink.'</a>
		</div>';
}
?>

<?php if($model->PostID != null && $model->PostID != 0 && $model->ArtistID == 175){
	echo '<p style="color:white">http://www.cvipnoticias.com/dplink/?id='.$model->PostID.'</p>';
}
?>

<script> 
/*************** Show hide by post type   ******************/
function getPostType() {
    var postType  = $("#post-posttype").val();
    if(postType == '1') {
        $("#postTitle").hide();
        $("#postImage").hide();
        $("#postVideo").hide();
		$("#postDescription").show();
    } else if(postType == '2') {
        $("#postTitle").show();
        $("#postImage").show();
        $("#postVideo").hide();
		$("#postDescription").show();
    } else if(postType == '3') {
        $("#postTitle").show();
        $("#postImage").hide();
        $("#postVideo").show();
		$("#postDescription").show();
    }  else if(postType == '5') {
        $("#postTitle").show();
        $("#postImage").hide();
        $("#postVideo").hide();
		$("#postDescription").hide();
    }
} 
  
function getIsExclusive() {
    if($("#post-isexclusive").is(':checked')) {
        $("#isShared").show();
    } else {
        $("#isShared").hide();
    }
}
</script>    
