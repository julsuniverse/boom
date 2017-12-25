<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Artist */

$this->title = $model->ArtistID;
$this->params['breadcrumbs'][] = ['label' => 'Artists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="artist-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->ArtistID], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->ArtistID], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'ArtistID',
            'ArtistName',
            'ProfileThumb',
            'Email:email',
            'DOB',
            'Nationality',
            'Address',
            'Website',
            'YoutubeChannelName',
            'YearsActive',
            'Genre',
            'AboutMe:ntext',
            'YouTubePageURL:url',
            'LinkedInPageURL:url',
            'TwitterPageURL:url',
            'FacebookPageURL:url',
            'DeviceType',
            'DeviceToken',
            'UserID',
            'CreatedBy',
            'UpdatedBy',
            'Created',
            'Updated',
        ],
    ]) ?>

</div>
