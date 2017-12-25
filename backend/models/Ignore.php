<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "ignore".
 *
 * @property integer $IgnoreID
 * @property integer $PostID
 * @property integer $ActivityID
 * @property string $Created
 * @property integer $CreatedBy
 */
class Ignore extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ignore';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['PostID', 'ActivityID', 'CreatedBy'], 'integer'],
            [['Created'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'IgnoreID' => 'Ignore ID',
            'PostID' => 'Post ID',
            'ActivityID' => 'Activity ID',
            'Created' => 'Created',
            'CreatedBy' => 'Created By',
        ];
    }
}
