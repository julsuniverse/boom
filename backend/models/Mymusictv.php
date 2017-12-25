<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "mymsuictv".
 *
 * @property integer $ID
 * @property string $ArtistID
 * @property string $AlbumTitle
 * @property string $AlbumLink
 * @property string $AlbumImage
 * @property string $Status
 * @property string $Created
 */
class Mymusictv extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mymusictv';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AlbumTitle','AlbumLink'],'required','on'=>'create','message' => 'Please enter {attribute}.'],
            [['AlbumTitle','AlbumLink'],'required','on'=>'update','message' => 'Please enter {attribute}.'],
            [['ArtistID'], 'required','message' => 'Please select artist.'],
            [['AlbumLink'],'url', 'defaultScheme' => 'http'],
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
            'AlbumTitle' => 'Album Title',
            'AlbumLink' => 'Album Link',
            'AlbumImage' => 'Album Image',
            'Status' => 'Status',
            'Created' => 'Created',
            'Updated'=>'Updated',
        ];
    }
    public function getStatus() 
    {
        return array("1" => "Active", "2" => "Inactive");
    }
}
