<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;

$id = '';
if(isset($_GET['id']) && $_GET['id'] != '') {
    $id = $_GET['id'];
}    

if(isset($_GET['appname']) && $_GET['appname'] != '') {
    $appname = $_GET['appname'];
} else {
    $appname =  'Boom Fan App';
}

if(isset($_GET['urlscheme']) && $_GET['urlscheme'] != '') {
    $urlscheme = $_GET['urlscheme'];
} else {
    $urlscheme =  'boomfanapp';
}

if(isset($_GET['packagename']) && $_GET['packagename'] != '') {
    $packagename = $_GET['packagename'];
} else {
    $packagename =  'com.boomuserapp';
}

$this->registerJs('
    function open() {
        // If its not an universal app, use IS_IPAD or IS_IPHONE
        if (IS_IOS) {		            
            window.location = "'.$urlscheme.'://view?id='.$id.'";			
            setTimeout(function() {
                // If the user is still here, open the App Store
                if (!document.webkitHidden) {
                    // Replace the Apple ID following "/id"
                    window.location = "'.Url::toRoute("site/post?id=".$id."&appname=".$appname).'";
                }
            }, 25);
        } else if (IS_ANDROID) {
            var link = "'.Url::toRoute("site/post?id=".$id."&appname=".$appname).'";		
			window.location = "intent://view/#Intent;scheme='.$urlscheme.';package='.$packagename.';end";
            //window.location = "intent://view/#Intent;scheme='.$urlscheme.';package='.$packagename.';S.browser_fallback_url="+link+";end";
			setTimeout(function() {
                // If the user is still here, open the App Store
                if (!document.webkitHidden) {
                    // Replace the Apple ID following "/id"
                    window.location = "'.Url::toRoute("site/post?id=".$id."&appname=".$appname).'";
                }
            }, 25);
        } else {
            window.location = "'.Url::toRoute("site/post?id=".$id."&appname=".$appname).'";
        }
    }

    var IS_IPAD = navigator.userAgent.match(/iPad/i) != null,
    IS_IPHONE = !IS_IPAD && ((navigator.userAgent.match(/iPhone/i) != null) || (navigator.userAgent.match(/iPod/i) != null)),
    IS_IOS = IS_IPAD || IS_IPHONE,
    IS_ANDROID = !IS_IOS && navigator.userAgent.match(/android/i) != null,
    IS_MOBILE = IS_IOS || IS_ANDROID;
    open();
    

');

$this->title = $appname;

?>

