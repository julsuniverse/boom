<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Applist;
use yii\data\SpDataProvider;

/**
 * SimilarappSearch represents the model behind the search form about `backend\models\Similarapp`.
 */
class ApplistSearch extends Applist
{
    /**
     * @inheritdoc
     */
    public $ListTitle;
    public $ArtistName;
    public $Status;
	public $CompanyID;
    public function rules()
    {
        return [
            [['AppID', 'ArtistID'], 'integer'],
            [['Name', 'Url', 'AppIcon', 'Type','ArtistName','ListTitle','Status'], 'safe'],
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
        /*$query = Similarapp::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'AppID' => $this->AppID,
            'ArtistID' => $this->ArtistID,
        ]);

        $query->andFilterWhere(['like', 'Name', $this->Name])
            ->andFilterWhere(['like', 'Url', $this->Url])
            ->andFilterWhere(['like', 'AppIcon', $this->AppIcon])
            ->andFilterWhere(['like', 'Type', $this->Type]);

        return $dataProvider;*/
        
        $arrParams = array();
        $arrParams['v_ListTitle'] = $this->ListTitle;
        $arrParams['v_ArtistName'] = '';
        $arrParams['v_Status'] = $this->Status;
		$arrParams['v_CompanyID'] = $this->CompanyID;
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_SimilarApps_GetList',
			//'totalCount'=>5,
			 'params'=>$arrParams,
			 'sort'=> [
				'attributes'=>[
					'ListID'=>[
						'asc'=>['ListID' => SORT_ASC],
						'desc'=>['ListID' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'ListID'
					],
                                        'ListTitle'=>[
						'asc'=>['ListTitle' => SORT_ASC],
						'desc'=>['ListTitle' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'ListTitle'
					],
                                        'DateAdded'=>[
						'asc'=>['DateAdded' => SORT_ASC],
						'desc'=>['DateAdded' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'DateAdded'
					],
                                        'Status'=>[
						'asc'=>['Status' => SORT_ASC],
						'desc'=>['Status' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Status'
					],
				],
				'defaultOrder'=>[
					'ListID' => SORT_DESC
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
