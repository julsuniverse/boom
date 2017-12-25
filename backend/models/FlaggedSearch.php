<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Flagged;
use yii\data\SpDataProvider;
/**
 * PostSearch represents the model behind the search form about `backend\models\Post`.
 */
class FlaggedSearch extends Flagged
{
    /**
     * @inheritdoc
     */
    public $ArtistName;
    public $Status;
    public $ArtistID;
	public $CompanyID;
    public function rules()
    {
        return [
            [['PostID', 'ArtistID', 'TotalComments', 'TotalUpVote', 'TotalShare', 'TotalFlag', 'IsShared', 'IsDelete', 'CreatedBy', 'UpdatedBy'], 'integer'],
            [['PostTitle', 'Description', 'ThumbImage', 'URL', 'IsExclusive', 'PostType', 'Created', 'Updated','ArtistName','Status'], 'safe'],
            [['Price'], 'number'],
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
        $arrParams['v_PostType'] = $this->PostType;
        $arrParams['v_Title'] = $this->PostTitle;
        $arrParams['v_ArtistName'] = $this->ArtistName;
		$arrParams['v_CompanyID'] = $this->CompanyID;
        if(Yii::$app->user->identity->UserType == 2) {
            $session = Yii::$app->session;
            $arrParams['v_ArtistID'] = $session->get('ArtistID');
        } else {
            $arrParams['v_ArtistID'] = '';
        }
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Flag_GetList',
			'params'=>$arrParams,
			'sort'=> [
				'attributes'=>[
					'PostID'=>[
						'asc'=>['PostID' => SORT_ASC],
						'desc'=>['PostID' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'PostID'
					],
                                        'PostType'=>[
						'asc'=>['PostType' => SORT_ASC],
						'desc'=>['PostType' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'PostType'
					],
                                        'FlaggedBy'=>[
						'asc'=>['FlaggedBy' => SORT_ASC],
						'desc'=>['FlaggedBy' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'FlaggedBy'
					],
                                        'ArtistName'=>[
						'asc'=>['ArtistName' => SORT_ASC],
						'desc'=>['ArtistName' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'ArtistName'
					],
                                        'PostTitle'=>[
						'asc'=>['PostTitle' => SORT_ASC],
						'desc'=>['PostTitle' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'PostTitle'
					],
                                        'DateFlagged'=>[
						'asc'=>['DateFlagged' => SORT_ASC],
						'desc'=>['DateFlagged' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'DateFlagged'
					],
				],
				'defaultOrder'=>[
					'PostID' => SORT_DESC
				]
			 ],  
			'pagination' => [
				  'pageSize' => 10,
			 ],
		]);
        return $spDataProvider;
    }
}
