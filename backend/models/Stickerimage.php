<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "stickerimage".
 *
 * @property integer $StickerImageID
 * @property integer $StickerID
 * @property string $Image
 * @property integer $IsDelete
 */
class Stickerimage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stickerimage';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['StickerID', 'IsDelete'], 'integer'],
            [['Image'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'StickerImageID' => 'Sticker Image ID',
            'StickerID' => 'Sticker ID',
            'Image' => 'Image',
            'IsDelete' => 'Is Delete',
        ];
    }
}
