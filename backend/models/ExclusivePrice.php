<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "ExclusivePrice".
 *
 * @property integer $ExclusivePriceID
 * @property string $StatusPrice
 * @property string $ImagePrice
 * @property string $VideoPrice
 */
class ExclusivePrice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'exclusiveprice';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['StatusPrice','ImagePrice','VideoPrice','StatusSKUID','ImageSKUID','VideoSKUID'], 'required','on' => 'update'],
            [['StatusPrice','ImagePrice','VideoPrice','StatusSKUID','ImageSKUID','VideoSKUID'], 'required','on' => 'create'],
            [['StatusPrice'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],        
            [['ImagePrice'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],        
            [['VideoPrice'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],        
            [['StatusPrice','ImagePrice','VideoPrice'],'number', 'min'=>0.01],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ExclusivePriceID' => 'Exclusive Price ID',
            'StatusPrice' => 'Status Price',
            'StatusSKUID' => 'Status Product ID',
            'ImagePrice' => 'Image Price',
            'ImageSKUID' => 'Image Product ID',
            'VideoPrice' => 'Video Price',
            'VideoSKUID' => 'Video Product ID',
        ];
    }
}
