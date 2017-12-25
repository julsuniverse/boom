<?php
use yii\helpers\Url;
$actionName = Yii::$app->controller->action->id;
$id = $_GET['id'];
?>

<div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" <?php if($actionName == 'update') { ?> class="active" <?php } ?> ><a href="<?php echo Url::to(['/qa/update?id='.$id]); ?>" >Update</a></li>
    <li role="presentation" <?php if($actionName == 'like') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/qa/like?id='.$id]); ?>" >Likes</a></li>
    <li role="presentation" <?php if($actionName == 'comment') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/qa/comment?id='.$id]); ?>" >Comments</a></li>
    <li role="presentation" <?php if($actionName == 'share') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/qa/share?id='.$id]); ?>" >Share</a></li>
  </ul>
</div>