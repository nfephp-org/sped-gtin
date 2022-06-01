<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
require_once '../bootstrap.php';

use NFePHP\Gtin\Gtin;
use NFePHP\Common\Certificate;

try {
    $cert = Certificate::readPfx(file_get_contents(__DIR__.'/../local/cert_2022.pfx'), 'hsksjlk8hssh776876siuytsuuyuiysu');
    $resp = Gtin::check('7898562932269', $cert)->consulta();

    echo "<pre>";
    print_r($resp);
    echo "<pre>";

} catch (\Exception $e) {
    echo $e->getMessage();
}

