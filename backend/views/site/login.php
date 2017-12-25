<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="login-wapper">
  <div class="site-login"> 
    <!--<h1><?= Html::encode($this->title) ?></h1>-->
    
<!--    <p class="pleasefill">Please fill out the following fields to login:</p>-->
    <div class="row">
      <div class="col-lg-12">
        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
        <?= $form->field($model, 'UserName') ?>
        <?= $form->field($model, 'Password')->passwordInput() ?>
        <?= $form->field($model, 'rememberMe')->checkbox() ?>
        <div class="form-group">
          <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>
        <p><a  href="<?php echo FORGOTPASSWORDPAGEBACKEND; ?>" target="_blank">Forgot password?</a></p>
        <?php ActiveForm::end(); ?>
      </div>
    </div>
  </div>
</div>
<style>
<?php
$backgrund = '';
if($_SERVER['SERVER_NAME'] == 'test.boomcollective.net'){
	$backgrund = 'background-color: black;';
}else if($_SERVER['SERVER_NAME'] == 'partners.appbeat.us'){
	$backgrund = 'background: url("../../image/appbeat/appbeat-bkg.png");';
}else{
	$backgrund = 'background: url("../../image/login-bg.jpg");';
}
?>
.wrap {<?php echo $backgrund; ?>background-size: cover;background-repeat: no-repeat;font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen, Ubuntu, Cantarell, Fira Sans, Droid Sans, Helvetica Neue, sans-serif;}
.login-wapper {    float: right;    background-color: rgba(0, 0, 0, 0.24);    padding: 35px;    color: #fff;}
.login-wapper p a {    color: #fff;}
.pleasefill {    background-color: #337AB7;    padding: 13px;}

</style>
