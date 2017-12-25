<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use backend\assets\AppAsset2;
use backend\assets\AppAssetAppbeat;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use kartik\spinner\Spinner;
use yii\helpers\Url;

$logoimg = "footer-logo.png";
$hidelogo = "";
if($_SERVER['SERVER_NAME'] == 'artist.boomcollective.net'){
	AppAsset::register($this);
	$logoimg = "footer-logo.png";
}else if($_SERVER['SERVER_NAME'] == 'test.boomcollective.net'){
	AppAsset2::register($this);
	$logoimg = "footer-logo2.png";
}else if($_SERVER['SERVER_NAME'] == 'partners.appbeat.us'){
	AppAssetAppbeat::register($this);
	$logoimg = "appbeat/06.png";
}else{
	AppAsset::register($this);
	$hidelogo = ".footer{ display:none; }";
}

if(isset(Yii::$app->user->identity->UserID) && Yii::$app->user->identity->UserID!='' && Yii::$app->user->identity->UserType == 2) {
    $artistData = backend\models\Artist::getArtistData(Yii::$app->user->identity->UserID);
    if(isset($artistData[0]['ArtistID']) && $artistData[0]['ArtistID']!= '') {
        $artistID = $artistData[0]['ArtistID'];
        $session = Yii::$app->session;
        $session->set('ArtistID', $artistID);
    }    
}    
header('Content-type: text/html; charset=UTF-8');
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

