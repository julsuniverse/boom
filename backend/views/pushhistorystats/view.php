<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Pushhistorystats */
if(isset($_GET['id']) && $_GET['id']!='') {
    $id = $_GET['id'];
    include 'tabs.php';
}
//$this->title = 'Push Notification Details';
$this->params['breadcrumbs'][] = ['label' => 'Pushhistorystats', 'url' => ['index']];
//$this->params['breadcrumbs'][] = $this->title;

?>
<div class="pushhistorystats-view">
<p class="pull-right">
        <?= yii\helpers\Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
</p>
    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'BatchID',
            'ArtistID',
            'Message',
            'TotalDevices',
            //'AndroidDelivered',
            //'IosDelivered',
            //'SearchCriterias',
            'Created',
            [
                'attribute' => 'Created',
                'value' => getTimezone($model->Created),
            ]
        ],
    ]) ?>

</div>
