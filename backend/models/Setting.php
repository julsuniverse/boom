<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "setting".
 *
 * @property integer $SettingID
 * @property string $QAModuleName
 * @property integer $IsQAEnable
 * @property integer $QaType
 * @property string $Created
 * @property integer $CreatedBy
 * @property string $Updated
 * @property integer $UpdatedBy
 * @property string $NoAdsProdID
 */
class Setting extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['QAModuleName','ArtistID'],'required'],
            [['Created', 'Updated'], 'safe'],
            [['QAModuleName'], 'string', 'max' => 255],
            [['VideoPrice','TextPrice','ProductPrice'],'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'SettingID' => 'Setting ID',
            'QAModuleName' => 'Qamodule Name',
            'IsQAEnable' => 'Is Qaenable',
            'QaType' => 'Qa Type',
            'Created' => 'Created',
            'CreatedBy' => 'Created By',
            'Updated' => 'Updated',
            'UpdatedBy' => 'Updated By',
        ];
    }
}