<?php echo $hidelogo; ?>  

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
if(Yii::$app->controller->id == "artistcompany")
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
if(Yii::$app->controller->id == "qa")
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
if(Yii::$app->controller->id == "setting")
{
    if(Yii::$app->controller->action->id == "update")
    {
        $class = "artistedit";
    }
}    
else if(Yii::$app->controller->id == "mymusictv")
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
else if(Yii::$app->controller->id == "nativeproducts")
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
    
}else if(Yii::$app->controller->id == "credentials")
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
else if(Yii::$app->controller->id == "purchase")
{
    if(Yii::$app->controller->action->id == "index")
    {
        $class = "artistgrid";
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
	else if(Yii::$app->controller->action->id == "pages")
    {
        $class = "postgrid";
    }
	else if(Yii::$app->controller->action->id == "createpage")
    {
        $class = "postgrid";
    }
	else if(Yii::$app->controller->action->id == "editpage")
    {
        $class = "postgrid";
    }
    
}
else if(Yii::$app->controller->id == "flagpost")
{
    if(Yii::$app->controller->action->id == "index"){
        $class = "postgrid";
    }
}
else if(Yii::$app->controller->id == "flagged" && (Yii::$app->controller->action->id == "index"))
{
    $class = "postgrid";
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
	else if(Yii::$app->controller->action->id == "index-company-admin")
    {
        $class = "adminusergrid";
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

	<div id="social-head">
		<div id="fb-icon" class="social-icon"></div>
		<div id="gplus-icon" class="social-icon"></div>
		<div id="twitter-icon" class="social-icon"></div>
		<div id="pin-icon" class="social-icon"></div>
		<div id="insta-icon" class="social-icon"></div>
		<div id="vim-icon" class="social-icon"></div>
		<div id="linkedin-icon" class="social-icon"></div>
		<div id="wire-icon" class="social-icon"></div>
	</div>
	
	<?php
	$userlabel = "";
	if(isset(Yii::$app->user->identity->UserName) && Yii::$app->user->identity->UserName != ''){ $userlabel = ucfirst(Yii::$app->user->identity->UserName); }else{ $userlabel = '';}
	if($_SERVER['SERVER_NAME'] == 'partners.appbeat.us'){ $userlabel = "appbeat";}
    NavBar::begin([
        'brandLabel' => $userlabel,
        'brandUrl' => ((isset(Yii::$app->user->identity->UserType) && Yii::$app->user->identity->UserType == 1) ? Url::toRoute('artist/index') : Url::toRoute('post/index')),
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    //$menuItems = array();
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
    } else if(Yii::$app->user->identity->UserType == 1) {
                $artistData = backend\models\Artist::find()->where("1")->asArray()->all();
                $artistID = $artistData[0]['ArtistID'];
                $menuItems[] = [
                    'label' => 'Artists',
                    'url' => ['/artist/index'],
                    'active'=>Yii::$app->controller->id == 'artist',
					'items'=>[
							['label' => 'Artists', 'url' =>['/artist/index'],'active'=>Yii::$app->controller->id == 'artist'],
                            ['label' => 'Artist Companies', 'url' =>['/artistcompany/index'],'active'=>Yii::$app->controller->id == 'artistcompany'],
                    ]
                ];
                $menuItems[] = [
                    'label' => 'Posts',
                    'url' => ['/post/index'],
                    'items'=>[
                            ['label' => 'Posts', 'url' =>['/post/index'],'active'=>Yii::$app->controller->id == 'post'],
                            ['label' => 'Flagged Posts', 'url' =>['/flagged/index'],'active'=>Yii::$app->controller->id == 'flagged'],
                    ]
                ];
                $menuItems[] = [
                    'label' => 'Flageed Comments',
                    'url' => ['/flagpost/index'],
                ];
                $menuItems[] = [
                    'label' => 'QA',
                    'url' => ['/qa/index'],
                    'active'=>Yii::$app->controller->id == 'qa',
                ];
                $menuItems[] = [
                    'label' => 'Users',
                    'url' => ['/member/index'],
					'items'=>[
						['label' => 'Users', 'url' => ['/member/index'],'active'=>Yii::$app->controller->id == 'member'],
						['label' => 'Purchase', 'url' => ['/purchase/index'],'active'=>Yii::$app->controller->id == 'purchase']
					]
                ];
                $menuItems[] = [
                    'label' => 'Stickers',
                    'url' => ['/sticker/index'],
                    'active'=>Yii::$app->controller->id == 'sticker',
                ];
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
					'items'=>[
						['label' => 'Staff', 'url' => ['/user/index'],'active'=>Yii::$app->controller->id == 'user/index'],
						['label' => 'Company Admin', 'url' => ['/user/index-company-admin'],'active'=>Yii::$app->controller->id == 'user/index-company-admin']
					]
                ];
                $menuItems[] = [
                    'label' => 'Settings',
                    'url' => ['/setting/update'],
                    'items'=>[
                            ['label' => 'QA setting', 'url' =>['/setting/update'],'active'=>Yii::$app->controller->id == 'setting'],
							['label' => 'Native Products', 'url' => ['/nativeproducts/index'],'active'=>Yii::$app->controller->id == 'nativeproducts'],
                            ['label' => 'Credentails setting', 'url' =>['/credentials/create'],'active'=>Yii::$app->controller->id == 'credentials']
                    ]
                ];
                $menuItems[] = [
                    'label' => 'Logout',
                    'url' => '#',
                    'linkOptions' => ['onclick'=>'return logoutconfirm();']
                ];
		}else if(Yii::$app->user->identity->UserType == 4) {
                $artistData = backend\models\Artist::find()->where("1")->asArray()->all();
                $artistID = $artistData[0]['ArtistID'];
                $menuItems[] = [
                    'label' => 'Artists',
                    'url' => ['/artist/index'],
                    'active'=>Yii::$app->controller->id == 'artist',
					'items'=>[
							['label' => 'Artists', 'url' =>['/artist/index'],'active'=>Yii::$app->controller->id == 'artist'],
                    ]
                ];
                $menuItems[] = [
                    'label' => 'Posts',
                    'url' => ['/post/index'],
                    'items'=>[
                            ['label' => 'Posts', 'url' =>['/post/index'],'active'=>Yii::$app->controller->id == 'post'],
                            ['label' => 'Flagged Posts', 'url' =>['/flagged/index'],'active'=>Yii::$app->controller->id == 'flagged'],
                    ]
                ];
                $menuItems[] = [
                    'label' => 'Flageed Comments',
                    'url' => ['/flagpost/index'],
                ];
                $menuItems[] = [
                    'label' => 'QA',
                    'url' => ['/qa/index'],
                    'active'=>Yii::$app->controller->id == 'qa',
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
                $menuItems[] = [
                    'label' => 'Similar Apps',
                    'url' => ['/similarapp/index'],
                    'active'=>Yii::$app->controller->id == 'similarapp',
                ];
                $menuItems[] = [
                    'label' => 'Settings',
                    'url' => ['/setting/update'],
					'items'=>[
                            ['label' => 'QA setting', 'url' =>['/setting/update'],'active'=>Yii::$app->controller->id == 'setting'],
                    ]
                ];
                $menuItems[] = [
                    'label' => 'Logout',
                    'url' => '#',
                    'linkOptions' => ['onclick'=>'return logoutconfirm();']
                ];
        } else if(Yii::$app->user->identity->UserType == 2) {
                $menuItems[] = [
                    'label' => 'Post',
                    'url' => ['/post/index'],
                    'active'=>Yii::$app->controller->id == 'post',
                ];
                $menuItems[] = [
                    'label' => 'QA',
                    'url' => ['/qa/index'],
                    'active'=>Yii::$app->controller->id == 'qa',
                ];
                $menuItems[] = [
                    'label' => 'Flagged Comments',
                    'url' => ['/flagpost/index'],
                ];
                $menuItems[] = [
                    'label' => 'Flagged Post',
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
                    'label' => 'Settings',
                    'url' => ['/setting/update?id='.Yii::$app->user->identity->ArtistID],
                    'active'=>Yii::$app->controller->id == 'setting',
                ];
                $menuItems[] = [
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
       <p class="pull-right"><img src="<?php echo Url::base();?>/image/<?php echo $logoimg; ?>" /></p>
    </div>
</footer>   

<?php $this->endBody(); ?>
<script>    
$(document).on('pjax:send', function() {
    $("#spinnerLoader").show();
});    
$(document).on('pjax:complete', function() {
    $("#spinnerLoader").hide();
});
$("#w0").on("afterValidate", function (event, messages) {
    setTimeout(function(){showloader();}, 200);
});

/***************************** Q&A ***********************************/

$("#qa-form").on('beforeSubmit', function (e) {
            var form = $(this);
            if(form.find(".has-error").length) {
                 return false;
            }
            var editpostID = '<?php if(isset($_GET['id']) && $_GET['id']!='') { echo $_GET['id']; } ?>';
            var postType = $("#qapost-posttype").val();
            var postVideo = $("#qapost-postvideo").val();
            var postThumbImages = $("#qapost-thumbimage").val();
            var postDiscription = $("#qapost-description").val();
            var postAnswer = $("#qapost-reply").val();
            
            if(postType == '1' && postDiscription == '') {
                alert("Please enter question");
                return false;
            }
            if(postType == '1' && postAnswer == '' && editpostID != '')  {
                alert("Please enter answer");
                return false;
            }
            if(editpostID == '' && postVideo == '' && postType == 2) {
                alert("Please upload video");
                return false;
            }
            
            $("#spinnerLoader").show();
            var updatedID = '<?php if(isset($_GET['id']) && $_GET['id']!='') { echo $_GET['id']; } ?>';
            if(updatedID == '')  { 
                    var URL  = '<?php echo Url::toRoute('qa/createajax'); ?>';
                    var datastring = $("#qa-form").serialize();
            } else {         
                    var URL  = '<?php echo Url::toRoute('qa/updateajax'); ?>';
                    var datastring = $("#qa-form").serialize();
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
                                var bucket = new AWS.S3({params: {Bucket: '<?php echo S3BUCKET; ?>'}});
                                if(postType == "1") {
                                    //document.location.href='<?php echo Url::toRoute('qa/index'); ?>';
                                }
								
                                var fileChooser = document.getElementById('qapost-postvideo');
								var fileChooservideo = '';
                                if(fileChooser != null){fileChooservideo = document.getElementById('qapost-postvideo').value;}
                                var fileChooserforthumb = document.getElementById('qapost-videothumbimage');
                                var fileChooserforthumbImage = '';
								if(fileChooserforthumb != null){fileChooserforthumbImage = document.getElementById('qapost-videothumbimage').value;}
                                
                                var fileChooserReply = "";
                                var fileChooservideoReply = "";
                                var fileChooserforthumbReply = "";
                                var fileChooserforthumbImageReply = "";
                                if(editpostID != '') { 
                                    fileChooserReply = document.getElementById('qapost-postvideoreply');
                                    if(fileChooserReply != null){fileChooservideoReply = document.getElementById('qapost-postvideoreply').value;}
                                    fileChooserforthumbReply = document.getElementById('qapost-replythumbimage');
                                    if(fileChooserforthumbReply != null){fileChooserforthumbImageReply = document.getElementById('qapost-replythumbimage').value;}
                                }
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
												console.log(err);
												if(err != null){
													alert("Error! An error occoured during the upload.");
												}else{
													$.ajax({
														type : 'POST',
														url : '<?php echo Url::toRoute('qa/updatevideo'); ?>',
														data : 'id='+msg.id+'&filename='+renameFile+'&type=video',
														dataType: 'json',
														async: false, 
														success : function(msgOfFile) {
															if(msgOfFile.status == 'ok') {
																questionImageUpload(fileChooserforthumbImage,fileChooserforthumb,fileChooserforthumbImageReply,fileChooserforthumbReply,fileChooservideoReply,fileChooserReply,editpostID,msg);
															}    
														}    
													});
												}
                                            }); 
                                    }
                                } else {
									questionImageUpload(fileChooserforthumbImage,fileChooserforthumb,fileChooserforthumbImageReply,fileChooserforthumbReply,fileChooservideoReply,fileChooserReply,editpostID,msg);
                                }
                            } else {
                                alert("Something went wrong");
                                return false;
                            }
                        }
            });
            return false;
});

