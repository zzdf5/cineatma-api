<?php
require './../vendor/autoload.php';

use Brevo\Client\Configuration;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;

class SendMail
{
    private $apiInstance;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', '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');
        $this->apiInstance = new TransactionalEmailsApi(new Client(), $config);
    }

    public function send($toEmail, $toName, $subject, $htmlContent, $textContent = '')
    {
        $email = new SendSmtpEmail([
            'subject' => $subject,
            'sender' => ['name' => 'Cineatma', 'email' => 'joanthio87@gmail.com'],
            'to' => [['email' => $toEmail, 'name' => $toName]],
            'htmlContent' => $htmlContent,
            'textContent' => $textContent ?: strip_tags($htmlContent)
        ]);

        try {
            $result = $this->apiInstance->sendTransacEmail($email);
            return ['success' => true, 'result' => $result];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
