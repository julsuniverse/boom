<div class="post-update">
<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use backend\models\PostPages;

$this->title = 'Edit page';
$this->params['breadcrumbs'][] = ['label' => 'Post Page', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$PostID = $_GET['id'];
if(isset($PostID) && $PostID!='') {
    include 'tabs.php';
}
?>

<div class="post-create">
<h1><?= $model->PostTitle ?></h1>
<p class="pull-right">
        <?= Html::a('Back', ['pages?id='.$PostID], ['class' => 'btn btn-primary']) ?>
</p>
    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_formpage', [
        'model' => $model,
    ]) ?>

</div>
</div>