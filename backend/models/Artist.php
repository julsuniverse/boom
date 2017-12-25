<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "artist".
 *
 * @property integer $ArtistID
 * @property string $ArtistName
 * @property string $ProfileThumb
 * @property string $Email
 * @property string $DOB
 * @property string $Nationality
 * @property string $Address
 * @property string $Website
 * @property string $YoutubeChannelName
 * @property string $YearsActive
 * @property string $Genre
 * @property string $AboutMe
 * @property string $YouTubePageURL
 * @property string $LinkedInPageURL
 * @property string $TwitterPageURL
 * @property string $FacebookPageURL
 * @property string $DeviceType
 * @property string $DeviceToken
 * @property integer $UserID
 * @property integer $CreatedBy
 * @property integer $UpdatedBy
 * @property string $Created
 * @property string $Updated
 * @property string $OSArtistAppID
 * @property string $OSFanAppID
 */
class Artist extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public $UserName;
    public $ConfirmPassword;
    public $Password;
    public $ArtistImages;
    public $Status;
    public $ProductType;
    public $ProductPrice;
    public $ProductSKUID;
    public $AndroidPrice;
    public $AndroidSKUID;

    /*
     * for radio button (enable/disable no ads subs)
     * */
    public $NoAdsSubsEnabled=0;
    public $NoAdsProdID;
    
    public static function tableName()
    {
        return 'artist';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['Email','Website','AboutMe','Genre','YearsActive','TimeZone'],'required','on'=>'create','message' => 'Please enter {attribute}.'],
            [['Email','Website','AboutMe','Genre','YearsActive','TimeZone'],'required','on'=>'update','message' => 'Please enter {attribute}.'],
            ['Email','email','message' => 'Please enter your valid email id'],
            [['Password','ConfirmPassword'], 'required','on' => 'create','message' => 'Please enter {attribute}.'],
            [['Email'], 'checkProfileImage'],
            [['ArtistImages'], 'checkArtistImage'],
            [['ArtistName','UserName'], 'required','message' => 'Please enter {attribute}.'],
            //[['Address'], 'required','message' => 'Please enter residence address.'],
            //[['YouTubePageURL','TwitterPageURL','FacebookPageURL','InstagramPageURL'], 'required','message' => 'Please enter URL.'],
            [['YearsActive'], 'integer'],
