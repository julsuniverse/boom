<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Artist */
if(Yii::$app->user->identity->UserType == 1) 
{
    $this->title = 'Update Artist';
}
else
{
    $this->title = 'Update Profile';
}
$this->params['breadcrumbs'][] = ['label' => 'Artists', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->ArtistID, 'url' => ['view', 'id' => $model->ArtistID]];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="artist-update">
<?php
if(Yii::$app->user->identity->UserType == 1) 
{
?>
    <p class="pull-right">
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
    </p>
<?php
}
?>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,'galleryData'=>$galleryData,
    ]) ?>

</div>
