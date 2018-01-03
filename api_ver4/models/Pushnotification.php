<?php
namespace api\models;

use Yii;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */

class Pushnotification extends \yii\db\ActiveRecord
{
   public function getpushqueueDeviceTokensAndroid()
   {
      //$query = $query = new \yii\db\Query();
      //$query ->select(['BatchID,MemberID,DeviceType,DeviceToken,Message'])  
      //          ->from('pushqueue')
      //          ->where('Status = "1" AND DeviceType="2"')
      //$command2 = $query->createCommand();
      //$data = $command2->queryAll();
      //   return $data;
      $dataProvider = new \yii\data\SqlDataProvider([
                'sql' => 'SELECT BatchID,MemberID,DeviceType,DeviceToken,Message
                        FROM pushqueue',
                //'params' => [':Status' => 1,':DeviceType'=>2],
               // 'totalCount' => $count,
            ]);
      return $dataProvider;
   }
}
