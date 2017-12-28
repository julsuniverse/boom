<?php
namespace api_ver4\controllers;

use backend\models\Member;
use ErrorException;
use Yii;
use yii\filters\auth\HttpBasicAuth;
use yii\web\Controller;
use yii\filters\VerbFilter;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public $logWrite = true;
    const
        STATUS_DELETED = 0;
    const
        STATUS_ACTIVE = 10;
    const
        EXPIRE_TIME = 5; // Token expire time in minute
    const
        S3Bucket = S3BUCKET;
    const
        COGNITOID = COGNITO_POOL_ID;
    const
        BoomFolder = BOOM;
    const
        S3BucketAbsolutePath = S3_BUCKETABSOLUTE_PATH;
    const
        S3BucketPath = S3_BUCKETPATH;
    const
        S3BucketArtistImages = ARTIST_IMAGES;
    const
        S3BucketArtistThumb = ARTIST_THUMB_IMAGES;
    const
        S3BucketArtistMedium = ARTIST_MEDIUM_IMAGES;
    const
        S3BucketPostImages = POST_IMAGES;
    const
        S3BucketPostThumbImage = POST_THUMB_IMAGES;
    const
        S3BucketPostMediumImage = POST_MEDIUM_IMAGES;
    const
        S3BucketProfileImages = PROFILE_IMAGES;
    const
        S3BucketProfileThumbImages = PROFILE_THUMB_IMAGES;
    const
        S3BucketProfileMediumImages = PROFILE_MEDIUM_IMAGES;
    const
        S3BucketAppIcons = SIMILAR_APP_MEDIUM;
    const
        S3BucketStickers = STICKER_IMAGES;
    const
        S3BucketStickersThumb = STICKER_THUMB_IMAGES;
    const
        S3BucketStickersSmall = STICKER_SMALL_IMAGES;
    const
        S3BucketStickersMedium = STICKER_MEDIUM_IMAGES;
    const
        S3BucketPostVideos = POST_VIDEOS;
    const
        BoomPath = BOOM;
    const
        EncryptKey = AESKEY;
    const
        Limit = APILIMIT;
    const
        Thumbwidth = THUMB_WIDTH_SIZE;
    const
        Thumbheight = THUMB_HEIGHT_SIZE;
    const
        PostThumbwidth = POST_THUMB_WIDTH_SIZE;
    const
        PostThumbheight = POST_THUMB_HEIGHT_SIZE;
    const
        Mediumwidth = MEDIUM_WIDTH_SIZE;
    const
        Mediumheight = MEDIUM_HEIGHT_SIZE;
    const
        Documentroot = DOCUMENT_ROOT;
    const
        Project = PROJECT;
    const
        Uploads = UPLOADS;
    const
        Postlarge = POSTLARGE;
    const
        Postthumb = POSTTHUMB;
    const
        Blurthumb = POSTBLURTHUMB;
    const
        Postsmall = POSTSMALL;
    const
        Postmedium = POSTMEDIUM;
    const
        Fromemail = FROM_EMAIL;
    const
        Activationpage = ACTIVATIONPAGE;
    const
        Resetpasswordpage = RESETPASSWORDPAGE;
    const
        Postvideosthumb = POST_THUMBIMAGE_VIDEOS;
    const
        Postblurthumb = POST_THUMB_BLUR_IMAGES;
    const
        Postblurthumbvideos = POST_BLURTHUMBIMAGE_VIDEOS;
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBasicAuth::className(),
            'except' => [
                'getprofile',
            ],
        ];
        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'getprofile' => ['post'],
                'test' => ['post']
            ]
        ];
        return $behaviors;
    }

    public function beforeAction($event) {
        $action = $event->id;
        if (isset($this->actions[$action]))
        {
            $verbs = $this->actions[$action];
        }
        elseif (isset($this->actions['*']))
        {
            $verbs = $this->actions['*'];
        }
        else
        {
            return $event->isValid;
        }
        $verb = Yii::$app->getRequest()->getMethod();
        $allowed = array_map('strtoupper', $verbs);
        if (!in_array($verb, $allowed))
        {
            $this->setHeader(400);
            echo json_encode(array(
                'status' => 0,
                'error_code' => 400,
                'message' => 'Method not allowed'), JSON_PRETTY_PRINT);
            exit;
        }
        return true;
    }

    private function setHeader($status) {
        $statusHeader = 'HTTP/1.1' . $status . ' ' . _getStatusCodeMessageForUser($status);
        $contentType = "application/json; charset=utf-8";
        header($statusHeader);
        // header('Content-type: ' . $contentType);
        header('Content-Type: text/html; charset=utf-8');
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        // "This is api!";
        $rows = (new \yii\db\Query())
            ->select(['*'])
            ->from('artist')
            ->where(['id' => 1])
            ->limit(1)
            ->all();
        print_r($rows);
    }

    public function actionTest()
    {
        // "This is api!";
        $rows = (new \yii\db\Query())
            ->select(['*'])
            ->from('artist')
            ->where(['ArtistID' => 1])
            ->limit(1)
            ->all();
        print_r($rows);
    }

    public function actionGetprofile() {
        $logString  = "";

        try
        {
            $arrParams = Yii::$app->request->post();

            //print_r($arrParams);exit;
            //print_r(json_encode(['UserID' => 3, 'UserType' => 3, 'ProfileID' => 1, 'Language' => 'en']));
            $logString.="\n Params : ".$arrParams['params'].'\n';
            //print_r(json_decode($arrParams['params'])); exit;
            $data = json_decode($arrParams['params']);

            $availableParams = array(
                'UserID',
                'UserType',
                'ProfileID',
                'Language');
            $compareField = array_diff_key(array_keys($arrParams), $availableParams);

            /*$finalData = \Yii::$app->redis->get('getprofile');
            if ($finalData === false) {
                // $data нет в кэше, вычисляем заново
                $finalData = $profileData;

                // Сохраняем значение $data в кэше. Данные можно получить в следующий раз.
                \Yii::$app->redis->set('getprofile', $finalData);
            }*/

            if (count($compareField) == 0)
            {
                $userID = $data->UserID;
                $profileID = $data->ProfileID;
                $userType = $data->UserType;
                $language = $data->Language;
                $connection = Yii::$app->db;
                $procedure = "CALL Member_GetProfile('" . $userID . "','" . $userType . "','" . $profileID . "','" . self::EncryptKey . "','" . self::S3BucketPath . "','" . self::S3BucketProfileThumbImages . "')";
                $logString.="\n Member Get Profile : ".$procedure.'\n';
                $command = $connection->createCommand($procedure);
                    try {
                        $profileData = $connection->cache(function ($db) use ($command) {
                            $profileData = $command->queryAll();
                            return $profileData;
                        });
                    } catch (\Exception $e) {
                    }
                //$profileData = $command->queryAll();
                if (count($profileData) > 0)
                {
                    if ($userType == "2")
                    {
                        $postimagesprocedure = "CALL Artist_Image_List(2,0," . $profileID . ",'" . self::S3BucketPath . "','" . self::S3BucketArtistImages . "','" . self::S3BucketArtistThumb . "','" . self::S3BucketArtistMedium . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                    }
                    else
                    {
                        $postimagesprocedure = "CALL Post_Image_List(2,0," . $profileID . ",'" . self::S3BucketPath . "','" . self::S3BucketArtistImages . "','" . self::S3BucketPostThumbImage . "','" . self::S3BucketPostMediumImage . "')";
                    }
                    $logString.="\n Post image List : ".$postimagesprocedure.'\n';
                    $commandForImage = $connection->createCommand($postimagesprocedure);
                    try {
                        $imageData = $connection->cache(function ($db) use ($commandForImage) {
                            $imageData = $commandForImage->queryAll();
                            // Результат SQL запроса будет возвращен из кэша если
                            // кэширование запросов включено и результат запроса присутствует в кэше
                            return $imageData;
                        });
                    } catch (\Exception $e) {
                    }
                    //$imageData = $commandForImage->queryAll();
                    $profileData[0]['ArtistImage'] = $imageData;



                    $resultCode = 200;
                    $status = "1";
                }
                else
                {
                    $resultCode = 404;
                    $status = "0";
                }
                $resultMessage = _getStatusCodeMessageGetProfile($resultCode);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                if (isset($profileData[0]['DOB']) && ($profileData[0]['DOB'] == "01-01-1970" || $profileData[0]['DOB'] == "0000-00-00"))
                {
                    $profileData[0]['DOB'] = "";
                }
                // Пробуем извлечь $data из кэша.


                echo json_encode(['Status' => $status,
                    "Message" => $lngmsg,
                    'Result' => $profileData], JSON_PRETTY_PRINT);
            }
            else
            {
                //$this->setHeader(502);
                $resultMessage = _getStatusCodeMessageGetProfile(502);
                \Yii::$app->language = $language;
                $lngmsg = \Yii::t('api', $resultMessage);
                $this->setHeader(400);
                echo json_encode(['Status' => 0,
                    "Message" => $lngmsg], JSON_PRETTY_PRINT);
            }
        }
        catch (ErrorException $e)
        {
            $this->addLog($logString,$e);
            echo json_encode(['Status' => 0,
                "Message" => $e->getMessage()], JSON_PRETTY_PRINT);
        }
    }

    public function addLog($logString,$e) {
        if($this->logWrite) {
            $filename = '../web/log/'.Yii::$app->controller->action->id.'Log_' . date('Y-m-d-H:i:s') . '.txt';
            $log_current = "\n ************************";
            $log_current.= "\n DateTime : " .date("Y-m-d H:i:s");
            $log_current.= "\n Message : " . $e;
            $log_current.= "\n Data: " . $logString;
            file_put_contents($filename, $log_current,FILE_APPEND);
        }
    }

}
