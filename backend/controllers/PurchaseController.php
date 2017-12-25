<?php

namespace backend\controllers;

use Yii;
use backend\models\Memberpost;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\db\Query;
use yii\data\ActiveDataProvider;
use \backend\controllers\CsvExport;



class PurchaseController extends Controller
{
    public function behaviors()
    {
        $session = Yii::$app->session;
        if(Yii::$app->user->identity->UserType == 1) {
            return [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            'actions' => ['index', 'exportcsv'],
                            'roles' => ['@'],
                        ],
                    ],
                ],
            ];
        } else {
            return \Yii::$app->getResponse()->redirect(array('/site/logout',302));
        }
    }
    
    public function beforeAction($action)
    {
        if (\Yii::$app->getUser()->isGuest &&
            \Yii::$app->getRequest()->url !== Url::to(\Yii::$app->getUser()->loginUrl)
        ) {
            \Yii::$app->getResponse()->redirect(\Yii::$app->getUser()->loginUrl);
        }
        return parent::beforeAction($action);
    }

    /**
     * Lists all Globalvar models.
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new Memberpost();
		
		$isNative = null;
		if(isset($_GET['Memberpost']['isNative'])) {
            $isNative = $_GET['Memberpost']['isNative'];
        }
		
		if(Yii::$app->user->identity->UserType == 4){
			$companyID = Yii::$app->session->get('CompanyID');
			
			$wherecond = ['artist.CompanyID' => $companyID];
			if($isNative != null && $isNative != ""){$wherecond = ["AND",['artist.CompanyID' => $companyID],['memberpost.isNative' => $isNative]]; $model->isNative = $isNative;}
			
			$dataProvider = new ActiveDataProvider([
			'query' => Memberpost::find()
			->join('INNER JOIN', 'member', 'member.MemberID = memberpost.MemberID')
			->join('INNER JOIN', 'artist', 'member.ArtistID = artist.ArtistID')
			->where($wherecond),
			'pagination' => [
				'pageSize' => 20,
			],
			'sort' => ['defaultOrder' => ['Created' => SORT_DESC]],
			]);
			
		}else{
		
			$wherecond = [];
			if($isNative != null && $isNative != ""){$wherecond = ['isNative' => $isNative]; $model->isNative = $isNative;}
		
			$dataProvider = new ActiveDataProvider([
			'query' => Memberpost::find()->where($wherecond),
			'pagination' => [
				'pageSize' => 20,
			],
			'sort' => ['defaultOrder' => ['Created' => SORT_DESC]],
			]);
		
		}
		
        return $this->render('index', [            
            'model' => $model,
			'dataProvider' => $dataProvider,
        ]);
    }
    

    /**
     * Displays a single Globalvar model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


	public function actionExportcsv(){
		
		$month = $_POST['month'];
		$year = $_POST['year'];
		
		$data = Memberpost::find()->where('Created BETWEEN "'.$year.'-'.$month.'-01 00:00:00" AND "'.$year.'-'.(intval($month)+1).'-01 00:00:00"')->all();
		
		if(Yii::$app->user->identity->UserType == 4){
			$companyID = Yii::$app->session->get('CompanyID');
			$data = Memberpost::find()
			->join('INNER JOIN', 'member', 'member.MemberID = memberpost.MemberID')
			->join('INNER JOIN', 'artist', 'member.ArtistID = artist.ArtistID')
			->where('artist.CompanyID='.$companyID.' AND Created BETWEEN "'.$year.'-'.$month.'-01 00:00:00" AND "'.$year.'-'.(intval($month)+1).'-01 00:00:00"')
			->where(['artist.CompanyID' => $companyID])
			->all();
		}
		
		$mydata = array();
		foreach($data as $record){
			$SKUID = $this->getSKU($record->TransactionData);
			$transID = $this->getTransId($record->TransactionData);
			$devType = "";
			if(isset($record->DeviceType)){$devType = $record->DeviceType;}
			$token = "";
			if(isset($record->Token)){$token = $record->Token;}
			
			$obj = ['MemberPostID'=>$record->MemberPostID,
					'MemberID'=>$record->MemberID,
					'PostID'=>$record->PostID,
					'Cost'=>$record->Cost,
					'SKUID'=>$SKUID,
					'transactionID'=>$transID,
					'DeviceType'=>$devType,
					'Token'=>$token,
					'Created'=>$record->Created
					];
			array_push($mydata, $obj);
		}
		
		//echo count($data);
		
		CsvExport::export(
		$mydata, // a CActiveRecord array OR any CModel array
		array('MemberPostID'=>array(),'MemberID'=>array(),'PostID'=>array(),'Cost'=>array(),'SKUID'=>array(),'transactionID'=>array(),'DeviceType'=>array(),'Token'=>array(),'Created'=>array()),
		true, // boolPrintRows
		'Purchase'.date('d-m-Y H-i').".csv"
	   );
	}
	
	
	protected function getSKU($tdata){
		$spli = split('"SKU":', $tdata);
        if(count($spli) > 1){
			$sku = $spli[1];
			$sku = split('"', $sku)[1];
			return $sku;
		}else{return "";}
	}
	
	protected function getTransId($tdata){
		$spli = split('"TRANSACTIONID":', $tdata);
        if(count($spli) > 1){
			$transId = $spli[1];
			$transId = split('"', $transId)[1];
			return $transId;
		}else{return "";}
	}
	

    /**
     * Finds the Globalvar model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Artist the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = Memberpost::findOne($id);
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
}
