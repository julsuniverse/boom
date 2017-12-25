<?php

namespace backend\models;
use yii\helpers\ArrayHelper;

use Yii;

/**
 * This is the model class for table "postpages".
 */
class PostPages extends \yii\db\ActiveRecord
{

	public $PostTitle;
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'postpages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID', 'PostID', 'PageNumber', 'Type'], 'required'],
			[['Text', 'VideoUrl', 'ImageUrl', 'ImageWidth', 'ImageHeight', 'VideoThumbnailImage', 'VideoThumbnailImageWidth', 'VideoThumbnailImageHeight'], 'safe'],
			['PageNumber', 'number'],
			['PageNumber', 'unique', 'targetAttribute' => ['PageNumber', 'PostID'], 'message' => 'Page already exists']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
			'PostID' => 'Post Id',
			'PageNumber' => 'Page Number',
			'Type' => 'Type',
			'Text' => 'Text',
			'VideoUrl' => 'Video',
			'ImageUrl' => 'Image',
			'VideoThumbnailImage' => 'Thumb Image'
        ];
    }

}