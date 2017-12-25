<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "post".
 *
 * @property integer $PostID
 * @property integer $ArtistID
 * @property string $PostTitle
 * @property string $Description
 * @property string $ThumbImage
 * @property string $URL
 * @property integer $TotalComments
 * @property integer $TotalUpVote
 * @property integer $TotalShare
 * @property integer $TotalFlag
 * @property string $IsExclusive
 * @property integer $IsShared
 * @property string $Price
 * @property integer $IsDelete
 * @property string $PostType
 * @property integer $CreatedBy
 * @property integer $UpdatedBy
 * @property string $Created
 * @property string $Updated
 */
class Exclusivemember extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $PostImages;
    public $PostVideo;
    public static function tableName()
    {
        return 'post';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ArtistID','IsExclusive','IsShared','PostType','Description'], 'required','on'=>'create'],
            ['PostImages','checkImage','skipOnEmpty'=> false,'on'=>'create'],
            ['PostVideo','checkVideo','skipOnEmpty'=> false,'on'=>'create'],
            [['PostTitle'], 'required', 'when' => function ($model) {
                return $model->PostType != '1';
            }, 'whenClient' => "function (attribute, value) {
                return $('#post-posttype').val() != '1';
            }"],        
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'PostID' => 'Post ID',
            'ArtistID' => 'Artist ID',
            'PostTitle' => 'Post Title',
            'Description' => 'Description',
            'ThumbImage' => 'Thumb Image',
            'URL' => 'Url',
            'TotalComments' => 'Total Comments',
            'TotalUpVote' => 'Total Up Vote',
            'TotalShare' => 'Total Share',
            'TotalFlag' => 'Total Flag',
            'IsExclusive' => 'Is Exclusive',
            'IsShared' => 'Allow Sharing',
            'Price' => 'Price',
            'IsDelete' => 'Is Delete',
            'PostType' => 'Post Type',
            'CreatedBy' => 'Created By',
            'UpdatedBy' => 'Updated By',
            'Created' => 'Created',
            'Updated' => 'Updated',
        ];
    }
}
