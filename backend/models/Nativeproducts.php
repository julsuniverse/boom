<?php

namespace backend\models;
use yii\helpers\ArrayHelper;

use Yii;

/**
 * This is the model class for table "category".
 */
class Nativeproducts extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'nativeproducts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID', 'Price','ProductSKUIOS', 'ProductSKUANdr', 'Type', 'ComID'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'Id',
            'Price' => 'Price',
            'ProductSKUIOS' => 'ProductSKUIOS',
			'ProductSKUANdr' => 'ProductSKUANdr',
			'Type' => 'Type',
			'ComID' => 'ComID'
        ];
    }
	

}