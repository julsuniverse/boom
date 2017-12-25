<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Artist;
use yii\data\SpDataProvider;

/**
 * ArtistSearch represents the model behind the search form about `backend\models\Artist`.
 */
class ArtistSearch extends Artist
{
    /**
     * @inheritdoc
     */
    public $Status;
    public function rules()
    {
        return [
            [['ArtistID', 'UserID', 'CreatedBy', 'UpdatedBy'], 'integer'],
            [['ArtistName', 'ProfileThumb', 'Email', 'DOB', 'Nationality', 'Address', 'Website', 'YoutubeChannelName', 'YearsActive', 'Genre', 'AboutMe', 'YouTubePageURL', 'LinkedInPageURL', 'TwitterPageURL', 'FacebookPageURL', 'DeviceType', 'DeviceToken', 'Created', 'Updated','Status','CompanyID'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search()
    {   
        $arrParams = array();
        $arrParams['v_ArtistID'] = $this->ArtistID;
        $arrParams['v_ArtistName'] = $this->ArtistName;
        $arrParams['v_Email'] = $this->Email;
        $arrParams['v_Nationality'] = $this->Nationality;
        $arrParams['v_Status'] = $this->Status;
        $arrParams['v_Key'] = AESKEY;
		$arrParams['v_CompanyID'] = $this->CompanyID;
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Artist_GetList',
			//'totalCount'=>5,
			 'params'=>$arrParams,
			 'sort'=> [
				'attributes'=>[
					'ArtistID'=>[
						'asc'=>['ArtistID' => SORT_ASC],
						'desc'=>['ArtistID' =>SORT_DESC],
						'default'=>SORT_ASC,
					],
                                        'ArtistName'=>[
						'asc'=>['ArtistName' => SORT_ASC],
						'desc'=>['ArtistName' =>SORT_DESC],
						'default'=>SORT_ASC,
					],
                                        'Email'=>[
						'asc'=>['Email' => SORT_ASC],
						'desc'=>['Email' =>SORT_DESC],
						'default'=>SORT_ASC,
					],
                                        'Nationality'=>[
						'asc'=>['Nationality' => SORT_ASC],
						'desc'=>['Nationality' =>SORT_DESC],
						'default'=>SORT_ASC,
					],
                                        'JoinedDate'=>[
						'asc'=>['JoinedDate' => SORT_ASC],
						'desc'=>['JoinedDate' =>SORT_DESC],
						'default'=>SORT_ASC,
					],
                                        'TotalRegisteredUsers'=>[
						'asc'=>['TotalRegisteredUsers' => SORT_ASC],
						'desc'=>['TotalRegisteredUsers' =>SORT_DESC],
						'default'=>SORT_ASC,
					],
                                        'Status'=>[
						'asc'=>['Status' => SORT_ASC],
						'desc'=>['Status' =>SORT_DESC],
						'default'=>SORT_ASC,
					],
				],
				'defaultOrder'=>[
					'ArtistID' => SORT_DESC
				]
			 ],  
			 //'sort' => $sort,
			'pagination' => [
				  'pageSize' => 10,
			 ],	 
		]);
        return $spDataProvider;
    }
    
    
}
