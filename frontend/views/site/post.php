<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;
use \yii\web\Request;

$imageName = '';
$artistName = '';
$postType = '';
$video = '';
$postTitle = '';
$postDescription = '';
$s3GalleryImage = '';
$iosApp = "";
$androidApp = "";

if(isset($artist->ProfileThumb) && $artist->ProfileThumb!='') {
    $imageName = $artist->ProfileThumb;
    $s3GalleryImage=S3_BUCKETPATH.$artist->ArtistID.PROFILE_IMAGES.$imageName;
}    

if(isset($artist->ArtistName) && $artist->ArtistName!='') {
    $artistName =  stripslashes($artist->ArtistName);
}    

if(isset($post->PostTitle) && $post->PostTitle!= '') {
    $postTitle = stripslashes($post->PostTitle);
}    

if(isset($post->Description) && $post->Description!='') { 
    $postDescription = stripslashes($post->Description);
}
if(isset($post->PostType) && $post->PostType!='') { 
    $postType  = $post->PostType;
    $thumbImage = '';
    if($postType == '3') {
        $thumbImage = $post->ThumbImage;
        $video = $post->MobileStreamUrl;
    }
}    
if(isset($artist->IOSApp) && $artist->IOSApp!="") {
    $iosApp = $artist->IOSApp;
}
if(isset($artist->AndroidApp) && $artist->AndroidApp!="") {
    $androidApp = $artist->AndroidApp;
}



$siteUrl = (new Request)->getBaseUrl();
$iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
$iPad  = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
$mac  = stripos($_SERVER['HTTP_USER_AGENT'],"Macintosh");
$Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");


$windows = false;
if(preg_match('/windows|win32/i', $_SERVER['HTTP_USER_AGENT'])) {
    $windows = true;
}

if(isset($_GET['appname']) && $_GET['appname'] != '') {
    $appname = $_GET['appname'];
} else {
    $appname =  'Boom Fan App';
}
$this->title = $appname;

?>
<script type="text/javascript" src='http://content.jwplatform.com/libraries/DkwOvSfA.js'></script>

<style>
.imggallery {
    width: 200px;
    height: 200px;
}
.row-centered {
    text-align:center;
} 
.navbar-toggle {
    display: none !important;
}
</style>

<div class='container'>
    <div>
        <div>
            <?php if($s3GalleryImage != '') { ?>
                <img class='img-thumbnail imggallery img-circle center-block' src="<?php echo $s3GalleryImage; ?>" />
            <?php } else { ?>
                <center><h3>No post found</h3></center>
            <?php } ?>    
        </div>
        <div class='row row row-centered'>
            <h3><?php echo $artistName; ?></h3>
        </div>
    </div><br>    
    <?php if($postTitle != '') { ?>
    <div class='row'>
        <label class="control-label">Title : </label>
        <?php echo $postTitle; ?>
    </div><br>
    <?php } if($postType == '2') { 
                    if(isset($gallery))
                    {
                        echo '<div class="row">';
                        echo '<div id="myCarousel" class="carousel slide" data-ride="carousel">';    
                        echo '<ol class="carousel-indicators">';
                        foreach($gallery as $key => $value)
                        {
                            echo '<li data-target="#myCarousel" data-slide-to="'.$key.'" class="active"></li>';
                        }            
                        echo '</ol>';
                        echo '<div class="carousel-inner" role="listbox">';
                        foreach($gallery as $key => $value)
                        {
                            $imageName = $value['Image'];
                            $galleryID = $value['ID'];
                            $s3GalleryImage=S3_BUCKETPATH.$artist->ArtistID.POST_IMAGES.$imageName;
                            ?>
                            <div class="item <?php if($key == 0) { echo "active"; } ?>">
                                <img class='img-responsive center-block' src="<?php echo $s3GalleryImage; ?>" />
                            </div>
                            <?php
                        }
                        echo '</div>
                        <!-- Left and right controls -->
                        <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
                          <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                          <span class="sr-only">Previous</span>
                        </a>
                        <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
                          <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                          <span class="sr-only">Next</span>
                        </a>
                        </div></div>';
                    }  
            } 
            if($postType == '3') { ?>
            <div class='row'>
                <div id="container">Loading the player...</div>
            </div>    
            <?php }
            if($postDescription != '') { ?>
    <br><div class='row'>
        <label class="control-label">Description : </label>
        <?php echo $postDescription; ?>
    </div><br>
    <?php } ?>
    <div class="row">
            <div class="col-xs-6 button-apps">
            <?php if(($iPhone || $windows || $mac || $iPad) && $iosApp!="") { ?>
            <a target="blank" href="<?php echo $iosApp; ?>">
                    <p class='button-app'><img class='img-responsive' src="<?php echo $siteUrl.'/images/iphone.png'; ?>"></p>
            </a>
            </div>    
            <div class="col-xs-6 button-apps">    
            <?php } if(($Android || $windows || $mac) && $androidApp!="") { ?>
            <a target="blank" href="<?php echo $androidApp; ?>">
                    <p class='button-app'><img class='img-responsive' src="<?php echo $siteUrl.'/images/android.png'; ?>"></p>
            </a>    
            <?php } ?>
            </div>    
    </div>
</div>  

<script>
<?php if($video != '') { ?>    
var playerInstance = jwplayer('container');
playerInstance.setup({
    file: '<?php echo $video; ?>',
    image: '<?php echo $thumbImage; ?>',
    primary: 'flash',
    type: "mp4"
});
<?php } ?>
</script>
