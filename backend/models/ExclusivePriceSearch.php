<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\ExclusivePrice;

/**
 * ExclusivePriceSearch represents the model behind the search form about `backend\models\ExclusivePrice`.
 */
class ExclusivePriceSearch extends ExclusivePrice
{
    /**
     * @inheritdoc
     */
    public $ArtistID;
    public function rules()
    {
        return [
            [['ExclusivePriceID'], 'integer'],
            [['StatusPrice', 'ImagePrice', 'VideoPrice'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ExclusivePrice::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'ExclusivePriceID' => $this->ExclusivePriceID,
            'StatusPrice' => $this->StatusPrice,
            'ImagePrice' => $this->ImagePrice,
            'VideoPrice' => $this->VideoPrice,
        ]);

        return $dataProvider;
    }
}
