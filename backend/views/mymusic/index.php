<?php
/***************************************  My music required library *********************************/
require_once '../../common/config/autoload.php';
    $artistData = backend\models\Artist::getArtistData(Yii::$app->user->identity->UserID);
    if(isset($artistData[0]['ArtistID']) && $artistData[0]['ArtistID']!= '') 
    {
        $artistiddb = $artistData[0]['ArtistID'];
    }
$session = new SpotifyWebAPI\Session('0decc6d817cc4ea48e4a48066a4f507d', '8a003635a94e43e0ab8e8420db9e8bbd', 'http://localhost:8080/boom/backend/web/index.php/mymusic/index'); 
$api = new SpotifyWebAPI\SpotifyWebAPI();

/******************************************************************/
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'My Music';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mymuisc-index">

    <h1><?= Html::encode($this->title) ?></h1>
<?php
    $form = ActiveForm::begin();
    
    if(isset($_GET['code'])) 
    {    
        /********************************* GET ACCESS TOKEN ***************************************/

        $accessToken = $session->requestAccessToken($_GET['code']);
        $data = $api->setAccessToken($session->getAccessToken());

        /******************************************************************************************/

        $artistData = $api->me();
        $artistId = $artistData->id;
        $playlists = $api->getUserPlaylists($artistId, array(
            //'limit' => 5
        ));

        $playlistTracks =   $api->getUserPlaylistTracks('spironas','5Eh1rySJ0gyYvpOKMFNpqC');
        $playlistname   =   array();
        $tracklist      =   array();
        foreach($playlists->items as $playlist)
        {
           $playlistname[$playlist->id]    =   $playlist->name; 
        }
        foreach ($playlistTracks->items as $track) 
        {
            $track                  =      $track->track;
            $tracktitle             =      $track->name;
            $duration               =      $track->duration_ms;
            $trackid                =      $track->external_urls->spotify;
            $id                     =      $track->id;
            $trackuri               =      $track->uri;
            $tracklist[$trackid]    =      $track->name;
        }
        echo $form->field($model,'PlaylistName')->dropDownList($playlistname);
        echo $form->field($model,'TrackTitle')->dropDownList($tracklist);
    }
    else
    {
        $scopes = array(
            'scope' => array(
                'user-read-email',
                'user-library-modify',
                'playlist-modify-private',
                'playlist-modify-public',
                'playlist-read-private',
            ),
        );
        
        $this->registerJs('
            window.location.href= "'.$session->getAuthorizeUrl($scopes).'";
        ');        
    }
    ActiveForm::end();
?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Add to list', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php \yii\widgets\Pjax::begin(
            ['id' => 'ArtisttracksList', 'timeout' => false, 'enablePushState' => true, 'clientOptions' => ['method' => 'GET']]
        ); ?>
    <?= GridView::widget([
        'dataProvider' => $model->search(),        
        'filterModel' => $model,
        'columns' => [
            [
                'label'=>'TrackTitle',
                'attribute'=>'TrackTitle',
                'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
            [
                'label'=>'Duration',
                'attribute'=>'Duration',
                'filter'=>false,
            ],
            [
                'label'=>'PlaylistName',
                'attribute'=>'PlaylistName',
                'filterInputOptions' => [
		    'class'       => 'form-control magnifying',
		    'placeholder' => 'Search'
		]
            ],
        ],
    ]); ?>
    <?php \yii\widgets\Pjax::end(); ?>

</div>
