<?php

namespace backend\controllers;

use Yii;
use backend\models\Pushhistorystats;
use backend\models\PushhistorystatsSearch;
use backend\models\Pushqueue;
use backend\models\PushqueueSearch;
use backend\models\Member;
use backend\models\MemberSearch;
use backend\models\StickerSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PushhistorystatsController implements the CRUD actions for Pushhistorystats model.
 */
class PushhistorystatsController extends Controller
{
    public function behaviors()
    {
        if(Yii::$app->user->identity->UserType == 3) {
            return \Yii::$app->getResponse()->redirect(array('/site/logout',302));
        }
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Pushhistorystats models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PushhistorystatsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Pushhistorystats model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }
    public function actionUsers($id)
    {
        $searchModel = new PushqueueSearch();
        if(isset($_GET['PushqueueSearch'])) {
            $searchModel->attributes = $_GET['PushqueueSearch'];
        }
        return $this->render('users', [            
            'model' => $searchModel,
            
        ]);
    }

    /**
     * Creates a new Pushhistorystats model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        
        $model          =   new Pushhistorystats();
        $searchModel    =   new MemberSearch();
        $isselectall    =   '0';
        $selectedids    =   "";
        $unselectedids  =   "";
        $totaldevices   =   0;
        $batchid        =   0;
        $artistID       =   0;
        $save           =   0;
        $cnt            =   0;
         
        if(isset($_GET['MemberSearch']))
        {
            $searchModel->attributes = $_GET['MemberSearch'];
        }

        $userData = \backend\models\User::findAll(array("UserID"=>Yii::$app->user->identity->UserID));
        $searchModel->ArtistID =  $userData[0]['ArtistID'];
        $artistID   =    $userData[0]['ArtistID'];

        if(isset($_POST['Pushhistorystats']['FromJoinDate']) && $_POST['Pushhistorystats']['FromJoinDate']!='')
        {
            $searchModel->FromJoinDate = $_POST['Pushhistorystats']['FromJoinDate'];
        }
        if(isset($_POST['Pushhistorystats']['ToJoinDate']) && $_POST['Pushhistorystats']['ToJoinDate']!='')
        {
            $searchModel->ToJoinDate = $_POST['Pushhistorystats']['ToJoinDate'];
        }
        
        //if ($model->load(Yii::$app->request->post()) && $model->save())
        //{
        //    return $this->redirect(['view', 'id' => $model->BatchID]);
        //}
        //else
        //{
        
        if(isset($_POST['send']) && $_POST['send'] == 1)
        {
           
            $model->Message     =   $_POST['message'];
            $userData = \backend\models\User::findAll(array("UserID"=>Yii::$app->user->identity->UserID));
            $model->ArtistID    =   $userData[0]['ArtistID'];
            if(isset($_POST['isSelectAll']))
            {
                $isselectall    =   $_POST['isSelectAll'];
            }
            if(isset($_POST['selectedids']))
            {
                $selectedids    =   $_POST['selectedids'];
            }
            if(isset($_POST['unselectedids']))
            {
                $unselectedids    =   $_POST['unselectedids'];
            }
            if($isselectall == "1")
            {
                if($unselectedids == "")
                {
                    $members  =   $model->getSelectedMembers("","");
                }
                else
                {
                    $members  =     $model->getSelectedMembers($unselectedids);
                }
            }
            else
            {
                if($selectedids != "")
                {
                    $members  =   $model->getSelectedMembers("",$selectedids);
                }
            }

            $totaldevices           = count($members);
            $model->TotalDevices    = $totaldevices;
            if($model->save())
            {
                $batchid  = Yii::$app->db->getLastInsertID();
                
                if($batchid > 0)
                {
                    
                    for($i=0;$i<count($members);$i++)
                    {      
                        $queue              =   new Pushqueue();
                        $queue->BatchID     =   $batchid;
                        $queue->MemberID    =   $members[$i]['MemberID'];
                        $queue->DeviceType  =   $members[$i]['DeviceType'];
                        $queue->DeviceToken =   $members[$i]['DeviceToken'];
                        $queue->Status      =   '1';
                        $queue->Message     =   htmlspecialchars_decode($_POST['message'],ENT_QUOTES);
                        $save               =   $queue->save();
                        if($save > 0)
                            $cnt++;
                    }
                    if($cnt == count($members))
                    {
                        Yii::$app->session->setFlash('success', 'Push Notification has been scheduled successfully');
                        return $this->redirect('index');
                    }
                    else
                    {
                        print_r($queue->getErrors());die;
                        return $this->render('create', [
                            'model' => $searchModel
                        ]);
                    }
                }
                else
                {
                    return $this->render('create', [
                                    'model' => $searchModel
                                ]);
                }
            }
        }
        else
        {
            return $this->render('create', [
                                    'model' => $searchModel
                                ]); 
        }
        //}
    }

    /**
     * Updates an existing Pushhistorystats model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->BatchID]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Pushhistorystats model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Pushhistorystats model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Pushhistorystats the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Pushhistorystats::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