[['Website','YouTubePageURL','InstagramPageURL','TwitterPageURL','FacebookPageURL', 'BlogFeedUrl', 'ShopifyLink'],'url', 'defaultScheme' => 'http'],
            [['Password'], 'string', 'min' => 8],
            ['ConfirmPassword', 'required','on' => 'create'],
            ['ConfirmPassword', 'compare', 'compareAttribute'=>'Password', 'message'=>"Passwords doesn't match"],
            [['IOSApp','AndroidApp', 'CompanyID', 'AppName', 'Scheme', 'AppID'],'safe'],
            [['ProductPrice'],'number'],
            [['NoAdsSubsEnabled','NoAdsProdID'],'safe']
        ];
    }

    public function checkProfileImage() {
        $id = '';
        if(isset($_GET['id']) && $_GET['id']!= '') {
            $id = $_GET['id'];
        }
        if(isset($_FILES['Artist']['name']['ProfileThumb']) && $_FILES['Artist']['name']['ProfileThumb']!='') {
            $fileName = $_FILES['Artist']['name']['ProfileThumb'];
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if($extension != 'jpg' && $extension != 'png' && $extension != 'jpeg') {
                return $this->addError('ProfileThumb', 'Please upload valid profile image');
            }
        } else if($id == '') {    
            return $this->addError('ProfileThumb', 'Please upload profile image');
        }    
    }
    
    public function checkArtistImage() {
        $id = '';
        if(isset($_GET['id']) && $_GET['id']!= '') {
            $id = $_GET['id'];
        }
        if(isset($_FILES['Artist']['name']['ArtistImages']) && $_FILES['Artist']['name']['ArtistImages'][0] !='') {
            for($n=0;$n<count(count($_FILES['Artist']['name']['ArtistImages']));$n++) {
                $fileName = $_FILES['Artist']['name']['ArtistImages'][$n];
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if($extension != 'jpg' && $extension != 'png' && $extension != 'jpeg') {
                    return $this->addError('ArtistImages', 'Please upload valid artist image');
                }
            }    
        } else if($id == '') {    
            return $this->addError('ArtistImages', 'Please upload artist image');
        }
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ArtistID' => 'Artist ID',
            'ArtistName' => 'Name',
            'UserName' => 'Username',
            'ProfileThumb' => 'Profile Picture(s)',
            'Email' => 'Email',
            'DOB' => 'Birthdate',
            'Nationality' => 'Country',
            'Address' => 'Address',
            'Website' => 'Website',
            'YoutubeChannelName' => 'Youtube Channel',
            'YearsActive' => 'Active Since',
            'Genre' => 'Genre',
            'AboutMe' => 'About Me',
            'YouTubePageURL' => 'You Tube Page Url',
            'LinkedInPageURL' => 'Linked In Page Url',
            'TwitterPageURL' => 'Twitter Page Url',
            'FacebookPageURL' => 'Facebook Page Url',
			'BlogFeedUrl' => 'Blogger Link',
			'ShopifyLink' => 'Shopify Link',
            'DeviceType' => 'Device Type',
            'DeviceToken' => 'Device Token',
            'UserID' => 'User ID',
            'CreatedBy' => 'Created By',
            'UpdatedBy' => 'Updated By',
            'Created' => 'Created',
            'Updated' => 'Updated',
            'TotalRegisteredUsers' => '# Registered Users',
			'Display24h' => 'Display 24h Posts',
        ];
    }
    
    public function getArtistData($id) {
        return Artist::findAll(array('UserID'=>$id));
    }
    public function getArtistName($artistID)
    {
        $data   = Artist::findOne(array("ArtistID"=>$artistID));
        return $data->ArtistName;
    }
    public function getArtistDeviceToken($artistID)
    {
        $data   = Artist::findOne(array("ArtistID"=>$artistID));
        return $data->DeviceToken;
    }
    public function getArtistDeviceType($artistID)
    {
        $data   = Artist::findOne(array("ArtistID"=>$artistID));
        return $data->DeviceType;
    }
    public function getAllDevicesForCommentPush($postID,$artistID,$commentactivityid)
    {
        $connection     =   \yii::$app->db;

        $query          =   "SELECT ma.ActivityID,ma.MemberID,ma.ArtistID,
                                m.DeviceType AS MemberDeviceType,
                                m.DeviceToken AS MemberDeviceToken,
                                a.DeviceType AS ArtistDeviceType,
                                a.DeviceToken AS ArtistDeviceToken FROM 
                                memberactivity ma 
                                LEFT JOIN member m 
                                ON(m.MemberID=ma.MemberID AND m.DeviceToken != '') 
                                LEFT JOIN artist a ON(a.ArtistID = ma.ArtistID AND a.DeviceToken != '')
                                WHERE 
                                ma.PostID=".$postID." AND ma.ArtistID=".$artistID."
                                AND ma.ActivityTypeID=2 
                                AND ma.RefTable=1
                                AND ma.MemberID != 0
                                AND ma.MemberID != (SELECT MemberID FROM memberactivity WHERE ActivityID=".$commentactivityid.")
                                AND ma.ActivityID < ".$commentactivityid."
                                GROUP BY ma.MemberID;";
        $data           =   $connection->createCommand($query);
        $tokens         =   $data->queryAll();
        return $tokens;
    }
    public function getNotificationMessage($userType,$userID,$artistID,$postID)
    {
        $connection     =   \yii::$app->db;
        $artistdetail   =   Artist::findOne(array("ArtistID"=>$artistID));
        $artistname     =   $artistdetail->ArtistName;
        $postdata       =   Post::findOne(array("PostID"=>$postID));
        if($postdata['PostType'] == 1)
        {
            $posttitle  =   "status : '".$postdata['Description']."'";
        }
        else
        {
            $posttitle  =   "post : '".$postdata['PostTitle']."'";
        }
        if($userType == "3")
        {
            
            $query          =   "SELECT MemberName As Name FROM member WHERE MemberID=".$userID;
            $data           =   $connection->createCommand($query);
            $detail         =   $data->queryAll();
            if(trim($detail[0]['Name']) != "")
            {
                $message        =   $detail[0]['Name']." commented on ".$artistname."'s ".$posttitle;
            }
            else
            {
                $message        =   "A fan commented on ".$artistname." 's ".$posttitle;
            }
            
        }
        else if($userType == "2")
        {
            $message        =   $artistname." commented on his ".$posttitle;
        }
        
        return $message;
    }
    public function getNotificationMessageForComment($memberID,$artistID,$postID)
    {
        $connection     =   \yii::$app->db;
        $query          =   "SELECT ArtistName As Name FROM artist WHERE ArtistID=".$artistID;
        $data           =   $connection->createCommand($query);
        $detail         =   $data->queryAll();
        $postdata       =   Post::findOne(array("PostID"=>$postID));
        $posttitle  =   "status : '".$postdata['Description']."'";
        if(trim($detail[0]['Name']) != "")
        {
            $message        =   $detail[0]['Name']." has commented on your question";
        }
        else
        {
            $message        =   "Artist has commented on your question";
        } 
        return $message;
    }
    public function getNotificationMessageForPost($memberID,$artistID,$postID,$ignore)
    {
        if($postID != "") {
            $connection     =   \yii::$app->db;
            $query          =   "SELECT ArtistName As Name FROM artist WHERE ArtistID=".$artistID;
            $data           =   $connection->createCommand($query);
            $detail         =   $data->queryAll();
            $postdata       =   Post::findOne(array("PostID"=>$postID));
            
            if(trim($detail[0]['Name']) != "")
            {
                if($ignore == "1") {    
                    $message        =   $detail[0]['Name']." is ignoring this question as it may be in breach of the Ask Me a Question policy";
                } else if($ignore == "2") {    
                    $message        =   $detail[0]['Name']." will reply to you";
                } else {
                    $message        =   $detail[0]['Name']." has answered your question";
                }    
            }
            else
            {
                if($ignore == "1") {    
                    $message        =   "Artist is ignoring this question as it may be in breach of the Ask Me a Question policy";
                } else if($ignore == "2") {    
                    $message        =   "Artist will reply to you";
                } else {
                    $message        =   "Artist has answered your question";
                }
            }
        } else {
            $connection     =   \yii::$app->db;
            $query          =   "SELECT MemberName As Name FROM member WHERE MemberID=".$memberID;
            $data           =   $connection->createCommand($query);
            $detail         =   $data->queryAll();
            $postdata       =   Post::findOne(array("PostID"=>$postID));
            $posttitle  =   "status : '".$postdata['Description']."'";
            if(trim($detail[0]['Name']) != "")
            {
                $message        =   $detail[0]['Name']." has asked a question";
            }
            else
            {
                $message        =   "A fan has added question";
            }
        }    
        return $message;
    }
    public function getBulkNotificationMessageForPost($memberID,$artistID,$postType,$postTitle,$description)
    {
        $connection     =   \yii::$app->db;
        $query          =   "SELECT ArtistName As Name FROM artist WHERE ArtistID=".$artistID;
        $data           =   $connection->createCommand($query);
        $detail         =   $data->queryAll();
        if($postType == "1") {
            $type = "status";
        } if($postType == "2") {
            $type = "image";
        } if($postType == "3") {
            $type = "video";
        }
        if($postTitle == "" && $description=="") {
            $message        =   $detail[0]['Name']." has added new ".$type." post";
        } else {
            if($postTitle != "") {
                if(strlen($postTitle) >60) {
                    //$postString = substr($postTitle,0,60).'...';
                    $postString = $postTitle;
                } else {
                    $postString = $postTitle;
                }
            } else if($description != "") {
                if(strlen($description) >60) {
                    //$postString = substr($description,0,60).'...';
                    $postString = $description;
                } else {
                    $postString = $description;
                }
            }
            $replaced = preg_replace("/\\\\u([0-9A-F]{1,4})/i", "&#x$1;",$postString);
            $result = mb_convert_encoding($replaced, "UTF-16", "HTML-ENTITIES");
            $result = mb_convert_encoding($result, 'utf-8', 'utf-16');

            $resultHex = unpack('H*', $result);
            $resultHex = $resultHex[1];
            $resultHexStr = '\x' . implode('\x', str_split($resultHex, 2));
            //$message        =   $detail[0]['Name'].' has added new post '.$resultHexStr.' ';
            $message        =   $detail[0]['Name'].' has added new post - '.$postString.' ';
        }
        return $message;
    }
    public function getNotificationMessageForBlockUser($memberID,$artistID,$postID,$status)
    {
        if($postID != "") {
            $connection     =   \yii::$app->db;
            $query          =   "SELECT ArtistName As Name FROM artist WHERE ArtistID=".$artistID;
            $data           =   $connection->createCommand($query);
            $detail         =   $data->queryAll();
            $postdata       =   Post::findOne(array("PostID"=>$postID));
            
            if(trim($detail[0]['Name']) != "")
            {
                if($status == '1') {
                    $message        =   $detail[0]['Name']." has decided to block you from asking questions";
                } if($status == '2') {
                    $message        =   $detail[0]['Name']." has unblocked you";
                }    
            }
            else
            {
                if($status == '1') {
                    $message        =   "Artist has decided to block you from asking questions";
                } if($status == '2') {
                    $message        =   "Artist has unblocked you";
                }
            }
        } else {
            $connection     =   \yii::$app->db;
            $query          =   "SELECT MemberName As Name FROM member WHERE MemberID=".$memberID;
            $data           =   $connection->createCommand($query);
            $detail         =   $data->queryAll();
            $postdata       =   Post::findOne(array("PostID"=>$postID));
            $posttitle  =   "status : '".$postdata['Description']."'";
            if(trim($detail[0]['Name']) != "")
            {
                $message        =   $detail[0]['Name']." added question";
            }
            else
            {
                $message        =   "A fan added question";
            }
        }    
        return $message;
    }
    public function getNotificationMessageForArtist($userType,$memberID,$artistID,$postID)
    {
        $connection     =   \yii::$app->db;
        $query          =   "SELECT MemberName As Name FROM member WHERE MemberID=".$memberID;
        $data           =   $connection->createCommand($query);
        $detail         =   $data->queryAll();
        $postdata       =   Post::findOne(array("PostID"=>$postID));
        if($postdata['PostType'] == 1)
        {
            $posttitle  =   "status : '".$postdata['Description']."'";
        }
        else
        {
            $posttitle  =   "post : '".$postdata['PostTitle']."'";
        }
        if(trim($detail[0]['Name']) != "")
        {
            $message        =   $detail[0]['Name']." commented on your ".$posttitle;
        }
        else
        {
            $message        =   "A fan commented on your ".$posttitle;
        }
        return $message;
    }
    public function getSignupemailtemplate($name,$artistname,$link,$language)
    {
        if($language == "english")
        {
            $content    =   "<p>Dear ".$name.",</p>
                            <p>Thanks for signing up. Please click <a href='".$link."'>HERE</a> to activate your account.</p>
                            <p>As a registered user, you now enjoy access to all the updates from ".$artistname.".</p>
                            <p>Should you ever forget your password and need it reset, simply click on the 'Forgot Your Password?' button at the sign-in page.</p>
                            <p>Warmest regards,</p>"
                            .$artistname;
        }
        else if($language == "chinese")
        {
           $content    =   "<p>??? ".$name.",</p>
                            <p>???????<a href='".$link."'>???</a> ?????????</p>
                            <p>????????????????? ".$artistname. "??????</p>
                            <p>???????????????????????�?????�?????</p>
                            <p>?????</p>"
                            .$artistname;
        }
        else if($language == "japanese")
        {
            $content    =   "<p>??????????????<a href='".$link."'>??</a> ??????????????????</p>
                            <p>?????</p>
                            <p>???????????????????????????????</p>
                            <p>??????????????????????????????????</p>
                            <p>????????? '?????????????' ????????????</p>
                            <p>??</p>"
                            .$artistname;
        }
        else if($language == "spanish")
        {
            $content    =   '<p>Estimado "'.$name.'",</p>
                            <p>Gracias por registrarse. Haga clic <a href="'.$link.'">AQU�</a> para activar su cuenta.</p>
                            <p>Como usuario registrado, desde ahora puede acceder a todas las</p>
                            <p>actualizaciones de</p>
                            <p>Si alguna vez olvida su contrase�a y necesita restablecerla, simplemente</p>
                            <p>haga clic en el bot�n "�Olvid� su contrase�a?" que se encuentra en la p�gina</p>
                            <p>de inicio de sesi�n.</p>
                            <p>Un cordial saludo,</p>'
                            .$artistname;
        }
        else if($language == "korean")
        {
            $content = "????? 
                        ?? ??? ???? ?????. ??? ???? ??? ???????.
                        ?? ??? ?? ????? ?? ????? ???? ? ????.
                        ????? ???? ?? ????? ?? ??, ??? ???? '????? ???????' ??? ??????.
                        ?????.";
            /*$content    =   "<p>????? ".$name.",</p>
                            <p>?? ??? ???? ?????. <a href='".$link."'> ??</a>? ???? ??? ???????</p>
                            <p>?? ??? ?? ????? ?? ????? ???? ? ????.</p>
                            <p>????? ???? ?? ????? ?? ??, ??? ???? '???</p>
                            <p>?? ???????' ??? ??????.</p>
                            <p>?????.</p>"
                            .$artistname;*/
        }
        return $content;
    }
    public function getForgotemailtemplate($name,$artistname,$link)
    {
         $content    =   "<p>Dear ".$name.",</p>
                        <p>We have received a request for your account password to be reset.</p>
                        <p>Please click <a href='".$link."'>HERE</a> to choose a new password.</p>
                        <p>If you did not make this request, please contact <contact email> for help.</p>
                        <p>Thank you.</p>
                        <p>Warmest regards,</p>"
                        .$artistname;
        return $content;
    }
}
