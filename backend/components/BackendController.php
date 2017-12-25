<?php    
    namespace backend\components;
    use Yii;

    class BackendController extends \yii\web\Controller
    {
        public function init(){
            parent::init();
            if (Yii::$app->user->isGuest) {
                \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
            }
        }
    }
    
?>    
