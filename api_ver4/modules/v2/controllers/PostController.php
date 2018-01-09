<?php

namespace api\modules\v2\controllers;

use yii\rest\ActiveController;

/**
* Post controller API
*
* @auther Jalvin Vohra
**/

class PostController extends ActiveController {
	public $modelClass = 'api\modules\v2\models\Posts';
	
	/**
	 * @inheritdoc
	 **/
	public function beforeAction($action) {
		
		if(!parent::beforeAction($action)) {
			return false;
		}
		
		switch($action->id) {
			case 'create':
				print('<pre>');
				print_r($action->behaviors);
				print('</pre>');
				break;
			
		}
		return true;
	}
}