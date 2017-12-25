<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Post;
use yii\data\SpDataProvider;

/**
 * PostSearch represents the model behind the search form about `backend\models\Post`.
 */
class PostSearch extends Post
{
    /**
     * @inheritdoc
     */
    public $ArtistName;
    public $Status;
    public $ActivityID;
    public $ObjectMessage;
    public $Name;
	public $CompanyID;
    public function rules()
    {
        return [
            [['PostID', 'ArtistID', 'TotalComments', 'TotalUpVote', 'TotalShare', 'TotalFlag', 'IsShared', 'IsDelete', 'CreatedBy', 'UpdatedBy'], 'integer'],
            [['PostTitle', 'Description', 'ThumbImage', 'URL', 'IsExclusive', 'PostType', 'Created', 'Updated','ArtistName','Status','ObjectMessage','Name'], 'safe'],
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
        $arrParams['v_IsExclusive'] = $this->IsExclusive;
        $arrParams['v_Status'] = $this->Status;
		$arrParams['v_CompanyID'] = $this->CompanyID;
        if(Yii::$app->user->identity->UserType == 2) {
            $arrParams['v_ArtistID'] = Yii::$app->user->identity->ArtistID;
        } else {
            $arrParams['v_ArtistID'] = '';
        }
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Post_GetList',
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
                                        'DatePosted'=>[
						'asc'=>['DatePosted' => SORT_ASC],
						'desc'=>['DatePosted' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'DatePosted'
					],
                                        'TotalLikes'=>[
						'asc'=>['TotalLikes' => SORT_ASC],
						'desc'=>['TotalLikes' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'TotalLikes'
					],
                                        'TotalComments'=>[
						'asc'=>['TotalComments' => SORT_ASC],
						'desc'=>['TotalComments' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'TotalComments'
					],
                                        'TotalShare'=>[
						'asc'=>['TotalShare' => SORT_ASC],
						'desc'=>['TotalShare' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'TotalShare'
					],
                                        'TotalFlag'=>[
						'asc'=>['TotalFlag' => SORT_ASC],
						'desc'=>['TotalFlag' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'TotalFlag'
					],
                                        'IsExclusive'=>[
						'asc'=>['IsExclusive' => SORT_ASC],
						'desc'=>['IsExclusive' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'IsExclusive'
					],
                                        'Status'=>[
						'asc'=>['Status' => SORT_ASC],
						'desc'=>['Status' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Status'
					],
				],
				'defaultOrder'=>[
					'PostID' => SORT_DESC
				]
			 ],  
			 //'sort' => $sort,
			'pagination' => [
				  'pageSize' => 10
			 ],
		]);

        return $spDataProvider;
    }
    
    public function searchflaggedpost()
    {
        $arrParams = array();
        $arrParams['v_PostType'] = $this->PostType;
        $arrParams['v_Title'] = $this->PostTitle;
        $arrParams['v_ArtistName'] = $this->ArtistName;
        $arrParams['v_IsExclusive'] = $this->IsExclusive;
        $arrParams['v_Comment'] = $this->ObjectMessage;
        $arrParams['v_Status'] = $this->Status;
        if(Yii::$app->user->identity->UserType == 2) {
            $arrParams['v_ArtistID'] = Yii::$app->user->identity->ArtistID;
        } else {
            $arrParams['v_ArtistID'] = '';
        }
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Flagged_Post_GetList',
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
                                        'DatePosted'=>[
						'asc'=>['DatePosted' => SORT_ASC],
						'desc'=>['DatePosted' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'DatePosted'
					],
                                        'TotalLikes'=>[
						'asc'=>['TotalLikes' => SORT_ASC],
						'desc'=>['TotalLikes' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'TotalLikes'
					],
                                        'TotalComments'=>[
						'asc'=>['TotalComments' => SORT_ASC],
						'desc'=>['TotalComments' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'TotalComments'
					],
                                        'TotalShare'=>[
						'asc'=>['TotalShare' => SORT_ASC],
						'desc'=>['TotalShare' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'TotalShare'
					],
                                        'TotalFlag'=>[
						'asc'=>['TotalFlag' => SORT_ASC],
						'desc'=>['TotalFlag' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'TotalFlag'
					],
                                        'IsExclusive'=>[
						'asc'=>['IsExclusive' => SORT_ASC],
						'desc'=>['IsExclusive' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'IsExclusive'
					],
                                        'Status'=>[
						'asc'=>['Status' => SORT_ASC],
						'desc'=>['Status' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Status'
					],
				],
				'defaultOrder'=>[
					'PostID' => SORT_DESC
				]
			 ],  
			 //'sort' => $sort,
			'pagination' => [
				  'pageSize' => 10
			 ],
		]);

        return $spDataProvider;
    }
    
    public function searchName($activityID) {
        $arrParams = array();
        $arrParams['v_ActivityID'] = $activityID;
        $arrParams['v_Name'] = $this->Name;
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Flagged_Member',
			 'params'=>$arrParams,
			 'sort'=> [
				'attributes'=>[
					'Name'=>[
						'asc'=>['Name' => SORT_ASC],
						'desc'=>['Name' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Name'
					],
				],
				'defaultOrder'=>[
					'Name' => SORT_DESC
				]
			 ],  
			 //'sort' => $sort,
			'pagination' => [
				  'pageSize' => 10
			 ],
		]);

        return $spDataProvider;
    }
}
