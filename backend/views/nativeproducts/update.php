<?php

use yii\helpers\Html;


$this->title = 'Update Native Product';
$this->params['breadcrumbs'][] = ['label' => 'Native Product', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->ID, 'url' => ['view', 'id' => $model->ID]];
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
        'model' => $model,
    ]) ?>

</div>
