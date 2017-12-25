<?php
namespace backend\models;


use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "sticker".
 *
 * @property integer $StickerID
 * @property string $Title
 * @property string $Description
 * @property string $PurchaseType
 * @property string $Cost
 * @property string $ProductSKU
 * @property integer $ArtistID
 * @property integer $IsDelete
 * @property string $StickerImage
 * @property integer $CreatedBy
 * @property integer $UpdatedBy
 * @property string $Created
 * @property string $Updated
 */

class Sticker extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sticker';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Title','Description','Status','Cost','StickerImage', 'Type'], 'required','on' => 'create','message' => 'Please enter {attribute}.'],
            [['Title','Description','Status','Cost'], 'required','on' => 'update','message' => 'Please enter {attribute}.'],
            [['assignedArtists'], 'required','message' => 'Please select artist.'],
            
            //[['Description'], 'checkImage'],
            [['Description'],'string', 'length' => [1, 255]],
            [['Cost'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],  
			[['Call_Video_Url'], 'string', 'max' => 500],	//--Daniele	
        ];
    }
    
    public function checkImage() {
        $id = '';
        if(isset($_GET['id']) && $_GET['id']!= '') {
            $id = $_GET['id'];
        }
        if(isset($_FILES['Sticker']['name']['StickerImage']) && $_FILES['Sticker']['name']['StickerImage']!='') {
            $fileName = $_FILES['Sticker']['name']['StickerImage'];
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if($extension != 'jpg' && $extension != 'png' && $extension != 'jpeg') {
                return $this->addError('StickerImage', 'Please upload valid profile image');
            }
        } else if($id == '') {    
            return $this->addError('StickerImage', 'Please upload sticker image');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'StickerID' => 'Sticker ID',
            'Title' => 'Title',
            'Description' => 'Description',
            'PurchaseType' => 'Purchase Type',
            'Cost' => 'Price',
            'IOSSKUID' => 'iOS SKUID',
            'AndroidSKUID' => 'Android SKUID',
            'ArtistID' => 'Artist Name',
            'IsDelete' => 'Is Delete',
            'StickerImage' => 'Sticker Image',
            'CreatedBy' => 'Created By',
            'UpdatedBy' => 'Updated By',
            'Created' => 'Created',
            'Updated' => 'Updated',
			'Call_Video_Url' => 'Call Video Url', //--Daniele
			'Type' => 'Type', //Daniele
        ];
    }
    
    public function getArtistList() {
        $artistData = Artist::find()->select('ArtistID, ArtistName')->all();
        $listData=ArrayHelper::map($artistData,'ArtistID','ArtistName');
        return $listData;
    }
    
    public function getAllArtistList() {
        $artistData = Artist::find()->select("*,AES_DECRYPT(Email,Password('".AESKEY."')) AS Email")->all();
        return $artistData;
    }
    
    public function getStatus() {
        return array("1"=>"Active","2"=>"Inactive");
    }
    
    public function getStatusValue($id) {
        $status = array("1"=>"Active","2"=>"Inactive",""=>"Not Set");
        return $status[$id];
    }
	
	public $assignedArtists;

    public function getassignedArtists($stickerID){
         $artistIDs = '';
		 $artistData = Stickers_artist::find()->select('ArtistID')->where(['StickerID' => $stickerID])->all();
		 foreach($artistData as $art){
			$artistIDs = $artistIDs.$art->ArtistID.',';
		 }
		 return $this->assignedArtists = substr($artistIDs, 0, strlen($artistIDs)-1);
     }

    public function setassignedArtists($artistList){
         $this->assignedArtists = $artistList;
     }
	
}
