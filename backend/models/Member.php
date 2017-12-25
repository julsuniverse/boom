<?php
namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "member".
 *
 * @property integer $MemberID
 * @property string $MemberName
 * @property string $ProfileThumb
 * @property string $Email
 * @property string $DOB
 * @property string $ZipCode
 * @property string $MobileNo
 * @property string $Gender
 * @property integer $UnReadBadgeCount
 * @property string $DeviceType
 * @property string $DeviceToken
 * @property integer $ArtistID
 * @property integer $UserID
 * @property string $Created
 * @property string $Updated
 * @property integer $CreatedBy
 * @property integer $UpdatedBy
 */
class Member extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $UserName;
    public $ConfirmPassword;
    public $Password;
    public $Status;
    public static function tableName()
    {
        return 'member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['MemberName', 'Email','UserName','Status'], 'required', 'on'=>'create'], //,'message' => 'Please enter {attribute}.'
            ['MemberName','required','on'=>'create','message' => 'Please enter name'],
            ['Email','required','on'=>'create','message' => 'Please enter email'],
            ['UserName','required','on'=>'create','message' => 'Please enter username'],
            ['Password','required','on'=>'create','message' => 'Please enter password'],
            ['ConfirmPassword','required','on'=>'create','message' => 'Please enter confirm password'],
            ['Status','required','on'=>'create','message' => 'Please select status'],
            [['MemberName', 'Email','UserName','Status'], 'required', 'on'=>'update','message' => 'Please enter {attribute}.'], //'Gender','ZipCode','DOB'
            ['Email','email'],
            //[['ProfileThumb'], 'required','on' => 'create','message' => 'Please choose {attribute}.'],
            [['Password', 'ConfirmPassword'], 'required','on' => 'create','message' => 'Please enter {attribute}.'],
            //['ArtistID', 'required','message' => 'Please select artist.'],
			['ArtistID', 'safe'],
            //[['MobileNo'], 'number', 'max' => 13],
            [['Password'], 'string', 'min' => 8],
            //['ConfirmPassword', 'required','on' => 'create'],
            ['ConfirmPassword', 'compare', 'compareAttribute'=>'Password', 'message'=>"Passwords doesn't match"],
        ];
    }
    
    public function checkProfileImage() {
        if(isset($_FILES['Member']['name']['ProfileThumb']) && $_FILES['Member']['name']['ProfileThumb']!='') {
            $fileName = $_FILES['Member']['name']['ProfileThumb'];
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            if($extension != 'jpg' && $extension != 'png' && $extension != 'jpeg') {
                return $this->addError('ProfileThumb', 'Please upload valid profile image');
            }
        } else {    
            return $this->addError('ProfileThumb', 'Please upload profile image');
        }    
    }
    

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MemberID' => 'Member ID',
            'MemberName' => 'Name',
            'ProfileThumb' => 'Profile Thumb',
            'Email' => 'Email',
            'DOB' => 'Dob',
            'ZipCode' => 'Zip Code',
            'MobileNo' => 'Mobile No',
            'Gender' => 'Gender',
            'UnReadBadgeCount' => 'Un Read Badge Count',
            'DeviceType' => 'Device Type',
            'DeviceToken' => 'Device Token',
            'ArtistID' => 'Select Artist',
            'UserID' => 'User ID',
            'Created' => 'Created',
            'Updated' => 'Updated',
            'CreatedBy' => 'Created By',
            'UpdatedBy' => 'Updated By',
            'IsPushEnabled'=>'Show Notification'
        ];
    }
    
    public function getGender($id)
    {
        $genderData = array("1"=>"Male","2"=>"Female");
        return $genderData[$id];
    }
    public function getBdateCalculation($bdate)
    {
            $query =  Yii::$app->db->createCommand('SELECT GetAge("'.$bdate.'") AS Age')
                        ->queryAll();
            return $query[0]['Age'];
    }
    public function getMemberDeviceToken($memberID) {
        $data   = Member::findOne(array("MemberID"=>$memberID));
        return $data->DeviceToken;
    }
    public function getMemberDeviceType($memberID) {
        $data   = Member::findOne(array("MemberID"=>$memberID));
        return $data->DeviceType;
    }
    
    public function getBulkMemberDeviceToken($deviceType,$artistID) {
        $data   = Member::find()->where(" DeviceType=".$deviceType." AND ArtistID=".$artistID." AND IsPushEnabled='1' AND DeviceToken IS NOT NULL ")->groupBy('DeviceToken')->asArray()->all();
		$cmpydata   = Member::find()->where(" DeviceType=".$deviceType." AND ArtistID=0 AND IsPushEnabled='1' AND DeviceToken IS NOT NULL AND UserID IN (SELECT UserID FROM user_artist WHERE ArtistID = ".$artistID.")")->groupBy('DeviceToken')->asArray()->all();
		$data = array_merge($data, $cmpydata);
        return $data;
    }
	
	public function getMemberList(){
		$members = Member::find()->orderBy('MemberName ASC')->all();
		$listData=ArrayHelper::map($members,'MemberID','MemberName');
        return $listData;
	}
	
	public function getSingleMember($id){
		$members = Member::find()->where("MemberID=".$id)->all();
		$listData=ArrayHelper::map($members,'MemberID','MemberName');
        return $listData;
	}
    
}
