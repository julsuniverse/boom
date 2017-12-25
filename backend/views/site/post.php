<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\Url;


$imageName = $artist->ProfileThumb;
$s3GalleryImage=S3_BUCKETPATH.$artist->ArtistID.PROFILE_THUMB_IMAGES.$imageName;
$artistName =  stripslashes($artist->ArtistName);

$postTitle = '';
if(isset($post->PostTitle) && $post->PostTitle!= '') {
    $postTitle = stripslashes($post->PostTitle);
}    
$postDescription = '';
if(isset($post->Description) && $post->Description!='') { 
    $postDescription = stripslashes($post->Description);
}    
$postType  = $post->PostType;
?>

<html>
    <body>
        <div>
            <div>
                <div><img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" /></div>
                    <div><?php echo $artistName; ?></div>
                <?php if($postTitle != '') { ?>
                    <div><?php echo $postTitle; ?></div>
                <?php } if($postType == '2') { 
                                if(isset($gallery))
                                {
                                    echo '<div class="form-group imageList">';    
                                    foreach($gallery as $key => $value)
                                    {
                                        $imageName = $value['Image'];
                                        $galleryID = $value['ID'];
                                        $s3GalleryImage=S3_BUCKETPATH.$artist->ArtistID.POST_THUMB_IMAGES.$imageName;
                                        ?>
                                        <div class="imageGallery">
                                            <a href="<?php echo Url::toRoute('post/removeimage?id='.$galleryID); ?>" ><i class="glyphicon glyphicon-remove"></i></a>
                                            <img src="<?php echo $s3GalleryImage; ?>" class="img-responsive file-preview-image" />
                                        </div>
                                        <?php
                                    }
                                    echo '</div><div style="clear:both;"></div>';
                                }  
                        } if($postDescription != '') { ?>
                    <div><?php echo $postDescription; ?></div>
                <?php } ?>
            </div>    
        </div>
    </body>
</html>
