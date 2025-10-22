<?php

namespace App\Service;

class Mailer
{
    private $token;
    private $from;

    public function __construct(String $token, String $from)
    {
        $this->token = $token;
        $this->from = $from;
    }

    public function send(String $to, String $subject, String $html)
    {
        $mailtrap_api = 'https://send.api.mailtrap.io/api/send';

        $body = [
            'from' => ['email' => $this->from, 'name' => 'Calice Cup'],
            'to' => [['email' => $to]],
            'subject' => $subject,
            'html' => $html
        ];

        $json = json_encode($body);

        $header = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json),
            'Authorization: Bearer ' . $this->token
        ];

        $ch = curl_init($mailtrap_api);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        curl_exec($ch);
        curl_close($ch);
    }
}
