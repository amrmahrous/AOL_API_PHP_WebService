AOL_API_PHP_WebService
======================
use this library to get your information , get your rss list , add rss using aol api


USAGE EXAMPLE , see index.php
======================
session_start();
include_once 'aol_api_class.php';

$data = array();

$data['client_id'] = '';

$data['client_secret'] = '';

$data['callback_url'] = '';

new aol_api_class($data);


AUTHOR
======================
if you need any help , feel free to contact me
amr@amrmahrous.com

Amr Mahrous
