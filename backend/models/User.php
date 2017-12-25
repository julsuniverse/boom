<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use backend\models\User;

/**
 * This is the model class for table "user".
 *
 * @property integer $UserID
 * @property string $UserName
 * @property string $Password
 * @property string $Status
 * @property string $LastLoginDateTime
 * @property string $UserType
 * @property string $ActivationCode
 * @property string $ActivatedOn
 * @property integer $ArtistID
 * @property integer $CreatedBy
 * @property integer $UpdatedBy
 * @property string $Created
 * @property string $Updated
 */
class User extends \yii\db\ActiveRecord
{
    public $ConfirmPassword;
    public $rememberMe = true;
    private $_user;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserName','Password'], 'required','on' => 'login','message' => 'Please enter {attribute}.'],
            [['UserName','Email','Password','Status'], 'required','on' => 'create'],
            [['UserName','Email','Status'], 'required','on' => 'update'],
            ['Email','email'],
            [['Password'],'string','min' => 8, 'tooShort' => "Password should have at least 8 characters."],
            ['ConfirmPassword', 'required','on' => 'create'],
            ['ConfirmPassword', 'compare', 'compareAttribute'=>'Password', 'message'=>"Passwords doesn't match"],
            //['UserName', 'unique', 'targetClass' => '\backend\models\User', 'message' => 'This username has already been taken.','on' => array('create','update')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UserID' => 'User ID',
            'UserName' => 'Username',
            'Password' => 'Password',
            'ConfirmPassword' => 'Confirm Password',
            'Status' => 'Status',
            'LastLoginDateTime' => 'Last Login Date Time',
            'UserType' => 'User Type',
            'ActivationCode' => 'Activation Code',
            'ActivatedOn' => 'Activated On',
            'ArtistID' => 'Artist ID',
            'CreatedBy' => 'Created By',
            'UpdatedBy' => 'Updated By',
            'Created' => 'Created',
            'Updated' => 'Updated',
        ];
    }
    
    public function loginForAdmin($username,$rememberMe) {
        $userModel = new \common\models\User();
        $userData = $userModel->findByUsernameForAdmin($username);
        return Yii::$app->user->login($userData, $rememberMe ? 3600 * 24 * 30 : 0);
    }
    
    public function getStatus() {
        return array("1" => "Active", "2" => "Inactive", "3" => "Pending");
    }
    
    public function getStatusValue($id) {
        $status = array("1"=>"Active","2"=>"Inactive","3"=>"Pending",""=>"Not Set");
        return $status[$id];
    }
}
