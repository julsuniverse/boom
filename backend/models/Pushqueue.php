<?php

namespace backend\models;

use Yii;
use yii\data\SpDataProvider;
/**
 * This is the model class for table "pushqueue".
 *
 * @property integer $ID
 * @property string $BatchID
 * @property integer $MemberID
 * @property string $DeviceToken
 * @property string $DeviceType
 * @property string $Status
 * @property string $Message
 * @property string $Created
 * @property string $Delivered
 */
class Pushqueue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $Name;
    public $Email;
    public $AgeGroup;
    public $Datejoined;
    //public $DeviceType;
    public static function tableName()
    {
        return 'pushqueue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['BatchID', 'MemberID'], 'integer'],
            [['DeviceType', 'Status'], 'string'],
            [['Name','Email','AgeGroup','DeviceType','Created', 'Delivered'], 'safe'],
            [['DeviceToken'], 'string', 'max' => 1024],
            [['Message'], 'string', 'max' => 500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'BatchID' => 'Batch ID',
            'MemberID' => 'Member ID',
            'DeviceToken' => 'Device Token',
            'DeviceType' => 'Device Type',
            'Status' => 'Status',
            'Message' => 'Message',
            'Created' => 'Created',
            'Delivered' => 'Delivered',
        ];
    }
    public function searchForId($id, $array) 
    {
        $token = array();
        foreach ($array as $key => $val) 
        {
            if ($val['BatchID'] === $id) 
            {
                $token[] = $val['DeviceToken'];
            }
        }
        return $token;
    }
    public function getAndroidDevices()
    {
        $data   = Pushqueue::find()
                ->where("Status = '1' AND DeviceType = '2'")
                ->groupBy("DeviceToken")
                ->limit(100)
                ->all();
        return $data;
    }
    public function getIosDevices()
    {
        $data   = Pushqueue::find()
                ->where("Status = '1' AND DeviceType = '1'")
                ->groupBy("DeviceToken")
                ->limit(100)
                ->all();
        return $data;
    }
    public function getSentpushlist($batchid)
    {
        $data   = Pushqueue::find()
                ->where("Status = '3' AND BatchID=".$batchid)
                ->all();
        return $data;
    }
    public function addPushHistory($memberID,$deviceType,$deviceToken,$batchID)
    {
        $connection     =   \yii::$app->db;

        $query          =   "UPDATE pushqueue SET Status='3' "
                            . "WHERE BatchID=".$batchID." AND MemberID=".$memberID." AND DeviceType='".$deviceType."' AND DeviceToken='".$deviceToken."'";
        $data           =   $connection->createCommand($query);
        $exe            =   $data->execute();
        return $exe;
    }
}
