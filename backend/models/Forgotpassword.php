<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "forgotpassword".
 *
 * @property integer $ID
 * @property integer $UserID
 * @property string $UniqueCode
 * @property string $Status
 * @property string $Created
 */
class Forgotpassword extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $Username;
    public $password;
    public $confirmpassword;
    public static function tableName()
    {
        return 'forgotpassword';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Username','password','confirmpassword'], 'required','message'=>'Please enter {attribute}.'],
            [['password'],'string','min' => 8, 'tooShort' => "Password should have at least 8 characters."],
            ['confirmpassword', 'compare', 'compareAttribute'=>'password', 'message'=>"Passwords doesn't match"],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'confirmpassword' => 'Confirm Password',
        ];
    }
}
