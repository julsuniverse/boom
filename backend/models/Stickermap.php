<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "stickermap".
 *
 * @property integer $StickerMapID
 * @property integer $StickerID
 * @property integer $ArtistID
 * @property string $IOSSKUID
 * @property string $AndroidSKUID
 * @property string $Created
 * @property integer $CreatedBy
 * @property string $Updated
 * @property integer $UpdatedBy
 * @property integer $IsDelete
 */
class Stickermap extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stickermap';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['StickerID', 'ArtistID', 'CreatedBy', 'UpdatedBy', 'IsDelete'], 'integer'],
            [['Created', 'Updated'], 'safe'],
            [['IOSSKUID', 'AndroidSKUID'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'StickerMapID' => 'Sticker Map ID',
            'StickerID' => 'Sticker ID',
            'ArtistID' => 'Artist ID',
            'IOSSKUID' => 'Iosskuid',
            'AndroidSKUID' => 'Android Skuid',
            'Created' => 'Created',
            'CreatedBy' => 'Created By',
            'Updated' => 'Updated',
            'UpdatedBy' => 'Updated By',
            'IsDelete' => 'Is Delete',
        ];
    }
}
