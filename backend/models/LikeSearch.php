<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Like;
use yii\data\SpDataProvider;

/**
 * PostSearch represents the model behind the search form about `backend\models\Post`.
 */
class LikeSearch extends Like
{
    /**
     * @inheritdoc
     */
    public $MemberName;
    public $Email;
    public $Status;
    public $DateLiked;
    public $ArtistName;
    public $Website;
    public function rules()
    {
        return [
            [['PostID', 'ArtistID', 'TotalComments', 'TotalUpVote', 'TotalShare', 'TotalFlag', 'IsShared', 'IsDelete', 'CreatedBy', 'UpdatedBy'], 'integer'],
            [['PostTitle', 'Description', 'ThumbImage', 'URL', 'IsExclusive', 'PostType', 'Created', 'Updated','ArtistName','Status','MemberName','Email','DateLiked','ArtistName','Website'], 'safe'],
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
        $arrParams['v_PostID'] = $_GET['id'];
        $arrParams['v_MemberName'] = $this->MemberName;
        $arrParams['v_Email'] = $this->Email;
        $arrParams['v_Website'] = $this->Website;
        $arrParams['v_Key'] = AESKEY;
        $arrParams['v_Activity'] = '1';
        $arrParams['v_ArtistID'] = '';
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Post_Likes_Shares_GetList',
			//'totalCount'=>5,
			 'params'=>$arrParams,
			 'sort'=> [
				'attributes'=>[
					'DateLiked'=>[
						'asc'=>['DateLiked' => SORT_ASC],
						'desc'=>['DateLiked' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'DateLiked'
					],
                                        'MemberName'=>[
						'asc'=>['MemberName' => SORT_ASC],
						'desc'=>['MemberName' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'MemberName'
					],
                                        'Email'=>[
						'asc'=>['Email' => SORT_ASC],
						'desc'=>['Email' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Email'
					],
                                        
				],
				'defaultOrder'=>[
					'DateLiked' => SORT_DESC
				]
			 ],  
			'pagination' => [
				  'pageSize' => 10,
			 ],
			 
		]);

        return $spDataProvider;
    }
    
}
