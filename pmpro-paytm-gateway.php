<?php
/*
Plugin Name: Pmpro Paytm Gateway
Description: Pmpro Paytm Gateway
Version: 1.2.3
Author: FTI Technologies
Author URI: https://www.freelancetoindia.com/
*/

define("PMPRO_PAYTM_DIR", dirname(__FILE__));

//load payment gateway class

require_once(PMPRO_PAYTM_DIR . "/classes/class.pmprogateway_paytm.php");