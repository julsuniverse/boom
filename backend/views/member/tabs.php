<?php
use yii\helpers\Url;
$actionName = Yii::$app->controller->action->id;
$id = '';
if(isset($_GET['id']) && $_GET['id']!='') {
    $id = $_GET['id'];
}    
?>

<?php if(isset($id) && $id !='') : ?>
<div>
  <ul class="nav nav-tabs" role="tablist">
<!--    <li role="presentation" <?php if($actionName == 'view') { ?> class="active" <?php } ?> ><a href="<?php echo Url::to(['/member/view?id='.$id]); ?>" >Details</a></li>-->
    <li role="presentation" <?php if($actionName == 'update') { ?> class="active" <?php } ?> ><a href="<?php echo Url::to(['/member/update?id='.$id]); ?>" >Update</a></li>
    <li role="presentation" <?php if($actionName == 'like') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/member/like?id='.$id]); ?>" >Likes</a></li>
    <li role="presentation" <?php if($actionName == 'comment') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/member/comment?id='.$id]); ?>" >Comments</a></li>
    <li role="presentation" <?php if($actionName == 'exclusive') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/member/exclusive?id='.$id]); ?>" >Exclusive</a></li>
    <li role="presentation" <?php if($actionName == 'share') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/member/share?id='.$id]); ?>" >Share</a></li>
    <li role="presentation" <?php if($actionName == 'flag') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/member/flag?id='.$id]); ?>" >Flag</a></li>
<!--    <li role="presentation" <?php if($actionName == 'stickers') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/member/stickers?id='.$id]); ?>" >Stickers</a></li>-->
  </ul>
</div>
<?php endif; ?>