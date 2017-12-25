<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use kartik\spinner\Spinner;
use yii\helpers\Url;

AppAsset::register($this);

if(isset(Yii::$app->user->identity->UserID) && Yii::$app->user->identity->UserID!='' && Yii::$app->user->identity->UserType == 2) {
    $artistData = backend\models\Artist::getArtistData(Yii::$app->user->identity->UserID);
    if(isset($artistData[0]['ArtistID']) && $artistData[0]['ArtistID']!= '') {
        $artistID = $artistData[0]['ArtistID'];
        $session = Yii::$app->session;
        $session->set('ArtistID', $artistID);
    }    
}    


?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
<style>
#spinnerLoader {
    background: rgba(3, 0, 0, 0.48) none repeat scroll 0 0;
    cursor: pointer;
    display: block;
    height: 100%;
    left: 0;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 9;
}    

</style>
<link rel="stylesheet" href="http://harvesthq.github.io/chosen/chosen.css">
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => ((isset(Yii::$app->user->identity->UserName) && Yii::$app->user->identity->UserName != '') ? ucfirst(Yii::$app->user->identity->UserName) : ''),
        'brandUrl' => ((isset(Yii::$app->user->identity->UserType) && Yii::$app->user->identity->UserType == 1) ? Yii::$app->homeUrl : Url::toRoute('post/index')),
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
    } else if(Yii::$app->user->identity->UserType == 1) {
                $menuItems = [
                    ['label' => 'Home', 'url' => ['/site/index']],
                ];
                $menuItems[] = [
                    'label' => 'Artist',
                    'url' => ['/artist/index'],
                    'active'=>Yii::$app->controller->id == 'artist',
                ];
                $menuItems[] = [
                    'label' => 'Post',
                    'url' => ['/post/index'],
                    'active'=>Yii::$app->controller->id == 'post',
                ];
                $menuItems[] = [
                    'label' => 'Flagged',
                    'url' => ['/flagged/index'],
                    'active'=>Yii::$app->controller->id == 'flagged',
                ];
                $menuItems[] = [
                    'label' => 'Member',
                    'url' => ['/member/index'],
                    'active'=>Yii::$app->controller->id == 'member',
                ];
                $menuItems[] = [
                    'label' => 'Sticker',
                    'url' => ['/sticker/index'],
                    'active'=>Yii::$app->controller->id == 'sticker',
                ];
                $menuItems[] = [
                    'label' => 'Similar App',
                    'url' => ['/similarapp/index'],
                    'active'=>Yii::$app->controller->id == 'similarapp',
                ];
                $menuItems[] = [
                    'label' => 'Exclusive Pricing',
                    'url' => ['/exclusiveprice/update?id=1'],
                    'active'=>Yii::$app->controller->id == 'exclusiveprice',
                ];
                $menuItems[] = [
                    'label' => 'Admin User',
                    'url' => ['/user/index'],
                    'active'=>Yii::$app->controller->id == 'user',
                ];
                $menuItems[] = [
                    'label' => 'Push Notification',
                    'url' => ['/pushhistorystats/index'],
                    'active'=>Yii::$app->controller->id == 'pushhistorystats',
                ];
                $menuItems[] = [
                    //'label' => 'Logout (' . Yii::$app->user->identity->UserName . ')',
                    'label' => 'Logout',
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post']
                ];
        } else if(Yii::$app->user->identity->UserType == 2) {
                //$menuItems = [
                //    ['label' => 'Home', 'url' => ['/site/index']],
                //];
                $menuItems[] = [
                    'label' => 'Post',
                    'url' => ['/post/index'],
                    'active'=>Yii::$app->controller->id == 'post',
                ];
                $menuItems[] = [
                    'label' => 'Flagged',
                    'url' => ['/flagged/index'],
                    'active'=>Yii::$app->controller->id == 'flagged',
                ];
                $menuItems[] = [
                    'label' => 'Member',
                    'url' => ['/member/index'],
                    'active'=>Yii::$app->controller->id == 'member',
                ];
                $menuItems[] = [
                    'label' => 'Profile',
                    'url' => ['/artist/update?id='.$artistID],
                    'active'=>Yii::$app->controller->id == 'artist',
                ];
                $menuItems[] = [
                    //'label' => 'Logout (' . Yii::$app->user->identity->UserName . ')',
                    'label' => 'Logout',
                    'url' => ['/site/logout'],
                    'linkOptions' => ['data-method' => 'post']
                ];
        }        
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?php /*echo Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);*/ ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

    <div id="spinnerLoader" style="display:none;">
