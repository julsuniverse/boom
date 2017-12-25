<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $UserID
 * @property string $UserName
 * @property string $Password
 * @property string $Status
 * @property string $LastLoginDateTime
 * @property string $UserType
 * @property string $ActivationCode
 * @property string $ActivatedOn
 * @property integer $CreatedBy
 * @property integer $UpdatedBy
 * @property string $Created
 * @property string $Updated
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['UserName'], 'required'],
            [['Status', 'UserType'], 'string'],
            [['LastLoginDateTime', 'ActivatedOn', 'Created', 'Updated'], 'safe'],
            [['CreatedBy', 'UpdatedBy'], 'integer'],
            [['UserName', 'Password'], 'string', 'max' => 255],
            [['ActivationCode'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'UserID' => 'User ID',
            'UserName' => 'User Name',
            'Password' => 'Password',
            'Status' => 'Status',
            'LastLoginDateTime' => 'Last Login Date Time',
            'UserType' => 'User Type',
            'ActivationCode' => 'Activation Code',
            'ActivatedOn' => 'Activated On',
            'CreatedBy' => 'Created By',
            'UpdatedBy' => 'Updated By',
            'Created' => 'Created',
            'Updated' => 'Updated',
        ];
    }
    public function xmlparsing($url)
    {
        //define ('XMLFILE', $url);
        $items = array ();
        $i = 0;

        $xmlReader = new \XMLReader();
        $xmlReader->open($url, null, LIBXML_NOBLANKS);
        $isParserActive = false;
        $simpleNodeTypes = array ("title", "description", "media:title", "link", "author", "pubDate", "guid");

        while ($xmlReader->read ())
        {
            $nodeType = $xmlReader->nodeType;
            if ($nodeType != \XMLReader::ELEMENT && $nodeType != \XMLReader::END_ELEMENT) 
            { 
                continue; 
            }
            else if ($xmlReader->name == "item") 
            {
                if (($nodeType == \XMLReader::END_ELEMENT) && $isParserActive)
                { 
                    $i++; 
                }
                $isParserActive = ($nodeType != \XMLReader::END_ELEMENT);
            }

            if (!$isParserActive || $nodeType == \XMLReader::END_ELEMENT) { continue; }

            $name = $xmlReader->name;

            if (in_array ($name, $simpleNodeTypes)) 
            {
                $xmlReader->read ();
                $items[$i][$name] = $xmlReader->value;
            } 
            else if ($name == "media:thumbnail") 
            {
                $items[$i]['media:thumbnail'] = array (
                        "url" => $xmlReader->getAttribute("url"),
                        "width" => $xmlReader->getAttribute("width"),
                        "height" => $xmlReader->getAttribute("height"),
                        "type" => $xmlReader->getAttribute("type")
                );
            } 
            else if ($name == "media:content") 
            {
                $items[$i]['media:content'] = array (
                        "url" => $xmlReader->getAttribute("url"),
                        "width" => $xmlReader->getAttribute("width"),
                        "height" => $xmlReader->getAttribute("height"),
                        "filesize" => $xmlReader->getAttribute("fileSize"),
                        "expression" => $xmlReader->getAttribute("expression")
                );
            }
        }
        return $items;exit;
    }
}
