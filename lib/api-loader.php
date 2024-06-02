<?php
$premise_api_base = untrailingslashit(dirname(__FILE__)).'/api';

require_once("{$premise_api_base}/premise-api.php");
require_once("{$premise_api_base}/premise-api-provider.php");

/** Education Provider **/
require_once("{$premise_api_base}/premise-api-education-provider.php");

/** Graphics Provider **/
require_once("{$premise_api_base}/premise-api-graphics-provider.php");