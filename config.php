<?php
DEFINE('IS_PROD', false);

DEFINE('VERSION_API_PAYPAL', 124);
DEFINE('SITE_PATH', realpath(dirname(__FILE__)));

//DON'T TOUCH
if(IS_PROD){
    DEFINE('BASE_URL_API_PAYPAL', 'https://api-3t.paypal.com/nvp?');
    DEFINE('PAYPAL_SERVER', 'https://www.paypal.com/');
}else{
    DEFINE('BASE_URL_API_PAYPAL', 'https://api-3t.sandbox.paypal.com/nvp?');
    DEFINE('PAYPAL_SERVER', 'https://www.sandbox.paypal.com/');
}


// UPDATE THE FOLLOWING LINES
DEFINE('USERNAME_FACILITATOR', 'younes.sadmi-facilitator_api1.gmail.com');
DEFINE('PASSWORD_FACILITATOR', 'Y8L9MQSJL7MEPJSJ');
DEFINE('SIGNATURE_FACILITATOR', 'AFcWxV21C7fd0v3bYYYRCpSSRl31Aqkx9PCclDaHbdmVkgfpneMdajEk');