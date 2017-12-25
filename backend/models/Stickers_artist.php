<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "Stickers_artist".
 */
class Stickers_artist extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stickers_artist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ArtistID', 'StickerID'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ArtistID' => 'Artist ID',
            'StickerID' => 'Sticker ID',
        ];
    }
}