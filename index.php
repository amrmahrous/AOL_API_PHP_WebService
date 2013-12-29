<?php

session_start();
include_once 'aol_api_class.php';

$data = array();
$data['client_id'] = '';
$data['client_secret'] = '';
$data['callback_url'] = '';

new aol_api_class($data);


?>
