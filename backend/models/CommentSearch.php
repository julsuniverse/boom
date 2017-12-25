<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Like;
use backend\models\Comment;
use yii\data\SpDataProvider;

/**
 * PostSearch represents the model behind the search form about `backend\models\Post`.
 */
class CommentSearch extends Comment
{
    /**
     * @inheritdoc
     */
    public $MemberName;
    public $Email;
    public $Status;
    public $DateLiked;
    public $Comment;
    public $ArtistName;
    public function rules()
    {
        return [
            [['PostID', 'ArtistID', 'TotalComments', 'TotalUpVote', 'TotalShare', 'TotalFlag', 'IsShared', 'IsDelete', 'CreatedBy', 'UpdatedBy'], 'integer'],
            [['PostTitle', 'Description', 'ThumbImage', 'URL', 'IsExclusive', 'PostType', 'Created', 'Updated','ArtistName','Status','MemberName','Email','DateLiked','Comment','ArtistName'], 'safe'],
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
        $arrParams['v_Key'] = AESKEY;
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Post_Comments_GetList', //Admin_Users_Comments_GetList
			//'totalCount'=>5,
			 'params'=>$arrParams,
			 'sort'=> [
				'attributes'=>[
					'DateCommented'=>[
						'asc'=>['DateCommented' => SORT_ASC],
						'desc'=>['DateCommented' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'DateCommented'
					],
                                        'Comment'=>[
						'asc'=>['Comment' => SORT_ASC],
						'desc'=>['Comment' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Comment'
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
					'DateCommented' => SORT_DESC
				]
			 ],  
			'pagination' => [
				  'pageSize' => 10,
			 ],
			 
		]);

        return $spDataProvider;
    }
}
