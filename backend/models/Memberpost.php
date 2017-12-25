<?php

namespace backend\models;
use yii\helpers\ArrayHelper;

use Yii;

/**
 * This is the model class for table "memberpost".
 */
class Memberpost extends \yii\db\ActiveRecord
{

	public $memberName;
	public $SKUID;
	public $transactionID;
	

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'memberpost';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['MemberPostID', 'PostID', 'Cost', 'Created'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'MemberPostID' => 'Id',
            'PostID' => 'Post Id',
			'Cost' => 'Cost',
			'Created' => 'Created',
        ];
    }

}