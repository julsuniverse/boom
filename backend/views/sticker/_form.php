<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;

use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model backend\models\Sticker */
/* @var $form yii\widgets\ActiveForm */

$artistList = backend\models\Sticker::getAllArtistList();


//$readonly = array();
$readonly = array('prompt'=>'--Select Artist--');
if(!$model->isNewRecord)
{
    $readonly = array('disabled'=>'disabled','maxlength' => 100);
}


?>

<style>
.imageGallery {
    float:left;
    margin:5px;
    width : auto;
}
.imageList {
    border: 1px solid grey;
    float: left;
    height: auto;
    margin: 0;
    width: auto;
}
.table-striped > tbody > tr:nth-of-type(2n+1) {
    background-color: grey !important;
}
</style>

<div class="sticker-form">

    <?php
    $form = ActiveForm::begin([
        'options' => ['enctype'=>'multipart/form-data']
    ]);
    ?>
    
    <?php //echo $form->errorSummary($model); ?>
    
    <?php
    if(Yii::$app->user->identity->UserType == 1)
    {
        /*echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Sticker::getArtistList(),array('prompt'=>'--Select Artist--'))->label("Select Artist");*/
		echo $form->field($model, 'ArtistID')->hiddenInput()->label(false);
		echo $form->field($model, 'assignedArtists')->dropDownList(\backend\models\Sticker::getArtistList(),['multiple'=>'multiple','class'=>'form-control choosen'])->label('Artists');
    }
    else if(Yii::$app->user->identity->UserType == 4)
    {
		$companyID = Yii::$app->session->get('CompanyID');
		echo $form->field($model, 'ArtistID')->hiddenInput()->label(false);
		echo $form->field($model, 'assignedArtists')->dropDownList(\backend\models\Artist_company::getCompanyArtistList($companyID),['multiple'=>'multiple','class'=>'form-control choosen'])->label('Artists');
    }
	else
    {
        $model->ArtistID = Yii::$app->user->identity->ArtistID;
        echo $form->field($model, 'ArtistID')->hiddenInput()->label(false);
		echo $form->field($model, 'assignedArtists')->hiddenInput()->label(false);
    }
    ?>
    
    <?= $form->field($model, 'Title')->textInput(['maxlength' => 100]) ?>
    
    <?= $form->field($model, 'Description')->textarea(['maxlength' => true]) ?>
    
    <?php
    echo $form->field($model, 'StickerImage[]')->widget(FileInput::classname(), [
        'options' => ['multiple' => true],
        'pluginOptions' => [
            'allowedFileExtensions'=>['jpg','png','jpeg', 'gif'],
            'showPreview' => true,
            'showRemove' => false,
            'showUpload' => false,
        ]
    ])->label("Choose Image <span style='color:red;'>(Recommended image size : 618 x 618 - image should not be smaller than 300 x 300)</span>");
    
    if(count($stickerImages)>0)
    {
        echo '<div class="form-group imageList">';      
        ?>
        <?php foreach($stickerImages as $key => $value) { 
                $imageName = $value['Image'];
                //$s3GalleryImage=S3_BUCKETABSOLUTE_PATH.'/'.BOOM.$model->ArtistID.STICKER_THUMB_IMAGES.$imageName;
				$s3GalleryImage = $value['Url'];
        ?>
        <div class="imageGallery">
                <a href="<?php echo Url::toRoute('sticker/removestickerimage?id='.$value['StickerImageID']); ?>" onclick="return confirm('Are you sure you want to delete this image?')"><i class="glyphicon glyphicon-remove"></i></a>
                <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" />
        </div>
        <?php } echo '</div><div style="clear:both;"></div>';
    }
    ?>
    
    <input type="hidden" name="stickerImageCount" id="stickerImageCount" value="<?php if(count($stickerImages)>0) { echo '1'; } else { echo '0'; } ?>" >
    
	<?php
    if(Yii::$app->user->identity->UserType == 1)
    {
        echo $form->field($model, 'Type')->dropDownList(array('1'=>'Image', '2'=>'Video', '3'=>'Wallpaper', '4'=>'Photo'));
    }
    else
    {
        echo $form->field($model, 'Type')->dropDownList(array('1'=>'Image', '3'=>'Wallpaper', '4'=>'Photo'));
    }
    ?>
	
    <?= $form->field($model, 'Cost')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'Status')->dropDownList($model->getStatus()); ?>
    <?= $form->field($model, 'IOSSKUID')->textInput();?>
    <?= $form->field($model, 'AndroidSKUID')->textInput(); ?>
    
	
	<!--Daniele-->
	<div id="postVideo" <?php if(Yii::$app->user->identity->UserType != 1){ ?> style="display: none;" <?php } ?>>
        <?php
        echo $form->field($model, 'Call_Video_Url')->widget(FileInput::classname(), [
            'options' => ['multiple' => false],
            'pluginOptions' => [
                'allowedFileExtensions'=>['mp4'],
                'showPreview' => false,
                'showRemove' => false,
                'showUpload' => false,
            ]
        ])->label("Call Video Url");
        
        if($model->Call_Video_Url != null && $model->Call_Video_Url != "")
        {
            $s3GalleryVideo= $model->Call_Video_Url;
            $s3GalleryPoster= $model->StickerImage;
            echo xj\jplayer\VideoWidget::widget([
                'tagClass' => 'jp-video jp-video-360p',
                'skinAsset' => 'xj\jplayer\skins\PinkAssets', //OR xj\jplayer\skins\BlueAssets
                'mediaOptions' => [
                    'title' => $model->Title,
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
        ?>

	
	</div>
	
	<!--Daniele-->
	

	
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary','onclick'=>'return validate()']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?php

/********* Set Readonly ************/
$this->registerJs('
    $("#sticker-cost").keyup(function() {
            var cost = $("#sticker-cost").val();
            if(cost <= 0) {
                $(".iossku").attr("readonly","readonly");
                $(".androidsku").attr("readonly","readonly");
            } else if(cost > 0) {
                $(".iossku").removeAttr("readonly");
                $(".androidsku").removeAttr("readonly");
            }
    });
');
?>

<?php 
$this->registerJs('
    $("#w0").on("beforeSubmit", function (e) {    
        $("#spinnerLoader").show();
    });    
');
?>

<script>

/************** Set artists **********/
function setSelectedArtists(){
  var select = document.getElementById("sticker-assignedartists");
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


/************** custom validation *************/
function validate() {
    var stickerImage = $("#sticker-stickerimage").val();
			var ret = true;
    <?php if($model->isNewRecord) { ?>
            if(stickerImage == '') {
                alert("Please upload sticker image");
                ret = false;
            } else {
                ret = true;    
            }
    <?php } else { ?>
            if($("#stickerImageCount").val() == "0" && stickerImage == '') {
                alert("Please upload sticker image");
                ret = false;
            }
    <?php } ?>    
			//check if only 1 artist is select if cost > 0
			if($("#sticker-cost").val() > 0){
				if($("#sticker-assignedartists :selected").size() > 1){
					alert("Please select only one Artist. Multiple-artist is for free stickers only.");
					ret = false;
				}
			}
			
			if($("#sticker-cost").val() > 0){
				var IOSSKUID = $("#sticker-iosskuid").val();
				var androidSKUID = $("#sticker-androidskuid").val();
				var status = $("#sticker-status").val();
				if((IOSSKUID == null || IOSSKUID == "") && (androidSKUID == null || androidSKUID == "") && (status == "1")){
					console.log("fails");
					var r = confirm("Please set SKUID correct or set Status inactive. Save anyway?");
					if (r == true) {ret = true;}else{ret = false;}
				}
			}
			
			ret = validateFileSize();
			
			return ret;
}   


function validateFileSize(){
	var input = document.getElementById('sticker-stickerimage');
	if(input.files != null){
		for(var i= 0; i<input.files.length; i++){
			var file = input.files[i];
			var spli = file.name.split('.');
			var ext = spli[spli.length-1];
			console.log(ext);
			console.log(file.size);
			if(file.size > 500000 && ext == "gif"){
				alert("The size of the file " + file.name + " is greater than 500 KB. File size for .gif must be less than 500 KB."); 
				return false;
			}
		}
	}
	return true;
}

function getPurchaseType() {
    var purchaseType  = $("#sticker-purchasetype").val();
    if(purchaseType == 2) {
        $("#stickerCost").show();
    } else {
        $("#stickerCost").hide();
    }
}

function activeSKU(id) {
    if($("#arsitCheck"+id).is(':checked')) {
        $("#IOSSKU"+id).removeAttr('readonly');
        $("#AndroidSKU"+id).removeAttr('readonly');
    } else {
        $("#IOSSKU"+id).attr('readonly','readonly');
        $("#AndroidSKU"+id).attr('readonly','readonly');
        $("#IOSSKU"+id).val('');
        $("#AndroidSKU"+id).val('');
    }
}

/********* Check all **************/
function checkAll() {
    if($("#checkall").is(":checked")) {
        $(":checkbox").prop("checked", true); 
        <?php foreach($artistList as $key => $value) { ?>
            $("#IOSSKU<?php echo $key; ?>").removeAttr("readonly");
            $("#AndroidSKU<?php echo $key; ?>").removeAttr("readonly");
        <?php } ?>
    } else {
        $(":checkbox").prop("checked", false); 
        <?php foreach($artistList as $key => $value) { ?>
            $("#IOSSKU<?php echo $key; ?>").attr("readonly","readonly");
            $("#AndroidSKU<?php echo $key; ?>").attr("readonly","readonly");
        <?php } ?>
    }    
}
</script>  