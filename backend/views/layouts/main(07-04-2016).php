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
header('Content-type: text/html; charset=UTF-8') ;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="Content-type" value="text/html; charset=UTF-8" />
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
.close {
    color: white !important;
}

</style>
<link rel="stylesheet" href="http://harvesthq.github.io/chosen/chosen.css">
</head>
<body>
<?php $this->beginBody() ?>
<?php
$class  =   "";
if(Yii::$app->controller->id == "artist")
{
    if(Yii::$app->controller->action->id == "index")
    {
        $class = "artistgrid";
    }
    else if(Yii::$app->controller->action->id == "create")
    {
        $class = "artistadd";
    }
    else if(Yii::$app->controller->action->id == "update")
    {
        $class = "artistedit";
    }
    
}
else if(Yii::$app->controller->id == "post")
{
    if(Yii::$app->controller->action->id == "index")
    {
        $class = "postgrid";
    }
    else if(Yii::$app->controller->action->id == "create")
    {
        $class = "postadd";
    }
    else if(Yii::$app->controller->action->id == "update")
    {
        $class = "postedit";
    }
    else if(Yii::$app->controller->action->id == "like")
    {
        $class = "postlike";
    }
    else if(Yii::$app->controller->action->id == "comment")
    {
        $class = "postcomment";
    }
    else if(Yii::$app->controller->action->id == "share")
    {
        $class = "postshare";
    }
    
}
else if(Yii::$app->controller->id == "flagged" && (Yii::$app->controller->action->id == "index"))
{
    $class = "flaggedgrid";
}
else if(Yii::$app->controller->id == "member")
{
    if(Yii::$app->controller->action->id == "index")
    {
        $class = "usergrid";
    }
    else if(Yii::$app->controller->action->id == "update")
    {
        $class = "useredit";
    }
    else if(Yii::$app->controller->action->id == "like")
    {
        $class = "userlike";
    }
    else if(Yii::$app->controller->action->id == "comment")
    {
        $class = "usercomment";
    }
    else if(Yii::$app->controller->action->id == "exclusive")
    {
        $class = "userexclusive";
    }
    else if(Yii::$app->controller->action->id == "share")
    {
        $class = "usershare";
    }
    else if(Yii::$app->controller->action->id == "flag")
    {
        $class = "userflag";
    }
}
else if(Yii::$app->controller->id == "sticker")
{
    if(Yii::$app->controller->action->id == "index")
    {
        $class = "stickergrid";
    }
    else if(Yii::$app->controller->action->id == "create")
    {
        $class = "stickeradd";
    }
    else if(Yii::$app->controller->action->id == "update")
    {
        $class = "stickeredit";
    }
}
else if(Yii::$app->controller->id == "similarapp")
{
    if(Yii::$app->controller->action->id == "index")
    {
        $class = "similarappgrid";
    }
    else if(Yii::$app->controller->action->id == "create")
    {
        $class = "similarappadd";
    }
    else if(Yii::$app->controller->action->id == "update")
    {
        $class = "similarappedit";
    }
}
else if(Yii::$app->controller->id == "exclusiveprice" && Yii::$app->controller->action->id == "update")
{
    $class = "exclusivepricegrid";
}
else if(Yii::$app->controller->id == "user")
{
    if(Yii::$app->controller->action->id == "index")
    {
        $class = "adminusergrid";
    }
    else if(Yii::$app->controller->action->id == "create")
    {
        $class = "adminuseradd";
    }
    else if(Yii::$app->controller->action->id == "update")
    {
        $class = "adminuseredit";
    }
}
else if(Yii::$app->controller->id == "pushhistorystats")
{
    if(Yii::$app->controller->action->id == "index")
    {
        $class = "pushhistorystatsgrid";
    }
    else if(Yii::$app->controller->action->id == "create")
    {
        $class = "pushhistorystatsadd";
    }
    else if(Yii::$app->controller->action->id == "view")
    {
        $class = "pushhistorystatsview";
    }
    else if(Yii::$app->controller->action->id == "users")
    {
        $class = "pushhistorystatsusers";
    }
}
else if(Yii::$app->controller->id == "artisttracks" && (Yii::$app->controller->action->id == "index"))
{
    $class = "mymusicgrid";
}
else if(Yii::$app->controller->id == "site" && (Yii::$app->controller->action->id == "forgotpassword"))
{
    $class = "forgotpwdform";
}
?>
<div class="wrap <?php echo $class;?>">
    <?php
    NavBar::begin([
        'brandLabel' => ((isset(Yii::$app->user->identity->UserName) && Yii::$app->user->identity->UserName != '') ? ucfirst(Yii::$app->user->identity->UserName) : ''),
        'brandUrl' => ((isset(Yii::$app->user->identity->UserType) && Yii::$app->user->identity->UserType == 1) ? Url::toRoute('artist/index') : Url::toRoute('post/index')),
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    //$menuItems = array();
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
    } else if(Yii::$app->user->identity->UserType == 1) {
                //$menuItems = [
                //    ['label' => 'Home', 'url' => ['/site/index']],
                //];
                //$query = Yii::$app->getDb();
                //$command = $query->createCommand("SELECT GetUniqueCode()");
                $artistData = backend\models\Artist::find()->where("1")->asArray()->all();
                $artistID = $artistData[0]['ArtistID'];
                
                $menuItems[] = [
                    'label' => 'Artists',
                    'url' => ['/artist/index'],
                    'active'=>Yii::$app->controller->id == 'artist',
                ];
                $menuItems[] = [
                    'label' => 'Posts',
                    'url' => ['/post/index'],
                    //'active'=>Yii::$app->controller->id == 'post',
                    'items'=>[
                                    ['label' => 'Posts', 'url' =>['/post/index'],'active'=>Yii::$app->controller->id == 'post'],
                                    ['label' => 'Flagged Posts', 'url' =>['/flagged/index'],'active'=>Yii::$app->controller->id == 'flagged'],
                             ]
                ];
               /* $menuItems[] = [
                    'label' => 'Flagged',
                    'url' => ['/flagged/index'],
                    'active'=>Yii::$app->controller->id == 'flagged',
                ];*/
                $menuItems[] = [
                    'label' => 'Users',
                    'url' => ['/member/index'],
                    'active'=>Yii::$app->controller->id == 'member',
                ];
                $menuItems[] = [
                    'label' => 'Stickers',
                    'url' => ['/sticker/index'],
                    'active'=>Yii::$app->controller->id == 'sticker',
                ];
               /* $menuItems[] = [
                    'label' => 'Exclusive Pricing',
                    'url' => ['/exclusiveprice/update?id='.$artistID],
                    'active'=>Yii::$app->controller->id == 'exclusiveprice',
                ];*/
                $menuItems[] = [
                    'label' => 'Similar Apps',
                    'url' => ['/similarapp/index'],
                    'active'=>Yii::$app->controller->id == 'similarapp',
                ];
                $menuItems[] = [
                    'label' => 'My Music TV',
                    'url' => ['/mymusictv/index'],
                    'active'=>Yii::$app->controller->id == 'mymusictv',
                ];
                $menuItems[] = [
                    'label' => 'Staff',
                    'url' => ['/user/index'],
                    'active'=>Yii::$app->controller->id == 'user',
                ];
                
                $menuItems[] = [
                    //'label' => 'Logout (' . Yii::$app->user->identity->UserName . ')',
                    'label' => 'Logout',
                    'url' => '#',
                    'linkOptions' => ['onclick'=>'return logoutconfirm();']
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
                    'label' => 'Users',
                    'url' => ['/member/index'],
                    'active'=>Yii::$app->controller->id == 'member',
                ];
                $menuItems[] = [
                    'label' => 'Stickers',
                    'url' => ['/sticker/index'],
                    'active'=>Yii::$app->controller->id == 'sticker',
                ];
                /*$menuItems[] = [
                    'label' => 'Exclusive Pricing',
                    'url' => ['/exclusiveprice/update?id='.$artistID],
                    'active'=>Yii::$app->controller->id == 'exclusiveprice',
                ];*/
                $menuItems[] = [
                    'label' => 'Push Notification',
                    'url' => ['/pushhistorystats/index'],
                    'active'=>Yii::$app->controller->id == 'pushhistorystats',
                ];
                $menuItems[] = [
                    'label' => 'Profile',
                    'url' => ['/artist/update?id='.$artistID],
                    'active'=>Yii::$app->controller->id == 'artist',
                ];
                $menuItems[] = [
                    'label' => 'My Music',
                    'url' => ['/artisttracks/index'],
                    'active'=>Yii::$app->controller->id == 'artisttracks',
                ];
                $menuItems[] = [
                    'label' => 'My Music TV',
                    'url' => ['/mymusictv/index'],
                    'active'=>Yii::$app->controller->id == 'mymusictv',
                ];
                $menuItems[] = [
                    //'label' => 'Logout (' . Yii::$app->user->identity->UserName . ')',
                    'label' => 'Logout',
                    'url' => '#',
                    'linkOptions' => ['onclick'=>'return logoutconfirm();']
                ];
        } else {
            $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
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
       <p class="pull-left"><?//= date('Y') ?></p>
       <p class="pull-right"><img src="<?php echo Url::base();?>/image/footer-logo.png" /></p>
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
            var postThumbImages = $("#post-thumbimage").val();
            // for post images
            
            if(editpostID != "" && postImages == '')
            {
                if(postType == '2')
                {
                    var cnt = $("#postimages_cnt").val();
                    if(cnt == 0)
                    {
                        alert("Please choose Images.");
                        return false;
                    }
                }
            }
            if(editpostID != "" && postThumbImages == '')
            {
                if(postType == '2')
                {
                    var cntThumb = $("#postthumbimages_cnt").val();
                    if(cntThumb == 0)
                    {
                        alert("Please choose Thumb Images.");
                        return false;
                    }
                }
            }
            
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
                                var fileChooserforthumbImage = document.getElementById('post-videothumbimage').value;
                                if(updatedID == '' || postVideo != '' || fileChooserforthumbImage !='') {
                                        var fileChooser = document.getElementById('post-postvideo');
                                        var fileChooservideo = document.getElementById('post-postvideo').value;
                                    
                                        //for video thumb image
                                        var bucket = new AWS.S3({params: {Bucket: '<?php echo S3BUCKET; ?>'}});
                                        var fileChooserforthumb = document.getElementById('post-videothumbimage');
                                        if(fileChooserforthumbImage != "")
                                        {
                                            var fileforthumb = fileChooserforthumb.files[0];
                                            var updatedFile = fileChooserforthumb.files[0]['name'];
                                            var extension = updatedFile.split('.').pop();
                                            var updatedFileString = $.now()+'.'+extension;
                                            var finalImageName = msg.id+'_'+updatedFileString;
                                            var artistID = msg.ArtistID;
                                            var uploadimage = '<?php echo BOOM ?>'+artistID+'<?php echo POST_THUMBIMAGE_VIDEOS ?>'+finalImageName;
                                           
                                            var renameFilethumb = finalImageName;
                                            if(fileforthumb) {
                                                                    $("#progressForThumb").show();    
                                                                    var params = {Key: uploadimage, ContentType: fileforthumb.type, Body: fileforthumb};
                                                                    bucket.upload(params).on('httpUploadProgress', function(evt) {
                                                                    var percentage = parseInt((evt.loaded * 100) / evt.total);
                                                                    $("#progress-barForThumb")
                                                                        .css("width", percentage + "%")
                                                                        .attr("aria-valuenow", percentage)
                                                                        .text(percentage + "%");
                                                                    }).send(function(err, data) {
                                                                        $.ajax({
                                                                            type : 'POST',
                                                                            url : '<?php echo Url::toRoute('post/updateimage'); ?>',
                                                                            data : 'id='+msg.id+'&thumbfilename='+renameFilethumb+'&type=image',
                                                                            dataType: 'json',
                                                                            async: false, 
                                                                            success : function(msgOfFile) {
                                                                                if(msgOfFile.status == 'ok') {
                                                                                    if(fileChooservideo == "") {
                                                                                        document.location.href='<?php echo Url::toRoute('post/index'); ?>';
                                                                                    }
                                                                                }    
                                                                            }    
                                                                        });
                                                                    }); 

                                            }
                                        }
                                        
                                        
                                        
                                        // for video
                                        //var bucket = new AWS.S3({params: {Bucket: '<?php //echo S3BUCKET; ?>'}});
                                        if(fileChooservideo != "")
                                        {
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
                                                                            data : 'id='+msg.id+'&filename='+renameFile+'&type=video',
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
function logoutconfirm()
{
    if(confirm("Are you sure you want to logout?")) {
        window.location.href="<?php echo Url::toRoute('site/logout'); ?>";
    } else {
        return false;
    }
}
</script>   
<script src="http://harvesthq.github.io/chosen/chosen.jquery.js" type="text/javascript"></script>
<script type="text/javascript">
  $("form select").chosen({ search_contains: true });
  $("form select").trigger("chosen:updated");
</script>
</body>
</html>
<?php $this->endPage() ?>