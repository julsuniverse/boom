<?php

namespace backend\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "pushhistorystats".
 *
 * @property string $BatchID
 * @property integer $ArtistID
 * @property string $Message
 * @property string $TotalDevices
 * @property string $AndroidDelivered
 * @property string $IosDelivered
 * @property string $SearchCriterias
 * @property string $Created
 */
class Pushhistorystats extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pushhistorystats';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ArtistID'], 'required'],
            [['ArtistID', 'TotalDevices', 'AndroidDelivered', 'IosDelivered'], 'integer'],
            [['Created'], 'safe'],
            //[['Message'], 'string', 'max' => 500],
            [['SearchCriterias'], 'string', 'max' => 1000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'BatchID' => 'Batch ID',
            'ArtistID' => 'Artist Name',
            'Message' => 'Message',
            'TotalDevices' => '# Recipients',
            'AndroidDelivered' => 'Android Delivered',
            'IosDelivered' => 'Ios Delivered',
            'SearchCriterias' => 'Search Criterias',
            'Created' => 'Date Sent',
        ];
    }
    public function getArtistList()
    {
        $artistData = Artist::find()->select('ArtistID, ArtistName')->all();
        $listData=ArrayHelper::map($artistData,'ArtistID','ArtistName');
        return $listData;
    }
    public function getSelectedMembers($unselectedids="",$selectedids="")
    {
        $append =   "";
        if($unselectedids != "")
        {
            $append = " AND m.MemberID NOT IN (".$unselectedids.")";
        }
        if($selectedids != "")
        {
            $append = " AND m.MemberID IN (".$selectedids.")";
        }
        $query = "SELECT m.MemberID,m.DeviceType,m.DeviceToken FROM member m LEFT JOIN user u ON (u.UserID=m.UserID)
                            WHERE (m.DeviceType='1' OR m.DeviceType='2')
                            AND m.DeviceToken != ''
                            AND u.Status='1' ".$append;
        $exequery =  Yii::$app->db->createCommand($query)->queryAll();
        return $exequery;
    }
    public function getpushqueueDeviceTokensAndroid()
    {
            //$data = Yii::app()->db->createCommand()
            //        ->select('BatchID,MemberID,DeviceType,DeviceToken,Message')
            //        ->from('pushqueue')
            //        ->where('Status = "1" AND DeviceType="2"')
            //        ->queryAll();
            //    
            //return $data;
        
        //$query = $query = new \yii\db\Query();
        //$query ->select(['BatchID,MemberID,DeviceType,DeviceToken,Message'])  
        //        ->from('pushqueue')
        //        ->where('Status = "1" AND DeviceType="2"')
        //$command = $query->createCommand();
        //$data = $command->queryAll();
        //echo "<pre>";print_r($data);exit;

    }
}
