<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Pushqueue;
use yii\data\SpDataProvider;
/**
 * PushhistorystatsSearch represents the model behind the search form about `backend\models\Pushhistorystats`.
 */
class PushqueueSearch extends Pushqueue
{
    /**
     * @inheritdoc
     */
    public $Name;
    public $Email;
    public $AgeGroup;
    public $Datejoined;
    public $DeviceType;
    public function rules()
    {
        return [
            [['BatchID', 'MemberID'], 'integer'],
            [['DeviceType', 'Status'], 'string'],
            [['Name','Email','AgeGroup','DeviceType','Created', 'Delivered'], 'safe'],
            [['DeviceToken'], 'string', 'max' => 1024],
            [['Message'], 'string', 'max' => 500]
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
    public function search($batchid)
    {
        $arrParams = array();
        $arrParams['v_BatchID']     =   $batchid;
        $arrParams['v_Name']        =   $this->Name;
        $arrParams['v_Email']       =   $this->Email;
        $arrParams['v_Agegroup']    =   $this->AgeGroup;
        $arrParams['v_DeviceType']  =   $this->DeviceType;
        $arrParams['v_Key']         =   AESKEY;
      
        
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_SentPush_GetList',
			//'totalCount'=>5,
			 'params'=>$arrParams,
			 'sort'=> [
				'attributes'=>[
					'Name'=>[
						'asc'=>['Name' => SORT_ASC],
						'desc'=>['Name' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Name'
					],
                                        'Email'=>[
						'asc'=>['Email' => SORT_ASC],
						'desc'=>['Email' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Email'
					],
                                        'AgeGroup'=>[
						'asc'=>['AgeGroup' => SORT_ASC],
						'desc'=>['AgeGroup' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'AgeGroup'
					],
                                        'DateJoined'=>[
						'asc'=>['DateJoined' => SORT_ASC],
						'desc'=>['DateJoined' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'DateJoined'
					],
				],
				
			 ],
			 //'sort' => $sort,
			'pagination' => [
				  'pageSize' => 10,
			 ],
			 
		]);

        return $spDataProvider;
    }
}