function replyVideo(fileChooserforthumbImage,fileChooserforthumb,fileChooserforthumbImageReply,fileChooserforthumbReply,fileChooservideoReply,fileChooserReply,editpostID,msg) {
    if(fileChooservideoReply != "")
    {
        var bucket = new AWS.S3({params: {Bucket: '<?php echo S3BUCKET; ?>'}});
        var file = fileChooserReply.files[0];
        var updatedFile = fileChooserReply.files[0]['name'];
        var extension = updatedFile.split('.').pop();
        var updatedFileString = $.now()+'.'+extension;
        var finalImageName = 'reply_'+msg.id+'_'+updatedFileString;
        var artistID = msg.ArtistID;
        var renameFile = '<?php echo BOOM ?>'+artistID+'<?php echo POST_VIDEOS ?>'+finalImageName;
        if(file) {
                $(".progressForReply").show();    
                var params = {Key: renameFile, ContentType: file.type, Body: file};
                bucket.upload(params).on('httpUploadProgress', function(evt) {
                var percentage = parseInt((evt.loaded * 100) / evt.total);
                $("#progress-barForReply")
                    .css("width", percentage + "%")
                    .attr("aria-valuenow", percentage)
                    .text(percentage + "%");
                }).send(function(err, data) {
					console.log(err);
					if(err != null){
						alert("Error! An error occoured during the upload.");
					}else{
						$.ajax({
							type : 'POST',
							url : '<?php echo Url::toRoute('qa/updaterelplyvideo'); ?>',
							data : 'id='+msg.id+'&filename='+renameFile+'&type=video',
							dataType: 'json',
							async: false, 
							success : function(msgOfFile) {
								if(msgOfFile.status == 'ok') {
									replyImageUpload(fileChooserforthumbImage,fileChooserforthumb,fileChooserforthumbImageReply,fileChooserforthumbReply,fileChooservideoReply,fileChooserReply,editpostID,msg);
								}    
							}    
						});
					}
                }); 
        }
    } else {
        replyImageUpload(fileChooserforthumbImage,fileChooserforthumb,fileChooserforthumbImageReply,fileChooserforthumbReply,fileChooservideoReply,fileChooserReply,editpostID,msg);
    }
}

