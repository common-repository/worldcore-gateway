<?php

require_once('gateway-worldcore.php');

$wc=new WC_Gateway_Worldcore();
$wc->confirmation_handler->check_response();

?>