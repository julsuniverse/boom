<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "similarapp".
 *
 * @property integer $AppID
 * @property integer $ArtistID
 * @property string $Name
 * @property string $Url
 * @property string $AppIcon
 * @property string $Type
 */
class Applist extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $AppLogo;
    public static function tableName()
    {
        return 'applist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ArtistID','ListTitle','Status'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AppID' => 'App ID',
            'ArtistID' => 'Artist',
            'ListTitle' => 'List Title',
        ];
    }
    
    public function getStatus() {
        return array("1"=>"Active","2"=>"Inactive");
    }
    
    public function getStatusValue($id) {
        $status = array("1"=>"Active","2"=>"Inactive");
        return $status[$id];
    }
}
