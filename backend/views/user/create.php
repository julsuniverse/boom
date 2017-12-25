<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\User */

if($model->UserType == 1){$this->title = 'Add Staff';}else{$this->title = 'Add Company Admin';}
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-create">
<p class="pull-right">
        <?php
			$method = 'index';
			if($model->UserType == 4){$method = 'index-company-admin';}
		?>
        <?= Html::a('Back', [$method], ['class' => 'btn btn-primary']) ?>
</p>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
