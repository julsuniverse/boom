<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\Url;
use backend\models\User;

/* @var $this yii\web\View */
/* @var $model backend\models\Post */
/* @var $form yii\widgets\ActiveForm */

if(isset($_GET['id']) && $_GET['id']!='') {
    $id = $_GET['id'];
    include 'tabs.php';
}

$readOnly = array('onchange'=>'getPostType();','prompt'=>'--Select Post Type--');
$readOnlyForArtist = array('prompt'=>'--Select Artist--');
$readOnlyForUser = array('prompt'=>'--Select User--');
if(!$model->isNewRecord) {
    $readOnly = array('disabled'=>'disabled','onchange'=>'getPostType();','prompt'=>'--Select Post Type--');
    $readOnlyForArtist = array('disabled'=>'disabled','prompt'=>'--Select Artist--');
	$readOnlyForUser = array('disabled'=>'disabled','prompt'=>'--Select User--');
    $isExclusiveDisable = array('value'=>'1','onclick'=>'getIsExclusive();','disabled'=>'disabled');
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
</style>

<div class="post-form">

    <?php 
    $form = ActiveForm::begin([
                'options' => ['enctype'=>'multipart/form-data','id'=>'qa-form'
                ]
            ]); ?>
            
    <?php //echo $form->errorSummary($model); ?>
    
    <?php
    if(!$model->isNewRecord)
    {
        echo "<label class='control-label'>Date created: ".getTimezone($model->Created)."</label>";
    }
    ?>
    
    <?php echo $form->field($model, 'PostType')->dropDownList([ 1 => 'Text', 2 => 'Video'],$readOnly)->label("Type") ?>
    
    <?php
    if(Yii::$app->user->identity->UserType == 1)
    {
		echo $form->field($model, 'MemberID')->dropDownList(\backend\models\Member::getSingleMember($model->MemberID),$readOnlyForUser,array('prompt'=>'--Select User--'))->label("Select User");
        echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Sticker::getArtistList(),$readOnlyForArtist,array('prompt'=>'--Select Artist--'))->label("Select Artist");
    }else if(Yii::$app->user->identity->UserType == 4)
    {
        echo $form->field($model, 'MemberID')->dropDownList(\backend\models\Member::getSingleMember($model->MemberID),$readOnlyForUser,array('prompt'=>'--Select User--'))->label("Select User");
		$companyID = Yii::$app->session->get('CompanyID');
		echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Artist_company::getCompanyArtistList($companyID),$readOnlyForArtist,array('prompt'=>'--Select Artist--'))->label("Select Artist");
    }
    else
    {
        echo $form->field($model, 'MemberID')->dropDownList(\backend\models\Member::getSingleMember($model->MemberID),$readOnlyForUser,array('prompt'=>'--Select User--'))->label("Select User");
		$model->ArtistID = Yii::$app->user->identity->ArtistID;
        echo $form->field($model, 'ArtistID')->hiddenInput()->label(false);
    }
    ?>
	
    <?php if((!$model->isNewRecord && $model->PostType == '1') || $model->isNewRecord) { ?>
    <div id="question">
        <?php echo $form->field($model, 'Description')->textarea(['rows' => 6])->label('Question'); ?>
    </div>
    <?php } ?>
	
	
    
    <div id="postVideoQuestion">
		<div id="VideoQ" <?php if((!$model->isNewRecord && $model->PostType != '2') || $model->isNewRecord) { ?> style="display: none;" <?php } ?>>
        <?php
		$vidoptions = ['multiple' => false];
		if(!$model->isNewRecord){ $vidoptions = ['multiple' => false, 'disabled' => 'disabled'];}
        echo $form->field($model, 'PostVideo')->widget(FileInput::classname(), [
            'options' => $vidoptions,
            'pluginOptions' => [
                'allowedFileExtensions'=>['mp4'],
                'showPreview' => false,
                'showRemove' => false,
                'showUpload' => false,
            ]
        ])->label("Question Video");
        
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
		</div>
         <div class="progress" style="display: none;">
            <div class="progress-bar progress-bar-striped active" id="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
            progress    
            </div>
        </div>
		
		<div id="ThumbQ">
        <?php
        $thumboptions = ['multiple' => false];
		if(!$model->isNewRecord){ $thumboptions = ['multiple' => false, 'disabled' => 'disabled'];}
        echo $form->field($model, 'VideoThumbImage')->widget(FileInput::classname(), [
            'options' => $thumboptions,
            'pluginOptions' => [
                'allowedFileExtensions'=>['jpg','png','jpeg'],
                'showPreview' => true,
                'showRemove' => false,
                'showUpload' => false,
            ]
        ])->label("Question Thumb Image <span style='color:red;'>(Recommended image size : 600 x 400)</span>");
        
        if(!$model->isNewRecord && isset($model->VideoThumbImage) && $model->VideoThumbImage != "")
        {
            echo '<div class="form-group thumbimageList">';    
                $s3GalleryImage=S3_BUCKETPATH.$model->ArtistID.POST_THUMBIMAGE_VIDEOS.$model->VideoThumbImage;
                ?>
                <div class="imageGallery">
                    <a href="<?php echo Url::toRoute('post/removevideothumbimage?id='.$model->PostID); ?>" onclick="return confirm('Are you sure you want to delete this image?')"><i class="glyphicon glyphicon-remove"></i></a>
                    <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" />
                </div>
                <?php
            echo '</div><div style="clear:both;"></div>';
        }
        ?>
		</div>
    </div>
    
    <div class="progress" id="progressForThumb" style="display: none;">
        <div class="progress-bar progress-bar-striped active" id="progress-barForThumb" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
        progress
        </div>
    </div>
    
	
	<?php if((!$model->isNewRecord && $model->PostType == '1')) { ?>
        <div id="answer">
            <?php echo $form->field($model, 'Reply')->textarea(['rows' => 6])->label('Answer'); ?>
        </div>
    <?php } ?>
	
	
	
    <?php if(!$model->isNewRecord) { ?>
    <div id="postVideoReply" >
        <?php
		if($model->PostType == '2'){ echo "<div>"; }else{ echo "<div style='display:none'>"; }
        echo $form->field($model, 'PostVideoReply')->widget(FileInput::classname(), [
            'options' => ['multiple' => false],
            'pluginOptions' => [
                'allowedFileExtensions'=>['mp4'],
                'showPreview' => false,
                'showRemove' => false,
                'showUpload' => false,
            ]
        ])->label("Reply Video");
        
        if(!$model->isNewRecord && $model->ReplyStreamURL!='')
        {
            $s3GalleryVideo= $model->ReplyStreamURL;
            $s3GalleryPoster= $model->ReplyImage;
            echo xj\jplayer\VideoWidget::widget([
                'tagClass' => 'jp-video jp-video-360p',
                'skinAsset' => 'xj\jplayer\skins\PinkAssets', //OR xj\jplayer\skins\BlueAssets
                'mediaOptions' => [
                    //'title' => $model->ReplyImage,
                    'poster' => $s3GalleryPoster,
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
        else if(!$model->isNewRecord && $model->Reply != "")
        {
            echo "<span style='color:white;'><b>Video streaming is in progress...</b></span>";
        }
        ?>
        <div class="progress" id="progressForReply" style="display: none;">
            <div class="progress-bar progress-bar-striped active" id="progress-barForReply" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
            progress    
            </div>
        </div>
		
		<?php echo $form->field($model, 'TextReply')->textarea(['rows' => 6])->label('Text Reply'); ?>
		
		</div>
		
		
        <?php
		//Thumbnail reply
		if($model->PostType != '1'){ echo "<div>"; }else{ echo "<div style='display:none'>"; }
        echo $form->field($model, 'ReplyThumbImage')->widget(FileInput::classname(), [
            'options' => ['multiple' => false],
            'pluginOptions' => [
                'allowedFileExtensions'=>['jpg','png','jpeg'],
                'showPreview' => true,
                'showRemove' => false,
                'showUpload' => false,
            ]
        ])->label("Reply Thumb Image <span style='color:red;'>(Recommended image size : 600 x 400)</span>");
        
        if(!$model->isNewRecord && isset($model->ReplyThumbImage) && $model->ReplyThumbImage != "")
        {
            echo '<div class="form-group thumbimageList">';    
                $s3GalleryImage=S3_BUCKETPATH.$model->ArtistID.POST_THUMBIMAGE_VIDEOS.$model->ReplyThumbImage;
                ?>
                <div class="imageGallery">
                    <a href="<?php echo Url::toRoute('post/removevideothumbimage?id='.$model->PostID); ?>" onclick="return confirm('Are you sure you want to delete this image?')"><i class="glyphicon glyphicon-remove"></i></a>
                    <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" />
                </div>
                <?php
            echo '</div><div style="clear:both;"></div>';
        }
        ?>
		</div>
    </div>
    <?php } ?>

    <div class="progress" id="progressForReplyThumb" style="display: none;">
        <div class="progress-bar progress-bar-striped active" id="progress-barForReplyThumb" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
        progress
        </div>
    </div>
	
	
    <div>
        <span style="color:white"><?php echo $form->field($model, 'QAIgnore')->checkbox(); ?></span>
<!--        <span style="color:white"><?php echo $form->field($model, 'IsBlocked')->checkbox(); ?></span>-->
        <span style="color:white"><?php echo $form->field($model, 'BlockUser')->checkbox(); ?></span>
		<?php
			if(Yii::$app->user->identity->UserType == 1){
				echo "<span style='color:white'>".$form->field($model, 'IsPublic')->checkbox()."</span>";
				echo "<span style='color:white'>".$form->field($model, 'RequestedPrivacy')->checkbox()."</span>";
			}
		?>
    </div>    
    <?= $form->field($model, 'Status')->dropDownList($model->getStatus()); ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>


<script> 
/********** By post type show hide field *************/    
function getPostType() {
    var postType  = $("#qapost-posttype").val();
    if(postType == '1') {
        $("#VideoQ").hide();
		$("#ThumbQ").show();
        $("#postVideoReply").hide();
        $("#question").show();
        $("#answer").show();
    } else if(postType == '2') {
        $("#VideoQ").show();
		$("#ThumbQ").show();
        $("#postVideoReply").show();
        $("#question").hide();
        $("#answer").hide();
    }
}    
</script>    
