<?php

namespace backend\models;

use Yii;
use yii\web\UploadedFile;

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
class Qapost extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $PostImages;
    public $ThumbImage;
    public $PostVideo;
    public $PostThumbImageVideo;
    public $PostVideoReply;
    public $BlockUser;
    //public $ProductSKUID;
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
            [['ArtistID','PostType','Status'], 'required','on'=>'qacreate'],
            [['ArtistID','PostType','Status'], 'required','on'=>'qaupdate'],
            [['IsExclusive','IsShared','Status'], 'required','on'=>'create'],
            [['IsExclusive','IsShared','Status'], 'required','on'=>'update'],
            [['ArtistID','PostType'], 'required','message' => 'Please select {attribute}.'],
        ];
    }
    
    public function checkThumbImage() {
        $id = '';
        if(isset($_GET['id']) && $_GET['id']!= '') {
            $id = $_GET['id'];
        }
        if(isset($_FILES['Post']['name']['ThumbImage']) && $_FILES['Post']['name']['ThumbImage']!='') {
            $fileName = $_FILES['Post']['name']['ThumbImage'];
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            if($extension != 'jpg' && $extension != 'png' && $extension != 'jpeg') {
                return $this->addError('ThumbImage', 'Please upload valid thumb image');
            }
        } else if($id == '') {    
            return $this->addError('ThumbImage', 'Please choose thumb image');
        }
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
            'PostTitle' => 'Title',
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
            'ProductSKUID'=>'Product SKUID',
            'IsDelete' => 'Is Delete',
            'PostType' => 'Type',
            'CreatedBy' => 'Created By',
            'UpdatedBy' => 'Updated By',
            'Created' => 'Created',
            'Updated' => 'Updated',
            'QAIgnore' => 'Ignore Question',
            'IsBlocked' => 'Block Question',
            'BlockUser' => 'Block User',
			'RequestedPrivacy' => 'Not Requested Privacy',
        ];
    }
    public function getStatus() {
        return array("1"=>"Active","2"=>"Inactive","3"=>"Pending");
    }
    
    public function getStatusValue($id) {
        $status = array("1"=>"Active","2"=>"Inactive","3"=>"Pending",""=>"Not Set");
        return $status[$id];
    }
    
}
