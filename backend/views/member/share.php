<div class="post-update">
<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Shares';
$this->params['breadcrumbs'][] = $this->title;
$id = $_GET['id'];
if(isset($id) && $id!='') {
    include 'tabs.php';
}
?>
<div class="post-index">
<p class="pull-right">
        <?= yii\helpers\Html::a('Back', ['index'], ['class' => 'btn btn-primary']) ?>
</p>
    <h1><?php //echo Html::encode($this->title) ?></h1>

    <?php \yii\widgets\Pjax::begin(
            ['id' => 'ShareList', 'timeout' => false, 'enablePushState' => false, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            [
                'attribute'=>'PostType',
                'filter'=>Html::activeDropDownList($model, 'PostType',array(""=>"All","1"=>"Status","2"=>"Images","3"=>"Video"),['options' => [ "" => ['selected ' => true]],'class'=>'form-control']),
            ],
            [
                //'header'=>'Name',
                'attribute'=>'PostTitle',
                'value' => function($data) {
                    $stringDot = ''; 
                    $url = Url::toRoute('post/update?id='.$data['PostID']);
                    if($data['PostType'] == 'Status') {
                            if(strlen($data['Description'])>25) {
                                $stringDot = '....';
                            }
                            return Html::a(substr($data['Description'],0,25).$stringDot,$url,['data-pjax'=>"0"]);
                    } else {
                        return Html::a($data['PostTitle'],$url,['data-pjax'=>"0"]);
                    }
                },
                'format'=>'raw',        
            ],
            [
                //'header' => 'Artist Name',
                'attribute' => 'ArtistName',
                'visible' => ((Yii::$app->user->identity->UserType == 1) ? '1' : '0'),
            ],
            [
                //'header' => 'Website',
                'attribute' => 'Website',
                'filter'=>Html::activeDropDownList($model, 'Website',array(""=>"All","1"=>"Like","2"=>"Comment","3"=>"Facebook","4"=>"Twitter","5"=>"Flag"),['class'=>'form-control','options' => [ "" => ['selected ' => true]]]),
            ],
            [
                //'header' => 'Date Liked',
                'attribute' => 'DateLiked',
                'filter' => false,
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>

</div>
</div>