function replyImageUpload(fileChooserforthumbImage,fileChooserforthumb,fileChooserforthumbImageReply,fileChooserforthumbReply,fileChooservideoReply,fileChooserReply,editpostID,msg) {
    if(fileChooserforthumbImageReply != "")
    {
        var bucket = new AWS.S3({params: {Bucket: '<?php echo S3BUCKET; ?>'}});
        var fileforthumb = fileChooserforthumbReply.files[0];
        var updatedFile = fileChooserforthumbReply.files[0]['name'];
        var extension = updatedFile.split('.').pop();
        var updatedFileString = $.now()+'.'+extension;
        var finalImageName = msg.id+'_'+updatedFileString;
        var artistID = msg.ArtistID;
        var uploadimage = '<?php echo BOOM ?>'+artistID+'<?php echo POST_THUMBIMAGE_VIDEOS ?>'+finalImageName;
        var renameFilethumb = finalImageName;
        if(fileforthumb) {
            $("#progressForReplyThumb").show();    
            var params = {Key: uploadimage, ContentType: fileforthumb.type, Body: fileforthumb};
            bucket.upload(params).on('httpUploadProgress', function(evt) {
            var percentage = parseInt((evt.loaded * 100) / evt.total);
            $("#progress-barForReplyThumb")
                .css("width", percentage + "%")
                .attr("aria-valuenow", percentage)
                .text(percentage + "%");
            }).send(function(err, data) {
                var img = document.getElementById('qapost-replythumbimage'); 
                var width = img.clientWidth;
                var height = img.clientHeight;
                console.log(width);
                console.log(height);
				console.log(err);
				if(err != null){
					alert("Error! An error occoured during the upload.");
				}else{
					$.ajax({
						type : 'POST',
						url : '<?php echo Url::toRoute('qa/updatereplyimage'); ?>',
						data : 'id='+msg.id+'&thumbfilename='+renameFilethumb+'&type=image'+'&width='+width+'&height='+height+'&imgUrl='+uploadimage,
						dataType: 'json',
						async: false, 
						success : function(msgOfFile) {
							if(msgOfFile.status == 'ok') {
								//Audio Reply upload
								var fileChooserAudioReply = document.getElementById('qapost-audioreplyurl');
								if(fileChooserAudioReply != null && fileChooserAudioReply != ''){
									var fileChooserAudioReplyVal = document.getElementById('qapost-audioreplyurl').value;
									replyAudio(fileChooserAudioReplyVal,fileChooserAudioReply,msg);
								}else{
									document.location.href='<?php echo Url::toRoute('qa/index'); ?>';
								}
							}    
						}    


					});
				}
            }); 
        }
    } else {		
		document.location.href='<?php echo Url::toRoute('qa/index'); ?>';
    }
}

