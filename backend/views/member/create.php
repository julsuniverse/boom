<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Member */

$this->title = 'Create User';
$this->params['breadcrumbs'][] = ['label' => 'Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
.wrap {
	<?php if($_SERVER['SERVER_NAME'] == 'artist.boomcollective.net'){
		echo 'background-image: url("../../image/usergrid-bg.jpg") !important;';  
	}?>   
    background-size: cover !important; 
    background-position: center center !important;
}    
</style>
<p class="pull-right">
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
</p>
<div class="member-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
