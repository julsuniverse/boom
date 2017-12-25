<?php

namespace backend\models;
use yii\helpers\ArrayHelper;

use Yii;

/**
 */
class Feedarticles extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'feedarticles';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID', 'ArtistID', 'FeedUrl'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'ArtistID' => 'Artist ID',
			'FeedUrl' => 'Feed Article Url',
        ];
    }

}