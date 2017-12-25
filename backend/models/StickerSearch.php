<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Sticker;
use yii\data\SpDataProvider;


/**
 * StickerSearch represents the model behind the search form about `backend\models\Sticker`.
 */
class StickerSearch extends Sticker
{
    /**
     * @inheritdoc
     */
    public $ArtistName;
    public $Title;
    public $Description;
    public $ArtistID;
	public $CompanyID;

    public function rules()
    {
        return [
            [['StickerID', 'ArtistID', 'IsDelete', 'CreatedBy', 'UpdatedBy'], 'integer'],
            [['Title', 'Description', 'PurchaseType', 'ProductSKU', 'StickerImage', 'Created', 'Updated','ArtistName'], 'safe'],
            [['Cost'], 'number'],
            [['Cost'], 'number', 'numberPattern' => '/^\s*[-+]?[0-9]*[.,]?[0-9]+([eE][-+]?[0-9]+)?\s*$/'],        
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
		$arrParams['v_UserRole'] = Yii::$app->user->identity->UserType;//Daniele
        $arrParams['v_Title'] = $this->Title;
        $arrParams['v_Description'] = $this->Description;
        $arrParams['v_Cost']        = $this->Cost;
        if(Yii::$app->user->identity->UserType == 2) {
            $userData   =   User::findAll(array("UserID"=>Yii::$app->user->identity->UserID));
            $arrParams['v_ArtistID']        = $userData[0]['ArtistID'];
        } else {
            $arrParams['v_ArtistID'] = "";
        }    
		$arrParams['v_ArtistName'] = $this->ArtistName;//Daniele	
		$arrParams['v_CompanyID'] = $this->CompanyID;

        //$arrParams['v_Artist'] = $this->ArtistName;
        
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Stickers_GetList',
			//'totalCount'=>5,
			 'params'=>$arrParams,
			 'sort'=> [
				'attributes'=>[
					'StickerID'=>[
						'asc'=>['StickerID' => SORT_ASC],
						'desc'=>['StickerID' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'StickerID'
					],
                                        'Title'=>[
						'asc'=>['Title' => SORT_ASC],
						'desc'=>['Title' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Title'
					],
                                        'Description'=>[
						'asc'=>['Description' => SORT_ASC],
						'desc'=>['Description' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Description'
					],
                                        'Cost'=>[
						'asc'=>['Cost' => SORT_ASC],
						'desc'=>['Cost' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Cost'
					],
					                    'ArtistName'=>[
						'asc'=>['ArtistName' => SORT_ASC],
						'desc'=>['ArtistName' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Artist'
					],
				],
				'defaultOrder'=>[
					'StickerID' => SORT_DESC
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
