<?php

namespace backend\models;
use yii\helpers\ArrayHelper;

use Yii;

/**
 * This is the model class for table "artist_company".
 */
class Artist_company extends \yii\db\ActiveRecord
{

	public $assignedArtists;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'artist_company';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Id', 'Name'], 'required'],
			[['Website','Error_Msg_Subscribe', 'Profile_Image'],'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'Company ID',
            'Name' => 'Company Name',
        ];
    }
	
	//List of all Artist_Company
	public function getArtistCompanyList(){
		$artistData = Artist_company::find()->all();
		$listData=ArrayHelper::map($artistData,'Id','Name');
        return $listData;
	}
	
	//List of Artists with belonging to a company
	public function getCompanyArtistList($companyID){
		$artistData = Artist::find()->where('CompanyID='.$companyID)->all();
		$listData=ArrayHelper::map($artistData,'ArtistID','ArtistName');
        return $listData;
	}
	
	//List of Artists with no-company assigned
	public function getArtistList($companyID){
		$artistData = Artist::find()->where(['OR', 'CompanyID IS NULL', 'CompanyID='.$companyID])->all();
		$listData=ArrayHelper::map($artistData,'ArtistID','ArtistName');
        return $listData;
	}
	
	//List of all the Artists belonging to a company
	public function getassignedArtists($companyID){
        $artistIDs = '';
		$artistData = Artist::find()->select('ArtistID')->where(['CompanyID' => $companyID])->all();
		foreach($artistData as $art){
			$artistIDs = $artistIDs.$art->ArtistID.',';
		}
		return $this->assignedArtists = substr($artistIDs, 0, strlen($artistIDs)-1);
    }
	
	
	public function setassignedArtists($artistList){
         $this->assignedArtists = $artistList;
    }
	
	//function for checking if an artist belongs to a group
	public static function isInGroup($artistID){
		$model = Artist::findOne($artistID);
		if($model == null || $model->CompanyID == null){ return false;}else{return true;}
	}
}