<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Sticker */

$this->title = 'Add Sticker';
$this->params['breadcrumbs'][] = ['label' => 'Stickers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<style>
.wrap {
	<?php if($_SERVER['SERVER_NAME'] == 'artist.boomcollective.net'){
		echo 'background-image: url("../../image/sticker-bg.jpg") !important;';  
	}?>
    background-position: center center !important;
    background-size: cover !important;
}    
</style>

<div class="sticker-create">
<p class="pull-right">
        <?= Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
</p>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'stickerImages'=>array(),
    ]) ?>

</div>
