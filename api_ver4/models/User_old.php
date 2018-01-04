<?php
namespace api\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;


class User extends ActiveRecord implements IdentityInterface
{
	
	public $password;
	
	public function init() {		
		parent::init();
		\Yii::$app->user->enableSession = false;
	}
	/**
	 * @inheritdoc
	 **/
	public static function tableName() {		
		return 'user';
	}
	
	/**
	 * @inheritdoc
	 **/
	public static function primaryKey() {
		return ['id'];
	}
	
	/**
	 * Defining rules for table
	 **/
	public function rules() {
		return [
			[['username','age'],'required'],
			[['username'], 'unique'],
			[['id'],'integer'],
			[['password_hash','auth_key','email','status','access_token','expire_time'],'safe'],
			[['created_at','updated_at'], 'date', 'format'=>'yyyy-MM-dd HH:mm:ss'],
		];
	}
	
	public static function findIdentityByAccessToken($token, $type = null)
    {	
        return static::findOne(['access_token' => $token]);
    }
	
	public static function findIdentity($id) {		
		return static::findOne($id);
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getAuthKey() {
		return $this->auth_key;
	}
	
	public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }
	
	 /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
		return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword()
    {
		$this->password_hash = Yii::$app->security->generatePasswordHash($this->password);
    }
	
	 /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
	
}