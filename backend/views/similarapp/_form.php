<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\file\FileInput;
use yii\helpers\Url;
use backend\models\Sticker;

/* @var $this yii\web\View */
/* @var $model backend\models\Similarapp */
/* @var $form yii\widgets\ActiveForm */

if(!$model->isNewRecord) { 
    $artistData = explode(',',$model->ArtistID);
}


?>

<style>
.applist {
    border:1px white solid !important; padding: 10px;    
}    
</style>
<div class="similarapp-form">
    <?php $form = ActiveForm::begin([
                'options' => ['enctype'=>'multipart/form-data']
            ]); ?>
            
    <?php //echo $form->errorSummary($model); ?>
    
    <?php 
		if(Yii::$app->user->identity->UserType == 1){
			echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Sticker::getArtistList(),['multiple'=>'multiple','class'=>'form-control choosen'])->label('Artists');
		}else if(Yii::$app->user->identity->UserType == 4){
			$companyID = Yii::$app->session->get('CompanyID');
			echo $form->field($model, 'ArtistID')->dropDownList(\backend\models\Artist_company::getCompanyArtistList($companyID),['multiple'=>'multiple','class'=>'form-control choosen'])->label('Artists');
		}
	?>
    
    <?= $form->field($model, 'ListTitle')->textInput(['maxlength' => 100]); ?>
    
    <?php if(!$model->isNewRecord) { 
        ?>
        <?php foreach($similarApp as $key => $value) { 
            $title = $value->Title;
            $androidUrl = $value->AndroidUrl;
            $iphoneUrl = $value->IphoneUrl;
            $appIcon = $value->AppIcon;
            $galleryID = $value->AppID;
            $s3GalleryImage=S3_BUCKETABSOLUTE_PATH.'/'.SIMILAR_APP.$appIcon;
        ?>
        <div id="applist<?php echo $key+1; ?>" class="applist">
            <?php if($key != 0) { ?>
                <div><span class="glyphicon glyphicon-remove pull-right" style="cursor:pointer;" onclick="removeAppData('<?php echo $value->AppID; ?>','<?php echo $_GET['id']; ?>')"></span></div>
            <?php } ?>    
            <div>
                App Title  <input type="text" maxlength="100" name="AppTitle[]" id="AppTitle<?php echo $key+1; ?>" class="form-control" value="<?php echo $title; ?>">
                Android URL  <input type="text" maxlength="100" name="AndroidURL[]" id="AndroidURL<?php echo $key+1; ?>" class="form-control" value="<?php echo $androidUrl; ?>">
                IOS URL  <input type="text" maxlength="100" name="IphoneURL[]" id="IphoneURL<?php echo $key+1; ?>" class="form-control" value="<?php echo $iphoneUrl; ?>">
                App Logo  <span style='color:red;'>(Recommended image size : 300 x 300)</span><input type="file" name="AppLogo[]" id="AppLogo<?php echo $key+1; ?>" >
                <?php if($value->AppIcon != '') { ?>
                    <div>
                        <a href="<?php echo Url::toRoute('similarapp/removeimage?id='.$galleryID.'&appid='.$model->ListID); ?>" onclick="return confirm('Are you sure you want to delete this image?')"><i class="glyphicon glyphicon-remove"></i></a>
                        <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive" width="100" height="100" />
                    </div>    
                <?php } ?>
                <input type="hidden" name="UpdateAppIcon[]" id="UpdateAppIcon<?php echo $key+1; ?>" value="<?php echo $appIcon; ?>" >
                <input type="hidden" name="countApp" id="countApp<?php echo $key+1; ?>" value="<?php if(isset($value->AppIcon) && $value->AppIcon !='') { echo "1"; } else { echo "0"; } ?>" >
            </div>    
        </div>
        <?php } ?>
        <br>
        <input type="hidden" name="appID" id="appID" value="<?php echo count($similarApp); ?>">
    <?php } else { ?>
        <div id="applist1" class="applist">
            <div>
                App Title  <input type="text" maxlength="100" name="AppTitle[]" id="AppTitle1" class="form-control" value="">
                Android URL  <input type="text" maxlength="100" name="AndroidURL[]" id="AndroidURL1" class="form-control" value="">
                IOS URL  <input type="text" maxlength="100" name="IphoneURL[]" id="IphoneURL1" class="form-control" value="">
                App Logo  <span style='color:red;'>(Recommended image size : 300 x 300)</span><input type="file" name="AppLogo[]" id="AppLogo1" >
            </div>    
        </div>
        <br>
        <input type="hidden" name="appID" id="appID" value="1">
    <?php } ?> 
        
    <div>
        <button type="button" class="btn btn-primary" onclick="addApp();">Add App</button> 
    </div><br>        
    
    
    <?= $form->field($model, 'Status')->dropDownList($model->getStatus()); ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Save', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary','onclick'=>'return validate()']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<script>
