<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\Member */

$this->title = "View Member";
$this->params['breadcrumbs'][] = ['label' => 'Members', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
include 'tabs.php';
?>
<div class="member-view">

<!--    <h1><?= Html::encode($this->title) ?></h1>-->

<!--    <p>
        <?= Html::a('Update', ['update', 'id' => $model->MemberID], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->MemberID], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>-->

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'MemberName',
            [
                'header' => 'Profile Image',
                'attribute' => 'ProfileThumb',
                'value' =>S3_BUCKETPATH.$model['ArtistID'].PROFILE_IMAGES.$model['ProfileThumb'],
                'format' => ['image',['width'=>'100','height'=>'100']],
            ],
            'Email:email',
            [
                'header' => 'Date of birth',
                'attribute' => 'DOB',
            ],    
            'ZipCode',
            'MobileNo',
            [
                'header' => 'Gender',
                'attribute' => 'Gender',
                'value'=> $model->getGender($model['Gender']),
            ]
        ],
    ]) ?>

</div>
