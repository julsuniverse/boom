<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "artisttracks".
 *
 * @property integer $ID
 * @property integer $ArtistID
 * @property string $PlaylistID
 * @property string $PlaylistName
 * @property string $TrackID
 * @property string $TrackTitle
 * @property string $Duration
 * @property string $URI
 * @property string $Created
 */
class Artisttracks extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $Header;
    public $Playlist;
    public $Track;
    public static function tableName()
    {
        return 'artisttracks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ArtistID'], 'integer'],
            [['URI'], 'string'],
            [['Created'], 'safe'],
            [['PlaylistID', 'PlaylistName', 'TrackID', 'TrackTitle'], 'string', 'max' => 1000],
            [['Duration'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ID' => 'ID',
            'ArtistID' => 'Artist ID',
            'PlaylistID' => 'Playlist ID',
            'PlaylistName' => 'Playlist Name',
            'TrackID' => 'Track ID',
            'TrackTitle' => 'Track Title',
            'Duration' => 'Duration',
            'URI' => 'Uri',
            'Created' => 'Created',
        ];
    }
}
