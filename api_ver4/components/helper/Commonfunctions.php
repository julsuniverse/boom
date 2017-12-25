<?php
function _getStatusCodeMessageForUser($status) 
{
    $codes = array(
            200 => 'Success',
            201 => 'Username is already in use. Please enter a different username',
            202 => 'Account is blocked',
            303 => 'Please check your email to activate your account',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Either username or password is invalid. Please enter valid username and password.',
            405 => 'Username Incorrect',
            406 => 'Password Incorrect',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => "Parameter missing",
            504 => "Sorry, you are blocked by artist.",
            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}

function _getStatusCodeMessageForPurchase($status) 
{
            $codes = array(
                    200 => 'Success',
                    201 => 'Username is already in use. Please enter a different username',
                    202 => 'Please check your email to activate your account',
                    303 => 'Inactive',
                    401 => 'Unauthorized',
                    402 => 'Payment Required',
                    403 => 'Forbidden',
                    404 => 'Not Found',
                    405 => 'Username Incorrect',
                    406 => 'Password Incorrect',
                    500 => 'Internal Server Error',
                    501 => 'Not Implemented',
                    502 => "Invalid parameter",
            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageForForgotPassword($status) 
{
            $codes = array(
                    200 => 'We have sent a link to reset your password in your registered email. Please check your email.',
                    404 => 'Username is not found in our records. Please enter a valid username.',

            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}

function _getStatusCodeMessageForUsername($status) 
{
            $codes = array(
                    200 => 'User is available',
                    201 => 'Username is already in use. Please enter a different username',
                    202 => 'Pending',
                    303 => 'Inactive',
                    401 => 'Unauthorized',
                    402 => 'Payment Required',
                    403 => 'Forbidden',
                    404 => 'User already exists',
                    405 => 'Username Incorrect',
                    406 => 'Password Incorrect',
                    500 => 'Internal Server Error',
                    501 => 'Not Implemented',
                    502 => "Invalid parameter",
            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}

function _getStatusCodeMessageForUserRegistration($status) 
{
            $codes = array(
                    200 => 'Success',
                    201 => 'Username is already in use. Please enter a different username',
                    202 => 'Pending',
                    303 => 'Inactive',
                    401 => 'Unauthorized',
                    402 => 'Payment Required',
                    403 => 'Forbidden',
                    404 => 'Not Found',
                    405 => 'Username Incorrect',
                    406 => 'Password Incorrect',
                    500 => 'Internal Server Error',
                    501 => 'Not Implemented',
                    502 => "Invalid parameter",
            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageForEditprofile($status) 
{
            $codes = array(
                    200 => 'Your profile has been updated successfully.',
                    201 => 'Username is already in use. Please enter a different username',
                    202 => 'Pending',
                    303 => 'Inactive',
                    401 => 'Unauthorized',
                    402 => 'Payment Required',
                    403 => 'Forbidden',
                    404 => 'Not Found',
                    405 => 'Username Incorrect',
                    406 => 'Password Incorrect',
                    500 => 'Internal Server Error',
                    501 => 'Not Implemented',
                    502 => "Invalid parameter",
            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageForEditpost($status) 
{
            $codes = array(
                    200 => 'Your post has been updated successfully.',
                    404 => 'We seem to be experiencing heavy traffic on our server. Please try again later.',
                    500 => 'Internal Server Error',
                    502 => "Invalid parameter",
            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageForStickers($status)
{
     $codes = array(
                    200 => 'Success',
                    404 => 'No stickers available',
                    502 => 'Parameter missing',

            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageGetProfile($status)
{
     $codes = array(
                    200 => 'Success',
                    404 => 'We seem to be experiencing heavy traffic on our server. Please try again later.',
                    502 => 'Parameter missing',

            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageCommon($status)
{
     $codes = array(
                    200 => 'Success',
                    404 => 'We seem to be experiencing heavy traffic on our server. Please try again later.',
                    502 => 'Parameter missing',

            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageCommentList($status)
{
     $codes = array(
                    200 => 'Success',
                    404 => 'No comments.',
                    502 => 'Parameter missing',

            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessagePostList($status)
{
     $codes = array(
                    200 => 'Success',
                    404 => 'No posts.',
                    502 => 'Parameter missing',

            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageApplist($status)
{
     $codes = array(
                    200 => 'Success',
                    404 => 'No apps.',
                    502 => 'Parameter missing',

            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageLikelist($status)
{
     $codes = array(
                    200 => 'Success',
                    404 => 'No likes.',
                    502 => 'Parameter missing',

            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageSharelist($status)
{
     $codes = array(
                    200 => 'Success',
                    404 => 'No shares.',
                    502 => 'Parameter missing',

            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageFlaglist($status)
{
     $codes = array(
                    200 => 'Success',
                    404 => 'No flags.',
                    502 => 'Parameter missing',

            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageArtistHomescreen($status)
{
     $codes = array(
                    200 => 'Success',
                    404 => 'No details.',
                    502 => 'Parameter missing',

            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function _getStatusCodeMessageMymusic($status)
{
     $codes = array(
                    200 => 'Success',
                    404 => 'No tracks to display',
                    502 => 'Parameter missing',

            );
            return (isset($codes[$status])) ? $codes[$status] : '';
}
function getAccessToken() 
{
            $token = Yii::$app->security->generateRandomString();
            $model = User::findOne(['access_token'=>$token]);
            if($model) {
                    $token = $this->getAccessToken();
            }
            return $token; 
}

?>

