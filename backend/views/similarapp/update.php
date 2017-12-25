<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Similarapp */

$this->title = 'Update Similar App';
$this->params['breadcrumbs'][] = ['label' => 'Similarapps', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>

<div class="similarapp-update">
<p class="pull-right">
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
</p>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'similarApp' => $similarApp
    ]) ?>

</div>
