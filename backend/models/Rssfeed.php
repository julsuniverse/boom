<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "rssfeed".
 *
 * @property integer $feed_id
 * @property string $title
 * @property string $description
 * @property string $media_title
 * @property string $media_thumbnail
 * @property string $media_content
 * @property integer $is_delete
 * @property integer $status
 */
class Rssfeed extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rssfeed';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description', 'media_content'], 'string'],
            [['is_delete', 'status'], 'integer'],
            [['title', 'media_title', 'media_thumbnail'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'feed_id' => 'Feed ID',
            'title' => 'Title',
            'description' => 'Description',
            'media_title' => 'Media Title',
            'media_thumbnail' => 'Media Thumbnail',
            'media_content' => 'Media Content',
            'is_delete' => 'Is Delete',
            'status' => 'Status',
        ];
    }
}
