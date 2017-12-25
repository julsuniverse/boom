<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "blockuser".
 *
 * @property integer $BlockID
 * @property integer $MemberID
 * @property integer $ArtistID
 * @property integer $IsBlocked
 */
class Blockuser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'blockuser';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MemberID', 'ArtistID', 'IsBlocked'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'BlockID' => 'Block ID',
            'MemberID' => 'Member ID',
            'ArtistID' => 'Artist ID',
            'IsBlocked' => 'Is Blocked',
        ];
    }
}
