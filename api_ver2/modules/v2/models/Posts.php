<?php
namespace api\modules\v2\models;
use \yii\db\ActiveRecord;
/**
 * Posts Model
 *
 * @auther Jalvin Vohra
 */
class Posts extends ActiveRecord 
{
	/**
	 * @inheritdoc
	 **/
	public static function tableName() 
	{
		return 'posts';
	}
	
	/**
	 * @inheritdoc
	 **/
	public static function primaryKey() 
	{
		return ['id'];
	}
	
	/**
	 * Define rules for validation
	 **/
	public function rules() 
	{
		return [
			[['title', 'data'],'required'],
			[['id'], 'integer'],
			[['create_time', 'update_time'],'safe'],
		];
	}
}