/************** Remove added app from database *************/    
function removeAppData(id,appID) {
    document.location.href='<?php echo Url::toRoute('similarapp/removeapp?id=')?>'+id+'&appid='+appID;
}    

/************* Validate app ***************/
function validate() {
    var numberOfApps = $("#appID").val();
    for(var n=0; n<numberOfApps; n++) {
        var addID = parseInt(n) + parseInt(1);
        var AppTitle = $("#AppTitle"+addID).val();
        var AndroidURL = $("#AndroidURL"+addID).val();
        var IphoneURL = $("#IphoneURL"+addID).val();
        var AppLogo = $("#AppLogo"+addID).val();
        var countApp = $("#countApp"+addID).val();
        <?php if(!$model->isNewRecord) { ?>
            var UpdateAppIcon = $("#UpdateAppIcon"+addID).val();
        <?php } ?>
        var ext = '';    
        if(typeof AppLogo != 'undefined') {    
            var ext = AppLogo.split('.').pop().toLowerCase();
        }    
        if(n==0 && AppTitle == '') {
            if(AppTitle == "") {
                alert("Please enter app title");
                return false    
            }
            var URLCount = "";
            if(AndroidURL == '' && IphoneURL == "") {
                URLCount = "1";
            }
            if(URLCount == '1') {
                alert("Please enter Android URL or Iphone URL for "+AppTitle+" app");
                return false
            }
            <?php if($model->isNewRecord) { ?>
            if($.inArray(ext, ['gif','png','jpg','jpeg']) == -1) {
                alert("Please upload app logo for "+AppTitle+" app");
                return false
            }
            <?php } ?>
        } else {
            if(AppTitle != '') {
                var URLCount = "";
                if(AndroidURL == '' && IphoneURL == "") {
                    URLCount = "1";
                }
                if(URLCount == '1') {
                    alert("Please enter Android URL or Iphone URL for "+AppTitle+" app");
                    return false
                }
                <?php if(!$model->isNewRecord) { ?> 
                     if(typeof UpdateAppIcon == 'undefined' || countApp == "0")  { 
                        if($.inArray(ext, ['gif','png','jpg','jpeg']) == -1) {
                            alert("Please upload app logo for "+AppTitle+" app");
                            return false
                        }
                    }
                <?php } else { ?>
                 if($.inArray(ext, ['gif','png','jpg','jpeg']) == -1) {
                    alert("Please upload app logo for "+AppTitle+" app");
                    return false
                }   
                <?php } ?>    
            }
        }    
    }
}    
/*********** Add app **********/
function addApp() {
    var appID = $("#appID").val();
    var addAppID = parseInt(appID) + parseInt(1);
    $("#appID").val(addAppID);
    
    var finalAppString = '<div id="applist'+addAppID+'" class="applist"><span class="glyphicon glyphicon-remove" style="float:right;cursor:pointer;" aria-hidden="true" onclick="removeApp('+addAppID+');" ></span><div>App Title  <input type="text" maxlength="100" name="AppTitle[]" id="AppTitle'+addAppID+'" class="form-control" value="">Android URL  <input type="text" maxlength="100" name="AndroidURL[]" id="AndroidURL'+addAppID+'" class="form-control" value="">IOS URL  <input type="text" maxlength="100" name="IphoneURL[]" id="IphoneURL'+addAppID+'" class="form-control" value="">App Logo  <input type="file" name="AppLogo[]" id="AppLogo'+addAppID+'" ></div></div>';
    $("#applist"+appID).after(finalAppString);
}
/*********** Remove app **********/
function removeApp(id) {
    $("#applist"+id).remove();
    var appID = $("#appID").val();
    var addAppID = parseInt(appID) - parseInt(1);
    $("#appID").val(addAppID);
}
</script>    
<?php $this->registerJs('$("document").ready(function(){
        $(".default").val("Select Artists");
    });
');

if(!$model->isNewRecord) { 
    for($n=0;$n<count($artistData); $n++) { ?>
<?php $this->registerJs('$("document").ready(function(){
        var str = "'.$model->ArtistID.'";
        var artistArray = str.split(",");
        $("#applist-artistid").val(artistArray);
        $("form select").trigger("chosen:updated");
        $(".default").val("Select Artists");
    });');
} }

?>        
