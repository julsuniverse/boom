<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "memberactivity".
 *
 * @property integer $ActivityID
 * @property string $ActivityTitle
 * @property integer $MemberID
 * @property integer $ArtistID
 * @property integer $PostID
 * @property string $ActivityTypeID
 * @property integer $RefTableID
 * @property string $RefTable
 * @property string $ObjectMessage
 * @property string $StickerImage
 * @property string $Created
 */
class Memberactivity extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'memberactivity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MemberID', 'ArtistID', 'PostID', 'RefTableID'], 'integer'],
            [['ActivityTypeID', 'RefTable', 'ObjectMessage'], 'string'],
            [['Created'], 'safe'],
            [['ActivityTitle', 'StickerImage'], 'string', 'max' => 500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ActivityID' => 'Activity ID',
            'ActivityTitle' => 'Activity Title',
            'MemberID' => 'Member ID',
            'ArtistID' => 'Artist ID',
            'PostID' => 'Post ID',
            'ActivityTypeID' => 'Activity Type ID',
            'RefTableID' => 'Ref Table ID',
            'RefTable' => 'Ref Table',
            'ObjectMessage' => 'Object Message',
            'StickerImage' => 'Sticker Image',
            'Created' => 'Created',
        ];
    }
}
