<?php
use yii\helpers\Url;
use backend\models\Post;
/*use kartik\tabs\TabsX;
$items = [
    [
        'label'=>'<i class="glyphicon glyphicon-home"></i> Update',
        //'content'=>'sdsdsd',
        'active'=>true,
        'linkOptions'=>['data-url'=>Url::to(['/post/update?tab=1&id=17'])]
    ],
    [
        'label'=>'<i class="glyphicon glyphicon-user"></i> Likes',
        //'content'=>$content2,
        'linkOptions'=>['data-url'=>Url::to(['/post/update?tab=2&id=17'])]
    ],
    [
        'label'=>'<i class="glyphicon glyphicon-user"></i> Comments',
        //'content'=>$content3,
        'linkOptions'=>['data-url'=>Url::to(['/post/update?tab=3&id=17'])]
    ],
];

echo TabsX::widget([
    'items'=>$items,
    'position'=>TabsX::POS_ABOVE,
    'encodeLabels'=>false
]);*/

$actionName = Yii::$app->controller->action->id;
$id = $_GET['id'];
?>

<div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" <?php if($actionName == 'update') { ?> class="active" <?php } ?> ><a href="<?php echo Url::to(['/post/update?id='.$id]); ?>" >Update</a></li>
	<?php
		$post = Post::findOne($id);
		if($post->PostType == 5){?>
			<li role="presentation" <?php if($actionName == 'pages') { ?> class="active" <?php } ?> ><a href="<?php echo Url::to(['/post/pages?id='.$id]); ?>" >Pages</a></li>
	<?php } ?>
    <li role="presentation" <?php if($actionName == 'like') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/post/like?id='.$id]); ?>" >Likes</a></li>
    <li role="presentation" <?php if($actionName == 'comment') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/post/comment?id='.$id]); ?>" >Comments</a></li>
    <li role="presentation" <?php if($actionName == 'share') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/post/share?id='.$id]); ?>" >Share</a></li>
  </ul>
</div>