<?php echo Spinner::widget([
                'preset' => Spinner::LARGE,
                'color' => 'blue',
                'align' => 'center'
        ]);
?>
</div>    
    
<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Boom <?= date('Y') ?></p>

        <p class="pull-right">Powered by Boom</p>
    </div>
</footer>    

<?php $this->endBody() 
        
        
        
?>
<script>    
$(document).on('pjax:send', function() {
    $("#spinnerLoader").show();
})    
$(document).on('pjax:complete', function() {
    $("#spinnerLoader").hide();
});
$("#w0").on("afterValidate", function (event, messages) {
    setTimeout(function(){showloader();}, 200);
});
$("#ajax-post-form").on('beforeSubmit', function (e) {
            var editpostID = '<?php if(isset($_GET['id']) && $_GET['id']!='') { echo $_GET['id']; } ?>';
            var postType = $("#post-posttype").val();
            var postVideo = $("#post-postvideo").val();
            var postImages = $("#post-postimages").val();
            if(editpostID == '' && postVideo == '' && postType == 3) {
                alert("Please upload video");
                return false;
            }
            if(postType == '1' || postType == '2') {
                if(postType == '2' && postImages == '' && editpostID == '') {
                    alert("Please upload image");
                    return false;
                }  else {
                    $("#spinnerLoader").show();  
                    return true;
                }   
            }
            $("#spinnerLoader").show();
            var updatedID = '<?php if(isset($_GET['id']) && $_GET['id']!='') { echo $_GET['id']; } ?>';
            if(updatedID == '')  { 
                    var URL  = '<?php echo Url::toRoute('post/createajax'); ?>';
                    var datastring = $("#ajax-post-form").serialize();
            } else {         
                    var URL  = '<?php echo Url::toRoute('post/updateajax'); ?>';
                    var datastring = $("#ajax-post-form").serialize();
                    datastring+='&id='+updatedID;
            }    
            $.ajax({
                        type : 'POST',
                        url : URL,
                        data : datastring,
                        dataType: 'json',
                        async: false, 
                        success : function(msg) {
                            if(msg.status == 'ok') {
                                if(updatedID == '' || postVideo != '') {
                                        var bucket = new AWS.S3({params: {Bucket: '<?php echo S3BUCKET; ?>'}});
                                        var fileChooser = document.getElementById('post-postvideo');
                                        var file = fileChooser.files[0];
                                        var updatedFile = fileChooser.files[0]['name'];
                                        var extension = updatedFile.split('.').pop();
                                        var updatedFileString = $.now()+'.'+extension;
                                        var finalImageName = msg.id+'_'+updatedFileString;
                                        var artistID = msg.ArtistID;
                                        var renameFile = '<?php echo BOOM ?>'+artistID+'<?php echo POST_VIDEOS ?>'+finalImageName;
                                        if(file) {
                                                                $(".progress").show();    
                                                                var params = {Key: renameFile, ContentType: file.type, Body: file};
                                                                bucket.upload(params).on('httpUploadProgress', function(evt) {
                                                                var percentage = parseInt((evt.loaded * 100) / evt.total);
                                                                $("#progress-bar")
                                                                    .css("width", percentage + "%")
                                                                    .attr("aria-valuenow", percentage)
                                                                    .text(percentage + "%");
                                                                }).send(function(err, data) {
                                                                    $.ajax({
                                                                        type : 'POST',
                                                                        url : '<?php echo Url::toRoute('post/updatevideo'); ?>',
                                                                        data : 'id='+msg.id+'&filename='+renameFile,
                                                                        dataType: 'json',
                                                                        async: false, 
                                                                        success : function(msgOfFile) {
                                                                            if(msgOfFile.status == 'ok') {
                                                                                //alert("File uploaded successfully.");
                                                                                document.location.href='<?php echo Url::toRoute('post/index'); ?>';
                                                                            }    
                                                                        }    
                                                                    });
                                                                }); 

                                        }
                                } else {
                                    document.location.href='<?php echo Url::toRoute('post/index'); ?>';
                                }
                            } else {
                                alert("Something went wrong");
                                return false;
                            }
                        }
            });
            return false;
});

function showloader() {
    var errorSummary = $(".error-summary ul").html();
    if(errorSummary == '') {
        $("#spinnerLoader").show();  
    }    
}
</script>   
<script src="http://harvesthq.github.io/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
  $("form select").chosen();
  $("form select").trigger("chosen:updated");
</script>
</body>
</html>
<?php $this->endPage() ?>