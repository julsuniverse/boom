<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Exclusivemember;
use yii\data\SpDataProvider;

/**
 * PostSearch represents the model behind the search form about `backend\models\Post`.
 */
class ExclusivememberSearch extends Exclusivemember
{
    /**
     * @inheritdoc
     */
    public $MemberName;
    public $Email;
    public $Status;
    public $DateLiked;
    public $ArtistName;
    public function rules()
    {
        return [
            [['PostID', 'ArtistID', 'TotalComments', 'TotalUpVote', 'TotalShare', 'TotalFlag', 'IsShared', 'IsDelete', 'CreatedBy', 'UpdatedBy'], 'integer'],
            [['PostTitle', 'Description', 'ThumbImage', 'URL', 'IsExclusive', 'PostType', 'Created', 'Updated','ArtistName','Status','MemberName','Email','DateLiked'], 'safe'],
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
        $arrParams['v_UserID'] = $_GET['id'];
        $arrParams['v_PosType'] = $this->PostType;
        $arrParams['v_Title'] = $this->PostTitle;
        $arrParams['v_ArtistName'] = $this->ArtistName;
        
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Users_Exclusive_GetList',
			//'totalCount'=>5,
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
                                        'PostTitle'=>[
						'asc'=>['PostTitle' => SORT_ASC],
						'desc'=>['PostTitle' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'PostTitle'
					],
                                        'ArtistName'=>[
						'asc'=>['ArtistName' => SORT_ASC],
						'desc'=>['ArtistName' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'ArtistName'
					],
                                        'DateLiked'=>[
						'asc'=>['DateLiked' => SORT_ASC],
						'desc'=>['DateLiked' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'DateLiked'
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
