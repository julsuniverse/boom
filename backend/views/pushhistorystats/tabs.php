<?php
use yii\helpers\Url;
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


<div class="pushdetail">
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" <?php if($actionName == 'view') { ?> class="active" <?php } ?> ><a href="<?php echo Url::to(['/pushhistorystats/view?id='.$id]); ?>" >Details</a></li>
    <li role="presentation" <?php if($actionName == 'users') { ?> class="active" <?php } ?>><a href="<?php echo Url::to(['/pushhistorystats/users?id='.$id]); ?>" >Statistics</a></li>
  </ul>
</div>