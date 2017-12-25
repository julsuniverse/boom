<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\MemberSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="member-search">
    <div style="float: left;width:100%;">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'POST',
        ]); ?>
        <div class="col-xs-3">
                From Join Date : <?php echo DatePicker::widget([
                'name' => 'Member[FromJoinDate]', 
                'value' => $model->FromJoinDate,
                'options' => ['placeholder' => ''],
                'pluginOptions' => [
                        'format' => 'M-dd-yyyy',
                        'todayHighlight' => true,
                        'autoclose' => true,
                    ]
                ]);
            ?>
        </div>    
        <div class="col-xs-3">
            To Join Date : <?php echo DatePicker::widget([
            'name' => 'Member[ToJoinDate]', 
            'value' => $model->ToJoinDate,
            'options' => ['placeholder' => ''],
            'pluginOptions' => [
                    'format' => 'M-dd-yyyy',
                    'todayHighlight' => true,
                    'autoclose' => true,
                ]
            ]);
        ?>
        </div>
        <div class="form-group" style='padding-top:20px;'>
            <?php echo Html::submitButton('View', ['class' => 'btn btn-primary']) ?>
            <?php //echo Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div> 
    <div class="clearfix"></div>
</div>
