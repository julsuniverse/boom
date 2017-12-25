<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
?>
<div class="site-error">

    <!--    <h1><?php //echo Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?php //echo nl2br(Html::encode($message)) ?>
    </div>-->

    <h1>The above error occurred while the Web server was processing your request.</h1>
    
    <h1>Please contact us if you think this is a server error. Thank you.</h1>

</div>