function questionImageUpload(fileChooserforthumbImage,fileChooserforthumb,fileChooserforthumbImageReply,fileChooserforthumbReply,fileChooservideoReply,fileChooserReply,editpostID,msg) {
    if(fileChooserforthumbImage != "")
    {
        var bucket = new AWS.S3({params: {Bucket: '<?php echo S3BUCKET; ?>'}});
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
				console.log(err);
				if(err != null){
					alert("Error! An error occoured during the upload.");
				}else{
					$.ajax({
						type : 'POST',
						url : '<?php echo Url::toRoute('qa/updateimage'); ?>',
						data : 'id='+msg.id+'&thumbfilename='+renameFilethumb+'&type=image'+'&imgUrl='+uploadimage,
						dataType: 'json',
						async: false, 
						success : function(msgOfFile) {
							if(msgOfFile.status == 'ok') {
								if(editpostID !="") {
									replyVideo(fileChooserforthumbImage,fileChooserforthumb,fileChooserforthumbImageReply,fileChooserforthumbReply,fileChooservideoReply,fileChooserReply,editpostID,msg);
								} else {
									document.location.href='<?php echo Url::toRoute('qa/index'); ?>';    
								}    
							}    
						}    



					});
				}
            }); 
        }
    } else {
        replyVideo(fileChooserforthumbImage,fileChooserforthumb,fileChooserforthumbImageReply,fileChooserforthumbReply,fileChooservideoReply,fileChooserReply,editpostID,msg);
    }
}


