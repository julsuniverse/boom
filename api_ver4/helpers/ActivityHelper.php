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

    public static function getCommentList(array $commentData)
    {
        $refs = array();
        $list = array();

        foreach ($commentData as $row)
        {
            $ref = & $refs[$row['PostCommentActivityID']];

            $ref['PostCommentActivityID'] = $row['PostCommentActivityID'];
            $ref['ParentID'] = $row['ParentID'];
            $ref['Comment']      = $row['Comment'];
            $ref['TotalLikes'] = $row['TotalLikes'];
            $ref['TotalFlags'] = $row['TotalFlags'];
            $ref['CommentedDate'] = $row['CommentedDate'];
            $ref['StickerID'] = $row['StickerID'];
            $ref['StickerImage'] = $row['StickerImage'];
            $ref['StickerThumbImage'] = $row['StickerThumbImage'];
            $ref['StickerMediumImage'] = $row['StickerMediumImage'];
            $ref['ProfileID'] = $row['ProfileID'];
            $ref['IsArtistComment'] = $row['IsArtistComment'];
            $ref['MemberName'] = $row['MemberName'];
            $ref['UserID'] = $row['UserID'];
            $ref['ProfileThumb'] = $row['ProfileThumb'];
            $ref['PostFlagActivityID'] = $row['PostFlagActivityID'];
            $ref['PostLikeActivityID'] = $row['PostLikeActivityID'];
            $ref['CustomStickerUrl'] = $row['CustomStickerUrl'];

            if ($row['ParentID'] == null)
            {
                $list[$row['PostCommentActivityID']] = & $ref;
            }
            else
            {
                $refs[$row['ParentID']] ['children'] [$row['PostCommentActivityID']] = & $ref;
            }
        }

        $list = array_values($list);

        for($i = 0; $i < count($list); $i++) {

            if(!empty($list[$i]['children'])) {
                $list[$i]['children'] = array_values($list[$i]['children']);
                $children = array_shift($list[$i]);
                $list[$i] += ['children' => $children];
            }

        }
        return $list;
    }

}