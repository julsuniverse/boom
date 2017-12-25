<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Artist */

$this->title = 'Add Credentials';
$this->params['breadcrumbs'][] = ['label' => 'Credentail', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>



<div class="artist-create">
<p class="pull-right">
        
</p>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
