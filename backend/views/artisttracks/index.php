<?php
/***************************************  My music required library *********************************/
//echo Url::to(['artisttracks/index?playlist=']); die;
require_once 'common/config/autoload.php';
$artistData = backend\models\Artist::getArtistData(Yii::$app->user->identity->UserID);
if(isset($artistData[0]['Header']) && $artistData[0]['Header']!="")
    $model->Header = $artistData[0]['Header'];
else 
    $model->Header = "This is what I am listening while travelling";
if(isset($artistData[0]['ArtistID']) && $artistData[0]['ArtistID']!= '') 
{
    $artistiddb = $artistData[0]['ArtistID'];
}

$session    =   new SpotifyWebAPI\Session(SPOTIFY_CLIENT_ID, SPOTIFY_CLIENT_SECRET,MYMUSICURL); 
$api        =   new SpotifyWebAPI\SpotifyWebAPI();

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
<style>
.pointer {
    cursor: pointer;
}   
#example_filter {
    float : left;
}
.input-sm {
  background: #0c0b0b none repeat scroll 0 0 !important;
    border: 1px solid #545353 !important;
    border-radius: 0 !important;
    color: #fff !important;
    line-height: 32px !important;
    min-height: 35px !important;  
}
#artisttrackssearch-header{
  background: #0c0b0b none repeat scroll 0 0 !important;
    border: 1px solid #545353 !important;
    border-radius: 0 !important;
    color: #fff !important;
    line-height: 32px !important;
    min-height: 35px !important;
}    
#artisttrackssearch_track_chosen{
    background: #0c0b0b none repeat scroll 0 0 !important;
    border: 1px solid #545353 !important;
    border-radius: 0 !important;
    color: #fff !important;
    line-height: 32px !important;
    min-height: 35px !important;
}
.chosen-choices {
    background: #0c0b0b none repeat scroll 0 0 !important;
    border: 1px solid #545353 !important;
    border-radius: 0 !important;
    color: #fff !important;
    line-height: 32px !important;
    min-height: 35px !important;
}
</style>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/1.10.9/css/dataTables.bootstrap.min.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
<script src="https://cdn.datatables.net/1.10.9/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.9/js/dataTables.bootstrap.min.js"></script>
<div class="mymuisc-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php
        $form = ActiveForm::begin();
        echo $form->field($model,'Header')->textInput();
        echo Html::submitButton('Save Header', ['onclick'=>'return saveheader();','class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary','id' => 'savebtn']);
    ?>
    <br>
<?php
    $playlistID     =   '';
    $playlistname   =   array();
    $tracklist      =   array();
    $append         =   '~@@~';
    $sessionplaylistname    =   '';
    $form = ActiveForm::begin();
    
    if(isset($_GET['code'])) 
    {    
        /********************************* GET ACCESS TOKEN ***************************************/
        $accessToken = $session->requestAccessToken($_GET['code']);
        $data = $api->setAccessToken($session->getAccessToken());
        /******************************************************************************************/
        if(isset($_SESSION['playlistData'])) {
            $playlistname = $_SESSION['playlistData'];
        } else {
            $artistData = $api->me();
            $artistId = $artistData->id;
            $playlists = $api->getUserPlaylists($artistId, array());
            foreach($playlists->items as $playlist) {
                $playlistyid                    =   $playlist->id;
                $name                           =   $playlist->name;
                $playlistvalue                  =   $playlistyid.$append.$name;
                $playlistname[$playlistvalue]   =   $name;
            }    
            $_SESSION['playlistData'] = $playlistname;
        }
        if(isset($_SESSION['playlistID']) && $_SESSION['playlistID'] !='') 
        {
            $playlistID             =   $_SESSION['playlistID'];
            $sessionplaylistname    =   $_SESSION['playlistName'];
        }
        
        if(isset($playlistID) && $playlistID != "")
        {
            $playlistTracks =   $api->getUserPlaylistTracks('spiro-1',$playlistID); //5Eh1rySJ0gyYvpOKMFNpqC
            if(!empty($playlistTracks->items)) {
                foreach ($playlistTracks->items as $track) 
                {
                    $track                  =      $track->track;
                    $tracktitle             =      $track->name;
                    $duration               =      $track->duration_ms;
                    $trackid                =      $track->external_urls->spotify;
                    $id                     =      $track->id;
                    $thumbImage             =      $track->album->images[1]->url;
                    $trackuri               =      $track->uri;
                    $trackvalue             =      $id.$append.$track->name.$append.$duration.$append.$thumbImage.$append.$trackuri;
                    $tracklist[$trackvalue] =      $track->name;
                }
                $model->Playlist = $playlistID.$append.$sessionplaylistname; 
            }    
        }
        echo $form->field($model,'Playlist')->dropDownList($playlistname,['prompt'=>'--Select Playlist--','onchange'=>'getTracks();']);
        echo $form->field($model,'Track')->dropDownList($tracklist,['multiple'=>'multiple']);
        echo Html::hiddenInput('ArtistID', $artistiddb);
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
?>
    <br>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Add to list' : 'Save', ['onclick'=>'return validate();','class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary','id' => 'savebtn']) ?>
    </div>
<?php
    ActiveForm::end();
?>
<?php
$table =    backend\models\ArtisttracksSearch::getTrackListData($artistiddb,'','');
?>
<table id="example" class="table table-striped table-bordered" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Track Title</th>
                <th>Duration</th>
                <th>Playlist Name</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
        <?php   foreach($table as $track) {
                    $tracktitle  =      $track['TrackTitle'];
                    $duration    =      $track['Duration'];
                    $playlistnme =      $track['PlaylistName'];
                    $trackID    =       $track['ID'];
        ?>
                    <tr>
                        <td>
                            <?php echo $tracktitle; ?>
                        </td>
                        <td>
                            <?php echo floor($duration / 60000) . ":" . floor(($duration / 1000) % 60); ?>
                        </td>
                        <td>
                            <?php echo $playlistnme; ?>
                        </td>
                        <td><span class="glyphicon glyphicon-remove pointer" onclick="document.location.href='<?php echo Url::to(['artisttracks/delete?id='.$trackID]); ?>'"></span></td>
                    </tr>
        <?php } ?>                
        </tbody>
    </table>
</div>
<?php
$this->registerJs('$(document).ready(function() { $.noConflict(); $("#example").DataTable({"bPaginate": false,"bSortable": false, "aTargets": [1]}); }); ');
?>

<script>
function saveheader() {
    var headerString = $("#artisttrackssearch-header").val();
    var artistID = '<?php echo $artistiddb; ?>';
    $.ajax({
                type : "POST",
                url : "<?php echo Url::to(['artisttracks/updateheader']); ?>",
                data : "headerString="+headerString+'&artistID='+artistID,
                success : function(msg) {
                    return false;
                }
    });
    return false;
}
function getTracks() 
{
    var playlist    =   $("#artisttrackssearch-playlist").val();
    var playlistID  =   playlist.split('~@@~')[0];
    if(playlistID !='') 
    {
        document.location.href='<?php echo Url::to(['artisttracks/index?playlist=']); ?>'+playlist;
    }
}
function validate()
{
    if($("#artisttrackssearch-playlist").val() == "")
    {
        alert("Please select playlist");
        return false;
    }
    if($("#artisttrackssearch-track").val() == "")
    {
        alert("Please select a track");
        return false;
    }
}
</script>
