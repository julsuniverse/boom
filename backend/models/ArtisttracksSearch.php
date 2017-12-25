<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Artisttracks;
use yii\data\SpDataProvider;

/**
 * PostSearch represents the model behind the search form about `backend\models\Post`.
 */
class ArtisttracksSearch extends Artisttracks
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ArtistID'], 'integer'],
            [['URI'], 'string'],
            [['Created'], 'safe'],
            [['PlaylistID', 'PlaylistName', 'TrackID', 'TrackTitle'], 'string', 'max' => 1000],
            [['Duration'], 'string', 'max' => 255],
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
        $artistData = Artist::getArtistData(Yii::$app->user->identity->UserID);
        if(isset($artistData[0]['ArtistID']) && $artistData[0]['ArtistID']!= '') 
        {
            $artistiddb = $artistData[0]['ArtistID'];
        }
        $arrParams['v_ArtistID'] = $artistiddb;
        $arrParams['v_TrackTitle'] = $this->TrackTitle;
        $arrParams['v_PlaylistName'] = $this->PlaylistName;
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Artist_Track_List',
			//'totalCount'=>5,
			 'params'=>$arrParams,
			 'sort'=> [
				'attributes'=>[
                                        'TrackTitle'=>[
						'asc'=>['TrackTitle' => SORT_ASC],
						'desc'=>['TrackTitle' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'TrackTitle'
					],
                                        'Duration'=>[
						'asc'=>['Duration' => SORT_ASC],
						'desc'=>['Duration' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Duration'
					],
                                        'PlaylistName'=>[
						'asc'=>['PlaylistName' => SORT_ASC],
						'desc'=>['PlaylistName' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'PlaylistName'
					],
                                        
				],
				'defaultOrder'=>[
					'PlaylistName' => SORT_DESC
				]
			 ],  
			'pagination' => [
				  'pageSize' => 10,
			 ],
			 
		]);

        return $spDataProvider;
    }
    public function getTrackListData($artistID,$trackTitle="",$playlistName="")
    {
        $sp     =   "CALL Admin_Artist_Track_List(".$artistID.",'".$trackTitle."','".$trackTitle."')";
        $connection = \yii::$app->db;
        $command    =   $connection->createCommand($sp);
        $exe        =   $command->queryAll();
        return $exe;
    }
    
}
