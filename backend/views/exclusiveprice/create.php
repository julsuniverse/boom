<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\ExclusivePrice */

$this->title = 'Create Exclusive Price';
$this->params['breadcrumbs'][] = ['label' => 'Exclusive Prices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="exclusive-price-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
