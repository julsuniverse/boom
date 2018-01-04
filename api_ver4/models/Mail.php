<?php
/*namespace api\models;
/*require 'vendor/autoload.php';

use Yii;
use Aws\Ses\SesClient;*/
require '../../aws/aws-autoloader.php';
use Aws\Common\Enum\Region;
use Aws\Ses\SesClient;

class Mail
{
    /*public function sendSesEmail($to, $subject, $body="", $bodyHtml="")
    {
        try {   
            $client = SesClient::factory(array(
                'version'     => 'latest',
                'credentials' => array(
                    'key'       => 'AKIAJBFOB42S5BTVJWBQ',
                    'secret'    => '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',
                  ),
                'region' => 'us-east-1'
            ));

            $send = $client->sendEmail(array(
                'Source' => 'Suntec Rewards <rewards@sunteccity.com.sg>',
                'Destination' => array('ToAddresses' => array($to)),
                'Message' => array('Subject' => array('Data' => $subject), 'Body' => array('Html' => array('Data' => $bodyHtml)))));

            return 1;
        }
        catch(Exception $e){
            echo $e->getMessage();
            return 0;
        }
    }*/
    /*public function sendSesEmail($to, $subject, $body="", $bodyHtml="")
    {
        try {   
            $client = SesClient::factory(array(
                'version'     => 'latest',
                'credentials' => array(
                    'key'       => 'AKIAJBFOB42S5BTVJWBQ',
                    'secret'    => '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',
                  ),
                'region' => 'us-east-1'
            ));

            $send = $client->sendEmail(array(
                'Source' => 'Boom Video<rosemary@e2logy.com>',
                'Destination' => array('ToAddresses' => array($to)),
                'Message' => array('Subject' => array('Data' => $subject), 'Body' => array('Html' => array('Data' => $bodyHtml)))));

            return 1;
        }
        catch(Exception $e){
            echo $e->getMessage();
            return 0;
        }
    }*/
    public function sendSesEmail($to, $subject, $body="", $bodyHtml="")
    {
        
        try
        {
            $client = SesClient::factory(array(
                'version'     => 'latest',
                'region' => 'us-east-1',
                'credentials' => array(
                    'key'       => 'AKIAJBFOB42S5BTVJWBQ',
                    'secret'    => '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',
                  ),
            ));
            $emailSentId = $client->sendEmail(array(
                // Source is required
                'Source' => 'rosemary@e2logy.com',
                // Destination is required
                'Destination' => array(
                    'ToAddresses' => array($to)
                ),
                // Message is required
                'Message' => array(
                    // Subject is required
                    'Subject' => array(
                        // Data is required
                        'Data' => 'SES Testing',
                        'Charset' => 'UTF-8',
                    ),
                    // Body is required
                    'Body' => array(
                        'Text' => array(
                            // Data is required
                            'Data' => 'My plain text email',
                            'Charset' => 'UTF-8',
                        ),
                        'Html' => array(
                            // Data is required
                            'Data' => '<b>My HTML Email</b>',
                            'Charset' => 'UTF-8',
                        ),
                    ),
                ),
                'ReplyToAddresses' => array( 'replyTo@email.com' ),
                'ReturnPath' => 'bounce@email.com'
            ));
            return $emailSentId;
        }
        catch (SesException $exec) 
        {
            echo $exec->getmessage();
        }
        
    }
}*/

Yii::$app->mail->compose('contact/html') //'contact/html', ['contactForm' => $form]
    ->setFrom('rosemary@e2logy.com')
    ->setTo('rosemary@e2logy.com')
    ->setSubject('Testing')
    ->send();

echo 'sdsdsd';
die;
?>