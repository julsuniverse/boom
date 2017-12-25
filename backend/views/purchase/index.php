<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;
use backend\models\Memberpost;
use backend\models\Post;
use backend\models\Member;
use backend\models\Nativeproducts;
use backend\models\Artist;
use yii\widgets\ActiveForm;


$this->title = 'Purchase';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="artist-index">

	<div style="width:20%; float:right">
	<?php $form = ActiveForm::begin([
				'action' =>['exportcsv'],
                'options' => []
            ]); ?>
		<span style='color:white'>
			Select Month:
			<select name="month" style='color:black'>
				<option value="1">Jan</option>
				<option value="2">Feb</option>
				<option value="3">March</option>
				<option value="4">April</option>
				<option value="5">May</option>
				<option value="6">June</option>
				<option value="7">Jul</option>
				<option value="8">Aug</option>
				<option value="9">Sep</option>
				<option value="10">Oct</option>
				<option value="11">Nov</option>
				<option value="12">Dec</option>
			</select>
		</span>
		<span style='color:white'>
			Select Year:
			<select name="year" style='color:black'>
				<option value="2016">2016</option>
				<option value="2017">2017</option>
				<option value="2018">2018</option>
				<option value="2019">2019</option>
				<option value="2020">2020</option>
				<option value="2021">2021</option>
			</select>
		</span>

		<?= Html::submitButton('Export csv', ['class' => 'btn btn-success']) ?>

		<?php ActiveForm::end(); ?>
    </div>


    <h1><?= Html::encode($this->title) ?></h1>
    <?php \yii\widgets\Pjax::begin(
            ['id' => 'Purchase', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
		
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
		'filterModel' => $model,		
        'columns' => [
            [
                'attribute'=>'ArtistID',
				'label' => 'Artist',
				'filter' => false,
				'value' => function($data) {
					$artist = Artist::findOne($data['ArtistID']);
					if($artist != null){
						return $artist->ArtistName;
					}
                },
            ],
			[
                'attribute'=>'memberName',
				'filter' => false,
				'value' => function($data) {
					$post = Post::findOne($data['PostID']);
					if($post != null && $post->MemberID != null && $post->MemberID != "" && $post->MemberID != "0"){
						$member = Member::findOne($post->MemberID);
						return $member->MemberName;
					}
                },
            ],
			[
                'attribute'=>'Cost',
				'filter' => false,
				'value' => function($data) {
					if($data['ProductID'] == null){
						$sku = null;
						$spli = split('"SKU":', $data['TransactionData']);
						if(count($spli) > 1){
							$sku = $spli[1];
							$sku = split('"', $sku)[1];
						}
						$prod = Nativeproducts::find()->where('ProductSKUIOS="'.$sku.'"')->one();
						if($prod != null){ return $prod->Price; }
					}
					return $data['Cost'];
                },
            ],
			[
                'attribute'=>'SKUID',
				'filter' => false,
				'value' => function($data) {
					if($data['ProductID'] != null){ return $data['ProductID'];}
					$spli = split('"SKU":', $data['TransactionData']);
                    if(count($spli) > 1){
						$sku = $spli[1];
						$sku = split('"', $sku)[1];
						return $sku;
					}else{return null;}
                },
            ],
			/*[
                'attribute'=>'transactionID',
				'value' => function($data) {
					$spli = split('"TRANSACTIONID":', $data['TransactionData']);
                    if(count($spli) > 1){
						$transId = $spli[1];
						$transId = split('"', $transId)[1];
						return $transId;
					}else{return null;}
                },
            ],*/
			[
                'attribute'=>'Created',
				'filter' => false,
            ],
			[
                'attribute'=>'isNative',
				'value' => function($data) {
					if($data['isNative'] == 1){ return "true";}else{return "false";}
				},
				'filter'=>Html::ActiveDropDownList($model, 'isNative',array(""=>"All","1"=>"true","0"=>"false"),['class'=>'form-control']),
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>
</div>

<script>

hideEmpty();

function hideEmpty(){
	var table = document.getElementsByClassName("table table-striped table-bordered")[0];
	for(var i=1; i<table.rows.length; i++){
		var row = table.rows[i];
		var sku = row.cells[3].innerHTML;
		if(sku == '<span class="not-set">(not set)</span>'){
			table.deleteRow(i);
		}
	}
}

</script>
