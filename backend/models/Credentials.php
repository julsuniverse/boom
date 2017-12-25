<?php

namespace backend\models;
use yii\helpers\ArrayHelper;

use Yii;

/**
 * This is the model class for table "category".
 */
class Credentials extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'credentials';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ID', 'GUID','AffiliateId','artistID'], 'required'],
            [['StaticAdUnitId', 'VideoAdUnitId','BannerAdUnitId','NativeAdUnitId'], 'string'],
            [['AppId', 'AppSecret','InterstitialPlacementId','BannerPlacementId','NativeAdPlacementId'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'Id',
            'GUID' => 'GUID',
            'AffiliateId' => 'AffiliateId',
			'artistID' => 'artistID',
            'StaticAdUnitId' => 'StaticAdUnitId',
            'VideoAdUnitId' => 'VideoAdUnitId',
            'BannerAdUnitId' => 'BannerAdUnitId',
            'NativeAdUnitId' => 'NativeAdUnitId',
            'AppId' => 'AppId',
            'AppSecret' => 'AppSecret',
            'InterstitialPlacementId' => 'InterstitialPlacementId',
            'BannerPlacementId' => 'BannerPlacementId',
            'NativeAdPlacementId' => 'NativeAdPlacementId',

        ];
    }
	

}