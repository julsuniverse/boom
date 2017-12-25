<?php
namespace frontend\models;

use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

/**
 * Password reset form
 */
class ResetPasswordApp extends Model
{
    public $password;
    public $confirmpassword;
    public $token;
    public function rules()
    {
        return [
            [['password','confirmpassword'], 'required','message' => 'Please enter {attribute}.'],
            [['password'],'string','min' => 8, 'tooShort' => "Password should have at least 8 characters."],
            ['confirmpassword', 'compare', 'compareAttribute'=>'Password', 'message'=>"Passwords doesn't match"],
        ];
    }
    public function attributeLabels()
    {
        return [
            'confirmpassword' => 'Confirm Password',
        ];
    }
    public function resetPassword($password,$token)
    {
        $model = \backend\models\Forgotpassword::findOne(array("UniqueCode"=>$token,"Status"=>"2"));
        if(count($model) > 0)
        {
            $userid  = $model->UserID;
            $model->Status='1';
            $forgot = $model->save(false);
            
            $usermodel = \backend\models\User::findOne(array("UserID"=>$userid));
            $usermodel->Password= md5($password);
            $usermodel->Is_migrate=0;
            
            $user = $usermodel->save(false);
            if($forgot == 1 && $user == 1)
            {
                return 1;
            }
            else
            {
                return -1;
            }
            
        }
        else
        {
            return 0;
        }
    }
}
