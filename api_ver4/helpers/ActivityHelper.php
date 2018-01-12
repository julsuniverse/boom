<?php

namespace api_ver4\helpers;

use Yii;
use yii\base\Object;

class ActivityHelper extends Object
{
    public static function getLikes($artistID, $postID, $profileID, $activityTypeID)
    {
        $query = self::setQuery($artistID, $postID, $profileID, $activityTypeID);
        $result = [
            'PostLikeActivityID' => $query['id'],
            'TotalLikes' => $query['total'],
        ];
        return $result;
    }

    public static function getComments($artistID, $postID, $profileID, $activityTypeID)
    {
        $query = self::setQuery($artistID, $postID, $profileID, $activityTypeID);
        $result = [
            'PostCommentActivityID' => $query['id'],
            'TotalComments' => $query['total'],
        ];
        return $result;
    }

    private static function setQuery($artistID, $postID, $profileID, $activityTypeID)
    {
        $sql = 'SELECT COUNT(*) as count, (SELECT MAX(ActivityID) FROM memberactivity WHERE ArtistID='.$artistID.' AND PostID='.$postID.' AND MemberID='.$profileID.' AND ActivityTypeID='.$activityTypeID.') as id FROM memberactivity WHERE ArtistID='.$artistID.' AND PostID='.$postID.' AND ActivityTypeID='.$activityTypeID;
        $result = Yii::$app->db->createCommand($sql)->queryAll();

        return [
            'total' => $result[0]['count'],
            'id' => $result[0]['id'],
        ];
    }
}