<?php

require_once '../vendor/autoload.php';

$email = new App\Models\Email;

$email->send('custom.cms.dummy@gmail.com', 'Test Email', 'Test body.');

//var_dump($email);
