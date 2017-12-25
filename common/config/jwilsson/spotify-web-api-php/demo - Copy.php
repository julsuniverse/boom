<?php
require '../../../vendor/autoload.php';


$session = new SpotifyWebAPI\Session('0decc6d817cc4ea48e4a48066a4f507d', '8a003635a94e43e0ab8e8420db9e8bbd', 'http://localhost:8080/spotify/vendor/jwilsson/spotify-web-api-php/demo.php'); 
$api = new SpotifyWebAPI\SpotifyWebAPI();



if (isset($_GET['code'])) {    
    /*$session->requestAccessToken($_GET['code']);
    $data = $api->setAccessToken($session->getAccessToken());
    
    $artistData = $api->me();
    $artistId = $artistData->id;
    $playlists = $api->getUserPlaylists($artistId, array(
        'limit' => 5
    ));

    foreach ($playlists->items as $playlist) {
        echo '<a href="' . $playlist->external_urls->spotify . '">' . $playlist->name . '</a> <br>';
    }*/
    
    /********************************* GET ACCESS TOKEN ***************************************/
    
    $accessToken = $session->requestAccessToken($_GET['code']);
    $data = $api->setAccessToken($session->getAccessToken());

    /******************************************************************************************/
    
    $artistData = $api->me();
    $artistId = $artistData->id;
    $playlists = $api->getUserPlaylists($artistId, array(
        //'limit' => 5
    ));

    foreach ($playlists->items as $playlist) {
        echo '<a href="' . $playlist->external_urls->spotify . '">' . $playlist->name . '</a> <br>';
        //$playlistTracks = $api->getUserPlaylistTracks('spironas','3d6SYQPlxSdDnIAznawPYC');
        echo '<pre>';
        print_r($playlist);
        
    }
    $playlistTracks = $api->getUserPlaylistTracks('spironas','5Eh1rySJ0gyYvpOKMFNpqC');
    echo '<pre>';
    print_r($playlistTracks);
    die;
    foreach ($playlistTracks->items as $track) {
        $track = $track->track;
        echo '<a href="' . $track->external_urls->spotify . '">' . $track->name . '</a> <br>';
    }
    die;
} else {
    $scopes = array(
        'scope' => array(
            'user-read-email',
            'user-library-modify',
            'playlist-modify-private',
            'playlist-modify-public',
            'playlist-read-private',
        ),
    );

    header('Location: ' . $session->getAuthorizeUrl($scopes));
}

?>
