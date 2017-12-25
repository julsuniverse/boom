<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Member;
use yii\data\SpDataProvider;

/**
 * MemberSearch represents the model behind the search form about `backend\models\Member`.
 */
class MemberSearch extends Member
{
    /**
     * @inheritdoc
     */
    public $FromJoinDate;
    public $ToJoinDate;
    public $ArtistName;
    public $Status;
	public $CompanyID;
    public function rules()
    {
        return [
            [['MemberID', 'UnReadBadgeCount', 'ArtistID', 'UserID', 'CreatedBy', 'UpdatedBy'], 'integer'],
            [['MemberName', 'ProfileThumb', 'Email', 'DOB', 'ZipCode', 'MobileNo', 'Gender', 'DeviceType', 'DeviceToken', 'Created', 'Updated', 'ArtistName', 'Status'], 'safe'],
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
        $actionName = Yii::$app->controller->action->id;
        $fromJoinDate = '';
        $toJoinDate = '';
        
        if(isset($_POST['Pushhistorystats']['FromJoinDate']) && $_POST['Pushhistorystats']['FromJoinDate']!='')
        {
            $fromJoinDate = date("Y-m-d",strtotime($_POST['Pushhistorystats']['FromJoinDate']));
        }
        else if(isset($_POST['Member']['FromJoinDate']) && $_POST['Member']['FromJoinDate']!='')
        {
            $fromJoinDate = date("Y-m-d",strtotime($_POST['Member']['FromJoinDate']));
        }
        if(isset($_POST['Pushhistorystats']['ToJoinDate']) && $_POST['Pushhistorystats']['ToJoinDate']!='')
        {
            $toJoinDate = date("Y-m-d",strtotime($_POST['Pushhistorystats']['ToJoinDate']));
        }
        else if(isset($_POST['Member']['ToJoinDate']) && $_POST['Member']['ToJoinDate']!='')
        {
            $toJoinDate = date("Y-m-d",strtotime($_POST['Member']['ToJoinDate']));
        }
        
        $arrParams = array();
        $arrParams['v_FromJoinDate'] = $fromJoinDate;
        $arrParams['v_ToJoinDate'] = $toJoinDate;
        $arrParams['v_MemberName'] = $this->MemberName;
        $arrParams['v_Email'] = $this->Email;
        $arrParams['v_Zipcode'] = $this->ZipCode;
        $arrParams['v_MobileNo'] = $this->MobileNo;
        $arrParams['v_Gender'] = $this->Gender;
        $arrParams['v_ArtistName'] = $this->ArtistName;
        
        if($actionName == 'create') // for Push Notification create action
        {  
            $userData = \backend\models\User::findAll(array("UserID"=>Yii::$app->user->identity->UserID));
            $artistID =  $userData[0]['ArtistID'];
            $arrParams['v_ArtistID']    =   $artistID;
            $arrParams['v_AgeGroup']          =   $this->DOB;
            $arrParams['v_Status']  =   $this->Status;
            $arrParams['v_PushNotification']  =   2;
        }
        else
        {
            if(Yii::$app->user->identity->UserType == 2)
            {
                $session = Yii::$app->session;
                $arrParams['v_ArtistID'] = $session->get('ArtistID');
            }
            else
            {
                $arrParams['v_ArtistID'] = '';
            }
            $arrParams['v_AgeGroup']          =   "";
            $arrParams['v_Status']  =   $this->Status;
            $arrParams['v_PushNotification']  =   1;
            
        }
        $arrParams['v_DeviceType'] =$this->DeviceType;
        $arrParams['v_Key'] = AESKEY;
		$arrParams['v_CompanyID'] = $this->CompanyID;
        
        $spDataProvider = new SpDataProvider([
			'spName' => 'Admin_Users_GetList',
			//'totalCount'=>5,
			 'params'=>$arrParams,
			 'sort'=> [
				'attributes'=>[
					'MemberID'=>[
						'asc'=>['MemberID' => SORT_ASC],
						'desc'=>['MemberID' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'MemberID'
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
                                        'ZipCode'=>[
						'asc'=>['ZipCode' => SORT_ASC],
						'desc'=>['ZipCode' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'ZipCode'
					],
                                        'MobileNo'=>[
						'asc'=>['MobileNo' => SORT_ASC],
						'desc'=>['MobileNo' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'MobileNo'
					],
                                        'Gender'=>[
						'asc'=>['Gender' => SORT_ASC],
						'desc'=>['Gender' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Gender'
					],
                                        'ArtistName'=>[
						'asc'=>['ArtistName' => SORT_ASC],
						'desc'=>['ArtistName' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'ArtistName'
					],
                                        'Status'=>[
						'asc'=>['Status' => SORT_ASC],
						'desc'=>['Status' =>SORT_DESC],
						'default'=>SORT_ASC,
						'label'=>'Status'
					],
				],
				'defaultOrder'=>[
					'MemberID' => SORT_DESC
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
