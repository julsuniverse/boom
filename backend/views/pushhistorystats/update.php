<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Pushhistorystats */

$this->title = 'Update Pushhistorystats: ' . ' ' . $model->BatchID;
$this->params['breadcrumbs'][] = ['label' => 'Pushhistorystats', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->BatchID, 'url' => ['view', 'id' => $model->BatchID]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="pushhistorystats-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
