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
class Like extends \yii\db\ActiveRecord
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
    
    public function checkImage() {
        $PostType = $_POST['Post']['PostType'];
        if($PostType == 2) {
            if(isset($_FILES['Post']['name']['PostImages']) && count($_FILES['Post']['name']['PostImages'])>0) {
                for($n=0;$n<count(count($_FILES['Post']['name']['PostImages']));$n++) {
                    $fileName = $_FILES['Post']['name']['PostImages'][$n];
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    if($extension != 'jpg' && $extension != 'png' && $extension != 'jpeg') {
                        return $this->addError('PostImages', 'Please upload valid image');
                    }
                }    
            } else {    
                return $this->addError('PostImages', 'Please upload image');
            }
        }    
    }
    
    public function checkVideo() {
        $PostType = $_POST['Post']['PostType'];
        if($PostType == 3) {
            if(isset($_FILES['Post']['name']['PostVideo']) && $_FILES['Post']['name']['PostVideo']!='') {
                $fileName = $_FILES['Post']['name']['PostVideo'];
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                if($extension != 'mp4') {
                    return $this->addError('PostVideo', 'Please upload valid video');
                }
            } else {    
                return $this->addError('PostVideo', 'Please upload video');
            }
        }
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
