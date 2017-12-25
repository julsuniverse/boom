<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\User;
use yii\data\SpDataProvider;

/**
 * UserSearch represents the model behind the search form about `backend\models\User`.
 */
class UserSearch extends User
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserID', 'ArtistID', 'CreatedBy', 'UpdatedBy'], 'integer'],
            [['UserName', 'Email','Password', 'Status', 'LastLoginDateTime', 'UserType', 'ActivationCode', 'ActivatedOn', 'Created', 'Updated'], 'safe'],
        ];
    }

	public $Role;
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
        $arrParams['v_UserName'] = $this->UserName;
        $arrParams['v_Email'] = $this->Email;
        $arrParams['v_Status'] = $this->Status;
        $arrParams['v_Key'] = AESKEY;
		$arrParams['v_Role'] = $this->Role;
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Admin_GetList',
			'params'=>$arrParams,
			'sort'=> [
				'attributes'=>[
					'UserID'=>[
						'asc'=>['UserID' => SORT_ASC],
						'desc'=>['UserID' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'UserID'
					],
                                        'UserName'=>[
						'asc'=>['UserName' => SORT_ASC],
						'desc'=>['UserName' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'UserName'
					],
                                        'Email'=>[
						'asc'=>['Email' => SORT_ASC],
						'desc'=>['Email' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Email'
					],
                                        'Status'=>[
						'asc'=>['Status' => SORT_ASC],
						'desc'=>['Status' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Status'
					],
				],
				'defaultOrder'=>[
					'UserID' => SORT_DESC
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