/***********************************************************************/

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
                            var fileChooservideo = document.getElementById('post-postvideo').value;
                            if(msg.status == 'ok') {
                                var fileChooserforthumbImage = document.getElementById('post-videothumbimage').value;
                                if(postVideo != '' || fileChooserforthumbImage !='' || fileChooserforthumbImage !='') {
                                        var fileChooser = document.getElementById('post-postvideo');
                                        var fileChooservideo = document.getElementById('post-postvideo').value;

                                        console.log(fileChooservideo);
                                        console.log(postVideo);
                                    
                                        //for video thumb image
                                        var bucket = new AWS.S3({params: {Bucket: '<?php echo S3BUCKET; ?>'}});
                                        var fileChooserforthumb = document.getElementById('post-videothumbimage');
                                        if( (fileChooserforthumbImage != "" && postVideo != '') || fileChooserforthumbImage != "")
                                        {
                                            var fileforthumb = fileChooserforthumb.files[0];
                                            var updatedFile = fileChooserforthumb.files[0]['name'];
                                            var extension = updatedFile.split('.').pop();
                                            var updatedFileString = $.now()+'.'+extension;
                                            var finalImageName = msg.id+'_'+updatedFileString;
                                            var artistID = msg.ArtistID;
                                            var uploadimage = '<?php echo BOOM ?>'+artistID+'<?php echo POST_THUMBIMAGE_VIDEOS ?>'+finalImageName;
                                           
                                            var renameFilethumb = finalImageName;
                                            if(fileforthumb){
                                                                    $("#progressForThumb").show();    
                                                                    var params = {Key: uploadimage, ContentType: fileforthumb.type, Body: fileforthumb};
                                                                    bucket.upload(params).on('httpUploadProgress', function(evt) {
                                                                    var percentage = parseInt((evt.loaded * 100) / evt.total);
                                                                    $("#progress-barForThumb")
                                                                        .css("width", percentage + "%")
                                                                        .attr("aria-valuenow", percentage)
                                                                        .text(percentage + "%");
																	}).send(function(err, data) {
																		console.log(err);
																		if(err != null){
																			alert("Error! An error occoured during the upload.");
																		}else{
																		$.ajax({
                                                                            type : 'POST',
                                                                            url : '<?php echo Url::toRoute('post/updateimage'); ?>',
                                                                            data : 'id='+msg.id+'&thumbfilename='+renameFilethumb+'&type=image&artistid='+msg.ArtistID,
                                                                            dataType: 'json',
                                                                            async: false, 
                                                                            success : function(msgOfFile) {
                                                                                if(msgOfFile.status == 'ok') {
                                                                                    if(fileChooservideo == "") {
                                                                                        document.location.href='<?php echo Url::toRoute('post/index'); ?>';
                                                                                    } else {
                                                                                        if(fileChooservideo != "") {
                                                                                            
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
																															console.log(err);
																															if(err != null){
																																alert("Error! An error occoured during the upload.");
																															}else{
                                                                                                                               //alert("Update video postupdate");
																																$.ajax({
																																	type : 'POST',
																																	url : '<?php echo Url::toRoute('post/updatevideo'); ?>',
																																	data : 'id='+msg.id+'&filename='+renameFile+'&type=video',
																																	dataType: 'json',
																																	async: false,
																																	success : function(msgOfFile) {
																																		if(msgOfFile.status == 'ok') {
																																			//alert("File uploaded successfully.");
																																			console.log("video Update Done");
                                                                                                                                            document.location.href='<?php echo Url::toRoute('post/index'); ?>';
																																		}    
																																	}    
																																});
																															}
                                                                                                                        }); 

                                                                                                    }
                                                                                                }
                                                                                            }    
                                                                                    }    
                                                                                } 
																			});
																		}
                                                                    });
                                                            }    
                                        
                                        } else if(typeof fileChooservideo != "undefined" && fileChooservideo!="") {
                                                    console.log(fileChooservideo);
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
                                                                                console.log(err);
																				if(err != null){
																					alert("Error! An error occoured during the upload.");
																				}else{
																				   $.ajax({
																						type : 'POST',
																						url : '<?php echo Url::toRoute('post/updatevideo'); ?>',
																						data : 'id='+msg.id+'&filename='+renameFile+'&type=video',
																						dataType: 'json',
																						async: false,
																						success : function(msgOfFile) {
																							if(msgOfFile.status == 'ok') {
																								//alert("File uploaded successfully.");
                                                                                                console.log("File uploaded successfully.");
                                                                                                document.location.href='<?php echo Url::toRoute('post/index'); ?>';
                                                                                                // setTimeout(
                                                                                                //     function() {
                                                                                                //       window.location.href='<?php echo Url::toRoute('post/index'); ?>';
                                                                                                //     }, 15000);
																								
																							}    
																						}    
																					});
																				}
                                                                            }); 

                                                    }
												}
                                        } else {
                                            //alert("run here");
                                            document.location.href='<?php echo Url::toRoute('post/index'); ?>';
                                        }
                                        return false;
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