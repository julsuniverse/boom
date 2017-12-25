<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Artist */

$this->title = 'Add Native Product';
$this->params['breadcrumbs'][] = ['label' => 'Native Product', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>



<div class="artist-create">
<p class="pull-right">
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
</p>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
