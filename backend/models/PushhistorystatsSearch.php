<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Pushhistorystats;
/**
 * PushhistorystatsSearch represents the model behind the search form about `backend\models\Pushhistorystats`.
 */
class PushhistorystatsSearch extends Pushhistorystats
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['BatchID', 'ArtistID', 'TotalDevices', 'AndroidDelivered', 'IosDelivered'], 'integer'],
            [['Message', 'SearchCriterias', 'Created'], 'safe'],
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
		$wherecnd = "";
		if($this->ArtistID != null){ $wherecnd = " WHERE p.ArtistID = ".$this->ArtistID;}
        $cnt_query        = \yii::$app->db->createCommand("SELECT count(*) as total FROM pushhistorystats p".$wherecnd)->queryAll();
        $count = $cnt_query[0]['total'];
        $dataProvider = new \yii\data\SqlDataProvider([
                'sql' => 'SELECT p.BatchID,GetLongDate(p.Created) AS Created,
                                               IFNULL(p.Message,"") AS Message,
                                               IFNULL(a.ArtistName,"") AS ArtistName,
                                               IFNULL(p.TotalDevices,"") AS TotalDevices

                        FROM 
                        pushhistorystats p
                        LEFT JOIN artist a ON (a.ArtistID =p.ArtistID)'.$wherecnd,

                //'params' => [':start_date' => $year_start,':end_date'=>$year_end,':id'=> $userid],
                'totalCount' => $count,
            ]);
        

        $this->load($params);
        
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        //
        //$data->andFilterWhere([
        //    'BatchID' => $this->BatchID,
        //    'ArtistID' => $this->ArtistID,
        //    'TotalDevices' => $this->TotalDevices,
        //    'AndroidDelivered' => $this->AndroidDelivered,
        //    'IosDelivered' => $this->IosDelivered,
        //    'Created' => $this->Created,
        //]);
        //
        //$data->andFilterWhere(['like', 'Message', $this->Message])
        //    ->andFilterWhere(['like', 'SearchCriterias', $this->SearchCriterias]);

        return $dataProvider;
    }
}
