<?php

namespace App\Models;

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
//Alias the League Google OAuth2 provider class
use League\OAuth2\Client\Provider\Google;

class Email {
    private PHPMailer $mailer;

    public function __construct()
    {
        //SMTP needs accurate times, and the PHP time zone MUST be set
        //This should be done in your php.ini, but this is how to do it if you don't have access to that
        date_default_timezone_set('Etc/UTC');

        $this->mailer = new PHPMailer();

        //Tell PHPMailer to use SMTP
        $this->mailer->isSMTP();

        //Enable SMTP debugging
        //SMTP::DEBUG_OFF = off (for production use)
        //SMTP::DEBUG_CLIENT = client messages
        //SMTP::DEBUG_SERVER = client and server messages
        $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;

        //Set the hostname of the mail server
        $this->mailer->Host = gethostbyname('smtp.gmail.com');

        // Set the SMTP port number:
        // - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
        // - 587 for SMTP+STARTTLS
        $this->mailer->Port = 465;

        $this->mailer->Username = 'custom.cms.dummy@gmail.com';
        $this->mailer->Password = 'password1234_';

        //Set the encryption mechanism to use:
        // - SMTPS (implicit TLS on port 465) or
        // - STARTTLS (explicit TLS on port 587)
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        $this->mailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        //Whether to use SMTP authentication
        $this->mailer->SMTPAuth = true;

        //Set AuthType to use XOAUTH2
        $this->mailer->AuthType = 'XOAUTH2';

        //Start Option 1: Use league/oauth2-client as OAuth2 token provider
        //Fill in authentication details here
        //Either the gmail account owner, or the user that gave consent
        $email = 'custom.cms.dummy@gmail.com';
        $clientId = '549973375635-buihbffb6e6qnkpituc9ot540p1bpkdh.apps.googleusercontent.com';
        $clientSecret = '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';

        //Obtained by configuring and running get_oauth_token.php
        //after setting up an app in Google Developer Console.
        $refreshToken = '1//098GdJPijzST4CgYIARAAGAkSNwF-L9Iro0pt_GIg2KCTXLmFTnAy2BVbyHDTajDNLiYWBcr5fXg-eHj1-D0nDvIS_WzLK80MEHU';

        $this->mailer->oauthUserEmail = $email;
        $this->mailer->oauthClientId = $clientId;
        $this->mailer->oauthClientSecret = $clientSecret;
        $this->mailer->oauthRefreshToken = $refreshToken;

        //Create a new OAuth2 provider instance
        $provider = new Google(
            [
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
            ]
        );

        //Pass the OAuth provider instance to PHPMailer
        $this->mailer->setOAuth(
            new OAuth(
                [
                    'provider' => $provider,
                    'clientId' => $clientId,
                    'clientSecret' => $clientSecret,
                    'refreshToken' => $refreshToken,
                    'userName' => $email,
                ]
            )
        );
    }

    public function send(string $recipient, string $subject, string $body): void
    {
        //Set who the message is to be sent from
        //For gmail, this generally needs to be the same as the user you logged in as
        $this->mailer->setFrom('custom.cms.dummy@gmail.com', 'Custom CMS');

        //Set who the message is to be sent to
        $this->mailer->addAddress($recipient, 'John Doe');

        //Set the subject line
        $this->mailer->Subject = 'PHPMailer GMail XOAUTH2 SMTP test';

        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;
        $this->mailer->msgHTML("<h1>{$subject}</h1><p>{$body}</p>");

        //Replace the plain text body with one created manually
        $this->mailer->AltBody = $subject . ' ' . $body;

        //Attach an image file
        //$this->mailer->addAttachment('images/phpmailer_mini.png');

        //send the message, check for errors
        if (!$this->mailer->send()) {
            echo 'Mailer Error: ' . $this->mailer->ErrorInfo;
        } else {
            echo 'Message sent!';
        }
    }
}
