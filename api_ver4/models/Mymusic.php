<?php
namespace api\models;

use Yii;
require '../../spotify/vendor/autoload.php';
include_once('../../spotify/vendor/jwilsson/spotify-web-api-php/demo.php');

//
//class Mymusic
//{
//    public function test()
//    {
//        $session    =   new \SpotifyWebAPI\Session('0decc6d817cc4ea48e4a48066a4f507d', '8a003635a94e43e0ab8e8420db9e8bbd', 'http://localhost:8080/boom/spotify/vendor/jwilsson/spotify-web-api-php/demo.php'); 
//        $api        =   new \SpotifyWebAPI\SpotifyWebAPI();
//        
//        if(isset($_GET['code']))
//        {
//    
//            /********************************* GET ACCESS TOKEN ***************************************/
//            
//            $accessToken = $session->requestAccessToken($_GET['code']);
//            $data = $api->setAccessToken($session->getAccessToken());
//        
//            /******************************************************************************************/
//            
//            $artistData = $api->me();
//            $artistId = $artistData->id;
//            $playlists = $api->getUserPlaylists($artistId, array(
//                //'limit' => 5
//            ));
//        
//            /************************** Get All Playlist *********************************/
//            foreach ($playlists->items as $playlist)
//            {
//                echo '<a href="' . $playlist->external_urls->spotify . '">' . $playlist->name . '</a> <br>';
//            }
//            /*************************** Tracks For Test Playlist ************************/
//            echo '<p>Tracks For Test Playlist</p>';
//            $playlistTracks = $api->getUserPlaylistTracks('spironas','5Eh1rySJ0gyYvpOKMFNpqC');
//            
//            foreach ($playlistTracks->items as $track)
//            {
//                $track = $track->track;
//                echo '<a href="' . $track->external_urls->spotify . '">' . $track->name . '</a> <br>';
//            }
//        }
//        else
//        {
//            $scopes = array(
//                'scope' => array(
//                    'user-read-email',
//                    'user-library-modify',
//                    'playlist-modify-private',
//                    'playlist-modify-public',
//                    'playlist-read-private',
//                ),
//            );
//
//            header('Location: ' . $session->getAuthorizeUrl($scopes));
//        }
//    }
//}


?>
