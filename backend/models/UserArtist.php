<?php

namespace backend\models;
use yii\helpers\ArrayHelper;

use Yii;

/**
 * This is the model class for table "category".
 */
class UserArtist extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_artist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Id', 'UserID','MemberID', 'ArtistID','isSub'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'Id',
            'UserID' => 'UserID',
			'ArtistID' => 'ArtistID',
            'MemberID' => 'MemberID',
			'isSub' => 'isSub',
        ];
    }
	
	public function save($runValidation = true, $attributeNames = null)
    {
        if ($this->getIsNewRecord()) {
            return $this->insert($runValidation, $attributeNames);
        } else {
            return $this->update($runValidation, $attributeNames) !== false;
        }
    }

}