<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Similarapp */

$this->title = 'Add Similarapp';
$this->params['breadcrumbs'][] = ['label' => 'Similarapps', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="similarapp-create">
<p class="pull-right">
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
</p>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
