<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "similarapp".
 *
 * @property integer $AppID
 * @property string $Title
 * @property string $AndoidUrl
 * @property string $IphoneUrl
 * @property string $AppIcon
 */
class Similarapp extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'similarapp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Title', 'AndroidUrl', 'IphoneUrl', 'AppIcon'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AppID' => 'App ID',
            'Title' => 'Title',
            'AndoidUrl' => 'Andoid Url',
            'IphoneUrl' => 'Iphone Url',
            'AppIcon' => 'App Icon',
        ];
    }
}
