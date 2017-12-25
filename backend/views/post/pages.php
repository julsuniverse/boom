<div class="post-update">
<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use backend\models\PostPages;


$this->title = 'Pages';
$this->params['breadcrumbs'][] = $this->title;
$id = $_GET['id'];
if(isset($id) && $id!='') {
    include 'tabs.php';
}
?>
<div class="artist-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Add Page', ['createpage?id='.$id], ['class' => 'btn btn-success']) ?>
    </p>
    <?php \yii\widgets\Pjax::begin(
            ['id' => 'PageList', 'timeout' => false, 'clientOptions' => ['method' => 'GET']]
        ); ?>
		
    <?= GridView::widget([
        'dataProvider' => $dataProvider,        
        'columns' => [
            [
                'attribute'=>'PageNumber',
				'value' => function($data) {
                    $url = Url::toRoute('post/editpage?id='.$data['PostID'].'&pageID='.$data['ID']);
                    return Html::a($data['PageNumber'],$url,['data-pjax'=>"0"]);
                },
                'format'=>'raw',
            ],
			[
				'attribute'=>'Text',
				'value'=>function($data){
					if(strlen($data->Text) > 100){
						return substr($data['Text'],0,100).'...';
					}
					return $data['Text'];
				}
			],
			[
				'attribute'=>'Type',
				'value'=>function($data){
					$res = "";
					switch($data['Type']){
						case 1: $res = "Text"; break;
						case 2: $res = "Image"; break;
						case 3: $res = "Video"; break;
					}
					return $res;
				}
			],
			[
                'class' => 'yii\grid\ActionColumn',
                'template'=>'{delete}',
                'buttons' => [
	                'delete' => function ($url,$model) {
                            $url = Url::toRoute('post/deletepage?id='.$model['PostID'].'&pageID='.$model['ID']);
	                    return Html::a('<span onclick="return confirmDelete();" class="glyphicon glyphicon-trash"></span>',$url,['data-pjax'=>"0"]);
	                },
	        ],
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>
</div>
<script>
/********** Delete confirmation ****************/    
function confirmDelete() {
    if(confirm("Are you sure want to delete this page?")) {
        return true;
    } else {
        return false;
    }
}    
</script>   