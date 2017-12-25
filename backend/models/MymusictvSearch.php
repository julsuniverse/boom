<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Mymusictv;
use yii\data\SpDataProvider;

/**
 * ArtistSearch represents the model behind the search form about `backend\models\Artist`.
 */
class MymusictvSearch extends Mymusictv
{
    /**
     * @inheritdoc
     */
    public $ArtistName;
    public function rules()
    {
        return [
            [['ID','ArtistID'], 'integer'],
            [['AlbumTitle','AlbumLink', 'AlbumImage','Status','Created','Updated','ArtistName'], 'safe'],
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
        if(Yii::$app->user->identity->UserType == 1)
        {
            $arrParams['v_ArtistID']    =   '';
            $arrParams['v_ArtistName']    =   $this->ArtistName;
        }
        else
        {
            $arrParams['v_ArtistID'] =  Yii::$app->user->identity->ArtistID;
            $arrParams['v_ArtistName']    =   '';
            //$arrParams['v_ArtistID'] = Yii::$app->session->get('ArtistID');
        }
        
        $arrParams['v_AlbumTitle'] = $this->AlbumTitle;
        $arrParams['v_AlbumLink'] = $this->AlbumLink;
        $arrParams['v_Status'] = $this->Status;
        $arrParams['v_Key'] = AESKEY;
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Mymusictv_GetList',
			//'totalCount'=>5,
			 'params'=>$arrParams,
			 'sort'=> [
				'attributes'=>[
					'ID'=>[
						'asc'=>['ID' => SORT_ASC],
						'desc'=>['ID' =>SORT_DESC],
						'default'=>SORT_ASC,
					],
                                        'ArtistName'=>[
						'asc'=>['ArtistName' => SORT_ASC],
						'desc'=>['ArtistName' =>SORT_DESC],
						'default'=>SORT_ASC,
					],
                                        'AlbumTitle'=>[
						'asc'=>['AlbumTitle' => SORT_ASC],
						'desc'=>['AlbumTitle' =>SORT_DESC],
						'default'=>SORT_ASC,
					],
                                        'AlbumLink'=>[
						'asc'=>['AlbumLink' => SORT_ASC],
						'desc'=>['AlbumLink' =>SORT_DESC],
						'default'=>SORT_ASC,
					],
                                        'Status'=>[
						'asc'=>['Status' => SORT_ASC],
						'desc'=>['Status' =>SORT_DESC],
						'default'=>SORT_ASC,
					],
				],
				'defaultOrder'=>[
					'ID' => SORT_DESC
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
