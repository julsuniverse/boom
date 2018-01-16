<?php

namespace api_ver4\helpers;

use Yii;
use yii\base\Object;

class ActivityHelper extends Object
{
    /* ActivityTypeID:
        1 - like
        2 - Add Comment
        3 - FB Share
        4 - Twitter Share
        5 - Flag
        6 - Email Share
        7 - Whatsapp Share
        8 - Message Share
        9 - FB Messenger Share
    */
    public static function getData($activityTypeID = false)
    {
        $result = array();
        if($activityTypeID == 1)
        {
            $id = self::setQuery();
            $result += ['PostLikeActivityID' => $id+1];
            $result += ['TotalLikes' => 0];
        }
        else if($activityTypeID == 2)
        {
            $id = self::setQuery();
            $result += ['PostCommentActivityID' => $id];
            $result += ['TotalComments' => 0];
        }
        else if($activityTypeID == 3 || $activityTypeID == 4 || $activityTypeID == 6 || $activityTypeID == 7 || $activityTypeID == 8 || $activityTypeID == 9)
        {
            $result += ['TotalShares' => 0];
        }
        else if($activityTypeID == 5)
        {
            $id = self::setQuery();
            $result += ['PostFlagActivityID' => $id];
            $result += ['TotalFlags' => 0];
        }

        else
            return self::getError();

        return $result;
    }

    private static function setQuery()
    {
        $sql = 'SELECT ActivityID as id FROM memberactivity ORDER BY id DESC LIMIT 1';
        $result = Yii::$app->db->createCommand($sql)->queryOne();
        return $result['id'];
    }

    public static function getError()
    {
        return [
            'PostLikeActivityID' => 0,
            'TotalLikes' => 0,
        ];
    }
}