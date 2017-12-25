<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "gallery".
 *
 * @property integer $ID
 * @property string $Title
 * @property integer $ArtistID
 * @property integer $RefTableID
 * @property string $RefTableType
 * @property string $Type
 * @property string $Image
 * @property string $ClickURL
 * @property string $FullURL
 * @property integer $CreatedBy
 * @property integer $UpdatedBy
 * @property string $Created
 * @property string $Updated
 */
class Gallery extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'gallery';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ArtistID', 'RefTableID', 'CreatedBy', 'UpdatedBy'], 'integer'],
            [['RefTableType', 'Type'], 'string'],
            [['Created', 'Updated'], 'safe'],
            [['Title', 'Image', 'ClickURL', 'FullURL'], 'string', 'max' => 500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'Title' => 'Title',
            'ArtistID' => 'Artist ID',
            'RefTableID' => 'Ref Table ID',
            'RefTableType' => 'Ref Table Type',
            'Type' => 'Type',
            'Image' => 'Image',
            'ClickURL' => 'Click Url',
            'FullURL' => 'Full Url',
            'CreatedBy' => 'Created By',
            'UpdatedBy' => 'Updated By',
            'Created' => 'Created',
            'Updated' => 'Updated',
        ];
    }
}
